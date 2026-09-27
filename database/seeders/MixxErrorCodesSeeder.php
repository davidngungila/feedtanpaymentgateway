<?php

namespace Database\Seeders;

use App\Models\ProviderErrorCode;
use Illuminate\Database\Seeder;

class MixxErrorCodesSeeder extends Seeder
{
    public function run(): void
    {
        // W2A collection codes from the integration document.
        $w2a = [
            ['error000', 'Success', 'SUCCESS', false, false],
            ['error001', 'Service unavailable', 'FAILED', true, false],
            ['error010', 'Invalid customer reference', 'FAILED', false, false],
            ['error011', 'Customer reference locked', 'FAILED', false, true],
            ['error012', 'Invalid amount', 'FAILED', false, false],
            ['error013', 'Insufficient amount', 'FAILED', false, false],
            ['error014', 'Amount too high', 'FAILED', false, false],
            ['error015', 'Amount too low', 'FAILED', false, false],
            ['error016', 'Invalid payment', 'FAILED', false, true],
            ['error100', 'General error', 'FAILED', false, true],
            ['error111', 'Retry / no response — verify, never auto-fail', 'UNKNOWN', false, false],
        ];

        // A2W disbursement TXNSTATUS values from the document.
        $a2w = [
            ['200', 'Success', 'SUCCESS', false, false],
            ['0', 'Success', 'SUCCESS', false, false],
            ['100', 'Generic processing error — HOLD, verify first', 'HOLD', false, false],
            ['00026', 'PIN expired', 'FAILED', false, true],
            ['00031', 'Amount exceeds network allowance', 'FAILED', false, false],
            ['00042', 'Amount not in required multiple', 'FAILED', false, false],
            ['00317', 'Recipient account barred', 'FAILED', false, true],
            ['00410', 'Amount exceeds maximum', 'FAILED', false, false],
            ['02117', 'Sender account barred', 'FAILED', false, true],
            ['60014', 'Daily payer limit reached', 'FAILED', false, true],
            ['60017', 'Below minimum transaction value', 'FAILED', false, false],
            ['60018', 'Above maximum transaction value', 'FAILED', false, false],
            ['60019', 'Would violate minimum balance', 'FAILED', false, false],
            ['60021', 'Payee daily transaction limit reached', 'FAILED', false, true],
            ['60024', 'Maximum daily transaction value reached', 'FAILED', false, true],
            ['60028', 'Recipient maximum transaction value exceeded', 'FAILED', false, false],
            ['60030', 'Payee maximum balance exceeded', 'FAILED', false, true],
            ['60074', 'Payee transfer profile not defined', 'FAILED', false, true],
        ];

        foreach (array_merge($w2a, $a2w) as [$code, $desc, $status, $retryable, $manual]) {
            ProviderErrorCode::updateOrCreate(
                ['provider' => 'mixx', 'code' => $code],
                ['description' => $desc, 'internal_status' => $status, 'retryable' => $retryable, 'requires_manual_review' => $manual]
            );
        }
    }
}
