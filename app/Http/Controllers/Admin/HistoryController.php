<?php

namespace App\Http\Controllers\Admin;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

/** Admin > History: who changed what and when (Administrators only). */
class HistoryController extends AdminController
{
    public function index(Request $request)
    {
        $f = $request->validate([
            'user' => ['nullable', 'string', 'max:190'], 'action' => ['nullable', 'string', 'max:30'], 'area' => ['nullable', 'string', 'max:60'],
            'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'q' => ['nullable', 'string', 'max:100'],
        ]);

        $logs = ActivityLog::query()
            ->when($f['user'] ?? null, fn ($q, $v) => $q->where('user_email', $v))
            ->when($f['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($f['area'] ?? null, fn ($q, $v) => $q->where('subject_type', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v.' 00:00:00'))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v.' 23:59:59'))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w->where('summary', 'like', "%{$v}%")->orWhere('subject_label', 'like', "%{$v}%")))
            ->latest('created_at')->latest('id')
            ->paginate(40)->withQueryString();

        return view('admin.history', [
            'logs' => $logs,
            'users' => ActivityLog::query()->whereNotNull('user_email')->distinct()->orderBy('user_email')->pluck('user_name', 'user_email'),
            'areas' => ActivityLog::query()->whereNotNull('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type'),
        ]);
    }
}
