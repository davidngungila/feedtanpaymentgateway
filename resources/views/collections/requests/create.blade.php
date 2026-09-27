@extends('layouts.app')

@section('title', 'New Payment Request')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>New Payment Request</h2><p class="sub">The customer pays later via USSD push or a payment link.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('collections.requests') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('collections.requests.store') }}">
        @csrf
        <div class="field"><label>Mobile network *</label>@include('collections.partials.provider-select', ['providers' => $providers, 'selected' => old('provider', 'mpesa')])</div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Customer name *</label><input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Mfano: Juma Mwanza" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" value="{{ old('phone') }}" required placeholder="255712345678" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Amount (TZS) *</label><input type="number" name="amount" value="{{ old('amount') }}" required min="500" max="5000000" placeholder="5,000" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Expires at</label><input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="field"><label>Description</label><input type="text" name="description" value="{{ old('description') }}" placeholder="Malipo ya…" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Create request</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">Request lifecycle</h3>
    <ol class="detail-list">
        <li><strong>Pending</strong> — created, waiting. Collect via USSD push any time.</li>
        <li><strong>Make link</strong> — turn it into a shareable checkout link (<code class="mono">/c/pay/…</code>).</li>
        <li><strong>Sent</strong> — USSD push delivered; customer authorizes on phone.</li>
        <li><strong>Paid / expired / cancelled</strong> — terminal states with full history.</li>
    </ol>
    <div class="kv"><span class="k">Default expiry</span><span class="v">24 hours</span></div>
    <div class="kv"><span class="k">Amount limits</span><span class="v">TZS 500 – 5,000,000</span></div>
    <div class="kv"><span class="k">Links need</span><span class="v">no login to pay</span></div>
</div>
</div>
@endsection
