@extends('layouts.app')

@section('title', 'New Settlement Batch')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>New Settlement Batch</h2><p class="sub">{{ $eligible->count() }} eligible transaction(s) · Gross TZS {{ number_format($totals['amount'], 0) }} · Fees {{ number_format($totals['fee'], 0) }} · Net {{ number_format($totals['net'], 0) }}.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('settlements.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid" style="grid-template-columns:1fr 1.6fr;">
    <div class="settings-panel">
        <h3 style="margin-top:0;">Batch summary</h3>
        <div class="kv"><span class="k">Transactions</span><span class="v">{{ $eligible->count() }}</span></div>
        <div class="kv"><span class="k">Gross</span><span class="v">TZS {{ number_format($totals['amount'], 0) }}</span></div>
        <div class="kv"><span class="k">Fees</span><span class="v">TZS {{ number_format($totals['fee'], 0) }}</span></div>
        <div class="kv"><span class="k">Net</span><span class="v">TZS {{ number_format($totals['net'], 0) }}</span></div>
        <form method="GET" action="{{ route('settlements.create') }}" style="margin-top:14px;">
            <div class="field"><label>Provider</label>
                <select name="provider" onchange="this.form.submit()" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <option value="all" {{ $provider === 'all' ? 'selected' : '' }}>All providers</option>
                    @foreach (\App\Payments\ProviderRegistry::meta() as $code => $meta)
                        <option value="{{ $code }}" {{ $provider === $code ? 'selected' : '' }}>{{ $meta['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <form method="POST" action="{{ route('settlements.store') }}">
            @csrf
            <input type="hidden" name="provider" value="{{ $provider }}">
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field"><label>Period start</label><input type="date" name="period_start" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
                <div class="field"><label>Period end</label><input type="date" name="period_end" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            </div>
            <button class="btn btn-primary" type="submit" style="width:100%;" {{ $eligible->isEmpty() ? 'disabled' : '' }}>Batch {{ $eligible->count() }} transaction(s)</button>
        </form>
        <p class="prov-hint">Batching locks these transactions as <strong>batched</strong>. Approval moves them to <strong>settled</strong> — never editable directly.</p>
    </div>

    <div class="table-card" style="margin:0;">
        <div class="table-scroll" style="max-height:560px;overflow-y:auto;">
            <table>
                <thead><tr><th>TXN ID</th><th>Provider</th><th>Customer</th><th class="num">Amount</th><th class="num">Fee</th><th class="num">Net</th><th>Completed</th></tr></thead>
                <tbody>
                    @forelse ($eligible as $t)
                        <tr>
                            <td><span class="mono">{{ $t->txn_id }}</span><div class="cell-sub mono">{{ $t->reference }}</div></td>
                            <td><span class="prov-dot" style="background:{{ \App\Payments\ProviderRegistry::meta()[$t->provider]['color'] ?? '#999' }}"></span>{{ \App\Payments\ProviderRegistry::name($t->provider) }}</td>
                            <td>{{ $t->customer?->name ?? '—' }}<div class="cell-sub mono">{{ $t->customer?->maskedPhone() ?? '' }}</div></td>
                            <td class="num">{{ number_format($t->amount, 0) }}</td>
                            <td class="num">{{ number_format($t->provider_fee, 0) }}</td>
                            <td class="num">{{ number_format($t->net_amount, 0) }}</td>
                            <td>{{ $t->completed_at?->format('d M H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">No eligible transactions. SUCCESS collections become eligible automatically.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
