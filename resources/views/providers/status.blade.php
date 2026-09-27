@extends('layouts.app')

@section('title', $provider->name.' · Status Queries')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
@include('providers.partials.header', ['active' => 'status'])

<div class="settings-panel" style="max-width:640px;">
    <h3 style="margin-top:0;">Query collection status</h3>
    <form method="POST" action="{{ route('providers.status.query', $provider->code) }}" class="filterbar">
        @csrf
        <div class="field" style="flex:1;"><label style="font-size:11px;">Order reference</label><input type="text" name="reference" required placeholder="PAY…"></div>
        <div><button class="btn btn-primary" type="submit">Query</button></div>
    </form>
    @if (session('status_result'))
        @php $sr = session('status_result'); @endphp
        <div class="flash info" style="margin-top:12px;">
            {{ $sr['reference'] }} → <strong>{{ $sr['status'] }}</strong>
            @if ($sr['amount']) · TZS {{ number_format($sr['amount'], 0) }} @endif
            @if ($sr['error']) · {{ $sr['error'] }} @endif
        </div>
    @endif
</div>
    </div>
</div>
@endsection
