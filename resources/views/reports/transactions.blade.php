@extends('layouts.app')

@section('title', 'Transaction Reports')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Transaction Reports</h2><p class="sub">{{ $from->format('d M Y') }} → {{ $to->format('d M Y') }}</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mm.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'provider' => $provider, 'status' => $status]) }}">Export CSV</a></div>
</div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('mm.reports.transactions') }}" class="filterbar" style="width:100%">
            <div class="field"><label style="font-size:11px;">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}"></div>
            <div class="field"><label style="font-size:11px;">Status</label>
                <select name="status" onchange="this.form.submit()">
                    @foreach (['all' => 'All', 'pending' => 'Pending', 'success' => 'Success', 'failed' => 'Failed', 'reversed' => 'Reversed'] as $v => $l)
                        <option value="{{ $v }}" {{ $status === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
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

@include('collections.partials.txn-table', ['txns' => $txns, 'providers' => $providers])
@endsection
