<?php

namespace App\Services\Ghl;

use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Thin GoHighLevel API v2 client (services.leadconnectorhq.com).
 * The token is read from config only and is never logged or included in exceptions.
 */
class GhlClient
{
    /** Admin > GoHighLevel saves the token encrypted; .env is the fallback. */
    public function token(): string
    {
        $stored = Setting::get('ghl_api_token_enc');
        if (filled($stored)) {
            try {
                return Crypt::decryptString($stored);
            } catch (\Throwable) {
                // APP_KEY changed or value damaged: fall back to .env rather than sending garbage
            }
        }

        return (string) config('services.ghl.token');
    }

    public function locationId(): string
    {
        return (string) (Setting::get('ghl_location_id') ?: config('services.ghl.location_id'));
    }

    /** Where the credentials come from: 'admin', 'env' or null (not configured). */
    public function source(): ?string
    {
        if (filled(Setting::get('ghl_api_token_enc')) && filled(Setting::get('ghl_location_id'))) {
            return 'admin';
        }

        return filled(config('services.ghl.token')) && filled(config('services.ghl.location_id')) ? 'env' : null;
    }

    /** Credentials present AND not switched off in the admin. */
    public function enabled(): bool
    {
        return filled($this->token()) && filled($this->locationId()) && Setting::get('ghl_enabled', '1') !== '0';
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.ghl.base_url'))
            ->withToken($this->token())
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
