<?php

namespace App\Services\MobileMoney\Mixx;

use App\Payments\PaymentEngine;
use InvalidArgumentException;

/**
 * Pre-flight (before send) and post-flight (after response) guards.
 */
class MixxValidator
{
    public static function msisdn(string $input): string
    {
        $digits = PaymentEngine::normalizePhone($input);
        if ($digits === '' || ! preg_match('/^255\d{9}$/', $digits)) {
            throw new InvalidArgumentException('Invalid MSISDN. Expected Tanzanian mobile number, e.g. 2557XXXXXXXX.');
        }

        return $digits;
    }

    public static function amount(mixed $amount, MixxConfig $config): float
    {
        if (! is_numeric($amount) || (float) $amount <= 0) {
            throw new InvalidArgumentException('Amount must be a number greater than 0.');
        }

        $value = round((float) $amount, 2);

        if ($config->currency() !== 'TZS') {
            throw new InvalidArgumentException('Only TZS is supported for Mixx collections.');
        }
        if ($value < $config->minAmount() || $value > $config->maxAmount()) {
            throw new InvalidArgumentException(
                'Amount must be between TZS '.number_format($config->minAmount(), 0).' and TZS '.number_format($config->maxAmount(), 0).'.'
            );
        }

        return $value;
    }

    /**
     * Requested amount must equal the provider-confirmed amount,
     * otherwise the transaction is flagged AMOUNT_MISMATCH and is
     * NOT treated as a clean success.
     */
    public static function confirmAmount(float $requested, ?float $confirmed): bool
    {
        if ($confirmed === null) {
            return false;
        }

        return abs($requested - $confirmed) < 0.005;
    }

    public static function last4(string $msisdn): string
    {
        $digits = preg_replace('/[^0-9]/', '', $msisdn);

        return substr($digits, -4);
    }
}
