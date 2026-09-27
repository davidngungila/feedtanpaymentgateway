<?php

namespace App\Support\Security;

/**
 * Application-level crypto helpers.
 *
 * - Encrypt: Laravel Crypt (APP_KEY) for secrets at rest.
 * - Blind index: purpose-separated HMAC so values stay searchable
 *   (phone lookup, provider reference idempotency, API key auth)
 *   without storing them in plaintext.
 * - Mask: safe display (logs, UI, API) — never the raw value.
 */
class Crypto
{
    /**
     * Deterministic, purpose-bound hash for lookups.
     */
    public static function blindIndex(string $purpose, ?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $key = config("security.keys.{$purpose}");
        if (empty($key)) {
            $key = hash_hmac('sha256', "blind-index:{$purpose}", (string) config('app.key'));
        }

        return hash_hmac('sha256', mb_strtolower(trim($value)).'|'.$purpose, (string) $key);
    }

    public static function payloadHash(mixed $payload): string
    {
        $raw = is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', (string) $raw);
    }

    /**
     * 255712345678 -> 255******678
     */
    public static function maskPhone(?string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);
        if ($digits === '') {
            return '—';
        }

        $keepStart = (int) config('security.mask.phone_keep_start', 3);
        $keepEnd = (int) config('security.mask.phone_keep_end', 3);

        if (strlen($digits) <= $keepStart + $keepEnd) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, $keepStart)
            .str_repeat('*', strlen($digits) - $keepStart - $keepEnd)
            .substr($digits, -$keepEnd);
    }

    /**
     * Show only the last few chars: ••••••••8392
     */
    public static function maskSecret(?string $secret, int $visible = 4): string
    {
        if ($secret === null || $secret === '') {
            return '••••';
        }

        $visible = max(0, (int) $visible);
        $tail = $visible > 0 ? substr($secret, -$visible) : '';

        return '••••••••'.($tail !== '' ? $tail : '');
    }

    public static function maskApiKey(?string $prefix, ?string $key = null): string
    {
        $tail = $key ? substr($key, -4) : '••••';

        return ($prefix ?: 'pk').'_••••••••'.$tail;
    }

    /**
     * Strip sensitive values from arrays before logging / auditing.
     */
    public static function scrub(array $data): array
    {
        $sensitive = ['password', 'passwd', 'secret', 'client_secret', 'api_key', 'apikey', 'token', 'authorization', 'otp', 'pin', 'private_key', 'webhook_secret', 'recovery_code'];

        $out = [];
        foreach ($data as $k => $v) {
            $lk = strtolower((string) $k);
            $hit = false;
            foreach ($sensitive as $s) {
                if (str_contains($lk, $s)) {
                    $hit = true;
                    break;
                }
            }

            if ($hit) {
                $out[$k] = '***REDACTED***';
            } elseif ($lk === 'phone' || str_ends_with($lk, '_phone') || $lk === 'phone_number') {
                $out[$k] = self::maskPhone(is_scalar($v) ? (string) $v : null);
            } elseif (is_array($v)) {
                $out[$k] = self::scrub($v);
            } else {
                $out[$k] = $v;
            }
        }

        return $out;
    }
}
