@extends('layouts.app')

@section('title', 'Initiate Payment')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Initiate Payment</h2>
        <p class="sub">Customer → payment request → select network → provider API → customer authorization → webhook → SUCCESS / FAILED / PENDING.</p>
    </div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('collections.payments.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('collections.payments.store') }}">
        @csrf
        <div class="field">
            <label>Mobile network *</label>
            @include('collections.partials.provider-select', ['providers' => $providers, 'selected' => old('provider', 'auto'), 'allowAuto' => true])
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field">
                <label>Customer name *</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100" placeholder="Mfano: Juma Mwanza" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
            </div>
            <div class="field">
                <label>Phone number *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="12" placeholder="255712345678" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;">
            </div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field">
                <label>Amount (TZS) *</label>
                <input type="number" name="amount" value="{{ old('amount') }}" required min="500" max="5000000" placeholder="5,000" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
            </div>
            <div class="field">
                <label>Description</label>
                <input type="text" name="description" value="{{ old('description') }}" maxlength="255" placeholder="Malipo ya…" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Send USSD push</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">What happens next</h3>
    <ol class="detail-list">
        <li>A <strong>unified transaction</strong> (TXN ID + reference) is created instantly.</li>
        <li>The customer gets a <strong>USSD push</strong> and authorizes with their PIN.</li>
        <li>The provider <strong>webhook</strong> confirms the result — or poll with Refresh.</li>
        <li><strong>SUCCESS</strong> moves to reconciliation, then settlement. Records are immutable.</li>
    </ol>
    <div class="kv"><span class="k">Amount limits</span><span class="v">TZS 500 – 5,000,000</span></div>
    <div class="kv"><span class="k">Networks</span><span class="v">M-Pesa · Airtel · Mixx · HaloPesa · T-Pesa</span></div>
    <div class="kv"><span class="k">Auto-detect</span><span class="v">from phone prefix</span></div>
</div>
</div>
@endsection
