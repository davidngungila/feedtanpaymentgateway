@extends('layouts.app')

@section('title', 'Provider Reports')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Provider Reports</h2><p class="sub">{{ $from->format('d M Y') }} → {{ $to->format('d M Y') }} · all five networks, same structure.</p></div></div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('mm.reports.providers') }}" class="filterbar" style="width:100%">
            <div class="field"><label style="font-size:11px;">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}"></div>
            <div><button class="btn btn-ghost" type="submit">Apply</button></div>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Provider</th><th class="num">Collections</th><th class="num">Success</th><th class="num">Failed</th><th class="num">Success volume</th><th class="num">Fees</th></tr></thead>
            <tbody>
                @foreach ($providers as $code => $meta)
                    @php $r = $rows[$code] ?? null; @endphp
                    <tr>
                        <td><span class="prov-dot" style="background:{{ $meta['color'] }}"></span>{{ $meta['name'] }}</td>
                        <td class="num">{{ $r->count ?? 0 }}</td>
                        <td class="num">{{ $r->success_count ?? 0 }}</td>
                        <td class="num">{{ $r->failed_count ?? 0 }}</td>
                        <td class="num">{{ number_format($r->success_volume ?? 0, 0) }}</td>
                        <td class="num">{{ number_format($r->fees ?? 0, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
