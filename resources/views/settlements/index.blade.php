@extends('layouts.app')

@section('title', 'Settlements')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Settlements</h2><p class="sub">Batch eligible SUCCESS collections, approve, and track settlement reports.</p></div>
    <div class="view-actions"><a class="btn btn-primary" href="{{ route('settlements.create') }}">+ New batch</a></div>
</div>

@include('collections.partials.flash')

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Reference</th><th>Provider</th><th class="num">Items</th><th class="num">Gross</th><th class="num">Fees</th><th class="num">Net</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @forelse ($settlements as $s)
                    <tr>
                        <td class="mono">{{ $s->reference }}</td>
                        <td>{{ $s->provider ? \App\Payments\ProviderRegistry::name($s->provider) : 'All' }}</td>
                        <td class="num">{{ $s->items_count }}</td>
                        <td class="num">{{ number_format($s->total_amount, 0) }}</td>
                        <td class="num">{{ number_format($s->total_fee, 0) }}</td>
                        <td class="num">{{ number_format($s->net_amount, 0) }}</td>
                        <td><span class="pill pill-{{ strtolower($s->status) }}">{{ str_replace('_', ' ', $s->status) }}</span></td>
                        <td>{{ $s->created_at->format('d M H:i') }}</td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('settlements.show', $s) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state">No settlement batches yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $settlements->links() }}</div>
@endsection
