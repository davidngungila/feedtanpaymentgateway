<?php

namespace App\Http\Controllers;

use App\Models\ProviderTransaction;
use App\Models\Settlement;
use App\Models\SettlementItem;
use App\Support\Security\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index()
    {
        $settlements = Settlement::withCount('items')->latest()->paginate(15);

        return view('settlements.index', compact('settlements'));
    }

    public function create(Request $request)
    {
        $provider = $request->query('provider', 'all');
        $eligible = ProviderTransaction::where('status', 'SUCCESS')
            ->where('settlement_status', 'eligible')
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->get();

        return view('settlements.create', [
            'eligible' => $eligible,
            'provider' => $provider,
            'totals' => [
                'amount' => (float) $eligible->sum('amount'),
                'fee' => (float) $eligible->sum('provider_fee'),
                'net' => (float) $eligible->sum('net_amount'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provider' => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $eligible = ProviderTransaction::where('status', 'SUCCESS')
            ->where('settlement_status', 'eligible')
            ->whereNull('exception_type')
            ->when(! empty($data['provider']) && $data['provider'] !== 'all', fn ($q) => $q->where('provider', $data['provider']))
            ->lockForUpdate()
            ->get();

        if ($eligible->isEmpty()) {
            return back()->withErrors(['provider' => 'No eligible transactions to settle.']);
        }

        $settlement = DB::transaction(function () use ($eligible, $data) {
            $set = Settlement::create([
                'reference' => 'STL'.now()->format('YmdHis').random_int(100, 999),
                'provider' => (! empty($data['provider']) && $data['provider'] !== 'all') ? $data['provider'] : null,
                'period_start' => $data['period_start'] ?? null,
                'period_end' => $data['period_end'] ?? null,
                'total_amount' => (float) $eligible->sum('amount'),
                'total_fee' => (float) $eligible->sum('provider_fee'),
                'net_amount' => (float) $eligible->sum('net_amount'),
                'status' => 'pending_approval',
            ]);

            foreach ($eligible as $txn) {
                SettlementItem::create([
                    'settlement_id' => $set->id,
                    'provider_transaction_id' => $txn->id,
                    'amount' => $txn->amount,
                    'fee' => $txn->provider_fee,
                    'net_amount' => $txn->net_amount,
                    'status' => 'batched',
                ]);
                $txn->update(['settlement_status' => 'batched']);
            }

            return $set;
        });

        Audit::log('settlement.created', $settlement, ['items' => $eligible->count(), 'net' => $settlement->net_amount]);

        return redirect()->route('settlements.show', $settlement)->with('success', 'Settlement batch '.$settlement->reference.' created with '.$eligible->count().' items.');
    }

    public function show(Settlement $settlement)
    {
        $settlement->load(['items.transaction.customer', 'approver']);

        return view('settlements.show', compact('settlement'));
    }

    public function approve(Request $request, Settlement $settlement)
    {
        if ($settlement->status !== 'pending_approval') {
            return back()->withErrors(['status' => 'Only batches pending approval can be approved.']);
        }

        DB::transaction(function () use ($settlement, $request) {
            $settlement->update(['status' => 'approved', 'approved_by' => $request->user()->id]);
            $settlement->items()->update(['status' => 'settled']);
            ProviderTransaction::whereIn('id', $settlement->items()->pluck('provider_transaction_id'))->update(['settlement_status' => 'settled']);
        });

        Audit::log('settlement.approved', $settlement, ['net' => $settlement->net_amount]);

        return back()->with('success', 'Settlement '.$settlement->reference.' approved.');
    }

    public function transactions(Request $request)
    {
        $txns = ProviderTransaction::with(['customer','payment'])
            ->whereIn('settlement_status', ['eligible', 'batched', 'settled'])
            ->latest()->paginate(20);

        return view('settlements.transactions', ['txns' => $txns]);
    }

    public function reports()
    {
        $settlements = Settlement::orderByDesc('created_at')->limit(12)->get();

        return view('settlements.reports', compact('settlements'));
    }
}
