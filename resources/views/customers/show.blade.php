@extends('layouts.app')

@section('title', 'Customer · '.$customer->name)

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>{{ $customer->name }}</h2><p class="sub">Customer record · joined {{ $customer->created_at->format('d M Y') }}</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('customers.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Identifiers</h3><span class="link">encrypted at rest</span></div>
    <div class="panel-body">
        <div class="detail-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
            <div><div class="dk">Phone</div><div class="dv mono" id="phoneMasked">{{ $customer->maskedPhone() }}</div>
                @if (auth()->user()->hasPermission('customers.reveal'))
                    <button class="btn btn-sm btn-ghost" style="margin-top:8px;" onclick="revealPhone('{{ $customer->getRouteKey() }}')">Reveal (audited)</button>
                @endif
            </div>
            <div><div class="dk">Status</div><div class="dv"><span class="pill pill-{{ strtolower($customer->status) }}">{{ $customer->status }}</span></div></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Payment history</h3></div>
    <div class="panel-body">
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>TXN ID</th><th>Provider</th><th class="num">Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($customer->transactions as $t)
                            <tr>
                                <td class="mono">{{ $t->txn_id }}</td>
                                <td>{{ \App\Payments\ProviderRegistry::name($t->provider) }}</td>
                                <td class="num">{{ number_format($t->amount, 0) }}</td>
                                <td><span class="pill pill-{{ strtolower($t->status) }}">{{ $t->status }}</span></td>
                                <td>{{ $t->created_at->format('d M H:i') }}</td>
                                <td><a class="btn btn-sm btn-ghost" href="{{ $t->detailsUrl() }}">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state">No payments yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function revealPhone(id){
    fetch('/customers/' + id + '/reveal', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'}
    }).then(r => r.json()).then(d => {
        if (d.success) document.getElementById('phoneMasked').textContent = d.phone;
    });
}
</script>
@endsection
