<?php

namespace App\Concerns;

use App\Models\ActivityLog;

/** Adds the model's changes made in the admin to Admin > History. Models can list noisy fields in $activityIgnore. */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($m) => $m->logActivity('created'));
        static::updated(fn ($m) => $m->logActivity('updated'));
        static::deleted(fn ($m) => $m->logActivity(method_exists($m, 'isForceDeleting') && ! $m->isForceDeleting() ? 'trashed' : 'deleted'));
        static::registerModelEvent('restored', fn ($m) => $m->logActivity('restored'));
    }

    public function logActivity(string $action): void
    {
        $changes = ActivityLog::diff($this, $action);
        foreach ($this->activityIgnore ?? [] as $field) {
            unset($changes[$field]);
        }
        // For a new row only the title is useful; for a delete nothing more is needed.
        ActivityLog::record($action, $this, $action === 'updated' ? $changes : null);
    }
}
