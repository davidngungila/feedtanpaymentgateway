<?php

namespace App\Http\Controllers\MobileMoney;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use App\Models\ProviderErrorCode;
use App\Models\ProviderTransaction;
use App\Services\MobileMoney\Mixx\MixxConfig;
use App\Services\MobileMoney\Mixx\MixxHealthService;
use App\Services\MobileMoney\Mixx\MixxReconciliationService;
use App\Services\MobileMoney\Mixx\MixxTransactionService;
use App\Services\MobileMoney\Mixx\MixxValidator;
use App\Support\Security\Audit;
use App\Support\Security\Crypto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class MixxController extends Controller
{
    public function dashboard(Request $request)
    {
        $today = today();
        $base = ProviderTransaction::where('provider', 'mixx');

        $q = (clone $base)->with('customer')->latest();
        if ($request->filled('status') && $request->status !== 'all') {
            $map = ['success' => ['SUCCESS'], 'pending' => ['PENDING', 'PROCESSING'], 'failed' => ['FAILED'], 'unknown' => ['UNKNOWN', 'HOLD'], 'hold' => ['HOLD']];
            $q->whereIn('status', $map[strtolower($request->status)] ?? [strtoupper($request->status)]);
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('amount')) {
            $q->where('amount', $request->amount);
        }
        if ($request->filled('customer')) {
            $q->whereHas('customer', fn ($w) => $w->where('name', 'like', '%'.$request->customer.'%'));
        }
        if ($request->filled('msisdn')) {
            $digits = preg_replace('/[^0-9]/', '', $request->msisdn);
            $q->where('phone_hash', Crypto::blindIndex('customer', $digits));
        }
        foreach (['reference' => 'reference', 'txn_id' => 'txn_id', 'internal_reference' => 'internal_reference', 'customer_reference' => 'customer_reference_id', 'error' => 'exception_type'] as $in => $col) {
            if ($request->filled($in)) {
                $q->where($col, 'like', '%'.$request->input($in).'%');
            }
        }
        if ($request->filled('txnid')) {
            $q->where('provider_transaction_id_hash', Crypto::blindIndex('webhook', 'mixx:'.$request->txnid));
        }
        if ($request->filled('refid')) {
            $q->where('provider_refid_hash', Crypto::blindIndex('webhook', 'mixx:ref:'.$request->refid));
        }

        $transactions = $q->paginate(15)->withQueryString();

        return view('mixx.dashboard', [
            'health' => (new MixxHealthService())->snapshot(),
            'todayVolume' => (float) (clone $base)->whereDate('created_at', $today)->where('status', 'SUCCESS')->sum('amount'),
            'counts' => [
                'SUCCESS' => (clone $base)->where('status', 'SUCCESS')->count(),
                'PENDING' => (clone $base)->whereIn('status', ['PENDING', 'PROCESSING'])->count(),
                'FAILED' => (clone $base)->where('status', 'FAILED')->count(),
                'UNKNOWN' => (clone $base)->whereIn('status', ['UNKNOWN', 'HOLD'])->count(),
            ],
            'transactions' => $transactions,
            'filters' => $request->only(['status', 'from', 'to', 'amount', 'customer', 'msisdn', 'reference', 'txn_id', 'internal_reference', 'customer_reference', 'txnid', 'refid', 'error']),
        ]);
    }

    public function createPayment()
    {
        return view('mixx.create');
    }

    public function storePayment(Request $request)
    {
        $data = $request->validate([
            'msisdn' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1',
            'customer_name' => 'required|string|min:2|max:100',
            'customer_reference' => 'nullable|string|max:60',
            'sender_name' => 'nullable|string|max:100',
        ]);

        $result = MixxTransactionService::make()->collect([
            'msisdn' => $data['msisdn'],
            'amount' => $data['amount'],
            'customer_name' => $data['customer_name'],
            'customer_reference' => $data['customer_reference'] ?? null,
            'sender_name' => $data['sender_name'] ?? null,
        ], $request->user());

        if (! ($result['success'] ?? false) && ! isset($result['transaction'])) {
            return back()->withErrors(['msisdn' => $result['error'] ?? 'Collection failed.'])->withInput();
        }

        return redirect()->route('mixx.transactions.show', $result['transaction'])
            ->with('success', ($result['success'] ?? false) ? 'Collection successful.' : ($result['error'] ?? 'Recorded — verification pending.'));
    }

    public function showTransaction(ProviderTransaction $transaction)
    {
        abort_unless($transaction->provider === 'mixx', 404);
        $transaction->load(['customer', 'payment.attempts']);

        try {
            $apiLogs = \App\Models\ProviderApiLog::where('provider', 'mixx')
                ->where(function ($q) use ($transaction) {
                    $q->where('transaction_id', $transaction->internal_reference)
                        ->orWhere('transaction_id', $transaction->reference);
                })->latest()->limit(10)->get();
        } catch (\Throwable) {
            $apiLogs = collect();
        }

        return view('mixx.show', ['txn' => $transaction, 'apiLogs' => $apiLogs]);
    }

    public function verifyTransaction(ProviderTransaction $transaction)
    {
        abort_unless($transaction->provider === 'mixx', 404);
        $result = MixxTransactionService::make()->verify($transaction);
        Audit::log('mixx.manual.verify', $transaction, ['status' => $result['status'] ?? null]);

        return back()->with('success', 'Verification finished: '.($result['status'] ?? 'UNKNOWN'));
    }

    public function disbursements()
    {
        $items = Disbursement::where('provider', 'mixx')->latest()->paginate(15);

        return view('mixx.disbursements.index', ['items' => $items]);
    }

    public function createDisbursement()
    {
        return view('mixx.disbursements.create');
    }

    public function storeDisbursement(Request $request)
    {
        $data = $request->validate([
            'msisdn' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1',
            'pin' => 'required|string|max:50',
            'sender_name' => 'nullable|string|max:100',
            'brand_id' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'reference' => 'nullable|string|max:40',
        ]);

        // The PIN is used for this single request only — never persisted.
        $result = MixxTransactionService::make()->disburse([
            'msisdn' => $data['msisdn'],
            'amount' => $data['amount'],
            'pin' => $data['pin'],
            'sender_name' => $data['sender_name'] ?? 'FEEDTAN PAY',
            'brand_id' => $data['brand_id'] ?? null,
            'language' => $data['language'] ?? 'sw',
            'reference' => $data['reference'] ?? null,
        ], $request->user());

        if (! ($result['success'] ?? false) && ! isset($result['disbursement'])) {
            return back()->withErrors(['msisdn' => $result['error'] ?? 'Disbursement failed.'])->withInput();
        }

        return redirect()->route('mixx.disbursements.show', $result['disbursement'])
            ->with('success', ($result['success'] ?? false) ? 'Disbursement successful.' : ($result['error'] ?? 'Recorded — see status.'));
    }

    public function showDisbursement(Disbursement $disbursement)
    {
        abort_unless($disbursement->provider === 'mixx', 404);

        return view('mixx.disbursements.show', ['dis' => $disbursement]);
    }

    public function errorCodes()
    {
        $codes = ProviderErrorCode::where('provider', 'mixx')->orderBy('code')->paginate(30);

        return view('mixx.error-codes', ['codes' => $codes]);
    }

    public function reconciliation(Request $request)
    {
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subDays(7)->startOfDay();

        $result = (new MixxReconciliationService())->run($from, $to);

        return view('mixx.recon', ['from' => $from, 'to' => $to, 'summary' => $result['summary'], 'rows' => $result['rows']]);
    }
}
