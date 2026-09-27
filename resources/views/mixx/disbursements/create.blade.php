@extends('layouts.app')

@section('title', 'Mixx · New disbursement')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>New disbursement · A2W</h2><p class="sub">The PIN is used once for this request and never stored, logged or audited.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mixx.disbursements') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'disbursements'])
<div class="prov-content">
<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('mixx.disbursements.store') }}" autocomplete="off">
        @csrf
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Recipient MSISDN *</label><input type="text" name="msisdn" value="{{ old('msisdn') }}" required placeholder="2557XXXXXXXX" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            <div class="field"><label>Amount (TZS) *</label><input type="number" name="amount" value="{{ old('amount') }}" required min="1" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="field"><label>Disbursement PIN *</label><input type="password" name="pin" required autocomplete="new-password" placeholder="Never stored" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="field"><label>Sender name</label><input type="text" name="sender_name" value="{{ old('sender_name', 'FEEDTAN PAY') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Brand ID</label><input type="text" name="brand_id" value="{{ old('brand_id') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Language</label><select name="language" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"><option value="sw">sw</option><option value="en">en</option></select></div>
        </div>
        <div class="field"><label>Reference (optional)</label><input type="text" name="reference" value="{{ old('reference') }}" placeholder="Auto: FTD-YYYYMMDD-000001" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Submit disbursement</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">Safety rules</h3>
    <ol class="detail-list">
        <li><strong>PIN</strong> travels in the request only — scrubbed from logs, DB and audits.</li>
        <li><strong>TXNSTATUS 100</strong> goes to HOLD for verification, never instant fail.</li>
        <li>Duplicate references are <strong>rejected</strong> (idempotency).</li>
        <li>Barred accounts and limit breaches map to <strong>audited failures</strong>.</li>
    </ol>
</div>
</div>
</div>
</div>
@endsection
