<?php

namespace App\Services\MobileMoney\Mixx;

use App\Models\ProviderApiLog;
use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Mixx by Yas health snapshot for the dashboard:
 * API / connection / auth / collections / disbursement plus
 * pending / unknown / failed counters and last response time.
 */
class MixxHealthService
{
    public function snapshot(): array
    {
        $config = MixxConfig::make();
        $today = today();

        $api = $config->isConfigured();
        $credential = (bool) $config->credential();

        $lastLog = ProviderApiLog::where('provider', 'mixx')->latest()->first();
        $lastResponse = $lastLog?->created_at;
        $recentFailures = ProviderApiLog::where('provider', 'mixx')
            ->where('created_at', '>=', now()->subHour())
            ->where('success', false)
            ->count();

        $ok = fn (?bool $v) => $v === true;

        return [
            'api' => $ok($api),
            'connection' => $ok($api),
            'authentication' => $ok($api && $credential),
            'collections' => $ok($api),
            'disbursement' => $ok($api && $config->disbursePath()),
            'last_response' => $lastResponse,
            'recent_failures_1h' => $recentFailures,
            'pending' => ProviderTransaction::where('provider', 'mixx')->whereIn('status', ['PENDING', 'PROCESSING'])->count(),
            'unknown' => ProviderTransaction::where('provider', 'mixx')->whereIn('status', ['UNKNOWN', 'HOLD'])->count(),
            'failed' => ProviderTransaction::where('provider', 'mixx')->whereDate('created_at', $today)->where('status', 'FAILED')->count(),
            'queue_failed' => $this->failedJobs(),
            'scheduler_last_run' => $this->schedulerLastRun(),
        ];
    }

    protected function schedulerLastRun(): ?\Illuminate\Support\Carbon
    {
        try {
            $raw = Cache::get('scheduler.last_run');
            if ($raw instanceof \Illuminate\Support\Carbon) {
                return $raw;
            }
            if (is_string($raw) && $raw !== '') {
                return \Illuminate\Support\Carbon::parse($raw);
            }
        } catch (\Throwable) {
        }

        return null;
    }

    protected function failedJobs(): ?int
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('failed_jobs')) {
                return null;
            }

            return (int) DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            return null;
        }
    }
}
