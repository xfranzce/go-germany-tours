<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Appends every enquiry as a row to a Google Sheet through a small Google Apps Script web app
 * that the site owner pastes into their own sheet (Admin > Google Sheets). No Google Cloud setup.
 * The web app URL is stored encrypted; a shared token stops anyone else posting rows.
 */
class SheetsService
{
    public const HEADERS = [
        'Enquiry ID', 'Submitted (UTC)', 'Form', 'First name', 'Last name', 'Email', 'Phone', 'Country', 'Travel date',
        'Package', 'Time of year', 'Group size', 'Nights', 'Message', 'Quiz answers', 'Source',
        'UTM source', 'UTM medium', 'UTM campaign', 'UTM term', 'UTM content', 'GCLID', 'Page URL', 'Consent given', 'Terms version',
    ];

    /** Which tab of the sheet an enquiry goes to (created by the script when missing). */
    public static function tabFor(?string $form): string
    {
        return match ($form) {
            'hero_quiz' => 'Hero quiz',
            'package_quote', 'header_modal' => 'Get a Quote',
            'package_question' => 'Ask a Question',
            'contact' => 'Contact',
            default => 'Other',
        };
    }

    public function url(): ?string
    {
        $enc = Setting::get('sheets_webhook_url_enc');
        if (! filled($enc)) {
            return null;
        }
        try {
            return Crypt::decryptString($enc);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Shared secret that is also pasted into the Apps Script. Created on first use. */
    public function token(): string
    {
        $t = Setting::get('sheets_token');
        if (! filled($t)) {
            $t = Str::random(32);
            Setting::put('sheets_token', $t);
        }

        return $t;
    }

    public function enabled(): bool
    {
        return (bool) Setting::get('sheets_enabled') && filled($this->url());
    }

    public function row(Enquiry $e): array
    {
        $touch = $e->last_touch ?: ($e->first_touch ?: []);
        $quiz = collect($e->quiz_answers ?: [])->map(fn ($v, $k) => "$k: $v")->implode('; ');

        return [
            $e->id, $e->created_at?->utc()->format('Y-m-d H:i:s'), $e->form_name, $e->first_name, $e->last_name, $e->email,
            $e->phone_e164, $e->country_code ?? '', $e->travel_date, $e->package_name, $e->time_of_year, $e->group_size, $e->nights,
            $e->special_request, $quiz, $e->source,
            $touch['utm_source'] ?? '', $touch['utm_medium'] ?? '', $touch['utm_campaign'] ?? '', $touch['utm_term'] ?? '', $touch['utm_content'] ?? '',
            $touch['gclid'] ?? '', $e->page_url, $e->consent_given ? 'Yes' : 'No', $e->terms_version,
        ];
    }

    /** @throws \RuntimeException when the sheet did not accept the row */
    public function send(array $row, string $tab = 'Other'): void
    {
        $url = $this->url();
        if (! $url) {
            throw new \RuntimeException('No Google Sheets web app URL is saved.');
        }

        $res = Http::timeout(15)->asJson()->post($url, ['token' => $this->token(), 'tab' => $tab, 'headers' => self::HEADERS, 'row' => array_map(fn ($v) => (string) ($v ?? ''), $row)]);
        $json = $res->json();
        if (! $res->successful() || ! is_array($json)) {
            $hint = str_contains($url, '/a/macros/') || str_contains($url, '/macros/a/')
                ? ' The saved link contains your company domain, which makes Google ask for a sign-in. Use the plain link from Deploy > Manage deployments: https://script.google.com/macros/s/.../exec'
                : ' Check the web app is deployed with "Who has access" set to Anyone, and that you copied the Web app URL ending in /exec.';
            throw new \RuntimeException('Google did not return a valid answer (HTTP '.$res->status().').'.$hint);
        }
        if (empty($json['ok'])) {
            throw new \RuntimeException('The sheet script refused the row: '.mb_substr((string) ($json['error'] ?? 'unknown error'), 0, 200));
        }
    }

    /** Appends a clearly marked sample row. */
    public function test(): array
    {
        $row = array_fill(0, count(self::HEADERS), '');
        $row[1] = now()->utc()->format('Y-m-d H:i:s');
        $row[2] = 'TEST ROW (safe to delete)';
        $row[3] = 'Test';
        $row[4] = 'Row';
        $row[5] = 'test@example.com';
        try {
            $this->send($row, 'Test');
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'A test row was added to your sheet. You can delete it.'];
    }

    public function script(): string
    {
        $token = $this->token();

        return <<<JS
const TOKEN = '{$token}';

function doPost(e) {
  try {
    const d = JSON.parse(e.postData.contents);
    if (d.token !== TOKEN) return out({ ok: false, error: 'Wrong token' });
    const sh = getTab(d.tab || 'Enquiries', d.headers);
    const r = sh.getLastRow() + 1;
    const range = sh.getRange(r, 1, 1, d.row.length);
    range.setNumberFormat('@'); // keep +49... phone numbers as text
    range.setValues([d.row]);
    return out({ ok: true });
  } catch (err) {
    return out({ ok: false, error: String(err) });
  }
}

function getTab(name, headers) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sh = ss.getSheetByName(name);
  if (!sh) sh = ss.insertSheet(name);
  if (sh.getLastRow() === 0) {
    sh.appendRow(headers);
    sh.setFrozenRows(1);
  }
  return sh;
}

function out(o) {
  return ContentService.createTextOutput(JSON.stringify(o)).setMimeType(ContentService.MimeType.JSON);
}
JS;
    }
}
