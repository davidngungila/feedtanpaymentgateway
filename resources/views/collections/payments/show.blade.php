@extends('layouts.app')

@section('title', 'Transaction '.$txn->txn_id)

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>{{ $txn->txn_id }}</h2>
        <p class="sub">Internal transaction · reference <span class="mono">{{ $txn->reference }}</span></p>
    </div>
    <div class="view-actions">
        <form method="POST" action="{{ route('collections.payments.refresh', $txn) }}" style="display:inline;">
            @csrf
            <button class="btn btn-ghost" type="submit">Refresh status</button>
        </form>
        <a class="btn btn-ghost" href="{{ route('collections.payments.index') }}">Back</a>
    </div>
</div>

@include('collections.partials.flash')

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Internal transaction</h3><span class="pill pill-{{ strtolower($txn->status) }}">{{ $txn->status }}</span></div>
    <div class="panel-body">
        <div class="detail-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
            <div><div class="dk">TXN ID</div><div class="dv mono">{{ $txn->txn_id }}</div></div>
            <div><div class="dk">Provider</div><div class="dv">{{ \App\Payments\ProviderRegistry::name($txn->provider) }}</div></div>
            <div><div class="dk">Provider transaction ID</div><div class="dv mono">••••••••{{ substr((string) $txn->payment?->provider_reference, -4) ?: '—' }}</div></div>
            <div><div class="dk">Customer</div><div class="dv">{{ $txn->customer?->name ?? '—' }}</div></div>
            <div><div class="dk">Phone number</div><div class="dv mono">{{ $txn->customer?->maskedPhone() ?? '—' }}</div></div>
            <div><div class="dk">Amount</div><div class="dv">TZS {{ number_format($txn->amount, 0) }}</div></div>
            <div><div class="dk">Currency</div><div class="dv">{{ $txn->currency }}</div></div>
            <div><div class="dk">Reference</div><div class="dv mono">{{ $txn->reference }}</div></div>
            <div><div class="dk">Provider fee</div><div class="dv">TZS {{ number_format($txn->provider_fee, 0) }}</div></div>
            <div><div class="dk">Net amount</div><div class="dv">TZS {{ number_format($txn->net_amount, 0) }}</div></div>
            <div><div class="dk">Initiated at</div><div class="dv">{{ $txn->initiated_at?->format('d M Y H:i') ?? '—' }}</div></div>
            <div><div class="dk">Completed at</div><div class="dv">{{ $txn->completed_at?->format('d M Y H:i') ?? '—' }}</div></div>
            <div><div class="dk">Webhook status</div><div class="dv"><span class="pill pill-neutral">{{ $txn->webhook_status }}</span></div></div>
            <div><div class="dk">Settlement status</div><div class="dv"><span class="pill pill-{{ strtolower($txn->settlement_status) }}">{{ str_replace('_', ' ', $txn->settlement_status) }}</span></div></div>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Status history</h3></div>
    <div class="panel-body">
        @forelse ($txn->payment?->histories ?? [] as $h)
            <div style="font-size:13px;padding:6px 0;border-bottom:1px solid var(--line);">{{ $h->from_status }} → <strong>{{ $h->to_status }}</strong> <span style="color:var(--ink-soft);">· {{ $h->source }} · {{ $h->created_at->format('d M H:i') }}</span></div>
        @empty
            <div class="empty-state">No status changes recorded yet.</div>
        @endforelse
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Corrections (refunds &amp; reversals)</h3><span class="link">SUCCESS records are never edited</span></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('collections.payments.refund.request', $txn->payment ?? 0) }}" class="filterbar" style="margin-bottom:12px;">
            @csrf
            <div class="field"><label style="font-size:11px;">Refund amount</label><input type="number" name="amount" min="1" max="{{ $txn->amount }}" required></div>
            <div class="field" style="flex:1;"><label style="font-size:11px;">Reason</label><input type="text" name="reason" required maxlength="500" placeholder="Reason for refund"></div>
            <div><button class="btn btn-ghost" type="submit">Request refund</button></div>
        </form>
        <form method="POST" action="{{ route('collections.payments.reverse', $txn->payment ?? 0) }}" class="filterbar" onsubmit="return confirm('Record a reversal? The original transaction stays immutable.');">
            @csrf
            <div class="field" style="flex:1;"><label style="font-size:11px;">Reason</label><input type="text" name="reason" required maxlength="500" placeholder="Reason for reversal"></div>
            <div><button class="btn btn-danger" type="submit">Record reversal</button></div>
        </form>
    </div>
</div>
@endsection
