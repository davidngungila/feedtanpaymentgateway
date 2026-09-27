<?php

namespace App\Services\MobileMoney\Mixx;

use App\Models\Provider;
use App\Models\ProviderCredential;

/**
 * Mixx by Yas integration settings.
 *
 * Endpoints, credentials and network details MUST come from the current
 * Mixx/Yas onboarding agreement. The values below are the shape of that
 * agreement (base URL, paths, auth style, company name, limits) — never
 * hard-coded production secrets. Transport defaults to HTTPS; the exact
 * secure arrangement follows the partner agreement, not the old
 * document (which describes XML over plain HTTP).
 */
class MixxConfig
{
    public const PROVIDER = 'mixx';

    public function __construct(protected string $environment = 'live') {}

    public static function make(string $environment = 'live'): self
    {
        return new self($environment);
    }

    public function provider(): ?Provider
    {
        return Provider::where('code', self::PROVIDER)->first();
    }

    public function credential(): ?ProviderCredential
    {
        return ProviderCredential::whereHas('provider', fn ($q) => $q->where('code', self::PROVIDER))
            ->where('environment', $this->environment)
            ->first();
    }

    /**
     * Direct transport values: stored provider configuration wins,
     * file/env config is the fallback. This is what the provider
     * Configuration page writes.
     */
    protected function direct(string $key, mixed $default = null): mixed
    {
        $stored = $this->provider()?->config['direct'][$key] ?? null;
        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        return config("mobile_money.providers.mixx.direct.{$key}", $default);
    }

    public function baseUrl(): ?string
    {
        $url = rtrim((string) $this->direct('base_url', ''), '/');

        return $url !== '' ? $url : null;
    }

    public function initiatePath(): ?string
    {
        $v = $this->direct('initiate_path');

        return $v ? (string) $v : null;
    }

    public function statusPath(): ?string
    {
        $v = $this->direct('status_path');

        return $v ? (string) $v : null;
    }

    public function disbursePath(): ?string
    {
        $v = $this->direct('disburse_path');

        return $v ? (string) $v : null;
    }

    public function disburseStatusPath(): ?string
    {
        $v = $this->direct('disburse_status_path');

        return $v ? (string) $v : null;
    }

    public function testPath(): string
    {
        return (string) $this->direct('test_path', '/');
    }

    public function authType(): string
    {
        return (string) $this->direct('auth_type', 'api-key');
    }

    public function companyName(): string
    {
        return $this->provider()?->config['company_name']
            ?? config('mobile_money.providers.mixx.company_name', 'FEEDTAN PAY');
    }

    public function currency(): string
    {
        return config('mobile_money.currency', 'TZS');
    }

    public function minAmount(): float
    {
        return (float) config('mobile_money.min_amount', 500);
    }

    public function maxAmount(): float
    {
        return (float) config('mobile_money.max_amount', 5000000);
    }

    public function timeout(): int
    {
        return (int) config('mobile_money.providers.mixx.timeout', 30);
    }

    public function verifyAfterMinutes(): int
    {
        return (int) config('mobile_money.providers.mixx.verify_after_minutes', 5);
    }

    public function manualReviewAfterMinutes(): int
    {
        return (int) config('mobile_money.providers.mixx.manual_review_after_minutes', 60);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl() !== null && $this->initiatePath() !== null;
    }
}
