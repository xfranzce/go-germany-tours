<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\SheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/** Admin > Google Sheets: send every enquiry to a spreadsheet (web app URL stored encrypted). */
class SheetsController extends AdminController
{
    public function edit(SheetsService $sheets)
    {
        $url = $sheets->url();

        return view('admin.sheets', [
            'hasUrl' => filled($url),
            'urlHint' => $url ? substr($url, 0, 40).'…'.substr($url, -8) : null,
            'enabled' => (bool) Setting::get('sheets_enabled'),
            'script' => $sheets->script(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'sheets_webhook_url' => ['nullable', 'string', 'regex:~^https://script\.google\.com/(a/)?macros/((a/)?[A-Za-z0-9.-]+/)?s/[A-Za-z0-9_-]+/exec$~'],
        ], ['sheets_webhook_url.regex' => 'That does not look like a Web app URL. It starts with https://script.google.com/ and ends with /exec.']);

        if (filled($data['sheets_webhook_url'] ?? null)) {
            Setting::put('sheets_webhook_url_enc', Crypt::encryptString(trim($data['sheets_webhook_url'])));
        }
        if ($request->boolean('remove_url')) {
            Setting::put('sheets_webhook_url_enc', null);
        }
        Setting::put('sheets_enabled', $request->boolean('sheets_enabled') ? '1' : '0');

        return redirect()->route('admin.sheets.edit')->with('status', 'Google Sheets settings saved.');
    }

    public function test(SheetsService $sheets)
    {
        $r = $sheets->test();

        return back()->with($r['ok'] ? 'status' : 'sheets_error', $r['message']);
    }
}
