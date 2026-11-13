<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Services\Ghl\GhlClient;
use App\Services\Ghl\GhlException;
use App\Services\Ghl\GhlSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Sends one enquiry to GoHighLevel. Never affects the enquiry itself: failures only set ghl_status. */
class SyncEnquiryToGhl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(GhlClient $client, GhlSync $sync): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry) {
            return;
        }
        if (! $client->enabled()) {
            $enquiry->update(['ghl_status' => 'off']);

            return;
        }

        try {
            $contactId = $sync->sync($enquiry);
            $enquiry->update([
                'ghl_status' => 'synced', 'ghl_contact_id' => $contactId ?: null, 'ghl_error' => null,
                'ghl_synced_at' => now(), 'ghl_attempts' => $enquiry->ghl_attempts + 1,
            ]);
        } catch (\Throwable $e) {
            $retriable = ! ($e instanceof GhlException) || $e->retriable();
            $isSync = $this->job?->getConnectionName() === 'sync';
            $final = ! $retriable || $isSync || ($this->job && $this->attempts() >= $this->tries);

            $enquiry->update([
                'ghl_status' => $final ? 'failed' : 'pending',
                'ghl_error' => mb_substr($e->getMessage(), 0, 500),
                'ghl_attempts' => $enquiry->ghl_attempts + 1,
            ]);
            Log::error('GHL sync failed', ['enquiry' => $enquiry->id, 'error' => $e->getMessage()]);

            if ($retriable && ! $isSync && ! $final) {
                throw $e; // let the queue retry with backoff
            }
        }
    }
}
