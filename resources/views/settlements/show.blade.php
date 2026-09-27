@extends('layouts.app')

@section('title', 'Settlement '.$settlement->reference)

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>{{ $settlement->reference }}</h2><p class="sub">{{ $settlement->provider ? \App\Payments\ProviderRegistry::name($settlement->provider) : 'All providers' }} · Net TZS {{ number_format($settlement->net_amount, 0) }}</p></div>
    <div class="view-actions">
        @if ($settlement->status === 'pending_approval' && auth()->user()->hasPermission('settlements.approve'))
            <form method="POST" action="{{ route('settlements.approve', $settlement) }}" style="display:inline;">@csrf<button class="btn btn-primary" type="submit">Approve</button></form>
        @endif
        <a class="btn btn-ghost" href="{{ route('settlements.index') }}">Back</a>
    </div>
</div>

@include('collections.partials.flash')

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Batch</h3><span class="pill pill-{{ strtolower($settlement->status) }}">{{ str_replace('_', ' ', $settlement->status) }}</span></div>
    <div class="panel-body">
        <div class="detail-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
            <div><div class="dk">Gross</div><div class="dv">TZS {{ number_format($settlement->total_amount, 0) }}</div></div>
            <div><div class="dk">Fees</div><div class="dv">TZS {{ number_format($settlement->total_fee, 0) }}</div></div>
            <div><div class="dk">Net</div><div class="dv">TZS {{ number_format($settlement->net_amount, 0) }}</div></div>
            <div><div class="dk">Approved by</div><div class="dv">{{ $settlement->approver?->name ?? '—' }}</div></div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>TXN ID</th><th>Provider</th><th class="num">Amount</th><th class="num">Fee</th><th class="num">Net</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($settlement->items as $item)
                    <tr>
                        <td class="mono">{{ $item->transaction?->txn_id ?? '—' }}</td>
                        <td>{{ $item->transaction ? \App\Payments\ProviderRegistry::name($item->transaction->provider) : '—' }}</td>
                        <td class="num">{{ number_format($item->amount, 0) }}</td>
                        <td class="num">{{ number_format($item->fee, 0) }}</td>
                        <td class="num">{{ number_format($item->net_amount, 0) }}</td>
                        <td><span class="pill pill-neutral">{{ $item->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No items.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
