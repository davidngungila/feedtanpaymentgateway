@extends('layouts.app')

@section('title', 'Payment Requests')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Payment Requests</h2><p class="sub">Ask a customer to pay — then collect via USSD push or share a payment link.</p></div>
    <div class="view-actions"><a class="btn btn-primary" href="{{ route('collections.requests.create') }}">+ New request</a></div>
</div>

@include('collections.partials.flash')

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Reference</th><th>Customer</th><th>Provider</th><th class="num">Amount</th><th>Status</th><th>Expires</th><th></th></tr></thead>
            <tbody>
                @forelse ($requests as $r)
                    <tr>
                        <td class="mono">{{ $r->reference }}</td>
                        <td>{{ $r->customer?->name ?? '—' }}<div class="cell-sub mono">{{ $r->customer?->maskedPhone() ?? '' }}</div></td>
                        <td>{{ \App\Payments\ProviderRegistry::name($r->provider) }}</td>
                        <td class="num">{{ number_format($r->amount, 0) }}</td>
                        <td><span class="pill pill-{{ strtolower($r->status) }}">{{ $r->status }}</span></td>
                        <td>{{ $r->expires_at?->format('d M H:i') ?? '—' }}</td>
                        <td style="white-space:nowrap;">
                            @if ($r->status === 'pending')
                                <form method="POST" action="{{ route('collections.requests.collect', $r) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-primary" type="submit">Collect</button></form>
                                <form method="POST" action="{{ route('collections.links.store') }}" style="display:inline;">@csrf<input type="hidden" name="payment_request_id" value="{{ $r->getRouteKey() }}"><button class="btn btn-sm btn-ghost" type="submit">Make link</button></form>
                                <form method="POST" action="{{ route('collections.requests.cancel', $r) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-ghost" type="submit">Cancel</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">No payment requests yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $requests->links() }}</div>
@endsection
