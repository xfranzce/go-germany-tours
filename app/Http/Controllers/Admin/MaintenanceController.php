<?php

namespace App\Http\Controllers\Admin;

use App\Http\Middleware\MaintenanceMode;
use App\Models\Setting;
use Illuminate\Http\Request;

/** On/off switch for maintenance mode (Administrators only). */
class MaintenanceController extends AdminController
{
    public function edit()
    {
        return view('admin.maintenance', ['enabled' => MaintenanceMode::enabled(), 'message' => Setting::get('maintenance_message')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:600']]);
        $enabled = $request->boolean('enabled');

        Setting::put('maintenance_enabled', $enabled ? '1' : '0');
        Setting::put('maintenance_message', trim((string) ($data['message'] ?? '')) ?: null);

        return redirect()->route('admin.maintenance.edit')->with('status', $enabled
            ? 'Maintenance mode is ON. Visitors now see the maintenance page.'
            : 'Maintenance mode is OFF. The site is live again.');
    }
}
