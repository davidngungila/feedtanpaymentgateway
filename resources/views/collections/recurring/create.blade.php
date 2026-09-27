@extends('layouts.app')

@section('title', 'New Recurring Collection')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>New Recurring Collection</h2><p class="sub">Schedule automatic collections — every run is a normal engine collection with its own TXN ID.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('collections.recurring') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('collections.recurring.store') }}">
        @csrf
        <div class="field"><label>Mobile network *</label>@include('collections.partials.provider-select', ['providers' => $providers, 'selected' => old('provider', 'mpesa')])</div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Customer name *</label><input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Mfano: Amina Juma" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" value="{{ old('phone') }}" required placeholder="255712345678" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="field"><label>Amount (TZS) *</label><input type="number" name="amount" value="{{ old('amount') }}" required min="500" max="5000000" placeholder="5,000" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Frequency *</label><select name="frequency" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly" selected>Monthly</option></select></div>
            <div class="field"><label>First run *</label><input type="datetime-local" name="next_run_at" value="{{ old('next_run_at') }}" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Schedule</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">How recurring works</h3>
    <ol class="detail-list">
        <li><strong>Daily</strong> — collected every day from the first run.</li>
        <li><strong>Weekly</strong> — collected every 7 days from the first run.</li>
        <li><strong>Monthly</strong> — collected once a month, ideal for contributions &amp; savings.</li>
        <li>Each run sends a fresh USSD push the customer authorizes on their phone.</li>
        <li>Pause or resume anytime — history is kept per run.</li>
    </ol>
    <div class="kv"><span class="k">Amount limits</span><span class="v">TZS 500 – 5,000,000</span></div>
    <div class="kv"><span class="k">Phone format</span><span class="v mono">255712345678</span></div>
    <div class="kv"><span class="k">Statuses</span><span class="v">active · paused</span></div>
</div>
</div>
@endsection
