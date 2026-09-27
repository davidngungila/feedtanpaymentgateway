<?php

namespace App\Http\Controllers;

use App\Models\ProviderTransaction;
use App\Payments\ProviderRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    protected function range(Request $request): array
    {
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->subDays(29)->startOfDay();

        return [$from, $to];
    }

    public function daily(Request $request)
    {
        [$from, $to] = $this->range($request);
        $provider = $request->query('provider', 'all');

        $rows = ProviderTransaction::whereBetween('created_at', [$from, $to])
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count, SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as success_volume, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count, SUM(provider_fee) as fees', ['SUCCESS', 'SUCCESS'])
            ->groupBy('day')->orderBy('day')->get();

        return view('reports.daily', ['rows' => $rows, 'from' => $from, 'to' => $to, 'provider' => $provider, 'providers' => ProviderRegistry::meta()]);
    }

    public function providers(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = ProviderTransaction::whereBetween('created_at', [$from, $to])
            ->selectRaw('provider, COUNT(*) as count, SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as success_volume, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed_count, SUM(provider_fee) as fees', ['SUCCESS', 'SUCCESS', 'FAILED'])
            ->groupBy('provider')->get()->keyBy('provider');

        return view('reports.providers', ['rows' => $rows, 'from' => $from, 'to' => $to, 'providers' => ProviderRegistry::meta()]);
    }

    public function transactions(Request $request)
    {
        [$from, $to] = $this->range($request);
        $status = $request->query('status', 'all');
        $provider = $request->query('provider', 'all');

        $txns = ProviderTransaction::with(['customer','payment'])
            ->whereBetween('created_at', [$from, $to])
            ->when($status !== 'all', fn ($q) => $q->where('status', strtoupper($status)))
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->latest()->paginate(20)->withQueryString();

        return view('reports.transactions', ['txns' => $txns, 'from' => $from, 'to' => $to, 'status' => $status, 'provider' => $provider, 'providers' => ProviderRegistry::meta()]);
    }

    public function fees(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = ProviderTransaction::whereBetween('created_at', [$from, $to])
            ->selectRaw('provider, SUM(amount) as gross, SUM(provider_fee) as fees, SUM(net_amount) as net, COUNT(*) as count')
            ->groupBy('provider')->get();

        return view('reports.fees', ['rows' => $rows, 'from' => $from, 'to' => $to, 'providers' => ProviderRegistry::meta()]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->range($request);
        $provider = $request->query('provider', 'all');
        $status = $request->query('status', 'all');

        $txns = ProviderTransaction::whereBetween('created_at', [$from, $to])
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->when($status !== 'all', fn ($q) => $q->where('status', strtoupper($status)))
            ->orderBy('created_at')->cursor();

        $filename = 'transactions-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($txns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['txn_id', 'provider', 'reference', 'amount', 'currency', 'provider_fee', 'net_amount', 'status', 'webhook_status', 'settlement_status', 'initiated_at', 'completed_at']);
            foreach ($txns as $t) {
                fputcsv($out, [$t->txn_id, $t->provider, $t->reference, $t->amount, $t->currency, $t->provider_fee, $t->net_amount, $t->status, $t->webhook_status, $t->settlement_status, $t->initiated_at, $t->completed_at]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
