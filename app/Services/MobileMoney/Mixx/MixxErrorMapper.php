<?php

namespace App\Services\MobileMoney\Mixx;

use App\Models\ProviderErrorCode;

/**
 * Maps provider codes to internal statuses — never hard-coded
 * if/else chains scattered through the app.
 *
 * Canonical statuses: SUCCESS | FAILED | UNKNOWN | HOLD | PROCESSING | PENDING
 *
 * error111 (retry/no-response) and TXNSTATUS 100 (generic processing
 * error) NEVER become FAILED directly: they go to UNKNOWN / HOLD so
 * verification runs before any further financial move.
 */
class MixxErrorMapper
{
    /**
     * Built-in fallback used when the DB table has no row
     * (admins can tune every code on the Error Codes page).
     *
     * @return array{internal_status:string,retryable:bool,requires_manual_review:bool,description:string}
     */
    public static function fallback(string $code): array
    {
        return match (strtolower(trim($code))) {
            'error000', '200', '0' => ['internal_status' => 'SUCCESS', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Success'],
            'error111' => ['internal_status' => 'UNKNOWN', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Retry / no response — verify before any further action'],
            '100' => ['internal_status' => 'HOLD', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Generic processing error — HOLD, verify, then SUCCESS / FAILED / MANUAL REVIEW'],
            'error001' => ['internal_status' => 'FAILED', 'retryable' => true, 'requires_manual_review' => false, 'description' => 'Service unavailable'],
            'error010' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Invalid customer reference'],
            'error011' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => true, 'description' => 'Customer reference locked'],
            'error012' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Invalid amount'],
            'error013' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Insufficient amount'],
            'error014' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Amount too high'],
            'error015' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => false, 'description' => 'Amount too low'],
            'error016' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => true, 'description' => 'Invalid payment'],
            'error100' => ['internal_status' => 'FAILED', 'retryable' => false, 'requires_manual_review' => true, 'description' => 'General error'],
            default => ['internal_status' => 'UNKNOWN', 'retryable' => false, 'requires_manual_review' => true, 'description' => 'Unmapped provider code — manual review'],
        };
    }

    /**
     * @return array{internal_status:string,retryable:bool,requires_manual_review:bool,description:string,code:string}
     */
    public static function map(string $provider, ?string $code): array
    {
        $code = (string) ($code ?? '');
        if ($code !== '') {
            try {
                $row = ProviderErrorCode::where('provider', $provider)->where('code', $code)->first();
                if ($row) {
                    return [
                        'internal_status' => $row->internal_status,
                        'retryable' => (bool) $row->retryable,
                        'requires_manual_review' => (bool) $row->requires_manual_review,
                        'description' => $row->description,
                        'code' => $code,
                    ];
                }
            } catch (\Throwable) {
            }
        }

        return self::fallback($code) + ['code' => $code];
    }

    /**
     * A2W TXNSTATUS values from the document.
     *
     * @return array{internal_status:string,retryable:bool,requires_manual_review:bool,description:string,code:string}
     */
    public static function mapTxnStatus(?string $status): array
    {
        $status = (string) ($status ?? '');

        try {
            $row = ProviderErrorCode::where('provider', 'mixx')->where('code', $status)->first();
            if ($row) {
                return [
                    'internal_status' => $row->internal_status,
                    'retryable' => (bool) $row->retryable,
                    'requires_manual_review' => (bool) $row->requires_manual_review,
                    'description' => $row->description,
                    'code' => $status,
                ];
            }
        } catch (\Throwable) {
        }

        return self::fallback($status) + ['code' => $status];
    }
}
