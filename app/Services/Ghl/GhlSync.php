<?php

namespace App\Services\Ghl;

use App\Models\Enquiry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/** Upserts the enquiry contact in GHL, keeping first-touch values from earlier submissions. */
class GhlSync
{
    public function __construct(private GhlClient $client, private GhlPayloadBuilder $builder) {}

    /** Field name => id map, cached for an hour. */
    public function fieldIds(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget('ghl.field_ids');
        }

        return Cache::remember('ghl.field_ids', 3600, fn () => $this->resolve($this->client->customFields()));
    }

    /**
     * Map OUR field names to GHL ids. A field matches by exact name, or by GHL's fieldKey
     * (contact.special_request) so a field that already exists under a slightly different
     * label (e.g. created by hand) is reused instead of being created twice.
     *
     * @param  array<int, array<string, mixed>>  $remote
     * @return array<string, string>
     */
    private function resolve(array $remote): array
    {
        $byName = collect($remote)->pluck('id', 'name');
        $byKey = collect($remote)->filter(fn ($f) => filled($f['fieldKey'] ?? null))->pluck('id', 'fieldKey');
        $ids = $byName->all();
        foreach (GhlFields::all() as $name => $type) {
            if (! isset($ids[$name]) && $byKey->has(self::fieldKey($name))) {
                $ids[$name] = $byKey[self::fieldKey($name)];
            }
        }

        return $ids;
    }

    /** GHL's key for a custom field name: "Special Request" => "contact.special_request". */
    public static function fieldKey(string $name): string
    {
        return 'contact.'.trim((string) preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($name)), '_');
    }

    /** @return string GHL contact id */
    public function sync(Enquiry $enquiry): string
    {
        $values = $this->builder->fieldValues($enquiry);
        $ids = $this->fieldIds();

        // A field we need is missing from the cache: refresh once (e.g. ghl:setup just ran).
        if (array_diff_key($values, $ids)) {
            $ids = $this->fieldIds(true);
        }

        $existing = $this->client->findContactByEmail($enquiry->email);
        $existingValues = collect($existing['customFields'] ?? [])->mapWithKeys(fn ($f) => [$f['id'] => $f['value'] ?? ($f['field_value'] ?? null)])->all();

        $customFields = [];
        foreach ($values as $name => $value) {
            if (! isset($ids[$name])) {
                Log::warning("GHL field '{$name}' does not exist in GHL; run php artisan ghl:setup");

                continue;
            }
            // Returning contact: never overwrite first-touch fields that already have a value.
            if (str_ends_with($name, ' First') && filled($existingValues[$ids[$name]] ?? null)) {
                continue;
            }
            $customFields[] = ['id' => $ids[$name], 'field_value' => $value];
        }

        $result = $this->client->upsertContact($this->builder->contact($enquiry) + ['customFields' => $customFields]);

        return (string) ($result['contact']['id'] ?? '');
    }

    /**
     * Create any missing GHL custom fields (same as `php artisan ghl:setup`).
     *
     * @return array{created: string[], existing: int, failed: array<string, string>}
     */
    public function ensureFields(): array
    {
        $existing = collect($this->resolve($this->client->customFields()));
        $created = [];
        $failed = [];
        foreach (GhlFields::all() as $name => $type) {
            if ($existing->has($name)) {
                continue;
            }
            try {
                $this->client->createCustomField($name, $type);
                $created[] = $name;
            } catch (\Throwable $e) {
                // GHL says the field key is already taken: it exists, so that is fine.
                if ($e instanceof GhlException && str_contains($e->getMessage(), 'already exists')) {
                    $existing->put($name, 'exists');

                    continue;
                }
                $failed[$name] = $e->getMessage();
            }
        }
        \Illuminate\Support\Facades\Cache::forget('ghl.field_ids');

        return ['created' => $created, 'existing' => $existing->only(array_keys(GhlFields::all()))->count(), 'failed' => $failed];
    }
}
