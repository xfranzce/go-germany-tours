<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\Ghl\GhlClient;

class DashboardController extends Controller
{
    public function __invoke(GhlClient $ghl)
    {
        if (auth('admin')->user()->isEditor()) {
            return redirect()->route('admin.packages.index');
        }

        return view('admin.dashboard', [
            'thisWeek' => Enquiry::where('created_at', '>=', now()->startOfWeek())->count(),
            'newCount' => Enquiry::where('status', 'new')->count(),
            'unactioned' => Enquiry::where('status', 'new')->where('created_at', '<', now()->subDay())->count(),
            'ghlFailed' => Enquiry::where('ghl_status', 'failed')->count(),
            'recent' => Enquiry::latest()->take(8)->get(),
            'ghlOn' => $ghl->enabled(),
        ]);
    }
}
