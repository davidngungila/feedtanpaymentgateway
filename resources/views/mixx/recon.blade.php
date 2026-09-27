@extends('layouts.app')

@section('title', 'Mixx reconciliation')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Mixx reconciliation</h2><p class="sub">{{ $from->format('d M Y') }} → {{ $to->format('d M Y') }} · references, amounts, MSISDN, dates, statuses.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mixx.dashboard') }}">Back</a></div>
</div>

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'recon'])
<div class="prov-content">
<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('mixx.recon') }}" class="filterbar" style="width:100%">
            <div class="field"><label style="font-size:11px;">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}"></div>
            <div><button class="btn btn-primary" type="submit">Run reconciliation</button></div>
        </form>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Summary</h3></div>
    <div class="panel-body">
        @if (empty($summary))
            <div class="empty-state">Nothing in range.</div>
        @else
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @foreach ($summary as $result => $count)
                    <span class="pill pill-neutral">{{ $result }} · {{ $count }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>TXN</th><th>FEEDTAN ref</th><th class="num">Amount</th><th>Status</th><th>Result</th><th>At</th></tr></thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td class="mono">{{ $r['txn_id'] }}</td>
                        <td class="mono">{{ $r['internal_reference'] ?? '—' }}</td>
                        <td class="num">{{ number_format($r['amount'], 0) }}</td>
                        <td>{{ $r['status'] }}</td>
                        <td><span class="pill pill-{{ $r['result'] === 'MATCHED' ? 'success' : 'failed' }}">{{ $r['result'] }}</span></td>
                        <td>{{ $r['created_at']->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No rows in range.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
</div>
@endsection
