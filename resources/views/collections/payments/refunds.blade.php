@extends('layouts.app')

@section('title', 'Refunds')

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
    <div>
        <h2>Refunds &amp; Reversals</h2>
        <p class="sub">Corrections are separate audited records — original SUCCESS transactions are never edited.</p>
    </div>
</div>

@include('collections.partials.flash')

<div class="tabs">
    <a class="tab" href="{{ route('collections.payments.index') }}">All</a>
    <a class="tab" href="{{ route('collections.payments.pending') }}">Pending</a>
    <a class="tab" href="{{ route('collections.payments.successful') }}">Successful</a>
    <a class="tab" href="{{ route('collections.payments.failed') }}">Failed</a>
    <a class="tab" href="{{ route('collections.payments.reversed') }}">Reversed</a>
    <a class="tab active" href="{{ route('collections.payments.refunds') }}">Refunds</a>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Refunds</h3></div>
    <div class="panel-body">
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Reference</th><th>Payment</th><th class="num">Amount</th><th>Status</th><th>Requested</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($refunds as $r)
                            <tr>
                                <td class="mono">{{ $r->reference }}</td>
                                <td class="mono">{{ $r->payment?->reference ?? '—' }}</td>
                                <td class="num">{{ number_format($r->amount, 0) }}</td>
                                <td><span class="pill pill-{{ strtolower($r->status) }}">{{ $r->status }}</span></td>
                                <td>{{ $r->created_at->format('d M H:i') }}</td>
                                <td>
                                    @if ($r->status === 'requested' && auth()->user()->hasPermission('payments.refund.approve'))
                                        <form method="POST" action="{{ route('collections.payments.refund.approve', $r) }}" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-primary" type="submit">Approve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state">No refunds yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top:12px">{{ $refunds->links() }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Reversals</h3></div>
    <div class="panel-body">
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Reference</th><th>Payment</th><th class="num">Amount</th><th>Status</th><th>Recorded</th></tr></thead>
                    <tbody>
                        @forelse ($reversals as $r)
                            <tr>
                                <td class="mono">{{ $r->reference }}</td>
                                <td class="mono">{{ $r->payment?->reference ?? '—' }}</td>
                                <td class="num">{{ number_format($r->amount, 0) }}</td>
                                <td><span class="pill pill-{{ strtolower($r->status) }}">{{ $r->status }}</span></td>
                                <td>{{ $r->created_at->format('d M H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state">No reversals yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top:12px">{{ $reversals->links() }}</div>
    </div>
</div>
@endsection
