<?php

namespace App\Payments\Providers;

use App\Support\Security\Crypto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Shared behaviour for the five provider adapters.
 *
 * Each adapter talks to its OWN provider API directly, using the
 * credentials stored encrypted on the provider (Credentials tab) plus
 * the endpoint configuration below. There is no shared aggregator:
 * M-Pesa, Airtel Money, Mixx by Yas, HaloPesa and T-Pesa each keep
 * their own transport, while all five normalize into the same
 * internal transaction structure for the payment engine.
 *
 * Configure per provider (Configuration tab / config/mobile_money.php):
 *   direct.base_url      e.g. https://api.provider.co.tz
 *   direct.initiate_path e.g. /v1/collections/ussd-push
 *   direct.status_path   e.g. /v1/collections/{reference}
 *   direct.test_path     e.g. /v1/ping
 *   direct.auth_type     bearer | api-key | none
 *
 * Until a provider is configured, initiate()/query() fail CLOSED with a
 * clear message — never a faked success.
 */
abstract class BaseProviderAdapter implements ProviderAdapterInterface
{
    abstract public function code(): string;

    abstract public function name(): string;

    public function color(): string
    {
        return config("mobile_money.providers.{$this->code()}.color", '#4D3422');
    }

    public function supportsPhone(string $digits): bool
    {
        $prefixes = (array) config("mobile_money.providers.{$this->code()}.prefixes", []);
        $local = preg_replace('/[^0-9]/', '', $digits);
        if (str_starts_with($local, '255')) {
            $local = substr($local, 3);
        } elseif (str_starts_with($local, '0')) {
            $local = substr($local, 1);
        }

        foreach ($prefixes as $prefix) {
            if (str_starts_with($local, (string) $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function initiate(array $input): array
    {
        $endpoint = $this->directEndpoint('initiate_path');
        if (! $endpoint) {
            return $this->notConfigured('FAILED');
        }

        Log::info('Payment initiated', [
            'provider' => $this->code(),
            'reference' => $input['order_reference'] ?? null,
            'amount' => $input['amount'] ?? null,
            'phone' => Crypto::maskPhone($input['phone'] ?? null),
            'status' => 'PENDING',
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->authHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json']))
                ->post($endpoint, [
                    'amount' => $input['amount'],
                    'currency' => $input['currency'] ?? 'TZS',
                    'orderReference' => $input['order_reference'],
                    'phoneNumber' => $input['phone'],
                    'customerName' => $input['customer_name'] ?? null,
                    'description' => $input['description'] ?? null,
                ]);

            $status = $response->status();
            $body = $response->json() ?? ['raw' => $response->body()];

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'status' => 'FAILED',
                    'provider_reference' => null,
                    'raw' => ['error' => $body['message'] ?? $body['error'] ?? "HTTP {$status}", 'status' => $status],
                    'error' => $body['message'] ?? $body['error'] ?? $this->name().' request failed.',
                    'http_status' => $status,
                ];
            }

            return [
                'success' => true,
                'status' => $this->normalizeStatus($body['status'] ?? 'PENDING'),
                'provider_reference' => $body['transactionId'] ?? $body['paymentReference'] ?? $body['id'] ?? null,
                'raw' => is_array($body) ? $body : ['body' => $body],
                'error' => null,
                'http_status' => $status,
            ];
        } catch (\Throwable $e) {
            Log::error('Provider initiate exception', ['provider' => $this->code(), 'message' => $e->getMessage()]);

            return [
                'success' => false,
                'status' => 'FAILED',
                'provider_reference' => null,
                'raw' => ['error' => $e->getMessage()],
                'error' => 'Could not reach '.$this->name().': '.$e->getMessage(),
                'http_status' => 0,
            ];
        }
    }

    public function query(string $providerReference): array
    {
        $template = config("mobile_money.providers.{$this->code()}.direct.status_path");
        $base = rtrim((string) config("mobile_money.providers.{$this->code()}.direct.base_url", ''), '/');
        if (! $base || ! $template) {
            return $this->notConfigured('PENDING');
        }

        try {
            $endpoint = $base.str_replace('{reference}', urlencode($providerReference), $template);
            $response = Http::timeout(30)
                ->withHeaders($this->authHeaders(['Accept' => 'application/json']))
                ->get($endpoint);

            $status = $response->status();
            $body = $response->json() ?? ['raw' => $response->body()];

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'status' => 'PENDING',
                    'amount' => null,
                    'provider_reference' => $providerReference,
                    'raw' => $body,
                    'error' => $body['message'] ?? $this->name().' status query failed.',
                    'http_status' => $status,
                ];
            }

            $data = is_array($body) && isset($body[0]) ? $body[0] : $body;
            if (is_array($body) && isset($body['data'][0])) {
                $data = $body['data'][0];
            }

            return [
                'success' => true,
                'status' => $this->normalizeStatus($data['status'] ?? $body['status'] ?? null),
                'amount' => isset($data['collectedAmount']) ? (float) $data['collectedAmount'] : (isset($data['amount']) ? (float) $data['amount'] : null),
                'provider_reference' => $data['transactionId'] ?? $data['paymentReference'] ?? $data['id'] ?? $providerReference,
                'raw' => is_array($body) ? $body : ['body' => $body],
                'error' => null,
                'http_status' => $status,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'PENDING',
                'amount' => null,
                'provider_reference' => $providerReference,
                'raw' => null,
                'error' => 'Could not reach '.$this->name().': '.$e->getMessage(),
                'http_status' => 0,
            ];
        }
    }

    /**
     * Connectivity + credential check against the provider's own API.
     * Reports honestly: reachable / unauthorized / not configured.
     *
     * @return array{success:bool,message:string}
     */
    public function testConnection(): array
    {
        $base = rtrim((string) config("mobile_money.providers.{$this->code()}.direct.base_url", ''), '/');
        if (! $base) {
            return ['success' => false, 'message' => 'No direct API base URL configured for '.$this->name().'. Set it on the Configuration tab.'];
        }

        $credential = null;
        try {
            $credential = \App\Models\ProviderCredential::whereHas('provider', fn ($q) => $q->where('code', $this->code()))
                ->where('environment', 'live')->first();
        } catch (\Throwable) {
        }
        if (! $credential || empty($credential->client_id)) {
            return ['success' => false, 'message' => 'No live credentials stored for '.$this->name().'. Add them on the Credentials tab first.'];
        }

        try {
            $path = config("mobile_money.providers.{$this->code()}.direct.test_path", '/');
            $response = Http::timeout(15)->withHeaders($this->authHeaders(['Accept' => 'application/json']))->get($base.'/'.ltrim($path, '/'));
            $status = $response->status();

            if ($status === 401 || $status === 403) {
                return ['success' => false, 'message' => $this->name().' reachable but rejected the credentials (HTTP '.$status.').'];
            }
            if ($response->successful() || $status === 404) {
                return ['success' => true, 'message' => 'Connection OK — '.$this->name().' API reachable (HTTP '.$status.').'];
            }

            return ['success' => false, 'message' => $this->name().' returned HTTP '.$status.'.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not reach '.$this->name().': '.$e->getMessage()];
        }
    }

    public function verifyWebhookSignature(Request $request): array
    {
        $secret = $this->webhookSecret();
        $signature = $request->header('X-Provider-Signature') ?? $request->header('X-Signature') ?? $request->input('signature');

        if (config('security.webhooks.require_signature', true)) {
            if (empty($secret)) {
                return ['valid' => false, 'event_id' => null, 'error' => 'No webhook secret configured for '.$this->name().'.', 'http_status' => 401];
            }
            if (empty($signature)) {
                return ['valid' => false, 'event_id' => null, 'error' => 'Missing webhook signature.', 'http_status' => 401];
            }

            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expected, (string) $signature)) {
                return ['valid' => false, 'event_id' => null, 'error' => 'Invalid webhook signature.', 'http_status' => 401];
            }
        }

        $timestamp = $request->header('X-Timestamp') ?? $request->input('timestamp');
        if ($timestamp && abs(time() - (int) $timestamp) > (int) config('security.webhooks.tolerance_seconds', 300)) {
            return ['valid' => false, 'event_id' => null, 'error' => 'Webhook timestamp outside tolerance (replay protection).', 'http_status' => 401];
        }

        $payload = $request->all();
        $eventId = $payload['eventId'] ?? $payload['event_id'] ?? $payload['id'] ?? null;

        return ['valid' => true, 'event_id' => $eventId ? (string) $eventId : null, 'error' => null, 'http_status' => 200];
    }

    public function extractWebhookEvent(Request $request): array
    {
        $payload = $request->all();
        $data = $payload['data'] ?? $payload;

        return [
            'provider_transaction_id' => $data['transactionId'] ?? $data['paymentReference'] ?? $data['id'] ?? null,
            'order_reference' => $data['orderReference'] ?? $data['order_reference'] ?? $payload['orderReference'] ?? null,
            'status' => $this->normalizeStatus($data['status'] ?? $payload['status'] ?? null),
            'amount' => isset($data['collectedAmount']) ? (float) $data['collectedAmount'] : (isset($data['amount']) ? (float) $data['amount'] : null),
            'phone' => $data['phoneNumber'] ?? $data['paymentPhoneNumber'] ?? $data['msisdn'] ?? null,
            'raw' => $payload,
        ];
    }

    public function normalizeStatus(?string $providerStatus): string
    {
        return match (strtoupper((string) $providerStatus)) {
            'SUCCESS', 'SUCCESSFUL', 'COMPLETED', 'SETTLED' => 'SUCCESS',
            'FAILED', 'FAIL', 'DECLINED', 'CANCELLED', 'CANCELED', 'ERROR' => 'FAILED',
            'REVERSED', 'REFUNDED' => 'REVERSED',
            'PROCESSING', 'AUTHORIZED' => 'PROCESSING',
            default => 'PENDING',
        };
    }

    protected function directEndpoint(string $key): ?string
    {
        $base = rtrim((string) config("mobile_money.providers.{$this->code()}.direct.base_url", ''), '/');
        $path = (string) config("mobile_money.providers.{$this->code()}.direct.{$key}", '');

        return ($base !== '' && $path !== '') ? $base.'/'.ltrim($path, '/') : null;
    }

    /**
     * Auth headers built from the provider's OWN stored credentials.
     * Secrets are used here only — never logged, never returned.
     */
    protected function authHeaders(array $base = []): array
    {
        $credential = null;
        try {
            $credential = \App\Models\ProviderCredential::whereHas('provider', fn ($q) => $q->where('code', $this->code()))
                ->where('environment', 'live')->first();
        } catch (\Throwable) {
        }

        $type = config("mobile_money.providers.{$this->code()}.direct.auth_type", 'api-key');

        if ($credential && $type === 'bearer' && ! empty($credential->api_key)) {
            $base['Authorization'] = 'Bearer '.$credential->api_key;
        } elseif ($credential && ! empty($credential->api_key)) {
            $base['api-key'] = $credential->api_key;
            if (! empty($credential->client_id)) {
                $base['client-id'] = $credential->client_id;
            }
        }

        return $base;
    }

    /**
     * Webhook secret: stored credential first, provider env second.
     * The raw secret is only ever used here for HMAC — never logged,
     * never passed to views, never returned in API responses.
     */
    protected function webhookSecret(): ?string
    {
        try {
            $credential = \App\Models\ProviderCredential::whereHas('provider', fn ($q) => $q->where('code', $this->code()))
                ->where('environment', 'live')
                ->first();

            if ($credential && ! empty($credential->webhook_secret)) {
                return $credential->webhook_secret;
            }
        } catch (\Throwable) {
        }

        $envKey = config("mobile_money.providers.{$this->code()}.webhook_secret_env");

        return $envKey ? env($envKey) : null;
    }

    protected function notConfigured(string $status): array
    {
        return [
            'success' => false,
            'status' => $status,
            'provider_reference' => null,
            'amount' => null,
            'raw' => null,
            'error' => 'Direct '.$this->name().' integration is not configured yet. Set the API base URL on Configuration and store credentials on Credentials.',
            'http_status' => 503,
        ];
    }
}
