@extends('layouts.app')

@section('title', 'Reconciliation')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div><h2>Reconciliation</h2><p class="sub">{{ $counts['unmatched'] }} unmatched · {{ $counts['eligible'] }} eligible for settlement · {{ $counts['exceptions'] }} exceptions.</p></div>
    <div class="view-actions">
        <form method="POST" action="{{ route('reconciliation.approve') }}" style="display:inline;">@csrf<button class="btn btn-primary" type="submit">Approve review</button></form>
    </div>
</div>

@include('collections.partials.flash')

<div class="tabs">
    <a class="tab {{ $tab === 'unmatched' ? 'active' : '' }}" href="{{ route('reconciliation.unmatched') }}">Unmatched ({{ $counts['unmatched'] }})</a>
    <a class="tab {{ $tab === 'provider' ? 'active' : '' }}" href="{{ route('reconciliation.provider') }}">Provider Transactions</a>
    <a class="tab {{ $tab === 'exceptions' ? 'active' : '' }}" href="{{ route('reconciliation.exceptions') }}">Exceptions ({{ $counts['exceptions'] }})</a>
</div>

@if ($tab === 'unmatched')
    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>TXN ID</th><th>Provider</th><th>Customer</th><th class="num">Amount</th><th>Status</th><th>Received</th><th></th></tr></thead>
                <tbody>
                    @forelse ($unmatched as $t)
                        <tr>
                            <td class="mono">{{ $t->txn_id }}</td>
                            <td>{{ \App\Payments\ProviderRegistry::name($t->provider) }}</td>
                            <td>{{ $t->customer?->name ?? '—' }}<div class="cell-sub mono">{{ $t->customer?->maskedPhone() ?? '' }}</div></td>
                            <td class="num">{{ number_format($t->amount, 0) }}</td>
                            <td><span class="pill pill-{{ strtolower($t->status) }}">{{ $t->status }}</span></td>
                            <td>{{ $t->created_at->format('d M H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ route('reconciliation.match', $t) }}" class="filterbar">
                                    @csrf
                                    <div class="field"><input type="text" name="payment_reference" required placeholder="PAY…" style="width:130px;"></div>
                                    <div><button class="btn btn-sm btn-ghost" type="submit">Match</button></div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Nothing unmatched. Provider money with no local record lands here.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px">{{ $unmatched->links() }}</div>
@endif

@if ($tab === 'provider')
    @php $providers = \App\Payments\ProviderRegistry::meta(); @endphp
    @include('collections.partials.txn-table', ['txns' => $providerTxns, 'providers' => $providers])
@endif

@if ($tab === 'exceptions')
    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Provider</th><th>Event</th><th>Signature</th><th>Processing</th><th>Error</th><th></th></tr></thead>
                <tbody>
                    @forelse ($exceptions as $e)
                        <tr>
                            <td>{{ \App\Payments\ProviderRegistry::name($e->provider) }}</td>
                            <td class="mono">{{ $e->event_id ?? substr($e->payload_hash, 0, 12).'…' }}</td>
                            <td>{{ $e->signature_status }}</td>
                            <td><span class="pill pill-neutral">{{ $e->processing_status }}</span></td>
                            <td style="font-size:12px;">{{ $e->error ?? '—' }}</td>
                            <td>
                                @if ($e->processing_status !== 'resolved')
                                    <form method="POST" action="{{ route('reconciliation.resolve', $e) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-ghost" type="submit">Resolve</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">No exceptions. Rejected webhooks and failed deliveries land here.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px">{{ $exceptions->links() }}</div>
@endif
@endsection
