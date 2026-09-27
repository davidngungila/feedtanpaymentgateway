@extends('layouts.app')

@section('title', 'New Payout')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;border:none;cursor:pointer;font-family:inherit;}
    .tab.active{background:var(--coffee-900);color:#fff;}
    .balance-card{background:linear-gradient(135deg,var(--coffee-900),var(--coffee-700));color:#fff;border-radius:16px;padding:18px;position:relative;overflow:hidden;}
    .balance-card::after{content:"";position:absolute;right:-30px;top:-30px;width:120px;height:120px;border-radius:50%;background:rgba(212,162,76,.18);}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>New Payout</h2>
        <p class="sub">Send money to a mobile-money account, bank account or Lipa Namba — verified before dispatch.</p>
    </div>
    <div class="view-actions">
        <a href="{{ route('payouts.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="tabs">
    <button type="button" class="tab active" id="tabSimu" onclick="switchPayoutTab('simu')">Namba ya Simu</button>
    <button type="button" class="tab" id="tabBank" onclick="switchPayoutTab('bank')">Bank</button>
    <button type="button" class="tab" id="tabLipa" onclick="switchPayoutTab('lipa')">Lipa Namba</button>
</div>

{{-- Namba ya Simu Form --}}
<form id="payoutFormSimu" method="POST" action="{{ route('payouts.store') }}" data-payout-form style="display:block;">
    @csrf
    <input type="hidden" name="method" value="mobile_money">
    <input type="hidden" name="gateway" value="clickpesa">
    <div class="create-grid">
        <div class="settings-panel">
            <div class="settings-section"><h4>Beneficiary</h4></div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Beneficiary name *</label>
                    <input type="text" name="beneficiary_name" placeholder="e.g. Aisha Juma" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                </div>
                <div class="field">
                    <label>Namba ya Simu *</label>
                    <input type="text" name="beneficiary_phone" placeholder="+255 7xx xxx xxx" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                </div>
            </div>
            <div class="field">
                <label>Phone Number for verification *</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" name="beneficiary_account" id="beneficiaryAccountSimu" placeholder="2557xxxxxxxx" required style="flex:1;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="verifyAccountSimu()" style="white-space:nowrap;">Verify Name</button>
                </div>
                <div id="verifyResultSimu" style="margin-top:8px; font-size:12.5px; display:none; padding:8px 10px; border-radius:8px;"></div>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Network</label>
                    <select name="network_id" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <option value="">Auto (from account)</option>
                        @foreach(($networks ?? []) as $n)
                            <option value="{{ $n->id }}">{{ $n->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Account type</label>
                    <select name="account_type" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <option value="personal">Personal</option>
                        <option value="business">Business / Till</option>
                    </select>
                </div>
            </div>

            <div class="settings-section"><h4>Amount & Purpose</h4></div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Amount (TZS) *</label>
                    <input type="number" step="0.01" min="1000" name="amount" placeholder="500000" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <div class="prov-hint">Min TZS 1,000 · Max TZS {{ number_format($limits['per_txn'] ?? 10000000, 0) }}</div>
                </div>
                <div class="field">
                    <label>Currency</label>
                    <select name="currency" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <option value="TZS">TZS – Tanzanian Shilling</option>
                        <option value="USD">USD – US Dollar</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Purpose *</label>
                <select name="purpose" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <option value="supplier_payment">Supplier payment</option>
                    <option value="refund">Customer refund</option>
                    <option value="salary">Salary / Commission</option>
                    <option value="withdrawal">Cash withdrawal</option>
                    <option value="bills">Bills & Utilities</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="field">
                <label>Internal notes</label>
                <textarea name="notes" rows="2" placeholder="Reason, invoice reference" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></textarea>
            </div>
            <div class="field" style="background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;">
                <label>Cashier OTP * <span style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--ink-soft);">— sent to your phone via SMS</span></label>
                <div style="display:flex;gap:8px;">
                    <input type="text" name="otp" placeholder="6-digit code" required maxlength="6" pattern="\d{6}" style="flex:1;letter-spacing:.2em;text-align:center;font-weight:700;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="requestOtp('simu')" style="white-space:nowrap;">Send OTP</button>
                </div>
                <input type="hidden" name="otp_phone" value="{{ auth()->user()?->phone ?? '' }}">
                <div class="prov-hint">Cashier must verify via OTP to initiate.</div>
            </div>
        </div>
        <div>
            <div class="balance-card" style="margin-bottom:18px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.8;">Available Balance</div>
                <b style="font-size:22px;display:block;margin-top:4px;">@money($balances['available'] ?? 3420000)</b>
                <div style="font-size:12.5px;opacity:.7;margin-top:4px;">Live balance</div>
            </div>
            <div class="settings-panel">
                <h3 style="margin-top:0;">How mobile payout works</h3>
                <ol class="detail-list">
                    <li><strong>Verify name</strong> — confirm the account holder before sending.</li>
                    <li><strong>Enter amount</strong> — within per-transaction limits.</li>
                    <li><strong>Cashier OTP</strong> — proves an authorized cashier initiated it.</li>
                    <li>Above <strong>TZS {{ number_format($limits['approval_threshold'] ?? 1000000, 0) }}</strong> it queues for supervisor approval.</li>
                </ol>
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;margin-top:8px;">Submit Namba ya Simu →</button>
            </div>
        </div>
    </div>
</form>

{{-- Bank Form --}}
<form id="payoutFormBank" method="POST" action="{{ route('payouts.store') }}" data-payout-form style="display:none;">
    @csrf
    <input type="hidden" name="method" value="bank">
    <input type="hidden" name="gateway" value="clickpesa">
    <div class="create-grid">
        <div class="settings-panel">
            <div class="settings-section"><h4>Beneficiary</h4></div>
            <div class="field">
                <label>Beneficiary name *</label>
                <input type="text" name="beneficiary_name" placeholder="e.g. John Doe" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
            </div>
            <div class="field">
                <label>Account Number *</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" name="beneficiary_account" id="beneficiaryAccountBank" placeholder="0152xxxxxxx" required style="flex:1;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="verifyAccountBank()" style="white-space:nowrap;">Verify Name</button>
                </div>
                <div id="verifyResultBank" style="margin-top:8px; font-size:12.5px; display:none; padding:8px 10px; border-radius:8px;"></div>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Bank name *</label>
                    <select name="bank_name" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <option value="">Select bank</option>
                        <option value="CRDB">CRDB Bank</option>
                        <option value="NMB">NMB Bank</option>
                        <option value="NBC">NBC</option>
                        <option value="EQUITY">Equity Bank</option>
                        <option value="ABSA">ABSA</option>
                        <option value="DTB">DTB</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>BIC / Bank Code *</label>
                    <input type="text" name="bic" placeholder="CRDBTZTZ" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                </div>
            </div>
            <div class="field">
                <label>Account Currency</label>
                <select name="accountCurrency" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <option value="TZS">TZS</option>
                    <option value="USD">USD</option>
                </select>
            </div>

            <div class="settings-section"><h4>Amount & Purpose</h4></div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Amount (TZS) *</label>
                    <input type="number" step="0.01" min="1000" name="amount" placeholder="500000" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                </div>
                <div class="field">
                    <label>Currency</label>
                    <select name="currency" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <option value="TZS">TZS</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Purpose *</label>
                <select name="purpose" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <option value="supplier_payment">Supplier payment</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="field">
                <label>Internal notes</label>
                <textarea name="notes" rows="2" placeholder="Invoice reference" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></textarea>
            </div>
            <div class="field" style="background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;">
                <label>Cashier OTP *</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" name="otp" placeholder="6-digit code" required maxlength="6" pattern="\d{6}" style="flex:1;letter-spacing:.2em;text-align:center;font-weight:700;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="requestOtp('bank')" style="white-space:nowrap;">Send OTP</button>
                </div>
                <input type="hidden" name="otp_phone" value="{{ auth()->user()?->phone ?? '' }}">
            </div>
        </div>
        <div>
            <div class="balance-card" style="margin-bottom:18px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.8;">Available Balance</div>
                <b style="font-size:22px;display:block;margin-top:4px;">@money($balances['available'] ?? 3420000)</b>
            </div>
            <div class="settings-panel">
                <h3 style="margin-top:0;">How bank payout works</h3>
                <ol class="detail-list">
                    <li><strong>Verify name</strong> — account number + BIC resolves the holder.</li>
                    <li><strong>Enter amount</strong> — bank transfers settle per-bank schedule.</li>
                    <li><strong>Cashier OTP</strong> — required before submit.</li>
                    <li>Large amounts queue for <strong>supervisor approval</strong>.</li>
                </ol>
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;margin-top:8px;">Submit Bank Transfer →</button>
            </div>
        </div>
    </div>
</form>

{{-- Lipa Namba Form --}}
<form id="payoutFormLipa" method="POST" action="{{ route('payouts.store') }}" data-payout-form style="display:none;">
    @csrf
    <input type="hidden" name="method" value="lipa_namba">
    <input type="hidden" name="gateway" value="clickpesa">
    <div class="create-grid">
        <div class="settings-panel">
            <div class="settings-section"><h4>Beneficiary</h4></div>
            <div class="field">
                <label>Beneficiary name *</label>
                <input type="text" name="beneficiary_name" placeholder="e.g. Shop Ltd" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Lipa Namba *</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="lipa_namba" id="beneficiaryAccountLipa" placeholder="48001268" required style="flex:1;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="verifyAccountLipa()" style="white-space:nowrap;">Verify</button>
                    </div>
                </div>
                <div class="field">
                    <label>Provider Code *</label>
                    <input type="text" name="provider_code" placeholder="503" list="lipaProviders2" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <datalist id="lipaProviders2">
                        <option value="503">Vodacom M-Pesa — 503</option>
                        <option value="504">Tigo Pesa — 504</option>
                    </datalist>
                </div>
            </div>
            <div class="field">
                <label>TanQR (alternative)</label>
                <input type="text" name="qr_code" placeholder="tz.go.bot.tips ..." style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                <div class="prov-hint">Provide either Lipa Namba + Provider Code OR TanQR, not both.</div>
            </div>
            <div id="verifyResultLipa" style="margin-top:8px; font-size:12.5px; display:none; padding:8px 10px; border-radius:8px;"></div>

            <div class="settings-section"><h4>Amount & Purpose</h4></div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field">
                    <label>Amount (TZS) *</label>
                    <input type="number" step="0.01" min="1000" name="amount" placeholder="500000" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                </div>
                <div class="field">
                    <label>Currency</label>
                    <select name="currency" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                        <option value="TZS">TZS</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Internal notes</label>
                <textarea name="notes" rows="2" placeholder="Reason" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></textarea>
            </div>
            <div class="field" style="background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;">
                <label>Cashier OTP *</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" name="otp" placeholder="6-digit code" required maxlength="6" pattern="\d{6}" style="flex:1;letter-spacing:.2em;text-align:center;font-weight:700;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="requestOtp('lipa')" style="white-space:nowrap;">Send OTP</button>
                </div>
                <input type="hidden" name="otp_phone" value="{{ auth()->user()?->phone ?? '' }}">
            </div>
        </div>
        <div>
            <div class="balance-card" style="margin-bottom:18px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.8;">Available Balance</div>
                <b style="font-size:22px;display:block;margin-top:4px;">@money($balances['available'] ?? 3420000)</b>
            </div>
            <div class="settings-panel">
                <h3 style="margin-top:0;">How Lipa Namba works</h3>
                <ol class="detail-list">
                    <li><strong>Verify</strong> — Lipa Namba + provider code resolves the merchant.</li>
                    <li>Alternatively paste a <strong>TanQR</strong> string.</li>
                    <li><strong>Cashier OTP</strong> — required before submit.</li>
                    <li>Dispatch and track under <strong>Payouts</strong>.</li>
                </ol>
                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;margin-top:8px;">Submit Lipa Namba →</button>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
    function switchPayoutTab(tab){
        document.getElementById('tabSimu').classList.toggle('active', tab==='simu');
        document.getElementById('tabBank').classList.toggle('active', tab==='bank');
        document.getElementById('tabLipa').classList.toggle('active', tab==='lipa');
        document.getElementById('payoutFormSimu').style.display = tab==='simu' ? 'block' : 'none';
        document.getElementById('payoutFormBank').style.display = tab==='bank' ? 'block' : 'none';
        document.getElementById('payoutFormLipa').style.display = tab==='lipa' ? 'block' : 'none';
    }
    function fmt(n){ return 'TZS ' + Number(n).toLocaleString('en-US',{maximumFractionDigits:2}); }
    async function verifyAccountSimu(){
        const account = document.getElementById('beneficiaryAccountSimu').value.trim();
        const resultEl = document.getElementById('verifyResultSimu');
        if(!account){ toast('Enter phone number first','error'); return; }
        resultEl.style.display='block'; resultEl.style.background='var(--sand-100)'; resultEl.style.color='var(--ink-soft)'; resultEl.style.border='1px solid var(--line)'; resultEl.textContent='Verifying account...';
        try {
            const r = await fetch('{{ route('payouts.verify-account') }}', {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                body: JSON.stringify({ phoneNumber: account, amount: 1000, orderReference: 'VERIFY'+Date.now(), method: 'mobile_money' })
            });
            const data = await r.json();
            if(data.success){
                resultEl.style.background='var(--acacia-100)'; resultEl.style.color='var(--acacia-600)'; resultEl.style.border='1px solid var(--acacia-500)';
                resultEl.innerHTML='✓ Verified: <strong>'+(data.accountName||'Valid')+'</strong> · '+(data.channelProvider||'');
                const nameInput = document.querySelector('#payoutFormSimu input[name="beneficiary_name"]');
                if(nameInput && !nameInput.value.trim() && data.accountName) nameInput.value=data.accountName;
            } else {
                resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.textContent='✕ '+ (data.error||'Failed');
            }
        } catch(e){ resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.textContent='Network error: '+e.message; }
    }
    async function verifyAccountBank(){
        const account = document.getElementById('beneficiaryAccountBank').value.trim();
        const resultEl = document.getElementById('verifyResultBank');
        if(!account){ toast('Enter account number first','error'); return; }
        resultEl.style.display='block'; resultEl.style.background='var(--sand-100)'; resultEl.textContent='Verifying bank account...';
        try {
            const bic = document.querySelector('#payoutFormBank input[name="bic"]')?.value || document.querySelector('#payoutFormBank select[name="bank_name"]')?.value || 'CRDBTZTZ';
            const r = await fetch('{{ route('payouts.verify-account') }}', {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                body: JSON.stringify({ accountNumber: account, bic: bic, amount: 1000, orderReference: 'VERIFY'+Date.now(), method: 'bank' })
            });
            const data = await r.json();
            if(data.success){
                resultEl.style.background='var(--acacia-100)'; resultEl.style.color='var(--acacia-600)'; resultEl.innerHTML='✓ Verified: <strong>'+(data.accountName||'Valid')+'</strong> · '+ (data.channelProvider||'') +' · '+(data.nameLookupStatus||'RESOLVED');
                const nameInput = document.querySelector('#payoutFormBank input[name="beneficiary_name"]');
                if(nameInput && !nameInput.value.trim() && data.accountName) nameInput.value=data.accountName;
            } else {
                resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.textContent='✕ '+ (data.error||'Failed');
            }
        } catch(e){ resultEl.style.background='var(--danger-100)'; resultEl.textContent='Network error: '+e.message; }
    }
    async function verifyAccountLipa(){
        const lipa = document.getElementById('beneficiaryAccountLipa').value.trim();
        const resultEl = document.getElementById('verifyResultLipa');
        if(!lipa){ toast('Enter Lipa Namba first','error'); return; }
        resultEl.style.display='block'; resultEl.textContent='Verifying Lipa Namba...';
        try {
            const providerCode = document.querySelector('#payoutFormLipa input[name="provider_code"]')?.value || '503';
            const r = await fetch('{{ route('payouts.verify-account') }}', {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                body: JSON.stringify({ lipaNamba: lipa, providerCode: providerCode, amount: 1000, orderReference: 'VERIFY'+Date.now(), method: 'lipa_namba' })
            });
            const data = await r.json();
            if(data.success){
                resultEl.style.background='var(--acacia-100)'; resultEl.style.color='var(--acacia-600)'; resultEl.innerHTML='✓ Verified: <strong>'+(data.accountName||'Valid')+'</strong>';
                const nameInput = document.querySelector('#payoutFormLipa input[name="beneficiary_name"]');
                if(nameInput && !nameInput.value.trim() && data.accountName) nameInput.value=data.accountName;
            } else {
                resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.textContent='✕ '+ (data.error||'Failed');
            }
        } catch(e){ resultEl.textContent='Network error: '+e.message; }
    }
    async function requestOtp(tab){
        const phone = document.querySelector('#payoutForm'+tab.charAt(0).toUpperCase()+tab.slice(1)+' input[name="otp_phone"]')?.value || '{{ auth()->user()?->phone ?? '' }}' || prompt('Enter phone for OTP:');
        if(!phone){ toast('Phone required for OTP','error'); return; }
        try {
            const r=await fetch('{{ route('payouts.otp.generate') }}',{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                body: JSON.stringify({phone: phone, purpose: 'initiate'})
            });
            const data=await r.json();
            if(data.success){
                toast(data.message||'OTP sent','success');
                if(data.debug_otp) toast('Demo OTP: '+data.debug_otp,'success');
            } else {
                toast(data.message||'OTP failed','error');
            }
        } catch(e){ toast('Network error','error'); }
    }
    window.requestOtp = requestOtp;
    switchPayoutTab('simu');
    document.querySelectorAll('[data-payout-form]').forEach(f=>{
        f.addEventListener('submit', e=>{
            e.preventDefault();
            submitForm(f,{method:'POST', done:(d)=>{
                toast(d.message||'Payout submitted','success');
                setTimeout(()=>{ window.location.href=d.redirect || '{{ route("payouts.index") }}'; },700);
            }});
        });
    });
</script>
@endsection
