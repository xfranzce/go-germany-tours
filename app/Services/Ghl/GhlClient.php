<?php

namespace App\Services\Ghl;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin GoHighLevel API v2 client (services.leadconnectorhq.com).
 * The token is read from config only and is never logged or included in exceptions.
 */
class GhlClient
{
    public function enabled(): bool
    {
        return filled(config('services.ghl.token')) && filled(config('services.ghl.location_id'));
    }

    public function locationId(): string
    {
        return (string) config('services.ghl.location_id');
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.ghl.base_url'))
            ->withToken((string) config('services.ghl.token'))
            ->withHeaders(['Version' => config('services.ghl.api_version'), 'Accept' => 'application/json'])
            ->acceptJson()->asJson()->timeout(15);
    }

    /** @throws GhlException */
    private function check(Response $r, string $what): array
    {
        if ($r->successful()) {
            return $r->json() ?? [];
        }
        // Body is included but the Authorization header never is.
        throw new GhlException("GHL {$what} failed: HTTP {$r->status()} ".mb_substr($r->body(), 0, 300), $r->status());
    }

    /** @return array<int, array{id: string, name: string}> */
    public function customFields(): array
    {
        return $this->check($this->http()->get("/locations/{$this->locationId()}/customFields"), 'list custom fields')['customFields'] ?? [];
    }

    public function createCustomField(string $name, string $dataType): array
    {
        return $this->check($this->http()->post("/locations/{$this->locationId()}/customFields", [
            'name' => $name, 'dataType' => $dataType, 'model' => 'contact',
        ]), "create field '{$name}'");
    }

    public function findContactByEmail(string $email): ?array
    {
        $r = $this->http()->get('/contacts/search/duplicate', ['locationId' => $this->locationId(), 'email' => $email]);
        if ($r->status() === 404) {
            return null;
        }

        return $this->check($r, 'duplicate search')['contact'] ?? null;
    }

    public function upsertContact(array $payload): array
    {
        return $this->check($this->http()->post('/contacts/upsert', $payload + ['locationId' => $this->locationId()]), 'upsert contact');
    }

    public function deleteContact(string $id): void
    {
        $this->check($this->http()->delete("/contacts/{$id}"), 'delete contact');
    }

    public function getLocation(): array
    {
        return $this->check($this->http()->get("/locations/{$this->locationId()}"), 'get location');
    }
}
