<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryDetailPageTest extends TestCase
{
    use RefreshDatabase;

    /** Regression: an enquiry already synced to the Google Sheet made the detail page crash with a 500 (sheet_synced_at was not cast to a date). */
    public function test_the_detail_page_opens_for_an_enquiry_that_has_been_synced_to_the_sheet_and_ghl(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $e = Enquiry::create([
            'form_name' => 'hero_quiz', 'first_name' => 'main', 'last_name' => 'quiz 01', 'email' => 't@example.com', 'phone_country' => '+1', 'phone_e164' => '+15551234567',
            'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v', 'consented_at' => now(),
            'ghl_status' => 'synced', 'ghl_synced_at' => now(), 'email_sent_at' => now(),
            'sheet_status' => 'synced', 'sheet_synced_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')->get("/admin/enquiries/{$e->id}")->assertOk()->assertSee('Google Sheet')->assertSee('synced');
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $e->fresh()->sheet_synced_at);
    }
}
