<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\Ghl\GhlClient;
use App\Services\Ghl\GhlFields;
use App\Services\Ghl\GhlSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/** Admin > GoHighLevel: enter the credentials, switch the sync on or off, test it, create the custom fields. */
class GhlController extends AdminController
{
    public function edit(GhlClient $ghl)
    {
        $token = $ghl->token();

        return view('admin.ghl', [
            'enabled' => $ghl->enabled(), 'source' => $ghl->source(),
            'switchedOff' => Setting::get('ghl_enabled', '1') === '0',
            'locationId' => Setting::get('ghl_location_id', ''),
            'tokenHint' => filled($token) ? '••••••••'.substr($token, -4) : null,
            'fieldCount' => count(GhlFields::all()),
            'failed' => Enquiry::where('ghl_status', 'failed')->count(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ghl_location_id' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{10,40}$/'],
            'ghl_api_token' => ['nullable', 'string', 'min:20', 'max:600'],
        ], ['ghl_location_id.regex' => 'The Location ID is letters and numbers only (about 20 characters).']);

        Setting::put('ghl_location_id', $data['ghl_location_id'] ?? null);
        if (filled($data['ghl_api_token'] ?? null)) {
            Setting::put('ghl_api_token_enc', Crypt::encryptString(trim($data['ghl_api_token'])));
        }
        if ($request->boolean('remove_token')) {
            Setting::put('ghl_api_token_enc', null);
        }
        Setting::put('ghl_enabled', $request->boolean('ghl_enabled') ? '1' : '0');
        Cache::forget('ghl.field_ids');

        return redirect()->route('admin.ghl.edit')->with('status', 'GoHighLevel settings saved.');
    }

    /** Checks the token + location and that the custom fields exist. Creates nothing. */
    public function test(GhlClient $ghl, GhlSync $sync)
    {
        if (! $ghl->enabled()) {
            return back()->withErrors(['ghl' => 'GoHighLevel is off: add the Location ID and API token, tick "Send enquiries to GoHighLevel", and save first.']);
        }

        $messages = [];
        try {
            $loc = $ghl->getLocation();
            $messages[] = 'Connected to GoHighLevel: '.($loc['location']['name'] ?? $loc['name'] ?? $ghl->locationId()).'.';
        } catch (\Throwable $e) {
            $messages[] = 'Token and location could not be verified ('.$e->getMessage().'). If the token lacks the locations.readonly scope this is expected; checking the custom fields next.';
        }

        try {
            $have = count(array_intersect_key($sync->fieldIds(true), GhlFields::all()));
            $need = count(GhlFields::all());
            $messages[] = $have === $need
                ? "All {$need} custom fields are present. Enquiries will sync."
                : ($need - $have)." of {$need} custom fields are missing. Press \"Create missing custom fields\".";
        } catch (\Throwable $e) {
            return back()->withErrors(['ghl' => 'GoHighLevel rejected the request: '.$e->getMessage().' Check the token, the Location ID and the token scopes.'])->with('ghl_messages', $messages);
        }

        return back()->with('status', 'Connection test finished.')->with('ghl_messages', $messages);
    }

    public function setup(GhlClient $ghl, GhlSync $sync)
    {
        if (! $ghl->enabled()) {
            return back()->withErrors(['ghl' => 'GoHighLevel is off: save the Location ID and API token first.']);
        }

        try {
            $r = $sync->ensureFields();
        } catch (\Throwable $e) {
            return back()->withErrors(['ghl' => 'Could not read the custom fields: '.$e->getMessage().' Check the token has the custom fields read and write scopes.']);
        }
        $messages = [count($r['created']).' custom field(s) created, '.$r['existing'].' already existed.'];
        foreach ($r['failed'] as $name => $why) {
            $messages[] = "Failed: {$name}: {$why}";
        }

        return back()->with('status', $messages[0])->with('ghl_messages', $messages);
    }
}
