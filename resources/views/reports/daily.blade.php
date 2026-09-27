@extends('layouts.app')

@section('title', 'Daily Collections')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Daily Collections</h2><p class="sub">{{ $from->format('d M Y') }} → {{ $to->format('d M Y') }}</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mm.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'provider' => $provider]) }}">Export CSV</a></div>
</div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('mm.reports.daily') }}" class="filterbar" style="width:100%">
            <div class="field"><label style="font-size:11px;">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">Provider</label>
                <select name="provider" onchange="this.form.submit()">
                    <option value="all" {{ $provider === 'all' ? 'selected' : '' }}>All</option>
                    @foreach ($providers as $code => $meta)<option value="{{ $code }}" {{ $provider === $code ? 'selected' : '' }}>{{ $meta['name'] }}</option>@endforeach
                </select>
            </div>
            <div><button class="btn btn-ghost" type="submit">Apply</button></div>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Day</th><th class="num">Collections</th><th class="num">Successful</th><th class="num">Success volume</th><th class="num">Fees</th></tr></thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($r->day)->format('d M Y') }}</td>
                        <td class="num">{{ $r->count }}</td>
                        <td class="num">{{ $r->success_count }}</td>
                        <td class="num">{{ number_format($r->success_volume, 0) }}</td>
                        <td class="num">{{ number_format($r->fees, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No collections in this range.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
