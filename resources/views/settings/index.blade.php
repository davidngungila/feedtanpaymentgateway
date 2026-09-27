@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
    @php
        $gen = $settings['general'] ?? [];
        $comm = $settings['commissions'] ?? [];
        $sec = $settings['security'] ?? [];
        $notif = $settings['notifications'] ?? [];
        $gw = $settings['gateways'] ?? [];
        $payoutCfg = $settings['payouts'] ?? [];
        $smsCfg = $settings['sms'] ?? [];
        // Removed panes fallback to general
        if (in_array($pane, ['commissions','cashpoint'], true)) { $pane = 'general'; }
    @endphp
    <div class="view-head">
        <div>
            <h2>System Settings</h2>
            <p class="sub">Business profile, payments gateways, payout controls, security & notifications · ClickPesa Feedtan Online.</p>
        </div>
        <div class="view-actions">
            <button class="btn btn-ghost" onclick="window.location.reload()">Reset form</button>
            <button class="btn btn-ghost" onclick="openModal('testApiModal')">Test API</button>
        </div>
    </div>

    <div style="width:100%;">
        <div class="settings-panel" id="settingsPanel" style="width:100%; max-width:none; padding:28px; box-shadow:var(--shadow-sm);">
            @if ($pane === 'general')
                <h3>General · Business Profile</h3>
                <form method="POST" action="{{ route('settings.store') }}" data-settings-form>
                    @csrf
                    <div class="field"><label>Business name</label><input type="text" name="general[business_name]" value="{{ $gen['business_name'] ?? 'ClickPesa Feedtan Online' }}" placeholder="ClickPesa Feedtan Online"></div>
                    <div class="field"><label>Legal name / TIN</label><input type="text" name="general[legal_name]" value="{{ $gen['legal_name'] ?? '' }}" placeholder="Feedtan Co. Ltd — TIN 123-456-789"></div>
                    <div class="field"><label>Address</label><input type="text" name="general[address]" value="{{ $gen['address'] ?? '' }}" placeholder="Mikocheni, Dar es Salaam, Tanzania"></div>
                    <div class="form-row">
                        <div class="field"><label>Contact email</label><input type="email" name="general[contact_email]" value="{{ $gen['contact_email'] ?? '' }}" placeholder="hello@clickpesa.co.tz"></div>
                        <div class="field"><label>Contact phone</label><input type="text" name="general[contact_phone]" value="{{ $gen['contact_phone'] ?? '' }}" placeholder="+255 7xx xxx xxx"></div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Default currency</label>
                            <select name="general[currency]">
                                <option value="TZS" {{ ($gen['currency'] ?? 'TZS') === 'TZS' ? 'selected' : '' }}>TZS – Tanzanian Shilling</option>
                                <option value="KES" {{ ($gen['currency'] ?? '') === 'KES' ? 'selected' : '' }}>KES – Kenyan Shilling</option>
                                <option value="UGX" {{ ($gen['currency'] ?? '') === 'UGX' ? 'selected' : '' }}>UGX – Ugandan Shilling</option>
                                <option value="USD" {{ ($gen['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                            </select>
                        </div>
                        <div class="field"><label>Timezone</label>
                            <select name="general[timezone]">
                                <option value="Africa/Dar_es_Salaam" {{ ($gen['timezone'] ?? 'Africa/Dar_es_Salaam')==='Africa/Dar_es_Salaam'?'selected':'' }}>Africa/Dar_es_Salaam</option>
                                <option value="UTC" {{ ($gen['timezone'] ?? '')==='UTC'?'selected':'' }}>UTC</option>
                            </select>
                        </div>
                    </div>
                    <div class="field"><label>Receipt footer text</label><textarea name="general[receipt_footer]" rows="2" placeholder="Thank you for using ClickPesa Feedtan Online">{{ $gen['receipt_footer'] ?? 'Thank you for using ClickPesa Feedtan Online · support@clickpesa.co.tz' }}</textarea></div>
                    <div class="field">
                        <label>Branding — Logo URL</label>
                        <input type="text" name="general[logo_url]" value="{{ $gen['logo_url'] ?? '' }}" placeholder="https://.../logo.svg">
                        <div style="font-size:11.5px;color:var(--ink-soft);margin-top:6px;">Used on receipts, PDFs and emails. SVG or PNG, 240×60 recommended.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save general settings</button>
                </form>

            @elseif ($pane === 'gateways')
                <h3>ClickPesa Configuration — Simple</h3>
                <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px;">ClickPesa only — live credentials for <code style="background:var(--sand-100);padding:2px 6px;border-radius:6px;">clickpesanew</code>.</p>

                <form method="POST" action="{{ route('settings.store') }}" data-settings-form>
                    @csrf
                    <div class="field"><label>Webhook Endpoint</label>
                        <div style="display:flex;gap:8px;">
                            <code style="flex:1;background:var(--sand-100);padding:10px 12px;border-radius:8px;border:1px solid var(--line);font-size:12px;word-break:break-all;">{{ url('/api/webhooks/payments') }}</code>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="navigator.clipboard.writeText('{{ url('/api/webhooks/payments') }}');toast('Copied','success')">Copy</button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Client ID *</label>
                            <input type="text" name="gateways[clickpesa][client_id]" value="{{ $gw['clickpesa']['client_id'] ?? config('services.clickpesa.client_id') }}" placeholder="IDDbB4UFwaPGi8ECWX7xiSDapLIBLM3O" required>
                        </div>
                        <div class="field"><label>API Key *</label>
                            <div class="pwd-wrap"><input type="password" name="gateways[clickpesa][api_key]" value="" placeholder="{{ !empty($gw['clickpesa']['api_key']) ? '••••••••••••••••' : 'SKGM6Jx4iA4PO2b8buIV5xw1JIbueFNBLWLctdNx5B' }}" required><button type="button" class="pwd-toggle" onclick="this.previousElementSibling.type=this.previousElementSibling.type==='password'?'text':'password'">👁</button></div>
                        </div>
                    </div>
                    <div class="field"><label>Webhook Secret</label><div class="pwd-wrap"><input type="password" name="gateways[clickpesa][webhook_secret]" value="" placeholder="whsec_..."><button type="button" class="pwd-toggle" onclick="this.previousElementSibling.type=this.previousElementSibling.type==='password'?'text':'password'">👁</button></div></div>

                    <div style="display:flex;gap:10px;margin-top:18px;">
                        <button type="submit" class="btn btn-primary">Save gateway settings</button>
                        <button type="button" class="btn btn-ghost" onclick="openModal('testApiModal')">Test connection</button>
                    </div>
                </form>

            @elseif ($pane === 'payouts')
                <h3>Payout Controls</h3>
                <form method="POST" action="{{ route('settings.store') }}" data-settings-form>
                    @csrf
                    <div class="form-row">
                        <div class="field"><label>Daily payout limit (TZS)</label><input type="number" name="payouts[daily_limit]" step="any" min="0" value="{{ $payoutCfg['daily_limit'] ?? 50000000 }}"></div>
                        <div class="field"><label>Per-transaction max (TZS)</label><input type="number" name="payouts[per_txn_max]" step="any" min="0" value="{{ $payoutCfg['per_txn_max'] ?? 10000000 }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Per-transaction min (TZS)</label><input type="number" name="payouts[per_txn_min]" step="any" min="0" value="{{ $payoutCfg['per_txn_min'] ?? 1000 }}"></div>
                        <div class="field"><label>Require approval above (TZS)</label><input type="number" name="payouts[approval_threshold]" step="any" min="0" value="{{ $payoutCfg['approval_threshold'] ?? 1000000 }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Approval mode</label>
                            <select name="payouts[approval_mode]">
                                <option value="single" {{ ($payoutCfg['approval_mode'] ?? 'single')==='single'?'selected':'' }}>Single approver</option>
                                <option value="dual" {{ ($payoutCfg['approval_mode'] ?? '')==='dual'?'selected':'' }}>Dual approval (4-eyes)</option>
                            </select>
                        </div>
                        <div class="field"><label>Auto-approve below threshold?</label>
                            <select name="payouts[auto_approve]">
                                <option value="1" {{ ($payoutCfg['auto_approve'] ?? '1')=='1'?'selected':'' }}>Yes — dispatch immediately</option>
                                <option value="0" {{ ($payoutCfg['auto_approve'] ?? '')=='0'?'selected':'' }}>No — queue for review</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Default payout fee %</label><input type="number" step="any" name="payouts[default_fee_percent]" value="{{ $payoutCfg['default_fee_percent'] ?? 1.2 }}"></div>
                        <div class="field"><label>Fixed fee (TZS)</label><input type="number" step="any" name="payouts[fixed_fee]" value="{{ $payoutCfg['fixed_fee'] ?? 500 }}"></div>
                    </div>
                    <div class="field"><label>Allowed payout methods</label>
                        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:6px;">
                            @php $allowed = $payoutCfg['allowed_methods'] ?? ['mobile_money','bank','wallet']; @endphp
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--coffee-700);"><input type="checkbox" name="payouts[allowed_methods][]" value="mobile_money" {{ in_array('mobile_money',$allowed)?'checked':'' }}> Mobile Money</label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--coffee-700);"><input type="checkbox" name="payouts[allowed_methods][]" value="bank" {{ in_array('bank',$allowed)?'checked':'' }}> Bank</label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--coffee-700);"><input type="checkbox" name="payouts[allowed_methods][]" value="wallet" {{ in_array('wallet',$allowed)?'checked':'' }}> Wallet</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save payout settings</button>
                </form>

            @elseif ($pane === 'security')
                <h3>Security & Compliance</h3>
                <form method="POST" action="{{ route('settings.store') }}" data-settings-form>
                    @csrf
                    <div class="form-row">
                        <div class="field"><label>Max transaction limit (TZS)</label><input type="number" name="security[max_transaction_limit]" step="any" min="0" value="{{ $sec['max_transaction_limit'] ?? 3000000 }}" placeholder="3,000,000"></div>
                        <div class="field"><label>Min withdrawal limit (TZS)</label><input type="number" name="security[min_withdrawal_limit]" step="any" min="0" value="{{ $sec['min_withdrawal_limit'] ?? 1000 }}" placeholder="1,000"></div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Require approval above (TZS)</label><input type="number" name="security[require_approval_above]" step="any" min="0" value="{{ $sec['require_approval_above'] ?? 1000000 }}" placeholder="1,000,000"></div>
                        <div class="field"><label>Session timeout (minutes)</label><input type="number" name="security[session_timeout_minutes]" step="1" min="1" value="{{ $sec['session_timeout_minutes'] ?? 30 }}"></div>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Enforce 2FA for admins</strong><span>Require authenticator for all admin logins and payout approvals.</span></div>
                        <select name="security[enforce_2fa_admin]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($sec['enforce_2fa_admin'] ?? '1')=='1'?'selected':'' }}>Enforced</option>
                            <option value="0" {{ ($sec['enforce_2fa_admin'] ?? '')=='0'?'selected':'' }}>Optional</option>
                        </select>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Enforce 2FA for payouts</strong><span>Require 2FA challenge when creating/approving payouts.</span></div>
                        <select name="security[enforce_2fa_payouts]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($sec['enforce_2fa_payouts'] ?? '1')=='1'?'selected':'' }}>Enforced</option>
                            <option value="0" {{ ($sec['enforce_2fa_payouts'] ?? '')=='0'?'selected':'' }}>Optional</option>
                        </select>
                    </div>
                    <div class="form-row" style="margin-top:12px;">
                        <div class="field"><label>IP allowlist (comma-separated, optional)</label><input type="text" name="security[ip_allowlist]" value="{{ $sec['ip_allowlist'] ?? '' }}" placeholder="196.x.x.x, 102.x.x.x"></div>
                        <div class="field"><label>Audit retention (days)</label><input type="number" name="security[audit_retention_days]" value="{{ $sec['audit_retention_days'] ?? 365 }}"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save security settings</button>
                </form>

            @elseif ($pane === 'sms')
                <h3>SMS Configuration</h3>
                <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px;">Configure SMS provider, templates and beneficiaries. Test mode sends dummy data (no charge).</p>
                <form method="POST" action="{{ route('settings.store') }}" data-settings-form>
                    @csrf
                    <div class="field"><label>SMS Base URL</label><input type="text" name="sms[base_url]" value="{{ $smsCfg['base_url'] ?? 'https://messaging-service.co.tz' }}" placeholder="https://messaging-service.co.tz"></div>
                    <div class="field"><label>API Token — Generate Token <span style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--ink-soft);">— Bearer token (f9a89f439206e27169ead766463ca92c)</span></label><div class="pwd-wrap"><input type="password" name="sms[token]" value="" placeholder="{{ !empty($smsCfg['token']) ? '••••••••••••••••' : 'f9a89f439206e27169ead766463ca92c' }}"><button type="button" class="pwd-toggle" onclick="this.previousElementSibling.type=this.previousElementSibling.type==='password'?'text':'password'">👁</button></div></div>
                    <div class="form-row">
                        <div class="field"><label>Sender ID</label><input type="text" name="sms[from]" value="{{ $smsCfg['from'] ?? 'FEEDTAN CMG' }}" placeholder="FEEDTAN CMG"></div>
                        <div class="field"><label>API Timeout (seconds)</label><input type="number" name="sms[timeout]" value="{{ $smsCfg['timeout'] ?? 30 }}" min="5" max="120"></div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Enable SMS Notifications</label>
                            <select name="sms[enabled]">
                                <option value="1" {{ ($smsCfg['enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ ($smsCfg['enabled'] ?? '0') == '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Test Mode (No Real SMS)</label>
                            <select name="sms[test_mode]">
                                <option value="1" {{ ($smsCfg['test_mode'] ?? '0') == '1' ? 'selected' : '' }}>Test Mode — dummy data, no charge</option>
                                <option value="0" {{ ($smsCfg['test_mode'] ?? '0') == '0' ? 'selected' : '' }}>Live — real SMS</option>
                            </select>
                        </div>
                    </div>
                    <div class="field"><label>Payment SMS Template <span style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--ink-soft);">— use {customer_name}, {amount}, {reference}</span></label><textarea name="sms[template]" rows="3" placeholder="Hello {customer_name}, your payment of {amount} TZS has been received. Reference: {reference}">{{ $smsCfg['template'] ?? 'Hello {customer_name}, your payment of {amount} TZS has been received. Reference: {reference}' }}</textarea></div>

                    <div class="field" style="margin-top:18px;">
                        <label>Beneficiaries <span style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--ink-soft);">— phone numbers to notify (255...)</span></label>
                        <div id="beneficiariesList" style="display:flex;flex-direction:column;gap:8px;">
                            @php $beneficiaries = $smsCfg['beneficiaries'] ?? ['']; @endphp
                            @foreach($beneficiaries as $idx => $ben)
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <input type="text" name="sms[beneficiaries][]" value="{{ $ben }}" placeholder="2557xxxxxxxx" style="flex:1;padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;font-size:13px;">
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="this.parentElement.remove()" style="padding:8px 10px;">✕</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="addBeneficiary()" style="margin-top:8px;">+ Add another beneficiary</button>
                    </div>

                    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Save SMS Settings</button>
                        <button type="button" class="btn btn-ghost" onclick="testSmsConnection()">Test Connection</button>
                        <button type="button" class="btn btn-ghost" onclick="testSmsSend(false)" style="background:var(--acacia-100);color:var(--acacia-600);border:1px solid var(--acacia-500);">Send Test SMS (Live)</button>
                        <button type="button" class="btn btn-ghost" onclick="testSmsSend(true)" style="background:var(--gold-100);color:#8a6418;border:1px solid var(--gold-500);">Send Test SMS (Test Mode)</button>
                    </div>
                    <div id="smsTestResult" style="margin-top:12px; display:none; padding:12px; border-radius:10px; font-size:12.5px; white-space:pre-wrap; word-break:break-word; font-family:ui-monospace,monospace; max-height:240px; overflow:auto;"></div>
                </form>
                <script>
                    function addBeneficiary(){
                        const list=document.getElementById('beneficiariesList');
                        const div=document.createElement('div');
                        div.style.cssText='display:flex;gap:8px;align-items:center;';
                        div.innerHTML='<input type=\"text\" name=\"sms[beneficiaries][]\" placeholder=\"2557xxxxxxxx\" style=\"flex:1;padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;font-size:13px;\"><button type=\"button\" class=\"btn btn-ghost btn-sm\" onclick=\"this.parentElement.remove()\" style=\"padding:8px 10px;\">✕</button>';
                        list.appendChild(div);
                    }
                    async function testSmsConnection(){
                        const resultEl=document.getElementById('smsTestResult');
                        resultEl.style.display='block'; resultEl.style.background='var(--sand-100)'; resultEl.style.border='1px solid var(--line)'; resultEl.style.color='var(--ink-soft)'; resultEl.textContent='Testing SMS API connection...';
                        try {
                            const r=await fetch('{{ route('settings.test-messaging') }}', {
                                method:'POST',
                                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                                body: JSON.stringify({test:true, connection_only:true})
                            });
                            const data=await r.json();
                            if(data.success){
                                resultEl.style.background='var(--acacia-100)'; resultEl.style.color='var(--acacia-600)'; resultEl.style.border='1px solid var(--acacia-500)';
                                resultEl.textContent='✓ Connection OK: '+ (data.message||'Authorized') + (data.raw ? '\n\n'+JSON.stringify(data.raw,null,2).substring(0,600) : '');
                                toast('SMS connection OK','success');
                            } else {
                                resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.style.border='1px solid var(--danger)';
                                resultEl.textContent='✕ Connection failed: '+(data.error||data.message||'Unknown')+'\n\n'+JSON.stringify(data.raw||data,null,2).substring(0,600);
                                toast(data.error||'Connection failed','error');
                            }
                        } catch(e){ resultEl.style.background='var(--danger-100)'; resultEl.textContent='Network error: '+e.message; toast('Network error','error'); }
                    }
                    async function testSmsSend(isTest){
                        const resultEl=document.getElementById('smsTestResult');
                        const firstBen = document.querySelector('#beneficiariesList input[name=\"sms[beneficiaries][]\"]')?.value?.trim() || '255655000000';
                        const template = document.querySelector('textarea[name=\"sms[template]\"]')?.value || 'Hello {customer_name}, your payment of {amount} TZS has been received. Reference: {reference}';
                        const text = template.replace('{customer_name}','Test Customer').replace('{amount}','10000').replace('{reference}','TEST'+Date.now());
                        resultEl.style.display='block'; resultEl.style.background='var(--sand-100)'; resultEl.textContent=(isTest?'Testing SMS (Test Mode, no charge)...':'Sending live SMS to '+firstBen+'...');
                        try {
                            const r=await fetch('{{ route('settings.test-messaging') }}', {
                                method:'POST',
                                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                                body: JSON.stringify({to: firstBen, text: text, test: isTest})
                            });
                            const data=await r.json();
                            if(data.success){
                                resultEl.style.background='var(--acacia-100)'; resultEl.style.color='var(--acacia-600)'; resultEl.textContent='✓ '+(isTest?'Test SMS (dummy) sent':'SMS sent')+': '+(data.message||'OK')+'\nTo: '+firstBen+'\nText: '+text+(data.raw ? '\n\n'+JSON.stringify(data.raw,null,2).substring(0,600):'');
                                toast(isTest?'Test SMS sent (dummy)':'SMS sent','success');
                            } else {
                                resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.textContent='✕ Send failed: '+(data.error||data.message||'Unknown')+'\n\n'+JSON.stringify(data.raw||data,null,2).substring(0,600);
                                toast(data.error||'Send failed','error');
                            }
                        } catch(e){ resultEl.style.background='var(--danger-100)'; resultEl.textContent='Network error: '+e.message; toast('Network error','error'); }
                    }
                    function testSms(){
                        const form=document.querySelector('form[data-settings-form]');
                        const data=new FormData(form);
                        // Use test endpoint
                        fetch('{{ route('settings.test-messaging') }}', {
                            method:'POST',
                            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                            body: JSON.stringify({to: (data.get('sms[beneficiaries][]') || '255655000000'), text: 'Test SMS from ClickPesa Feedtan Online', test: true}),
                        }).then(r=>r.json()).then(d=>toast(d.message||'Test sent','success')).catch(()=>toast('Test failed','error'));
                        // Simpler: just call the existing test modal with messaging
                        openModal('testApiModal');
                        document.getElementById('testGatewaySelect').value='messaging';
                        toggleTestFields();
                    }
                </script>
            @else
                <h3>Notifications</h3>
                <form method="POST" action="{{ route('settings.store') }}" data-settings-form>
                    @csrf
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Daily summary email</strong><span>Volume, commissions and settlement summary every day at 07:00.</span></div>
                        <select name="notifications[email_daily_summary]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($notif['email_daily_summary'] ?? '1') == 1 ? 'selected' : '' }}>On</option>
                            <option value="0" {{ ($notif['email_daily_summary'] ?? '') == 0 ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Payment received alerts</strong><span>Notify on every successful online payment (email + in-app).</span></div>
                        <select name="notifications[email_payments]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($notif['email_payments'] ?? '1') == 1 ? 'selected' : '' }}>On</option>
                            <option value="0" {{ ($notif['email_payments'] ?? '') == 0 ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Payout approval requests</strong><span>Notify approvers when a payout is queued.</span></div>
                        <select name="notifications[payout_approval]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($notif['payout_approval'] ?? '1') == 1 ? 'selected' : '' }}>On</option>
                            <option value="0" {{ ($notif['payout_approval'] ?? '') == 0 ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Payout status (completed/failed)</strong><span>Notify requester when payout succeeds or fails.</span></div>
                        <select name="notifications[payout_status]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($notif['payout_status'] ?? '1') == 1 ? 'selected' : '' }}>On</option>
                            <option value="0" {{ ($notif['payout_status'] ?? '') == 0 ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>SMS float alerts</strong><span>Alert when float drops below threshold.</span></div>
                        <select name="notifications[sms_float_alerts]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($notif['sms_float_alerts'] ?? '0') == 1 ? 'selected' : '' }}>On</option>
                            <option value="0" {{ ($notif['sms_float_alerts'] ?? '0') == 0 ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-text"><strong>Failed transaction alerts</strong><span>Immediate email when transaction/payment fails.</span></div>
                        <select name="notifications[email_failed_txns]" style="padding:8px 10px;border:1.5px solid var(--line);border-radius:9px;font-size:13px;font-weight:600;background:var(--white);color:var(--coffee-700);">
                            <option value="1" {{ ($notif['email_failed_txns'] ?? '1') == 1 ? 'selected' : '' }}>On</option>
                            <option value="0" {{ ($notif['email_failed_txns'] ?? '') == 0 ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:18px;">Save notification settings</button>
                </form>
            @endif
        </div>
    </div>

    <div class="modal-backdrop" id="testApiModal">
        <div class="popup" style="max-width:520px; width:100%; margin:auto;">
            <div class="modal-head"><h3>Test ClickPesa API</h3><button class="modal-close" onclick="closeModal('testApiModal')">✕</button></div>
            <form id="testApiForm" method="POST" action="{{ route('settings.test-gateway') ?? '#' }}" data-test-form>
                @csrf
                <input type="hidden" name="gateway" value="clickpesa">
                <div class="modal-body">
                    <div id="testProgressWrap" style="margin-top:4px; display:none;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;color:var(--ink-soft);margin-bottom:6px;">
                            <span id="testStepLabel">Step 1/4 — Validating credentials</span>
                            <span id="testProgressPct">0%</span>
                        </div>
                        <div style="height:8px;background:var(--sand-100);border:1px solid var(--line);border-radius:20px;overflow:hidden;">
                            <div id="testProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,var(--terracotta-600),var(--gold-500));border-radius:20px;transition:width .4s ease;"></div>
                        </div>
                        <div style="display:flex;gap:6px;margin-top:10px;">
                            <span class="test-step" data-step="1" style="flex:1;text-align:center;padding:6px 4px;border-radius:8px;background:var(--sand-100);border:1px solid var(--line);font-size:10px;font-weight:700;color:var(--ink-soft);">1. Validate</span>
                            <span class="test-step" data-step="2" style="flex:1;text-align:center;padding:6px 4px;border-radius:8px;background:var(--sand-100);border:1px solid var(--line);font-size:10px;font-weight:700;color:var(--ink-soft);">2. Generate</span>
                            <span class="test-step" data-step="3" style="flex:1;text-align:center;padding:6px 4px;border-radius:8px;background:var(--sand-100);border:1px solid var(--line);font-size:10px;font-weight:700;color:var(--ink-soft);">3. Verify</span>
                            <span class="test-step" data-step="4" style="flex:1;text-align:center;padding:6px 4px;border-radius:8px;background:var(--sand-100);border:1px solid var(--line);font-size:10px;font-weight:700;color:var(--ink-soft);">4. Done</span>
                        </div>
                    </div>
                    <div id="testApiResult" style="margin-top:12px; display:none; padding:12px; border-radius:10px; font-size:12.5px; font-family:ui-monospace,monospace; white-space:pre-wrap; word-break:break-word; max-height:200px; overflow:auto;"></div>
                </div>
                <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('testApiModal')">Close</button><button type="submit" class="btn btn-primary" id="testApiBtn">Test ClickPesa</button></div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-settings-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitForm(form, { method: 'POST', done: () => toast('Settings saved successfully.', 'success') });
        });
    });
    document.querySelectorAll('[data-cashpoint-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitForm(form, { method: 'PUT', done: () => toast('Cash point saved successfully.', 'success') });
        });
    });
    function setTestProgress(step, label){
        const pct = Math.round(step/4*100);
        const bar=document.getElementById('testProgressBar');
        const pctEl=document.getElementById('testProgressPct');
        const labelEl=document.getElementById('testStepLabel');
        if(bar) bar.style.width=pct+'%';
        if(pctEl) pctEl.textContent=pct+'%';
        if(labelEl) labelEl.textContent='Step '+step+'/4 — '+label;
        document.querySelectorAll('.test-step').forEach(el=>{
            const s=parseInt(el.dataset.step,10);
            if(s < step){ el.style.background='var(--acacia-100)'; el.style.color='var(--acacia-600)'; el.style.borderColor='var(--acacia-500)'; }
            else if(s===step){ el.style.background='var(--terracotta-100)'; el.style.color='var(--terracotta-600)'; el.style.borderColor='var(--terracotta-500)'; }
            else { el.style.background='var(--sand-100)'; el.style.color='var(--ink-soft)'; el.style.borderColor='var(--line)'; }
        });
    }
    document.querySelectorAll('[data-test-form]').forEach(f=>{
        f.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const resultEl=document.getElementById('testApiResult');
            const btn=document.getElementById('testApiBtn');
            const wrap=document.getElementById('testProgressWrap');
            if(wrap) wrap.style.display='block';
            setTestProgress(1,'Validating credentials');
            if(resultEl){ resultEl.style.display='block'; resultEl.style.background='var(--sand-100)'; resultEl.style.border='1px solid var(--line)'; resultEl.style.color='var(--ink-soft)'; resultEl.textContent='Testing ClickPesa live...'; }
            if(btn){ btn.disabled=true; btn.textContent='Testing...'; }
            try {
                setTestProgress(2,'Generating token');
                const fd=new FormData(f);
                // Small delay to show step
                await new Promise(r=>setTimeout(r,400));
                setTestProgress(3,'Verifying token');
                const r=await fetch(f.action,{
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                    body: fd
                });
                const data=await r.json().catch(()=>({}));
                setTestProgress(4, data.success ? 'Complete — token OK' : 'Complete — failed');
                if(resultEl){
                    if(data.success){
                        resultEl.style.background='var(--acacia-100)'; resultEl.style.color='var(--acacia-600)'; resultEl.style.border='1px solid var(--acacia-500)';
                        resultEl.textContent='✓ '+(data.message||'OK')+'\n\n'+JSON.stringify(data.raw||data.body||data,null,2).substring(0,800);
                    } else {
                        resultEl.style.background='var(--danger-100)'; resultEl.style.color='var(--danger)'; resultEl.style.border='1px solid var(--danger)';
                        resultEl.textContent='✕ '+(data.message||data.error||'Failed')+' (HTTP '+(data.status||'?')+')\n\n'+JSON.stringify(data.raw||data.body||data,null,2).substring(0,800);
                    }
                }
                toast(data.message||(data.success?'OK':'Failed'), data.success?'success':'error');
            } catch(err){
                setTestProgress(4,'Complete — network error');
                if(resultEl){ resultEl.style.background='var(--danger-100)'; resultEl.textContent='Network error: '+err.message; }
                toast('Network error','error');
            } finally {
                if(btn){ btn.disabled=false; btn.textContent='Test ClickPesa'; }
            }
        });
    });
    // Auto-start ClickPesa test when modal opens
    const origOpenModal = window.openModal;
    window.openModal = function(id){
        if(origOpenModal) origOpenModal(id);
        else {
            const el=document.getElementById(id);
            if(el) el.classList.add('show');
        }
        if(id==='testApiModal'){
            const form=document.getElementById('testApiForm');
            const resultEl=document.getElementById('testApiResult');
            if(resultEl){ resultEl.style.display='none'; resultEl.textContent=''; }
            // Auto-submit after a short delay to allow modal to show
            setTimeout(()=>{ if(form) form.requestSubmit(); }, 300);
        }
    };
</script>
@endsection
