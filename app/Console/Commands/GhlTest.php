<?php

namespace App\Console\Commands;

use App\Services\Ghl\GhlClient;
use App\Services\Ghl\GhlFields;
use App\Services\Ghl\GhlSync;
use Illuminate\Console\Command;

class GhlTest extends Command
{
    protected $signature = 'ghl:test {--keep : Do not delete the test contact afterwards}';

    protected $description = 'Check the GHL token and location, and upsert a clearly labelled test contact';

    public function handle(GhlClient $client, GhlSync $sync): int
    {
        if (! $client->enabled()) {
            $this->error('GHL is OFF: GHL_API_TOKEN and/or GHL_LOCATION_ID are empty in .env.');

            return self::FAILURE;
        }

        try {
            $loc = $client->getLocation();
            $this->info('Token and location OK: '.($loc['location']['name'] ?? $loc['name'] ?? $client->locationId()));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line('(Location lookup needs the locations.readonly scope; continuing with the contact test.)');
        }

        try {
            $ids = $sync->fieldIds(true);
            $missing = array_diff_key(GhlFields::all(), $ids);
            $missing ? $this->warn(count($missing).' custom fields are missing. Run: php artisan ghl:setup') : $this->info('All '.count($ids).' fields present.');

            $fields = [];
            foreach ([GhlFields::FORM_NAME => 'CONNECTION TEST', GhlFields::tracking('utm_source', 'last') => 'ghl-test'] as $name => $value) {
                if (isset($ids[$name])) {
                    $fields[] = ['id' => $ids[$name], 'field_value' => $value];
                }
            }
            $result = $client->upsertContact([
                'firstName' => 'TEST', 'lastName' => 'DO NOT CONTACT (Go Germany Tours connection test)',
                'email' => 'ghl-connection-test@example.com', 'tags' => ['connection-test'], 'customFields' => $fields,
            ]);
            $id = $result['contact']['id'] ?? null;
            $this->info('Test contact upserted: '.$id);

            if ($id && ! $this->option('keep')) {
                try {
                    $client->deleteContact($id);
                    $this->info('Test contact deleted.');
                } catch (\Throwable $e) {
                    $this->warn('Could not delete the test contact (needs contacts.write): remove it by hand. '.$e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
