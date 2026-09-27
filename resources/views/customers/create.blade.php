@extends('layouts.app')

@section('title', 'Add Customer')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Add Customer</h2><p class="sub">Sensitive identifiers are encrypted on save; only a hash is kept for lookup.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('customers.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Full name *</label><input type="text" name="name" value="{{ old('name') }}" required placeholder="Mfano: Juma Mwanza" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" value="{{ old('phone') }}" required placeholder="255712345678" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
        </div>
        <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="juma@example.co.tz" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>National ID / reference</label><input type="text" name="national_id" value="{{ old('national_id') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Address</label><input type="text" name="address" value="{{ old('address') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Save customer</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">Privacy by design</h3>
    <ol class="detail-list">
        <li>Phone, email, ID and address are <strong>encrypted at rest</strong>.</li>
        <li>Search uses a <strong>blind index</strong> — plaintext is never stored for lookup.</li>
        <li>Everywhere in the UI the phone shows masked (<code class="mono">255******678</code>).</li>
        <li>Full reveal needs the <strong>customers.reveal</strong> permission and is audited.</li>
    </ol>
    <div class="kv"><span class="k">Phone format</span><span class="v mono">255712345678</span></div>
    <div class="kv"><span class="k">Duplicates</span><span class="v">one record per phone</span></div>
    <div class="kv"><span class="k">History</span><span class="v">all runs linked automatically</span></div>
</div>
</div>
@endsection
