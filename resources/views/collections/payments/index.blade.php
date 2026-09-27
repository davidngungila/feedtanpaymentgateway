@extends('layouts.app')

@section('title', $title.' · Payments')

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
        <h2>{{ $title }}</h2>
        <p class="sub">One engine, five providers — every collection lands in the same internal transaction structure.</p>
    </div>
    <div class="view-actions">
        <a class="btn btn-primary" href="{{ route('collections.payments.create') }}">+ Initiate Payment</a>
    </div>
</div>

@include('collections.partials.flash')

<div class="tabs">
    <a class="tab {{ request()->routeIs('collections.payments.index') ? 'active' : '' }}" href="{{ route('collections.payments.index') }}">All</a>
    <a class="tab {{ request()->routeIs('collections.payments.pending') ? 'active' : '' }}" href="{{ route('collections.payments.pending') }}">Pending</a>
    <a class="tab {{ request()->routeIs('collections.payments.successful') ? 'active' : '' }}" href="{{ route('collections.payments.successful') }}">Successful</a>
    <a class="tab {{ request()->routeIs('collections.payments.failed') ? 'active' : '' }}" href="{{ route('collections.payments.failed') }}">Failed</a>
    <a class="tab {{ request()->routeIs('collections.payments.reversed') ? 'active' : '' }}" href="{{ route('collections.payments.reversed') }}">Reversed</a>
    <a class="tab {{ request()->routeIs('collections.payments.refunds') ? 'active' : '' }}" href="{{ route('collections.payments.refunds') }}">Refunds</a>
</div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ url()->current() }}" class="filterbar" style="width:100%">
            <div class="field">
                <label style="font-size:11px;">Provider</label>
                <select name="provider" onchange="this.form.submit()">
                    <option value="all" {{ $provider === 'all' ? 'selected' : '' }}>All providers</option>
                    @foreach ($providers as $code => $meta)
                        <option value="{{ $code }}" {{ $provider === $code ? 'selected' : '' }}>{{ $meta['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1;min-width:200px;">
                <label style="font-size:11px;">Search TXN ID / reference</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="TXN-… or PAY…">
            </div>
            <div><button class="btn btn-ghost" type="submit">Filter</button></div>
        </form>
    </div>
</div>

@include('collections.partials.txn-table', ['txns' => $transactions, 'providers' => $providers])
@endsection
