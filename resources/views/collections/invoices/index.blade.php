@extends('layouts.app')

@section('title', 'Invoices')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Invoices</h2><p class="sub">Bill a customer — then collect via their mobile-money network.</p></div>
    <div class="view-actions"><a class="btn btn-primary" href="{{ route('collections.invoices.create') }}">+ New invoice</a></div>
</div>

@include('collections.partials.flash')

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Number</th><th>Customer</th><th>Provider</th><th class="num">Amount</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($invoices as $i)
                    <tr>
                        <td class="mono">{{ $i->number }}</td>
                        <td>{{ $i->customer?->name ?? '—' }}<div class="cell-sub mono">{{ $i->customer?->maskedPhone() ?? '' }}</div></td>
                        <td>{{ $i->provider ? \App\Payments\ProviderRegistry::name($i->provider) : 'Any' }}</td>
                        <td class="num">{{ number_format($i->amount, 0) }}</td>
                        <td>{{ $i->due_at?->format('d M Y') ?? '—' }}</td>
                        <td><span class="pill pill-{{ strtolower($i->status) }}">{{ $i->status }}</span></td>
                        <td style="white-space:nowrap;">
                            @if (in_array($i->status, ['issued', 'overdue', 'sent']))
                                <form method="POST" action="{{ route('collections.invoices.collect', $i) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-primary" type="submit">Collect</button></form>
                                <form method="POST" action="{{ route('collections.invoices.cancel', $i) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-ghost" type="submit">Cancel</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">No invoices yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $invoices->links() }}</div>
@endsection
