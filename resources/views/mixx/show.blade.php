@extends('layouts.app')

@section('title', 'Mixx transaction '.$txn->txn_id)

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>{{ $txn->internal_reference ?? $txn->txn_id }}</h2><p class="sub">Mixx W2A collection · unified record {{ $txn->txn_id }}</p></div>
    <div class="view-actions">
        @if (! $txn->isTerminal() || $txn->exception_type)
            <form method="POST" action="{{ route('mixx.transactions.verify', $txn) }}" style="display:inline;">@csrf<button class="btn btn-ghost" type="submit">Verify now</button></form>
        @endif
        <a class="btn btn-ghost" href="{{ route('mixx.dashboard') }}">Back</a>
    </div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => null])
<div class="prov-content">
<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Internal transaction</h3><span class="pill pill-{{ strtolower($txn->status) }}">{{ $txn->status }}</span></div>
    <div class="panel-body">
        <div class="detail-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
            <div><div class="dk">TXN ID</div><div class="dv mono">{{ $txn->txn_id }}</div></div>
            <div><div class="dk">FEEDTAN TXNID</div><div class="dv mono">{{ $txn->internal_reference ?? '—' }}</div></div>
            <div><div class="dk">Customer reference</div><div class="dv mono">{{ $txn->customer_reference_id ?? '—' }}</div></div>
            <div><div class="dk">Mixx TXNID</div><div class="dv mono">••••••••</div></div>
            <div><div class="dk">Mixx REFID</div><div class="dv mono">••••••••</div></div>
            <div><div class="dk">Customer</div><div class="dv">{{ $txn->customer?->name ?? '—' }}</div></div>
            <div><div class="dk">MSISDN</div><div class="dv mono">••••••{{ $txn->phone_last4 ?? '' }}</div></div>
            <div><div class="dk">Sender</div><div class="dv">{{ $txn->sender_name_confirmed ?? '—' }}</div></div>
            <div><div class="dk">Amount</div><div class="dv">TZS {{ number_format($txn->amount, 0) }}</div></div>
            <div><div class="dk">Confirmed</div><div class="dv">TZS {{ number_format($txn->expected_amount ?? $txn->amount, 0) }}</div></div>
            <div><div class="dk">Initiated</div><div class="dv">{{ $txn->initiated_at?->format('d M Y H:i') ?? '—' }}</div></div>
            <div><div class="dk">Completed</div><div class="dv">{{ $txn->completed_at?->format('d M Y H:i') ?? '—' }}</div></div>
            <div><div class="dk">Webhook</div><div class="dv"><span class="pill pill-neutral">{{ $txn->webhook_status }}</span></div></div>
            <div><div class="dk">Settlement</div><div class="dv"><span class="pill pill-{{ strtolower($txn->settlement_status) }}">{{ str_replace('_', ' ', $txn->settlement_status) }}</span></div></div>
            @if ($txn->exception_type)<div><div class="dk">Exception</div><div class="dv"><span class="pill pill-failed">{{ $txn->exception_type }}</span></div></div>@endif
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Attempts (never overwritten)</h3></div>
    <div class="panel-body">
        @forelse ($txn->payment?->attempts()->orderBy('attempt_number')->get() ?? [] as $a)
            <div style="font-size:13px;padding:7px 0;border-bottom:1px solid var(--line);">
                <strong>#{{ $a->attempt_number }} {{ $a->action }}</strong> →
                <span class="pill pill-{{ strtolower($a->status ?? ($a->success ? 'success' : 'failed')) }}">{{ $a->status ?? ($a->success ? 'SUCCESS' : 'FAILED') }}</span>
                @if ($a->error_code)<span class="mono" style="font-size:11.5px;">{{ $a->error_code }}</span>@endif
                <span style="color:var(--ink-soft);">· {{ $a->duration_ms !== null ? $a->duration_ms.'ms' : '' }} {{ $a->created_at->format('d M H:i') }}</span>
                @if ($a->error)<div style="color:var(--ink-soft);font-size:12px;">{{ $a->error }}</div>@endif
            </div>
        @empty
            <div class="empty-state">No attempts recorded.</div>
        @endforelse
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Provider API log (payloads encrypted)</h3></div>
    <div class="panel-body">
        @forelse ($apiLogs as $log)
            <div style="font-size:13px;padding:7px 0;border-bottom:1px solid var(--line);">
                <strong class="mono">{{ $log->operation }}</strong> · HTTP {{ $log->http_status ?? '—' }} · {{ $log->duration_ms ?? '—' }}ms · attempt {{ $log->attempt }}
                <span class="pill pill-{{ $log->success ? 'success' : 'failed' }}">{{ $log->success ? 'ok' : 'fail' }}</span>
                <span style="color:var(--ink-soft);">{{ $log->created_at->format('d M H:i') }}</span>
                @if ($log->error)<div style="color:var(--danger);font-size:12px;">{{ $log->error }}</div>@endif
            </div>
        @empty
            <div class="empty-state">No API calls logged for this transaction.</div>
        @endforelse
    </div>
</div>
</div>
</div>
@endsection
