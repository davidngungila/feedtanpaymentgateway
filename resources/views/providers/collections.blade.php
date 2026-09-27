@extends('layouts.app')

@section('title', $provider->name.' · Collections')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
@include('providers.partials.header', ['active' => 'collections'])
@include('collections.partials.txn-table', ['txns' => $collections, 'providers' => $tableProviders])
    </div>
</div>
@endsection
