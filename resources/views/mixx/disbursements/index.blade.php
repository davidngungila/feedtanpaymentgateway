@extends('layouts.app')

@section('title', 'Mixx disbursements')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Mixx disbursements · A2W</h2><p class="sub">Partner account → subscriber wallet. PIN is never stored.</p></div>
    <div class="view-actions"><a class="btn btn-primary" href="{{ route('mixx.disbursements.create') }}">+ New disbursement</a></div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'disbursements'])
<div class="prov-content">
<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Reference</th><th>Recipient</th><th class="num">Amount</th><th>Status</th><th>Initiated</th><th></th></tr></thead>
            <tbody>
                @forelse ($items as $d)
                    <tr>
                        <td class="mono">{{ $d->reference }}</td>
                        <td class="mono">••••••{{ $d->recipient_last4 ?? '' }}</td>
                        <td class="num">{{ number_format($d->amount, 0) }}</td>
                        <td><span class="pill pill-{{ strtolower($d->status) }}">{{ $d->status }}</span></td>
                        <td>{{ $d->created_at->format('d M H:i') }}</td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('mixx.disbursements.show', $d) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No disbursements yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $items->links() }}</div>
</div>
</div>
@endsection
