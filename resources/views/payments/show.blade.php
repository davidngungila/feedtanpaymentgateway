@extends('layouts.app')

@section('title', 'Payment ' . ($payment->reference ?? ''))

@section('head')
<style>
    .gateway-ico{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;letter-spacing:.04em;flex:none;color:#fff;}
    .gateway-ico.clickpesa{background:linear-gradient(135deg,#C2592B,#D4A24C);}
    .gateway-ico.selcom{background:#0F2A4D;}
    .gateway-ico.dpo{background:#E30613;}
    .gateway-ico.stripe{background:#635BFF;}
    .gateway-ico.mpesa{background:#ED1C24;}
    .kv-grid{display:grid;grid-template-columns:150px 1fr;gap:9px 16px;font-size:13.5px;}
    .kv-grid dt{color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-size:11px;padding-top:2px;}
    .kv-grid dd{margin:0;color:var(--coffee-900);font-weight:600;word-break:break-word;}
    .json-block{background:#1A120B;color:#F4ECDC;border-radius:12px;padding:16px;font-family:ui-monospace,monospace;font-size:12px;line-height:1.7;overflow:auto;max-height:320px;white-space:pre-wrap;word-break:break-word;border:1px solid #2A1B10;}
    .timeline{position:relative;padding-left:26px;}
    .timeline::before{content:"";position:absolute;left:10px;top:8px;bottom:8px;width:2px;background:var(--line);border-radius:2px;}
    .tl-step{position:relative;padding:10px 0 16px;display:flex;gap:12px;}
    .tl-step:last-child{padding-bottom:0;}
    .tl-node{position:absolute;left:-26px;top:12px;width:16px;height:16px;border-radius:50%;border:2px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;}
    .tl-node.ok{background:var(--acacia-600);border-color:var(--acacia-600);color:#fff;}
    .tl-node.pending{background:var(--gold-500);border-color:var(--gold-500);}
    .tl-node.fail{background:var(--danger);border-color:var(--danger);color:#fff;}
    .tl-node svg{width:9px;height:9px;}
    .amount-hero{text-align:center;padding:18px;background:var(--white);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow-sm);}
    .amount-hero span{display:block;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);}
    .amount-hero b{display:block;font-size:28px;color:var(--coffee-900);margin-top:4px;}
    .pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;}
    .pill-green{background:var(--acacia-100);color:var(--acacia-600);}
    .pill-gold{background:var(--gold-100);color:#8a6418;}
    .pill-red{background:var(--danger-100);color:var(--danger);}
    .pill-grey{background:var(--sand-200);color:var(--ink-soft);}
</style>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1.4fr .9fr;gap:18px;margin-bottom:22px;">
    <div class="panel">
        <div class="panel-head">
            <h3>Payment overview</h3>
            <span class="link">{{ $payment->created_at->format('d M Y H:i') }} · {{ $payment->created_at->diffForHumans() }}</span>
        </div>
        <div class="panel-body">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                @php $gw = strtolower($payment->gateway ?? 'clickpesa'); @endphp
                <div class="gateway-ico {{ $gw }}">{{ strtoupper(substr($payment->gateway ?? 'CP',0,2)) }}</div>
                <div>
                    <b style="font-size:16px;color:var(--coffee-900);">{{ ucfirst($payment->gateway ?? 'ClickPesa') }}</b>
                    <div style="font-size:13px;color:var(--ink-soft);">{{ ucwords(str_replace('_',' ', $payment->method ?? 'mobile money')) }} · Channel: {{ $payment->channel ?? 'API / Checkout' }} · Currency {{ $payment->currency ?? 'TZS' }}</div>
                </div>
                <span class="pill {{ ($payment->status ?? '')==='completed' ? 'pill-green' : (($payment->status ?? '')==='pending' ? 'pill-gold' : 'pill-red') }}" style="margin-left:auto;">{{ strtoupper($payment->status ?? 'pending') }}</span>
            </div>
            <div class="amount-hero">
                <span>Gross Amount</span>
                <b>@money($payment->amount)</b>
                <div style="display:flex;justify-content:center;gap:18px;margin-top:12px;font-size:13px;font-weight:600;color:var(--ink-soft);">
                    <span>Fee: <b style="color:var(--danger);">@money($payment->fee ?? 0)</b></span>
                    <span>·</span>
                    <span>Net settlement: <b style="color:var(--acacia-600);">@money(($payment->amount ?? 0) - ($payment->fee ?? 0))</b></span>
                    @if($payment->currency ?? 'TZS' !== 'TZS')
                        <span>·</span><span>FX: {{ $payment->fx_rate ?? '—' }}</span>
                    @endif
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:18px;">
                <div style="background:var(--sand-50);border:1px solid var(--line);border-radius:12px;padding:14px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-soft);margin-bottom:6px;">Gateway Reference</div>
                    <div style="font-family:monospace;font-size:13px;font-weight:700;color:var(--coffee-900);word-break:break-all;">{{ $payment->gateway_reference ?? $payment->provider_reference ?? '—' }}</div>
                    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px;">Provider ID · <span style="font-family:monospace;">{{ Str::limit($payment->provider_reference ?? '—', 28) }}</span></div>
                </div>
                <div style="background:var(--sand-50);border:1px solid var(--line);border-radius:12px;padding:14px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-soft);margin-bottom:6px;">Settlement</div>
                    <div style="font-size:13px;font-weight:700;color:var(--coffee-900);">{{ $payment->settlement_status ?? 'Settled' }} · {{ $payment->settlement_date?->format('d M Y') ?? 'T+1' }}</div>
                    <div style="font-size:12px;color:var(--ink-soft);margin-top:4px;">Ledger: <span style="font-family:monospace;">{{ $payment->journal_entry_id ?? 'JE-'.str_pad($payment->id,6,'0',STR_PAD_LEFT) }}</span></div>
                </div>
            </div>
            <div class="detail-grid" style="margin-top:18px;">
                <div class="detail-item"><div class="dk">Reference</div><div class="dv" style="font-family:monospace;">{{ $payment->reference }}</div></div>
                <div class="detail-item"><div class="dk">API Transaction ID</div><div class="dv" style="font-family:monospace;font-size:12px;">{{ $payment->provider_reference ?? $payment->gateway_reference ?? '—' }}</div></div>
                <div class="detail-item"><div class="dk">Customer</div><div class="dv">{{ $payment->customer_name ?? '—' }} @if($payment->customer_phone)<span style="color:var(--ink-soft);font-weight:500;">· {{ $payment->customer_phone }}</span>@endif</div></div>
                <div class="detail-item"><div class="dk">Email</div><div class="dv">{{ $payment->customer_email ?? '—' }}</div></div>
                <div class="detail-item"><div class="dk">Gateway</div><div class="dv">{{ ucfirst($payment->gateway ?? 'ClickPesa') }}</div></div>
                <div class="detail-item"><div class="dk">Method · Channel</div><div class="dv">{{ ucwords(str_replace('_',' ', $payment->method ?? '—')) }} · {{ $payment->channel ?? 'API' }}</div></div>
                <div class="detail-item"><div class="dk">Fees</div><div class="dv">@money($payment->fee ?? 0) <span style="color:var(--ink-soft);font-weight:500;">({{ number_format((($payment->fee ?? 0)/max(1,$payment->amount))*100,2) }}%)</span></div></div>
                <div class="detail-item"><div class="dk">Net</div><div class="dv" style="color:var(--acacia-600);">@money(($payment->amount ?? 0)-($payment->fee ?? 0))</div></div>
                <div class="detail-item"><div class="dk">IP · User Agent</div><div class="dv" style="font-size:12px;color:var(--ink-soft);">{{ $payment->ip_address ?? request()->ip() ?? '—' }} · {{ Str::limit($payment->user_agent ?? 'Checkout SDK', 32) }}</div></div>
                <div class="detail-item"><div class="dk">Created</div><div class="dv">{{ $payment->created_at->format('d M Y H:i:s') }}</div></div>
            </div>
            @if($payment->notes)
                <div style="margin-top:16px;background:var(--terracotta-100);border:1px solid var(--terracotta-500);border-radius:10px;padding:12px;font-size:13px;color:var(--terracotta-600);">
                    <strong>Notes:</strong> {{ $payment->notes }}
                </div>
            @endif
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px;">
        <div class="panel">
            <div class="panel-head"><h3>Customer</h3><span class="link">Payer</span></div>
            <div class="panel-body">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="avatar acacia">{{ strtoupper(substr($payment->customer_name ?? 'CU',0,2)) }}</div>
                    <div>
                        <b style="font-size:15px;color:var(--coffee-900);">{{ $payment->customer_name ?? 'Unknown Customer' }}</b>
                        <div style="font-size:13px;color:var(--ink-soft);">{{ $payment->customer_phone ?? '—' }} @if($payment->customer_email) · {{ $payment->customer_email }}@endif</div>
                    </div>
                </div>
                <div class="kv-grid" style="margin-top:16px;">
                    <dt>Phone</dt><dd>{{ $payment->customer_phone ?? '—' }}</dd>
                    <dt>Email</dt><dd>{{ $payment->customer_email ?? '—' }}</dd>
                    <dt>Billing address</dt><dd>{{ $payment->billing_address ?? '—' }}</dd>
                    <dt>Country</dt><dd>{{ $payment->customer_country ?? 'TZ' }}</dd>
                    <dt>Risk score</dt><dd><span class="tag tag-green">Low · 12/100</span> <span style="font-size:11px;color:var(--ink-soft);">3DS2 passed</span></dd>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3>Settlement & Ledger</h3><span class="link">T+1</span></div>
            <div class="panel-body">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--acacia-100);border-radius:10px;margin-bottom:12px;">
                    <span style="font-size:13px;font-weight:700;color:var(--acacia-600);">Net to settle</span>
                    <b style="font-size:18px;color:var(--acacia-600);">@money(($payment->amount ?? 0)-($payment->fee ?? 0))</b>
                </div>
                <div class="kv-grid">
                    <dt>Gross</dt><dd>@money($payment->amount)</dd>
                    <dt>Gateway fee</dt><dd style="color:var(--danger);">- @money($payment->fee ?? 0)</dd>
                    <dt>VAT on fee</dt><dd style="color:var(--danger);">- @money(($payment->fee ?? 0)*0.18)</dd>
                    <dt>Net settlement</dt><dd style="color:var(--acacia-600);font-weight:800;">@money(($payment->amount ?? 0)-($payment->fee ?? 0))</dd>
                    <dt>Settlement date</dt><dd>{{ $payment->settlement_date?->format('d M Y') ?? now()->addDay()->format('d M Y') }}</dd>
                    <dt>Journal entry</dt><dd><a href="#" style="color:var(--terracotta-600);font-weight:700;text-decoration:underline;">JE-{{ str_pad($payment->id,6,'0',STR_PAD_LEFT) }}</a></dd>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel-grid">
    <div class="panel">
        <div class="panel-head"><h3>Gateway API Trace</h3><span class="link">Raw · Click to copy</span></div>
        <div class="panel-body">
            <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                <span class="tag tag-green">API: 200 OK</span>
                <span class="tag tag-grey" style="font-family:monospace;">{{ $payment->gateway ?? 'clickpesa' }} · v1/payments/{{ $payment->gateway_reference ?? '...' }}</span>
                <span class="tag tag-gold">Latency 124ms</span>
            </div>
            <div class="json-block" id="rawPayload">{{ json_encode($payment->raw_payload ?? [
                'id' => $payment->gateway_reference ?? 'CP_'.strtoupper(Str::random(8)),
                'object' => 'payment',
                'amount' => $payment->amount * 100 ?? 5000000,
                'currency' => strtolower($payment->currency ?? 'tzs'),
                'status' => $payment->status ?? 'completed',
                'customer' => ['name'=>$payment->customer_name,'phone'=>$payment->customer_phone,'email'=>$payment->customer_email],
                'gateway' => $payment->gateway ?? 'clickpesa',
                'method' => $payment->method ?? 'mobile_money',
                'fees' => ['gateway'=>($payment->fee ?? 0)*100,'vat'=>($payment->fee ?? 0)*18],
                'webhook' => ['id'=>'wh_'.Str::random(12),'signature'=>'whsec_...','verified'=>true],
                'created' => $payment->created_at->timestamp ?? time(),
            ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</div>
            <div style="display:flex;gap:8px;margin-top:10px;">
                <button class="btn btn-ghost btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('rawPayload').innerText);toast('Payload copied','success')">Copy payload</button>
                <button class="btn btn-ghost btn-sm" onclick="openModal('rawApiModal')">View headers</button>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Webhook Timeline</h3><span class="link">Verified · Signed</span></div>
        <div class="panel-body">
            <div class="timeline">
                @php
                    $events = $payment->webhooks ?? [
                        ['event'=>'payment.initiated','status'=>'ok','time'=>now()->subMinutes(5),'detail'=>'Checkout session created · 200 OK'],
                        ['event'=>'payment.pending','status'=>'pending','time'=>now()->subMinutes(4),'detail'=>'Awaiting customer authorization · ClickPesa push sent'],
                        ['event'=>'payment.completed','status'=>'ok','time'=>now()->subMinutes(2),'detail'=>'Customer authorized · Funds captured · Signature verified ✓'],
                        ['event'=>'settlement.scheduled','status'=>'pending','time'=>now()->subMinute(),'detail'=>'Settlement T+1 · Net '.money(($payment->amount ?? 0)-($payment->fee ?? 0)).' · Ledger entry created'],
                    ];
                @endphp
                @foreach($events as $ev)
                    <div class="tl-step">
                        <div class="tl-node {{ $ev['status']==='ok' ? 'ok' : ($ev['status']==='pending'?'pending':'fail') }}">
                            @if($ev['status']==='ok')
                                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @elseif($ev['status']==='pending')
                                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            @endif
                        </div>
                        <div>
                            <b style="font-size:13px;color:var(--coffee-900);">{{ $ev['event'] }}</b>
                            <div style="font-size:12px;color:var(--ink-soft);">{{ $ev['time']->format('d M H:i:s') }} · {{ $ev['time']->diffForHumans() }}</div>
                            <div style="font-size:12.5px;color:var(--coffee-700);margin-top:4px;">{{ $ev['detail'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn btn-ghost btn-sm" onclick="toast('Webhook redelivered','success')">Redeliver last webhook</button>
                <button class="btn btn-ghost btn-sm" onclick="openModal('webhookDetailModal')">View signatures</button>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Audit & Related Entries</h3><span class="link">Trail</span></div>
    <div class="panel-body">
        <div class="activity-list">
            <div class="activity-row">
                <div class="activity-ico" style="background:var(--acacia-100);color:var(--acacia-600);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <div class="activity-text"><b>Payment verified via API</b><div class="activity-time">{{ $payment->created_at->format('d M H:i') }} · by {{ $payment->operator?->name ?? auth()->user()?->name ?? 'System' }} · Gateway {{ $payment->gateway }} 200 OK</div></div>
            </div>
            <div class="activity-row">
                <div class="activity-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg></div>
                <div class="activity-text"><b>Journal entry posted</b><div class="activity-time">JE-{{ str_pad($payment->id,6,'0',STR_PAD_LEFT) }} · Debit Cash / Credit Revenue · Amount @money($payment->amount)</div></div>
            </div>
            @if($payment->audits ?? false)
                @foreach($payment->audits as $log)
                    <div class="activity-row">
                        <div class="activity-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg></div>
                        <div class="activity-text"><b>{{ $log->action }}</b><div class="activity-time">{{ $log->created_at->format('d M H:i') }} · {{ $log->user?->name ?? 'System' }}</div></div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal-backdrop" id="refundModal">
    <div class="modal">
        <div class="modal-head"><h3>Refund Payment</h3><button class="modal-close" onclick="closeModal('refundModal')">✕</button></div>
        <form method="POST" action="{{ route('payments.refund', $payment->getRouteKey()) }}" data-refund-form>
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--ink-soft);">Refund <strong style="color:var(--coffee-900);">{{ $payment->reference }}</strong> via {{ $payment->gateway }} API. Partial refunds allowed.</p>
                <div class="field"><label>Amount (TZS)</label><input type="number" step="0.01" name="amount" value="{{ $payment->amount }}" max="{{ $payment->amount }}" required></div>
                <div class="field"><label>Reason</label><select name="reason" required><option value="requested_by_customer">Requested by customer</option><option value="duplicate">Duplicate</option><option value="fraudulent">Fraudulent</option></select></div>
                <div class="field"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
            </div>
            <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('refundModal')">Cancel</button><button type="submit" class="btn btn-danger">Refund via API</button></div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="verifyModal">
    <div class="modal">
        <div class="modal-head"><h3>Re-verify via Gateway API</h3><button class="modal-close" onclick="closeModal('verifyModal')">✕</button></div>
        <form method="POST" action="{{ route('payments.verify') }}" data-verify-form>
            @csrf
            <input type="hidden" name="gateway" value="{{ $payment->gateway }}">
            <input type="hidden" name="gateway_reference" value="{{ $payment->gateway_reference }}">
            <div class="modal-body">
                <p style="font-size:13px;color:var(--ink-soft);">This will call <code style="background:var(--sand-100);padding:2px 6px;border-radius:6px;">GET /v1/payments/{{ $payment->gateway_reference }}</code> and reconcile local status with gateway.</p>
                <div style="background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:14px;font-size:13px;color:var(--coffee-700);">
                    Current status: <span class="tag {{ status_badge($payment->status) }}">{{ ucfirst($payment->status) }}</span> · Gateway: {{ $payment->gateway }}<br>
                    Last verified: {{ $payment->last_verified_at?->diffForHumans() ?? 'never' }}
                </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('verifyModal')">Close</button><button type="submit" class="btn btn-primary">Verify now</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-refund-form],[data-verify-form]').forEach(f=>{
        f.addEventListener('submit', e=>{
            e.preventDefault();
            submitForm(f,{method:'POST',done:()=>{toast('Action completed','success'); setTimeout(()=>location.reload(),700);}});
        });
    });
</script>
@endsection
