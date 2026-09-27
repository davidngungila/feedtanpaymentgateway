@extends('layouts.app')

@section('title', 'Settlement Transactions')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Settlement Transactions</h2><p class="sub">Eligible, batched and settled collections.</p></div></div>

@include('collections.partials.flash')

@php $providers = \App\Payments\ProviderRegistry::meta(); @endphp
@include('collections.partials.txn-table', ['txns' => $txns, 'providers' => $providers])
@endsection
