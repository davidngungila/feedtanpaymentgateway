@extends('layouts.app')

@section('title', 'Mixx · New collection')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Mixx collection · W2A</h2><p class="sub">Wallet → FEEDTAN collection account (SYNC_BILLPAY_REQUEST).</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mixx.dashboard') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'collect'])
<div class="prov-content">
<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('mixx.collect.store') }}">
        @csrf
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Customer MSISDN *</label><input type="text" name="msisdn" value="{{ old('msisdn') }}" required placeholder="2557XXXXXXXX" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            <div class="field"><label>Amount (TZS) *</label><input type="number" name="amount" value="{{ old('amount') }}" required min="1" placeholder="50,000" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Customer name *</label><input type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100" placeholder="Sender name" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Sender name</label><input type="text" name="sender_name" value="{{ old('sender_name') }}" maxlength="100" placeholder="Defaults to customer" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="field"><label>Customer reference</label><input type="text" name="customer_reference" value="{{ old('customer_reference') }}" maxlength="60" placeholder="Auto: FTP-YYYYMMDD-000001" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Send collection request</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">How W2A works</h3>
    <ol class="detail-list">
        <li><strong>FEEDTAN TXNID</strong> (<code class="mono">FTP-…</code>) is generated — resubmits reuse it, never double-charge.</li>
        <li>The subscriber <strong>authorizes in their Mixx wallet</strong>.</li>
        <li>Result maps via the <strong>error engine</strong> — error111 becomes UNKNOWN, never blind FAILED.</li>
        <li>Confirmed amount must <strong>equal</strong> requested, or AMOUNT_MISMATCH is flagged.</li>
    </ol>
    <div class="kv"><span class="k">MSISDN</span><span class="v mono">2557XXXXXXXX</span></div>
    <div class="kv"><span class="k">Currency</span><span class="v">TZS only</span></div>
    <div class="kv"><span class="k">Reference</span><span class="v mono">FTP-YYYYMMDD-000001</span></div>
</div>
</div>
</div>
</div>
@endsection
