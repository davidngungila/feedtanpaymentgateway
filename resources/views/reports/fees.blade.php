@extends('layouts.app')

@section('title', 'Fees')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Fees</h2><p class="sub">{{ $from->format('d M Y') }} → {{ $to->format('d M Y') }} · gross vs fees vs net per provider.</p></div></div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('mm.reports.fees') }}" class="filterbar" style="width:100%">
            <div class="field"><label style="font-size:11px;">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}"></div>
            <div><button class="btn btn-ghost" type="submit">Apply</button></div>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Provider</th><th class="num">Collections</th><th class="num">Gross</th><th class="num">Fees</th><th class="num">Net</th></tr></thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td><span class="prov-dot" style="background:{{ $providers[$r->provider]['color'] ?? '#999' }}"></span>{{ $providers[$r->provider]['name'] ?? $r->provider }}</td>
                        <td class="num">{{ $r->count }}</td>
                        <td class="num">{{ number_format($r->gross, 0) }}</td>
                        <td class="num">{{ number_format($r->fees, 0) }}</td>
                        <td class="num">{{ number_format($r->net, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No fee data in this range.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
