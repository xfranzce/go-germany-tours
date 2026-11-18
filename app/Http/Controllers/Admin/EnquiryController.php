<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEnquiryToGhl;
use App\Models\Enquiry;
use App\Services\Ghl\GhlClient;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    private function filtered(Request $request)
    {
        return Enquiry::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $request->input('q')).'%';
                $q->where(fn ($w) => $w->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)
                    ->orWhere('email', 'like', $term)->orWhere('phone_e164', 'like', $term)->orWhere('package_name', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->input('source')))
            ->when($request->filled('form'), fn ($q) => $q->where('form_name', $request->input('form')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->latest();
    }

    public function index(Request $request)
    {
        return view('admin.enquiries.index', [
            'enquiries' => $this->filtered($request)->paginate(25)->withQueryString(),
            'sources' => Enquiry::whereNotNull('source')->distinct()->orderBy('source')->pluck('source'),
        ]);
    }

    public function show(Enquiry $enquiry, GhlClient $ghl)
    {
        return view('admin.enquiries.show', ['e' => $enquiry->load('package'), 'ghlOn' => $ghl->enabled()]);
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(config('gogermany.statuses')))],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $enquiry->update($data);

        return back()->with('status', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('status', 'Enquiry deleted.');
    }

    public function retryGhl(Enquiry $enquiry, GhlClient $ghl)
    {
        if (! $ghl->enabled()) {
            return back()->withErrors(['ghl' => 'GoHighLevel is switched off: GHL_API_TOKEN / GHL_LOCATION_ID are empty in .env.']);
        }
        $enquiry->update(['ghl_status' => 'pending', 'ghl_error' => null]);
        try {
            SyncEnquiryToGhl::dispatch($enquiry->id);
        } catch (\Throwable $e) {
            // The job records its own failure on the enquiry.
        }

        return back()->with('status', 'GoHighLevel sync queued. Refresh in a moment to see the result.');
    }

    /** Sends this enquiry to the Google Sheet right now (synchronously) so the result shows straight away. */
    public function retrySheet(Enquiry $enquiry, \App\Services\SheetsService $sheets)
    {
        if (! $sheets->enabled()) {
            return back()->withErrors(['sheet' => 'Google Sheets is off or has no URL saved. Check Admin > Google Sheets.']);
        }
        try {
            $sheets->send($sheets->row($enquiry), \App\Services\SheetsService::tabFor($enquiry->form_name));
            $enquiry->update(['sheet_status' => 'synced', 'sheet_error' => null, 'sheet_synced_at' => now()]);
        } catch (\Throwable $e) {
            $enquiry->update(['sheet_status' => 'failed', 'sheet_error' => mb_substr($e->getMessage(), 0, 500)]);

            return back()->withErrors(['sheet' => $e->getMessage()]);
        }

        return back()->with('status', 'Row added to the Google Sheet.');
    }

    public function export(Request $request)
    {
        $cols = ['id', 'created_at', 'status', 'form_name', 'first_name', 'last_name', 'email', 'phone_e164', 'package_name', 'travel_date',
            'time_of_year', 'group_size', 'nights', 'second_quote', 'special_request', 'source', 'ghl_status', 'consent_given', 'consented_at', 'terms_version', 'ip_address', 'page_url'];
        $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v; // stop spreadsheet formula injection
        $query = $this->filtered($request);

        return response()->streamDownload(function () use ($query, $cols, $safe) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_merge($cols, ['quiz_answers', 'utm_source_last', 'utm_campaign_last', 'gclid_last']));
            $query->chunk(500, function ($rows) use ($out, $cols, $safe) {
                foreach ($rows as $e) {
                    $line = array_map(fn ($c) => $safe(is_bool($e->$c) ? ($e->$c ? 'yes' : 'no') : (string) $e->$c), $cols);
                    $line[] = $safe(json_encode($e->quiz_answers));
                    $line[] = $safe($e->last_touch['utm_source'] ?? '');
                    $line[] = $safe($e->last_touch['utm_campaign'] ?? '');
                    $line[] = $safe($e->last_touch['gclid'] ?? '');
                    fputcsv($out, $line);
                }
            });
            fclose($out);
        }, 'enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
