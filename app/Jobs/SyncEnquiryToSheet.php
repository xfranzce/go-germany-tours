<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Services\SheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Adds one enquiry as a row in the Google Sheet. Never affects the enquiry: failures only set sheet_status. */
class SyncEnquiryToSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(SheetsService $sheets): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry) {
            return;
        }
        if (! $sheets->enabled()) {
            $enquiry->update(['sheet_status' => 'off']);

            return;
        }

        try {
            $sheets->send($sheets->row($enquiry));
            $enquiry->update(['sheet_status' => 'synced', 'sheet_error' => null, 'sheet_synced_at' => now()]);
        } catch (\Throwable $e) {
            $isSync = $this->job?->getConnectionName() === 'sync';
            $final = $isSync || ($this->job && $this->attempts() >= $this->tries);
            $enquiry->update(['sheet_status' => $final ? 'failed' : 'pending', 'sheet_error' => mb_substr($e->getMessage(), 0, 500)]);
            Log::error('Google Sheets sync failed', ['enquiry' => $enquiry->id, 'error' => $e->getMessage()]);

            if (! $final) {
                throw $e;
            }
        }
    }
}
