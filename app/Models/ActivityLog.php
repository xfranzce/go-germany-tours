<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** One line of the admin History: who did what, and when. Written automatically by the LogsActivity trait and the login screen. */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /** Friendly area name per model. */
    public const TYPES = [
        Package::class => 'Package', TourType::class => 'Tour type', Destination::class => 'Destination', Page::class => 'Page',
        Post::class => 'Travel guide', Testimonial::class => 'Testimonial', Faq::class => 'FAQ', TeamMember::class => 'Team member',
        QuizStep::class => 'Hero quiz step', QuizOption::class => 'Hero quiz option', Media::class => 'Media', AdminUser::class => 'Admin user',
        CodeSnippet::class => 'Header & Footer code', Setting::class => 'Site setting', Enquiry::class => 'Enquiry',
    ];

    public const ACTIONS = [
        'created' => 'Added', 'updated' => 'Updated', 'deleted' => 'Deleted', 'trashed' => 'Moved to trash', 'restored' => 'Restored',
        'login' => 'Logged in', 'logout' => 'Logged out', 'login_failed' => 'Failed login', 'other' => 'Other',
    ];

    /** Fields never stored in the history (noise). Fields matching SECRET are stored as "(hidden)". */
    private const IGNORE = ['updated_at', 'created_at', 'deleted_at', 'last_login_at', 'remember_token'];

    private const SECRET = '/password|token|secret|_enc$|webhook/i';

    protected $guarded = [];

    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];

    /** Records a change made by the signed-in admin user. Does nothing for public visitors, seeders and queue jobs. Never throws. */
    public static function record(string $action, ?Model $subject = null, ?array $changes = null, ?string $summary = null, ?AdminUser $actor = null): void
    {
        try {
            $actor ??= auth('admin')->user();
            if (! $actor && $action !== 'login_failed') {
                return;
            }

            $type = $subject ? (self::TYPES[$subject::class] ?? Str::headline(class_basename($subject))) : null;
            $label = $subject ? self::labelFor($subject) : null;
            $changes = $changes === null ? null : self::clean($changes, $subject instanceof Setting && preg_match(self::SECRET, (string) $subject->key) === 1);
            if ($action === 'updated' && ! $changes) {
                return;
            }

            if (! $summary) {
                $verb = strtolower(self::ACTIONS[$action] ?? $action);
                $fields = $action === 'updated' && $changes
                    ? ' ('.implode(', ', array_map(fn ($f) => str_replace('_', ' ', $f), array_slice(array_keys($changes), 0, 6))).(count($changes) > 6 ? ', ...' : '').')'
                    : '';
                $summary = ucfirst($verb).' '.strtolower($type ?? '').($label ? ' "'.Str::limit($label, 80).'"' : '').$fields;
            }

            static::create([
                'admin_user_id' => $actor?->id, 'user_name' => $actor?->name, 'user_email' => $actor?->email,
                'action' => $action, 'subject_type' => $type, 'subject_id' => $subject?->getKey(),
                'subject_label' => $label ? Str::limit($label, 250) : null,
                'summary' => Str::limit($summary, 490), 'changes' => $changes ?: null, 'ip' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e); // the history must never break a save
        }
    }

    /** Old and new values of a saved model: [field => [old, new]]. */
    public static function diff(Model $model, string $action): array
    {
        if ($action !== 'updated') {
            return [];
        }
        $out = [];
        foreach ($model->getChanges() as $field => $new) {
            $out[$field] = [$model->getOriginal($field), $new];
        }

        return $out;
    }

    private static function labelFor(Model $m): string
    {
        if ($m instanceof Setting) {
            return (string) $m->key;
        }
        foreach (['name', 'title', 'question', 'label', 'key', 'email'] as $f) {
            $v = $m->getAttribute($f);
            if (filled($v) && is_scalar($v)) {
                return (string) $v;
            }
        }
        if (filled($m->getAttribute('first_name'))) {
            return trim($m->first_name.' '.$m->last_name);
        }

        return '#'.$m->getKey();
    }

    /** $hideAll: the row is a secret setting (the field is called "value", so its key decides). */
    private static function clean(array $changes, bool $hideAll = false): array
    {
        $out = [];
        foreach ($changes as $field => [$old, $new]) {
            if (in_array($field, self::IGNORE, true) || $old == $new) {
                continue;
            }
            $out[$field] = $hideAll || preg_match(self::SECRET, $field) ? ['(hidden)', '(hidden)'] : [self::short($old), self::short($new)];
        }

        return $out;
    }

    private static function short(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return Str::limit(trim(strip_tags($s)), 160);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? Str::headline($this->action);
    }
}
