<?php

namespace App\Services\Ghl;

use App\Models\Enquiry;
use App\Models\Setting;

/**
 * Pure (no network) mapping from an Enquiry to GHL data, so it can be unit tested.
 * Returns custom field VALUES keyed by field NAME; GhlSync swaps names for IDs.
 */
class GhlPayloadBuilder
{
    /** @return array<string, string> field name => value (empty values are dropped) */
    public function fieldValues(Enquiry $e): array
    {
        $v = [
            GhlFields::TRAVEL_DATE => $e->travel_date,
            GhlFields::SECOND_QUOTE => $e->second_quote,
            GhlFields::SPECIAL_REQUEST => $e->special_request,
            GhlFields::PACKAGE => $e->package_name,
            GhlFields::FORM_NAME => $e->formLabel(),
            GhlFields::TIME_OF_YEAR => $e->time_of_year,
            GhlFields::GROUP_SIZE => $e->group_size,
            GhlFields::NIGHTS => $e->nights,
            GhlFields::CONSENT_GIVEN => $e->consent_given ? 'Yes' : 'No',
            GhlFields::CONSENT_AT => $e->consented_at?->toIso8601String(),
            GhlFields::CONSENT_VERSION => $e->terms_version,
        ];

        $answers = $e->quiz_answers ?? [];
        foreach ($answers as $key => $answer) {
            $v[GhlFields::quiz($key)] = $answer;
        }
        if ($answers) {
            $v[GhlFields::QUIZ_FULL] = collect($answers)->map(fn ($a, $k) => "{$k}: {$a}")->implode("\n");
        }

        foreach (['first' => $e->first_touch ?? [], 'last' => $e->last_touch ?? []] as $touch => $data) {
            foreach (array_keys(GhlFields::TRACKING) as $key) {
                $v[GhlFields::tracking($key, $touch)] = $data[$key] ?? null;
            }
        }

        // Plain utm_* / form_source fields: latest touch, falling back to the first touch.
        $touch = ($e->last_touch ?? []) + ($e->first_touch ?? []);
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_device', 'utm_adcreative', 'utm_adposition'] as $key) {
            $v[$key] = $touch[$key] ?? null;
        }
        $v['utm_gclid'] = $touch['gclid'] ?? null;
        $v['form_source'] = $this->formSource($e);

        return array_filter($v, fn ($x) => $x !== null && $x !== '');
    }

    /** Which form on which page, e.g. "Hero quiz (/)" or "Package enquiry (/packages/rhine-classic)". */
    public function formSource(Enquiry $e): string
    {
        $path = $e->page_url ? (parse_url($e->page_url, PHP_URL_PATH) ?: '/') : null;

        return $e->formLabel().($path ? " ({$path})" : '');
    }

    public function tags(Enquiry $e): array
    {
        return array_values(array_unique(array_filter([
            Setting::get('brand_name'),
            'source: '.($e->source ?: 'direct'),
            $e->package_name ? 'package: '.$e->package_name : null,
            $e->formLabel(),
        ])));
    }

    public function contact(Enquiry $e): array
    {
        $contact = [
            'firstName' => $e->first_name, 'lastName' => $e->last_name, 'email' => $e->email,
            'phone' => $e->phone_e164, 'tags' => $this->tags($e), 'source' => Setting::get('brand_name').' website',
        ];

        // Country follows the country picked next to the phone number (ISO code), never a fixed default.
        if ($e->country_code) {
            $contact['country'] = $e->country_code;
        }

        return $contact;
    }
}
