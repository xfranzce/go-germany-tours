<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\EnquiryService;
use App\Services\RecaptchaService;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/** Single endpoint for EVERY enquiry form (hero quiz, modal, package, contact). */
class EnquiryController extends Controller
{
    public function store(Request $request, EnquiryService $service, RecaptchaService $captcha)
    {
        $bag = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $request->input('form_id', 'enquiry')) ?: 'enquiry';

        // Honeypot: bots fill the hidden field. Pretend success, save nothing.
        if (filled($request->input('contact_url'))) {
            return $this->done($request, null);
        }

        $validator = Validator::make($request->all(), EnquiryService::rules(), EnquiryService::messages());

        // Verify reCAPTCHA only once everything else is valid (tokens are single-use). Fails closed.
        $validator->after(function ($v) use ($request, $captcha) {
            if ($v->errors()->isEmpty() && ! $captcha->verify($request->input('g-recaptcha-response'), $request->ip())) {
                $v->errors()->add('g-recaptcha-response', 'We could not verify you are human. Please tick the box again.');
            }
        });

        if ($validator->fails()) {
            return $this->fail($request, $validator->errors()->toArray(), $validator, $bag);
        }

        try {
            $enquiry = $service->create($validator->validated(), $request);
        } catch (ValidationException $e) {
            return $this->fail($request, $e->errors(), $e->validator, $bag);
        }

        return $this->done($request, $enquiry);
    }

    private function fail(Request $request, array $errors, $validator, string $bag)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $errors], 422);
        }

        return redirect(url()->previous().'#'.$bag)->withErrors($validator, $bag)->withInput($request->except('g-recaptcha-response', 'consent'));
    }

    private function done(Request $request, $enquiry)
    {
        $request->session()->flash('enquiry_done', [
            'id' => $enquiry?->id, 'form' => $enquiry?->form_name ?? $request->input('form_name'),
            'package' => $enquiry?->package_name,
        ]);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'redirect' => route('thank-you')])
            : redirect()->route('thank-you');
    }

    public function thankYou(Request $request)
    {
        $page = Page::byKey('thank-you');

        return view('pages.thank-you', [
            'page' => $page,
            // Present only on the first view right after a real submission, so reloads do not double count conversions.
            'done' => $request->session()->get('enquiry_done'),
            'seo' => Seo::make('Thank you for your enquiry', 'We have received your enquiry.', ['noindex' => true, 'canonical' => route('thank-you')]),
        ]);
    }
}
