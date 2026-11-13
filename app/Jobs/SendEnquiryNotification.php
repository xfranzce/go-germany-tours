<?php

namespace App\Jobs;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Emails the staff addresses from Admin > Site settings about a new enquiry. */
class SendEnquiryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        $to = array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', (string) Setting::get('notification_emails', '')))));
        $to = array_filter($to, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        if (! $enquiry || ! $to) {
            Log::warning('No notification emails set (Admin > Site settings): enquiry email not sent.', ['enquiry' => $this->enquiryId]);

            return;
        }

        Mail::to($to)->send(new EnquiryReceived($enquiry));
        $enquiry->update(['email_sent_at' => now()]);
    }
}
