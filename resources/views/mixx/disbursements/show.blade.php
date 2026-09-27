@extends('layouts.app')

@section('title', 'Disbursement '.$dis->reference)

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>{{ $dis->reference }}</h2><p class="sub">Mixx A2W disbursement · {{ $dis->internal_reference }}</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mixx.disbursements') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'disbursements'])
<div class="prov-content">
<div class="panel">
    <div class="panel-head"><h3>Disbursement</h3><span class="pill pill-{{ strtolower($dis->status) }}">{{ $dis->status }}</span></div>
    <div class="panel-body">
        <div class="detail-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
            <div><div class="dk">Recipient</div><div class="dv mono">{{ $dis->maskedRecipient() }}</div></div>
            <div><div class="dk">Amount</div><div class="dv">TZS {{ number_format($dis->amount, 0) }}</div></div>
            <div><div class="dk">Brand / Language</div><div class="dv">{{ $dis->brand_id ?? '—' }} / {{ $dis->language ?? '—' }}</div></div>
            <div><div class="dk">Provider message</div><div class="dv">{{ $dis->message ?? '—' }}</div></div>
            <div><div class="dk">Error code</div><div class="dv mono">{{ $dis->error_code ?? '—' }}</div></div>
            <div><div class="dk">Initiated</div><div class="dv">{{ $dis->created_at->format('d M Y H:i') }}</div></div>
            <div><div class="dk">Completed</div><div class="dv">{{ $dis->completed_at?->format('d M Y H:i') ?? '—' }}</div></div>
            @if ($dis->exception_type)<div><div class="dk">Exception</div><div class="dv"><span class="pill pill-failed">{{ $dis->exception_type }}</span></div></div>@endif
        </div>
    </div>
</div>
</div>
</div>
@endsection
