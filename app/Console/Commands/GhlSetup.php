<?php

namespace App\Console\Commands;

use App\Services\Ghl\GhlClient;
use App\Services\Ghl\GhlFields;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GhlSetup extends Command
{
    protected $signature = 'ghl:setup {--dry-run : List what would be created without creating it}';

    protected $description = 'Create any missing GoHighLevel custom fields (safe to re-run)';

    public function handle(GhlClient $client): int
    {
        if (! $client->enabled()) {
            $this->error('GHL is OFF: set GHL_API_TOKEN and GHL_LOCATION_ID in .env first (see docs/reference/GHL_INTEGRATION.md).');

            return self::FAILURE;
        }

        try {
            $existing = collect($client->customFields())->pluck('id', 'name');
        } catch (\Throwable $e) {
            $this->error('Could not list custom fields: '.$e->getMessage());
            $this->line('Check the token, the location ID and that the token has the custom fields read/write scopes.');

            return self::FAILURE;
        }

        $created = [];
        $skipped = [];
        foreach (GhlFields::all() as $name => $type) {
            if ($existing->has($name)) {
                $skipped[] = $name;

                continue;
            }
            if ($this->option('dry-run')) {
                $this->line("[dry run] would create: {$name} ({$type})");

                continue;
            }
            try {
                $client->createCustomField($name, $type);
                $created[] = $name;
                $this->info("Created: {$name} ({$type})");
            } catch (\Throwable $e) {
                $this->error("Failed: {$name}: ".$e->getMessage());
            }
        }

        Cache::forget('ghl.field_ids');
        $this->newLine();
        $this->info(count($created).' created, '.count($skipped).' already existed.');
        $this->line('Tip: in GHL you may move these fields into folders by hand. Do NOT rename them (names are matched exactly).');

        return self::SUCCESS;
    }
}
