<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryExportTest extends TestCase
{
    use RefreshDatabase;

    private function enquiry(string $form, string $name, string $date): Enquiry
    {
        $e = Enquiry::create([
            'form_name' => $form, 'first_name' => $name, 'last_name' => 'Test', 'email' => strtolower($name).'@example.com', 'phone_country' => '+1', 'phone_e164' => '+15551234567',
            'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v', 'consented_at' => now(), 'ghl_status' => 'off',
        ]);
        $e->forceFill(['created_at' => $date.' 12:00:00'])->save();

        return $e;
    }

    private function csv($response): string
    {
        return $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8')->streamedContent();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']), 'admin');
        $this->enquiry('hero_quiz', 'Alice', '2026-11-01');
        $this->enquiry('contact', 'Bob', '2026-11-10');
        $this->enquiry('package_quote', 'Cara', '2026-11-15');
        $this->enquiry('hero_quiz', 'Dan', '2026-11-20');
    }

    public function test_the_export_panel_lets_you_pick_dates_and_forms(): void
    {
        $this->get('/admin/enquiries')->assertOk()->assertSee('Forms to include')->assertSee('Hero quiz')->assertSee('Contact page')->assertSee('name="form[]"', false);
    }

    public function test_export_everything_when_nothing_is_chosen(): void
    {
        $csv = $this->csv($this->get('/admin/enquiries/export'));
        foreach (['Alice', 'Bob', 'Cara', 'Dan'] as $n) {
            $this->assertStringContainsString($n, $csv);
        }
    }

    public function test_export_only_the_chosen_forms(): void
    {
        $csv = $this->csv($this->get('/admin/enquiries/export?form[]=hero_quiz&form[]=contact'));
        $this->assertStringContainsString('Alice', $csv);
        $this->assertStringContainsString('Bob', $csv);
        $this->assertStringContainsString('Dan', $csv);
        $this->assertStringNotContainsString('Cara', $csv);
    }

    public function test_export_only_the_chosen_dates_inclusive(): void
    {
        $csv = $this->csv($this->get('/admin/enquiries/export?from=2026-11-10&to=2026-11-15'));
        $this->assertStringContainsString('Bob', $csv);
        $this->assertStringContainsString('Cara', $csv);
        $this->assertStringNotContainsString('Alice', $csv);
        $this->assertStringNotContainsString('Dan', $csv);
    }

    public function test_dates_and_forms_combine_and_the_file_is_named_after_the_range(): void
    {
        $response = $this->get('/admin/enquiries/export?from=2026-11-05&to=2026-11-30&form[]=hero_quiz');
        $csv = $this->csv($response);
        $this->assertStringContainsString('Dan', $csv);
        $this->assertStringNotContainsString('Alice', $csv);   // right form, but before the start date
        $this->assertStringNotContainsString('Bob', $csv);     // in range, wrong form
        $this->assertStringContainsString('-from-2026-11-05-to-2026-11-30.csv', $response->headers->get('content-disposition'));
    }

    public function test_an_empty_export_and_a_backwards_date_range_are_explained(): void
    {
        $this->get('/admin/enquiries/export?from=2027-01-01')->assertRedirect()->assertSessionHas('export_error');
        $this->get('/admin/enquiries/export?from=2026-11-20&to=2026-11-01')->assertSessionHasErrors('to');
    }

    public function test_the_list_can_be_filtered_by_form(): void
    {
        $this->get('/admin/enquiries?form[]=contact')->assertOk()->assertSee('Bob')->assertDontSee('Alice');
    }
}
