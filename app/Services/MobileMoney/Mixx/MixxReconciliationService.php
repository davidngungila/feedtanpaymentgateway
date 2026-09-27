<?php

namespace App\Services\MobileMoney\Mixx;

use App\Models\ProviderTransaction;
use App\Support\Security\Audit;
use Illuminate\Support\Carbon;

/**
 * Compares FEEDTAN records against Mixx state and classifies every
 * discrepancy. Result codes: MATCHED | MISSING_PROVIDER |
 * MISSING_FEEDTAN | AMOUNT_MISMATCH | REFERENCE_MISMATCH |
 * STATUS_MISMATCH | DUPLICATE | UNKNOWN
 */
class MixxReconciliationService
{
    /**
     * @return array{summary:array<string,int>,rows:array}
     */
    public function run(Carbon $from, Carbon $to): array
    {
        $txns = ProviderTransaction::with('payment')
            ->where('provider', 'mixx')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        // Duplicate provider TXNIDs inside the window.
        $dupHashes = $txns->filter(fn ($t) => $t->provider_transaction_id_hash)
            ->groupBy('provider_transaction_id_hash')
            ->filter(fn ($g) => $g->count() > 1)
            ->keys()
            ->flip();

        $rows = [];
        $summary = [];
        foreach ($txns as $txn) {
            $result = $this->classify($txn, isset($dupHashes[$txn->provider_transaction_id_hash]));
            $summary[$result] = ($summary[$result] ?? 0) + 1;

            if ($result !== 'MATCHED' && $txn->exception_type !== $result) {
                $txn->update(['exception_type' => $result]);
            }

            $rows[] = [
                'txn_id' => $txn->txn_id,
                'internal_reference' => $txn->internal_reference,
                'mixx_txnid' => $this->safeDecrypt($txn, 'provider_transaction_id_encrypted'),
                'mixx_refid' => $this->safeDecrypt($txn, 'provider_refid_encrypted'),
                'amount' => (float) $txn->amount,
                'expected' => $txn->expected_amount !== null ? (float) $txn->expected_amount : null,
                'status' => $txn->status,
                'result' => $result,
                'created_at' => $txn->created_at,
            ];
        }

        Audit::log('mixx.reconciliation.run', 'reconciliation', [
            'from' => $from->toDateTimeString(), 'to' => $to->toDateTimeString(), 'summary' => $summary,
        ]);

        return ['summary' => $summary, 'rows' => $rows];
    }

    protected function classify(ProviderTransaction $txn, bool $isDuplicate): string
    {
        if ($isDuplicate) {
            return 'DUPLICATE';
        }
        if (! $txn->payment_id) {
            return 'MISSING_FEEDTAN';
        }
        if ($txn->status === 'UNKNOWN' || $txn->status === 'HOLD') {
            return 'UNKNOWN';
        }
        if ($txn->expected_amount !== null && abs((float) $txn->amount - (float) $txn->expected_amount) >= 0.005) {
            return 'AMOUNT_MISMATCH';
        }
        if ($txn->status === 'SUCCESS' && empty($txn->provider_refid_hash)) {
            return 'REFERENCE_MISMATCH';
        }
        if ($txn->status === 'SUCCESS' && $txn->payment && $txn->payment->status !== 'completed') {
            return 'STATUS_MISMATCH';
        }
        if ($txn->status === 'SUCCESS' && ! empty($txn->provider_refid_hash)) {
            return 'MATCHED';
        }
        if (in_array($txn->status, ['PENDING', 'PROCESSING'], true)) {
            return 'MISSING_PROVIDER';
        }

        return $txn->status === 'FAILED' ? 'STATUS_MISMATCH' : 'UNKNOWN';
    }

    protected function safeDecrypt(ProviderTransaction $txn, string $attr): ?string
    {
        try {
            return $txn->{$attr} ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
