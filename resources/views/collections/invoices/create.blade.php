@extends('layouts.app')

@section('title', 'New Invoice')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>New Invoice</h2><p class="sub">Bill a customer — then collect through their mobile-money network.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('collections.invoices') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('collections.invoices.store') }}">
        @csrf
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Customer name *</label><input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Mfano: Juma Mwanza" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Phone *</label><input type="text" name="phone" value="{{ old('phone') }}" required placeholder="255712345678" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Preferred network</label><select name="provider" data-provider-select style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"><option value="">Any (auto)</option>@foreach ($providers as $code => $meta)<option value="{{ $code }}" data-prefixes="{{ implode(',', $meta['prefixes'] ?? []) }}" data-fee-percent="{{ $meta['fee']['percent'] ?? 0 }}" data-fee-flat="{{ $meta['fee']['flat'] ?? 0 }}">{{ $meta['name'] }}</option>@endforeach</select><div class="prov-hint" data-provider-hint></div></div>
            <div class="field"><label>Amount (TZS) *</label><input type="number" name="amount" value="{{ old('amount') }}" required min="500" max="5000000" placeholder="5,000" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="field"><label>Due date</label><input type="date" name="due_at" value="{{ old('due_at') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        <div class="field"><label>Notes</label><input type="text" name="notes" value="{{ old('notes') }}" placeholder="Mfano: Ada ya mwezi…" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Issue invoice</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">How invoicing works</h3>
    <ol class="detail-list">
        <li><strong>Issued</strong> — the bill exists; nothing charged yet.</li>
        <li><strong>Collect</strong> — sends a USSD push for the invoice amount.</li>
        <li><strong>Sent</strong> — push delivered; result arrives via webhook.</li>
        <li><strong>Paid</strong> — SUCCESS links back to this invoice automatically.</li>
    </ol>
    <div class="kv"><span class="k">Due date</span><span class="v">optional, shown on lists</span></div>
    <div class="kv"><span class="k">Network</span><span class="v">fixed or auto-detect</span></div>
    <div class="kv"><span class="k">Cancel</span><span class="v">issued / overdue / sent</span></div>
</div>
</div>
<script>
(function(){
    function fmt(n){ return 'TZS ' + Number(n || 0).toLocaleString('en-US', {maximumFractionDigits: 0}); }
    function update(form){
        var sel = form.querySelector('select[data-provider-select]');
        var hint = form.querySelector('[data-provider-hint]');
        if(!sel || !hint) return;
        var opt = sel.options[sel.selectedIndex];
        var prefixes = opt ? (opt.getAttribute('data-prefixes') || '') : '';
        var feeP = parseFloat(opt ? (opt.getAttribute('data-fee-percent') || '0') : '0') || 0;
        var feeF = parseFloat(opt ? (opt.getAttribute('data-fee-flat') || '0') : '0') || 0;
        var amt = parseFloat((form.querySelector('input[name="amount"]') || {}).value) || 0;
        var html = '';
        if(sel.value === ''){ html += 'Network auto-detected from the phone prefix at collect time. '; }
        else if(prefixes){ html += 'Prefixes: <strong>' + prefixes.split(',').join(', ') + '</strong>. '; }
        if(amt > 0){ var fee = Math.round((amt * feeP / 100 + feeF) * 100) / 100; html += 'Fee ≈ <strong>' + fmt(fee) + '</strong> · Net ≈ <strong>' + fmt(amt - fee) + '</strong>.'; }
        else { html += 'Enter an amount to preview fee & net.'; }
        hint.innerHTML = html;
    }
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('form').forEach(function(form){
            var sel = form.querySelector('select[data-provider-select]');
            if(!sel) return;
            update(form);
            sel.addEventListener('change', function(){ update(form); });
            var amt = form.querySelector('input[name="amount"]');
            if(amt) amt.addEventListener('input', function(){ update(form); });
        });
    });
})();
</script>
@endsection
