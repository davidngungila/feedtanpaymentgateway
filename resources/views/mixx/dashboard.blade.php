@extends('layouts.app')

@section('title', 'Mixx by Yas · Dashboard')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2><span class="prov-dot" style="background:#00377B;width:12px;height:12px;"></span>Mixx by Yas</h2>
    <p class="sub">Today's collections: TZS {{ number_format($todayVolume, 0 )}} · W2A collection + A2W disbursement.</p></div>
    <div class="view-actions">
        <a class="btn btn-primary" href="{{ route('mixx.collect') }}">+ New collection</a>
    </div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'dashboard'])
<div class="prov-content">

<div class="stat-grid">
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><span class="stat-trend up">Today</span></div>
        <div class="stat-value">{{ number_format($todayVolume, 0) }}</div>
        <div class="stat-label">TZS collected (SUCCESS)</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><span class="stat-trend up">Successful</span></div>
        <div class="stat-value">{{ $counts['SUCCESS'] }}</div>
        <div class="stat-label">collections all time</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
        <div class="stat-top"><span class="stat-trend up">Pending</span></div>
        <div class="stat-value">{{ $counts['PENDING'] }}</div>
        <div class="stat-label">awaiting confirmation</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
        <div class="stat-top"><span class="stat-trend down">Failed</span></div>
        <div class="stat-value">{{ $counts['FAILED'] }}</div>
        <div class="stat-label">failed · {{ $counts['UNKNOWN'] }} unknown/hold</div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Health</h3><span class="link">{{ $health['last_response'] ? 'last response '.$health['last_response']->format('H:i:s') : 'no calls yet' }}</span></div>
    <div class="panel-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;font-size:13px;">
            @foreach (['api' => 'API', 'connection' => 'Connection', 'authentication' => 'Authentication', 'collections' => 'Collections', 'disbursement' => 'Disbursement'] as $k => $label)
                <div style="display:flex;align-items:center;gap:8px;background:var(--sand-50);border:1px solid var(--line);border-radius:9px;padding:9px 12px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $health[$k] ? '#5E6E3F' : '#B33A3A' }};flex:none;"></span>
                    <span style="font-weight:700;">{{ $label }}</span>
                </div>
            @endforeach
        </div>
        <p class="prov-hint">Pending {{ $health['pending'] }} · Unknown/Hold {{ $health['unknown'] }} · Failed today {{ $health['failed'] }} · API failures (1h) {{ $health['recent_failures_1h'] }}@if(!is_null($health['queue_failed'])) · Failed jobs {{ $health['queue_failed'] }}@endif{{ $health['scheduler_last_run'] ? ' · Scheduler '.$health['scheduler_last_run']->format('H:i:s') : '' }}</p>
    </div>
</div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('mixx.dashboard') }}" class="filterbar" style="width:100%">
            <div class="field"><label style="font-size:11px;">Status</label>
                <select name="status" onchange="this.form.submit()">
                    @foreach (['all' => 'All', 'success' => 'Success', 'pending' => 'Pending', 'failed' => 'Failed', 'unknown' => 'Unknown', 'hold' => 'Hold'] as $v => $l)
                        <option value="{{ $v }}" {{ ($filters['status'] ?? 'all') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label style="font-size:11px;">From</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
            <div class="field"><label style="font-size:11px;">To</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <div class="field"><label style="font-size:11px;">Amount</label><input type="number" name="amount" value="{{ $filters['amount'] ?? '' }}" placeholder="50000"></div>
            <div class="field"><label style="font-size:11px;">Customer / MSISDN</label><input type="text" name="customer" value="{{ $filters['customer'] ?? '' }}" placeholder="Juma"></div>
            <div class="field"><label style="font-size:11px;">Reference / TXNID / REFID</label><input type="text" name="reference" value="{{ $filters['reference'] ?? '' }}" placeholder="FTP-…"></div>
            <div><button class="btn btn-ghost" type="submit">Filter</button></div>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Transaction</th><th>Customer</th><th class="num">Amount</th><th>Status</th><th>Initiated</th><th></th></tr></thead>
            <tbody>
                @forelse ($transactions as $t)
                    <tr>
                        <td><span class="mono">{{ $t->internal_reference ?? $t->txn_id }}</span><div class="cell-sub mono">{{ $t->customer_reference_id ?? '' }}</div></td>
                        <td>{{ $t->customer?->name ?? '—' }}<div class="cell-sub mono">••••••{{ $t->phone_last4 ?? '' }}</div></td>
                        <td class="num">{{ number_format($t->amount, 0) }}</td>
                        <td><span class="pill pill-{{ strtolower($t->status) }}">{{ $t->status }}</span>@if($t->exception_type)<div class="cell-sub">{{ $t->exception_type }}</div>@endif</td>
                        <td>{{ $t->created_at->format('d M H:i') }}</td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('mixx.transactions.show', $t) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No Mixx transactions yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $transactions->links() }}</div>
</div>
</div>
@endsection
