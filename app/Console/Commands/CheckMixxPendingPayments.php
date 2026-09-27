<?php

namespace App\Console\Commands;

use App\Models\ProviderTransaction;
use App\Services\MobileMoney\Mixx\MixxConfig;
use App\Services\MobileMoney\Mixx\MixxTransactionService;
use App\Support\Security\Audit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckMixxPendingPayments extends Command
{
    protected $signature = 'mixx:check-pending {--limit=50}';

    protected $description = 'Verify Mixx PENDING / UNKNOWN / HOLD collections (never blind-retries the charge)';

    public function handle(): int
    {
        Cache::put('scheduler.last_run', now()->toDateTimeString(), 3600);

        $config = MixxConfig::make();
        $svc = MixxTransactionService::make();
        $cutoff = now()->subMinutes($config->verifyAfterMinutes());
        $manualAfter = now()->subMinutes($config->manualReviewAfterMinutes());

        $txns = ProviderTransaction::with('payment')
            ->where('provider', 'mixx')
            ->whereIn('status', ['PENDING', 'PROCESSING', 'UNKNOWN', 'HOLD'])
            ->where('created_at', '<=', $cutoff)
            ->orderBy('created_at')
            ->limit((int) $this->option('limit'))
            ->get();

        $stats = ['checked' => 0, 'resolved' => 0, 'still_open' => 0, 'manual' => 0];
        foreach ($txns as $txn) {
            if (in_array($txn->status, ['UNKNOWN', 'HOLD'], true) && $txn->created_at <= $manualAfter) {
                $txn->update(['exception_type' => 'MANUAL_REVIEW']);
                Audit::log('mixx.verify.escalated', $txn, ['status' => $txn->status]);
                $stats['manual']++;
                continue;
            }

            $stats['checked']++;
            $result = $svc->verify($txn);
            if (in_array($result['status'] ?? '', ['SUCCESS', 'FAILED'], true)) {
                $stats['resolved']++;
            } else {
                $stats['still_open']++;
            }
        }

        Audit::log('mixx.pending.checked', 'scheduler', $stats);
        $this->info('Mixx pending check: '.json_encode($stats));

        return self::SUCCESS;
    }
}
