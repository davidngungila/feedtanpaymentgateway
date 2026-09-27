<?php

namespace App\Support\Security;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP two-factor authentication (RFC 6238 authenticator apps).
 *
 * - Secrets are encrypted at rest (users.two_factor_secret).
 * - Recovery codes are stored hashed and burn after a single use.
 * - Nothing sensitive is ever logged (see Crypto::scrub).
 */
class TwoFactor
{
    protected static function engine(): Google2FA
    {
        $engine = new Google2FA();
        $engine->setWindow(1);

        return $engine;
    }

    public static function generateSecret(): string
    {
        return static::engine()->generateSecretKey();
    }

    public static function otpauthUri(string $secret, string $email, string $issuer = 'Feedtan Payments'): string
    {
        return static::engine()->getQRCodeUrl($issuer, $email, $secret);
    }

    public static function verify(User $user, string $code): bool
    {
        if (empty($user->two_factor_secret)) {
            return false;
        }

        try {
            $secret = $user->two_factor_secret;
        } catch (\Throwable) {
            return false;
        }

        try {
            return static::engine()->verifyKey($secret, preg_replace('/\s+/', '', $code));
        } catch (\Throwable) {
            return false;
        }
    }

    public static function verifyPending(string $secret, string $code): bool
    {
        try {
            return static::engine()->verifyKey($secret, preg_replace('/\s+/', '', $code));
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return string[] 8 printable codes, each XXXX-XXXX-XXXX-XXXX
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $raw = strtoupper(substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(12))), 0, 16));
            $codes[] = implode('-', str_split($raw, 4));
        }

        return $codes;
    }

    public static function hashRecoveryCode(string $code): string
    {
        return hash('sha256', strtoupper(str_replace(['-', ' '], '', $code)));
    }

    /**
     * Check a recovery code and burn it (single use). Returns true on success.
     */
    public static function consumeRecoveryCode(User $user, string $code): bool
    {
        $hashes = $user->two_factor_recovery_codes ?? [];
        if (! is_array($hashes) || $hashes === []) {
            return false;
        }

        $wanted = static::hashRecoveryCode($code);
        foreach ($hashes as $i => $stored) {
            if (hash_equals((string) $stored, $wanted)) {
                unset($hashes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($hashes)])->save();

                return true;
            }
        }

        return false;
    }

    public static function remainingRecoveryCodes(User $user): int
    {
        $hashes = $user->two_factor_recovery_codes ?? [];

        return is_array($hashes) ? count($hashes) : 0;
    }
}
