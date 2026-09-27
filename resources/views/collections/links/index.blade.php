@extends('layouts.app')

@section('title', 'Payment Links')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Payment Links</h2><p class="sub">Shareable checkout links backed by a payment request. No login needed to pay.</p></div></div>

@include('collections.partials.flash')

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Request</th><th>Customer</th><th class="num">Amount</th><th>Link</th><th>Status</th><th>Expires</th><th></th></tr></thead>
            <tbody>
                @forelse ($links as $l)
                    <tr>
                        <td class="mono">{{ $l->request?->reference ?? '—' }}</td>
                        <td>{{ $l->request?->customer?->name ?? '—' }}</td>
                        <td class="num">{{ number_format($l->request?->amount ?? 0, 0) }}</td>
                        <td><a class="mono" style="font-size:11px;" href="{{ route('collections.links.checkout', $l->token) }}" target="_blank">/c/pay/{{ substr($l->token, 0, 12) }}…</a></td>
                        <td><span class="pill pill-{{ strtolower($l->status) }}">{{ $l->status }}</span></td>
                        <td>{{ $l->expires_at?->format('d M H:i') ?? '—' }}</td>
                        <td>
                            @if ($l->status === 'active')
                                <form method="POST" action="{{ route('collections.links.revoke', $l) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-ghost" type="submit">Revoke</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">No payment links yet. Create one from a pending payment request.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $links->links() }}</div>
@endsection
