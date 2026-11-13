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

        return Cache::remember('ghl.field_ids', 3600, fn () => collect($this->client->customFields())->pluck('id', 'name')->all());
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
}
