@extends('layouts.app')

@section('title', 'Payout ' . ($payout->reference ?? ''))

@section('head')
<style>
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
    .beneficiary-card{display:flex;align-items:center;gap:14px;padding:14px;background:var(--sand-50);border:1px solid var(--line);border-radius:12px;}
    .b-ico{width:42px;height:42px;border-radius:10px;background:var(--white);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);}
    .b-ico svg{width:18px;height:18px;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('payouts.index') }}" class="btn btn-ghost btn-sm">← Back to payouts</a>
            <span class="tag {{ status_badge($payout->status) }}">{{ ucfirst(str_replace('_',' ', $payout->status)) }}</span>
            <span class="tag tag-terracotta">{{ ucwords(str_replace('_',' ', $payout->method ?? 'mobile_money')) }} · {{ $payout->gateway ?? 'ClickPesa' }}</span>
            @if($payout->gateway_reference)<span class="tag tag-grey" style="font-family:monospace;font-size:11px;">{{ $payout->gateway_reference }}</span>@endif
        </div>
        <h2 style="margin-top:10px;">Payout {{ $payout->reference ?? 'PO-'.str_pad($payout->id,6,'0',STR_PAD_LEFT) }}</h2>
        <p class="sub">Disbursement lifecycle · Approval chain · Gateway dispatch · Ledger & reconciliation.</p>
    </div>
    <div class="view-actions">
        <a href="{{ route('payouts.receipt', $payout->getRouteKey()) }}" class="btn btn-ghost">Receipt</a>
        <a href="{{ route('payouts.receipt.pdf', $payout->getRouteKey()) }}" class="btn btn-ghost">Export PDF</a>
        @if(in_array($payout->status, ['completed']))
            <button class="btn btn-ghost" onclick="window.print()">Print advice</button>
        @endif
        @if(in_array($payout->status, ['pending','awaiting_approval']))
            @if(auth()->id() !== $payout->created_by)
                <button class="btn btn-primary" style="background:var(--acacia-600);" onclick="openModal('approveModal')">Approve</button>
                <button class="btn btn-danger" onclick="openModal('rejectModal')">Reject</button>
            @else
                <span class="tag tag-gold">Awaiting another approver (requester cannot approve own)</span>
            @endif
        @endif
        @if($payout->status==='failed')
            <button class="btn btn-primary" onclick="retryPayout('{{ $payout->getRouteKey() }}')">Retry via API</button>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr .9fr;gap:18px;margin-bottom:22px;">
    <div class="panel">
        <div class="panel-head"><h3>Payout Overview</h3><span class="link">{{ $payout->created_at->format('d M Y H:i') }} · {{ $payout->created_at->diffForHumans() }}</span></div>
        <div class="panel-body">
            <div class="amount-hero">
                <span>Amount to beneficiary</span>
                <b>@money($payout->amount)</b>
                <div style="display:flex;justify-content:center;gap:18px;margin-top:12px;font-size:13px;font-weight:600;color:var(--ink-soft);flex-wrap:wrap;">
                    <span>Fee: <b style="color:var(--danger);">@money($payout->fee ?? 0)</b></span>
                    <span>·</span>
                    <span>Total debit: <b style="color:var(--coffee-900);">@money(($payout->amount ?? 0)+($payout->fee ?? 0))</b></span>
                    <span>·</span>
                    <span>Purpose: <b style="color:var(--coffee-700);">{{ ucwords(str_replace('_',' ', $payout->purpose ?? 'other')) }}</b></span>
                </div>
            </div>

            <div class="beneficiary-card" style="margin-top:18px;">
                <div class="b-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                <div>
                    <b style="font-size:15px;color:var(--coffee-900);">{{ $payout->beneficiary_name ?? '—' }}</b>
                    <div style="font-size:13px;color:var(--ink-soft);">{{ $payout->beneficiary_account ?? $payout->beneficiary_phone ?? '—' }} @if($payout->bank_name) · {{ $payout->bank_name }}@endif · {{ ucwords(str_replace('_',' ', $payout->method ?? '')) }}</div>
                </div>
                <span class="tag {{ status_badge($payout->status) }}" style="margin-left:auto;">{{ ucfirst($payout->status) }}</span>
            </div>

            <div class="detail-grid" style="margin-top:18px;">
                <div class="detail-item"><div class="dk">Reference</div><div class="dv" style="font-family:monospace;">{{ $payout->reference ?? 'PO-'.str_pad($payout->id,6,'0',STR_PAD_LEFT) }}</div></div>
                <div class="detail-item"><div class="dk">Gateway ref</div><div class="dv" style="font-family:monospace;font-size:12px;">{{ $payout->gateway_reference ?? '—' }}</div></div>
                <div class="detail-item"><div class="dk">Method</div><div class="dv">{{ ucwords(str_replace('_',' ', $payout->method ?? '—')) }}</div></div>
                <div class="detail-item"><div class="dk">Gateway</div><div class="dv">{{ $payout->gateway ?? 'ClickPesa' }}</div></div>
                <div class="detail-item"><div class="dk">Source</div><div class="dv">{{ ucfirst($payout->source_account ?? 'settlement') }}</div></div>
                <div class="detail-item"><div class="dk">Purpose</div><div class="dv">{{ ucwords(str_replace('_',' ', $payout->purpose ?? 'other')) }}</div></div>
                <div class="detail-item"><div class="dk">Requested by</div><div class="dv">{{ $payout->requester?->name ?? $payout->created_by_name ?? auth()->user()?->name ?? 'System' }} · {{ $payout->created_at->format('d M H:i') }}</div></div>
                <div class="detail-item"><div class="dk">Approved by</div><div class="dv">{{ $payout->approver?->name ?? '—' }} @if($payout->approved_at) · {{ $payout->approved_at->format('d M H:i') }}@endif</div></div>
            </div>

            @if($payout->notes)
                <div style="margin-top:14px;background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;font-size:13px;color:var(--coffee-800);">
                    <strong>Notes:</strong> {{ $payout->notes }}
                </div>
            @endif
            @if($payout->rejection_reason)
                <div style="margin-top:14px;background:var(--danger-100);border:1px solid var(--danger);border-radius:10px;padding:12px;font-size:13px;color:var(--danger);">
                    <strong>Rejection:</strong> {{ $payout->rejection_reason }}
                </div>
            @endif
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px;">
        <div class="panel">
            <div class="panel-head"><h3>Approval Chain</h3><span class="link">Dual control</span></div>
            <div class="panel-body">
                <div class="timeline">
                    <div class="tl-step">
                        <div class="tl-node ok"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                        <div>
                            <b style="font-size:13px;color:var(--coffee-900);">Created</b>
                            <div style="font-size:12px;color:var(--ink-soft);">{{ $payout->created_at->format('d M Y H:i') }} · {{ $payout->requester?->name ?? 'Requester' }}</div>
                            <div style="font-size:12.5px;color:var(--coffee-700);margin-top:3px;">Payout queued for approval · Amount @money($payout->amount)</div>
                        </div>
                    </div>
                    @if($payout->approved_at)
                        <div class="tl-step">
                            <div class="tl-node ok"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                            <div>
                                <b style="font-size:13px;color:var(--coffee-900);">Approved</b>
                                <div style="font-size:12px;color:var(--ink-soft);">{{ $payout->approved_at->format('d M Y H:i') }} · {{ $payout->approver?->name ?? 'Approver' }}</div>
                                <div style="font-size:12.5px;color:var(--coffee-700);margin-top:3px;">{{ $payout->approval_notes ?? 'Approved – dispatching to gateway' }}</div>
                            </div>
                        </div>
                    @elseif(in_array($payout->status,['pending','awaiting_approval']))
                        <div class="tl-step">
                            <div class="tl-node pending"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
                            <div>
                                <b style="font-size:13px;color:var(--coffee-900);">Awaiting approval</b>
                                <div style="font-size:12px;color:var(--ink-soft);">Pending supervisor/admin · Requester cannot self-approve</div>
                            </div>
                        </div>
                    @endif
                    @if(in_array($payout->status, ['processing','completed']))
                        <div class="tl-step">
                            <div class="tl-node {{ $payout->status==='completed'?'ok':'pending' }}">
                                @if($payout->status==='completed')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                @endif
                            </div>
                            <div>
                                <b style="font-size:13px;color:var(--coffee-900);">{{ $payout->status==='completed' ? 'Dispatched & Confirmed' : 'Processing' }}</b>
                                <div style="font-size:12px;color:var(--ink-soft);">{{ $payout->dispatched_at?->format('d M Y H:i') ?? $payout->updated_at->format('d M Y H:i') }} · {{ $payout->gateway }} · {{ $payout->gateway_reference ?? '—' }}</div>
                                <div style="font-size:12.5px;color:var(--coffee-700);margin-top:3px;">{{ $payout->gateway_response ?? 'Gateway 200 OK · Funds delivered' }}</div>
                            </div>
                        </div>
                    @endif
                    @if($payout->status==='failed')
                        <div class="tl-step">
                            <div class="tl-node fail"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></div>
                            <div>
                                <b style="font-size:13px;color:var(--danger);">Failed</b>
                                <div style="font-size:12px;color:var(--ink-soft);">{{ $payout->failed_at?->format('d M Y H:i') ?? $payout->updated_at->format('d M Y H:i') }}</div>
                                <div style="font-size:12.5px;color:var(--danger);margin-top:3px;">{{ $payout->failure_reason ?? 'Gateway error – insufficient funds or invalid account' }}</div>
                            </div>
                        </div>
                    @endif
                    @if($payout->status==='rejected')
                        <div class="tl-step">
                            <div class="tl-node fail"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></div>
                            <div>
                                <b style="font-size:13px;color:var(--danger);">Rejected</b>
                                <div style="font-size:12px;color:var(--ink-soft);">{{ $payout->rejected_at?->format('d M Y H:i') ?? $payout->updated_at->format('d M Y H:i') }} · {{ $payout->rejector?->name ?? $payout->approver?->name ?? 'Reviewer' }}</div>
                                <div style="font-size:12.5px;color:var(--danger);margin-top:3px;">{{ $payout->rejection_reason ?? 'Rejected' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3>Ledger</h3><span class="link">Double-entry</span></div>
            <div class="panel-body">
                <div class="kv-grid">
                    <dt>Journal entry</dt><dd><a href="#" style="color:var(--terracotta-600);font-weight:700;font-family:monospace;">JE-{{ str_pad($payout->id,6,'0',STR_PAD_LEFT) }}</a> · {{ $payout->journal_status ?? 'posted' }}</dd>
                    <dt>Debit</dt><dd>Payout Expense · @money($payout->amount)</dd>
                    <dt>Debit</dt><dd>Gateway Fee Expense · @money($payout->fee ?? 0)</dd>
                    <dt>Credit</dt><dd>Settlement / Cash · @money(($payout->amount ?? 0)+($payout->fee ?? 0))</dd>
                    <dt>Balance after</dt><dd style="color:var(--acacia-600);">@money($payout->balance_after ?? 0)</dd>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel-grid">
    <div class="panel">
        <div class="panel-head"><h3>Gateway API Trace</h3><span class="link">{{ $payout->gateway ?? 'ClickPesa' }}</span></div>
        <div class="panel-body">
            <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
                <span class="tag {{ $payout->status==='completed' ? 'tag-green' : ($payout->status==='failed' ? 'tag-red' : 'tag-gold') }}">{{ $payout->gateway_status ?? $payout->status }}</span>
                <span class="tag tag-grey" style="font-family:monospace;">POST /v1/payouts</span>
                <span class="tag tag-terracotta">Latency {{ $payout->gateway_latency ?? '212ms' }}</span>
            </div>
            <div class="json-block">{{ json_encode($payout->gateway_payload ?? [
                'id' => $payout->gateway_reference ?? 'PO_'.strtoupper(Str::random(8)),
                'amount' => $payout->amount,
                'currency' => $payout->currency ?? 'TZS',
                'beneficiary' => ['name'=>$payout->beneficiary_name,'account'=>$payout->beneficiary_account,'bank'=>$payout->bank_name],
                'method' => $payout->method,
                'gateway' => $payout->gateway,
                'status' => $payout->status,
                'created' => $payout->created_at->timestamp ?? time(),
            ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</div>
            @if($payout->gateway_response)
                <div style="margin-top:10px;font-size:12.5px;color:var(--ink-soft);">Response: <code style="background:var(--sand-100);padding:6px 10px;border-radius:8px;border:1px solid var(--line);display:block;white-space:pre-wrap;margin-top:6px;">{{ is_string($payout->gateway_response) ? $payout->gateway_response : json_encode($payout->gateway_response, JSON_PRETTY_PRINT) }}</code></div>
            @endif
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Audit Log</h3><span class="link">Trail</span></div>
        <div class="panel-body">
            <div class="activity-list">
                <div class="activity-row">
                    <div class="activity-ico" style="background:var(--acacia-100);color:var(--acacia-600);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M16 8a6 6 0 0 1 6 6v7h-7a6 6 0 0 1-6-6v-1"></path></svg></div>
                    <div class="activity-text"><b>Payout created</b><div class="activity-time">{{ $payout->created_at->format('d M Y H:i') }} · {{ $payout->requester?->name ?? 'Requester' }} · @money($payout->amount)</div></div>
                </div>
                @if(isset($payout->audits))
                    @foreach($payout->audits as $log)
                        <div class="activity-row">
                            <div class="activity-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                            <div class="activity-text"><b>{{ $log->action }}</b><div class="activity-time">{{ $log->created_at->format('d M Y H:i') }} · {{ $log->user?->name ?? 'System' }}</div></div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals for approve/reject --}}
<div class="modal-backdrop" id="approveModal">
    <div class="modal">
        <div class="modal-head"><h3>Approve Payout</h3><button class="modal-close" onclick="closeModal('approveModal')">✕</button></div>
        <form method="POST" action="{{ route('payouts.approve', $payout->getRouteKey()) }}" data-approve-form>
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--ink-soft);">Approve <strong style="color:var(--coffee-900);">{{ $payout->reference ?? 'PO-'.str_pad($payout->id,6,'0',STR_PAD_LEFT) }}</strong> for @money($payout->amount) to {{ $payout->beneficiary_name ?? 'beneficiary' }}? This queues the payout to {{ $payout->gateway }} API.</p>
                <div class="field"><label>Supervisor OTP * <span style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--ink-soft);">— sent to {{ auth()->user()?->phone ?? '+255...' }} via SMS</span></label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="otp" placeholder="6-digit code" required maxlength="6" pattern="\d{6}" style="flex:1;letter-spacing:.2em;text-align:center;font-weight:700;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="requestApproveOtp()" id="requestOtpBtn" style="white-space:nowrap;">Send OTP</button>
                    </div>
                    <div style="font-size:11px;color:var(--ink-soft);margin-top:4px;">Cashier initiated — supervisor/admin must verify via OTP.</div>
                    <input type="hidden" name="otp_phone" value="{{ auth()->user()?->phone ?? '' }}">
                </div>
                <div class="field"><label>Approval notes</label><textarea name="notes" rows="2" placeholder="Verified, ready for dispatch"></textarea></div>
            </div>
            <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('approveModal')">Cancel</button><button type="submit" class="btn btn-primary" style="background:var(--acacia-600);">Approve with OTP</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="rejectModal">
    <div class="modal">
        <div class="modal-head"><h3>Reject Payout</h3><button class="modal-close" onclick="closeModal('rejectModal')">✕</button></div>
        <form method="POST" action="{{ route('payouts.reject', $payout->getRouteKey()) }}" data-reject-form>
            @csrf
            <div class="modal-body">
                <div class="field"><label>Reason *</label><select name="reason" required><option value="">Select</option><option value="incorrect_details">Incorrect details</option><option value="compliance">Compliance</option><option value="duplicate">Duplicate</option><option value="other">Other</option></select></div>
                <div class="field"><label>Notes</label><textarea name="notes" rows="3" required></textarea></div>
            </div>
            <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('rejectModal')">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function requestApproveOtp(){
        const btn=document.getElementById('requestOtpBtn');
        const phone=document.querySelector('input[name=\"otp_phone\"]')?.value || '{{ auth()->user()?->phone ?? '' }}';
        if(!phone){ toast('No phone for OTP','error'); return; }
        btn.disabled=true; btn.textContent='Sending...';
        try {
            const r=await fetch('{{ route('payouts.otp.generate') }}',{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json'},
                body: JSON.stringify({phone: phone, purpose:'approve'})
            });
            const data=await r.json();
            if(data.success){
                toast(data.message||'OTP sent via SMS','success');
                if(data.debug_otp) toast('Demo OTP: '+data.debug_otp,'success');
            } else {
                toast(data.message||'OTP failed','error');
            }
        } catch(e){ toast('Network error','error'); }
        finally { btn.disabled=false; btn.textContent='Send OTP'; }
    }
    async function retryPayout(id){
        if(!confirm('Retry this payout?')) return;
        try{
            const r=await fetch('/payouts/'+id+'/retry',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const d=await r.json().catch(()=>({}));
            if(r.ok && d.success){ toast(d.message||'Retried','success'); setTimeout(()=>location.reload(),700); } else toast(d.message||'Retry failed','error');
        }catch(e){ toast('Network error','error'); }
    }
    document.querySelectorAll('[data-approve-form],[data-reject-form]').forEach(f=>{
        f.addEventListener('submit', e=>{
            e.preventDefault();
            submitForm(f,{method:'POST',done:()=>{toast('Action completed','success'); setTimeout(()=>location.reload(),700);}});
        });
    });
</script>
@endsection
