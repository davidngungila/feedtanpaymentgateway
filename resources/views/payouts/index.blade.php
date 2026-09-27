@extends('layouts.app')

@section('title', 'Payouts')

@section('head')
<style>
    .beneficiary-ico{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--sand-100);color:var(--coffee-700);flex:none;}
    .beneficiary-ico svg{width:16px;height:16px;}
    .stepper{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .stepper .step{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;border:2px solid var(--line);background:var(--white);color:var(--ink-soft);}
    .stepper .step.done{background:var(--acacia-600);border-color:var(--acacia-600);color:#fff;}
    .stepper .step.active{background:var(--gold-500);border-color:var(--gold-500);color:#fff;}
    .stepper .bar{width:28px;height:2px;background:var(--line);border-radius:2px;}
    .stepper .bar.done{background:var(--acacia-600);}
    .limit-bar{height:8px;background:var(--sand-100);border-radius:10px;overflow:hidden;display:flex;}
    .limit-bar i{height:100%;background:linear-gradient(90deg,var(--terracotta-500),var(--gold-500));display:block;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Payouts</h2>
        <p class="sub">Create, approve and disburse payouts · Bank, Mobile Money & Wallet · Dual approval, ledger & API trace.</p>
    </div>
    <div class="view-actions">
        <button class="btn btn-ghost" onclick="openModal('payoutLimitsModal')">Limits</button>
        <a href="{{ route('payouts.create') }}" class="btn btn-primary">+ New Payout</a>
        @include('exports._export-modal', ['route'=>$exportRoute ?? '#','columns'=>$exportColumns ?? [],'title'=>'Payouts','subtitle'=>'All filtered payouts','filters'=>$filters ?? []])
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div><span class="stat-trend up">{{ $stats['pending'] ?? 0 }} pending</span></div>
        <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
        <div class="stat-label">Awaiting approval</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div><span class="stat-trend up">Today</span></div>
        <div class="stat-value">@money($stats['disbursed_today'] ?? 0)</div>
        <div class="stat-label">Disbursed today</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--terracotta-100);--stat-fg:var(--terracotta-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-7a6 6 0 0 1-6-6v-1"></path></svg></div><span class="stat-trend up">Month</span></div>
        <div class="stat-value">@money($stats['disbursed_month'] ?? 0)</div>
        <div class="stat-label">Disbursed this month · {{ $stats['count_month'] ?? 0 }} payouts</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div><span class="stat-trend down">{{ $stats['failed'] ?? 0 }} failed</span></div>
        <div class="stat-value">{{ $stats['failed'] ?? 0 }} <span style="font-size:14px;color:var(--ink-soft);">· {{ $stats['rejected'] ?? 0 }} rejected</span></div>
        <div class="stat-label">Failed / Rejected</div>
    </div>
</div>

<div class="balance-strip">
    <div class="balance-box" style="border-left:4px solid var(--acacia-600);">
        <div class="bb-label">Available for payout</div>
        <div class="bb-amount">@money($balances['available'] ?? 3420000)</div>
        <div class="bb-sub">Across all settlement accounts</div>
        <div class="limit-bar" style="margin-top:8px;"><i style="width:62%;"></i></div>
        <div style="font-size:11px;color:var(--ink-soft);margin-top:4px;">Daily limit @money($limits['daily'] ?? 50000000) · Used 62%</div>
    </div>
    <div class="balance-box" style="border-left:4px solid var(--gold-500);">
        <div class="bb-label">Pending approval value</div>
        <div class="bb-amount">@money($balances['pending_value'] ?? 780000)</div>
        <div class="bb-sub">Requires supervisor/admin approval</div>
    </div>
    <div class="balance-box" style="border-left:4px solid var(--terracotta-600);">
        <div class="bb-label">Avg processing time</div>
        <div class="bb-amount">{{ $balances['avg_time'] ?? '4.2' }} min</div>
        <div class="bb-sub">From approval to gateway 200 OK</div>
    </div>
    <div class="balance-box" style="border-left:4px solid var(--coffee-500);">
        <div class="bb-label">Success rate (30d)</div>
        <div class="bb-amount">{{ $balances['success_rate'] ?? '97.3' }}%</div>
        <div class="bb-sub">2.1% retry · 0.6% failed</div>
    </div>
</div>

<form method="GET" action="{{ route('payouts.index') }}" id="filterForm">
    <div class="table-card">
        <div class="table-toolbar">
            <input type="hidden" name="status" id="fStatus" value="{{ $filters['status'] ?? 'all' }}">
            <div class="chip-filters">
                <button type="button" class="chip {{ ($filters['status'] ?? 'all')==='all' ? 'active' : '' }}" onclick="setPayoutStatus('all')">All</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='pending' ? 'active' : '' }}" onclick="setPayoutStatus('pending')">Pending</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='approved' ? 'active' : '' }}" onclick="setPayoutStatus('approved')">Approved</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='processing' ? 'active' : '' }}" onclick="setPayoutStatus('processing')">Processing</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='completed' ? 'active' : '' }}" onclick="setPayoutStatus('completed')">Completed</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='failed' ? 'active' : '' }}" onclick="setPayoutStatus('failed')">Failed</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='rejected' ? 'active' : '' }}" onclick="setPayoutStatus('rejected')">Rejected</button>
            </div>
            <div class="table-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search beneficiary, phone, ref…" oninput="filterPayoutRows(this.value)">
            </div>
        </div>
        <div class="table-toolbar" style="border-bottom:none;padding-top:6px;">
            <select name="method" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);color:var(--coffee-700);font-weight:600;">
                <option value="all" {{ ($filters['method'] ?? 'all')==='all' ? 'selected' : '' }}>All methods</option>
                <option value="bank" {{ ($filters['method'] ?? '')==='bank' ? 'selected' : '' }}>Bank</option>
                <option value="mobile_money" {{ ($filters['method'] ?? '')==='mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                <option value="wallet" {{ ($filters['method'] ?? '')==='wallet' ? 'selected' : '' }}>Wallet</option>
            </select>
            <select name="gateway" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);color:var(--coffee-700);font-weight:600;">
                <option value="all" {{ ($filters['gateway'] ?? 'all')==='all' ? 'selected' : '' }}>All gateways</option>
                <option value="clickpesa">ClickPesa</option>
                <option value="selcom">Selcom</option>
                <option value="dpo">DPO</option>
            </select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);">
            <button type="submit" class="btn btn-ghost btn-sm">Apply</button>
        </div>
    </div>
</form>

<div class="table-card" style="margin-top:-24px;">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Payout Ref</th>
                    <th>Beneficiary</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Approved by</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="payoutsBody">
                @forelse($payouts as $p)
                    <tr data-id="{{ $p->getRouteKey() }}" data-href="{{ route('payouts.show', $p->getRouteKey()) }}" data-search="{{ strtolower(($p->reference ?? '').' '.($p->beneficiary_name ?? '').' '.($p->beneficiary_account ?? '')) }}" data-status="{{ $p->status }}" style="cursor:pointer;">
                        <td>
                            <div class="cell-title" style="font-family:monospace;">{{ $p->reference ?? 'PO-'.str_pad($p->id,6,'0',STR_PAD_LEFT) }}</div>
                            <div class="cell-sub">{{ $p->gateway ?? 'ClickPesa' }} · {{ $p->gateway_reference ? Str::limit($p->gateway_reference,14) : '—' }}</div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="beneficiary-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                                <div>
                                    <div class="cell-title">{{ $p->beneficiary_name ?? '—' }}</div>
                                    <div class="cell-sub">{{ $p->beneficiary_account ?? $p->beneficiary_phone ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="cell-title">@money($p->amount)</td>
                        <td><span class="tag {{ status_badge($p->status) }}">{{ ucfirst(str_replace('_',' ', $p->status)) }}</span></td>
                        <td>
                            @if($p->approver)
                                <div class="cell-title" style="font-size:12.5px;">{{ $p->approver->name }}</div>
                                <div class="cell-sub">{{ $p->approved_at?->format('d M H:i') ?? '' }}</div>
                            @else
                                <span class="cell-sub">— awaiting</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('payouts.show', $p->getRouteKey()) }}" title="View" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                @if(in_array($p->status, ['pending','awaiting_approval']))
                                    <button title="Approve" onclick="openApproveModal('{{ $p->getRouteKey() }}','{{ $p->reference ?? $p->id }}')" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--acacia-600);background:var(--acacia-100);display:flex;align-items:center;justify-content:center;color:var(--acacia-600);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button title="Reject" onclick="openRejectModal('{{ $p->getRouteKey() }}')" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--danger);background:var(--danger-100);display:flex;align-items:center;justify-content:center;color:var(--danger);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                @endif
                                @if($p->status==='failed')
                                    <button title="Retry payout" onclick="retryPayout('{{ $p->getRouteKey() }}')" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--gold-500);background:var(--gold-100);display:flex;align-items:center;justify-content:center;color:#8a6418;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <h4>No payouts yet</h4>
                        <p>Create your first payout to a bank or mobile money account. Payouts require approval above {{ money($limits['approval_threshold'] ?? 1000000) }}.</p>
                        <a href="{{ route('payouts.create') }}" class="btn btn-primary" style="margin-top:12px;">+ New Payout</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($payouts) && $payouts->hasPages())
        <div class="table-pager">
            <div class="pager-info">Showing {{ $payouts->firstItem() }}–{{ $payouts->lastItem() }} of {{ $payouts->total() }}</div>
            <div class="pager-pages">{{ $payouts->links('pagination.pager') }}</div>
        </div>
    @endif
</div>

{{-- Approve Modal --}}
<div class="modal-backdrop" id="approveModal">
    <div class="modal">
        <div class="modal-head"><h3>Approve Payout</h3><button class="modal-close" onclick="closeModal('approveModal')">✕</button></div>
        <form id="approveForm" method="POST" action="#">
            @csrf
            <div class="modal-body">
                <p style="font-size:13.5px;color:var(--ink-soft);">Approve <strong id="approveRef" style="color:var(--coffee-900);"></strong>? This will immediately queue the payout to the gateway API.</p>
                <div class="field"><label>Approval notes (optional)</label><textarea name="notes" rows="2" placeholder="e.g. Verified beneficiary, invoice #123"></textarea></div>
                <div style="background:var(--acacia-100);border:1px solid var(--acacia-500);border-radius:10px;padding:12px;font-size:12.5px;color:var(--acacia-600);">
                    <strong>Two-man rule:</strong> Approver cannot be the requester. Action is audit-logged and requires 2FA if enabled.
                </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('approveModal')">Cancel</button><button type="submit" class="btn btn-primary" style="background:var(--acacia-600);">Approve & Dispatch</button></div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-backdrop" id="rejectModal">
    <div class="modal">
        <div class="modal-head"><h3>Reject Payout</h3><button class="modal-close" onclick="closeModal('rejectModal')">✕</button></div>
        <form id="rejectForm" method="POST" action="#">
            @csrf
            <div class="modal-body">
                <div class="field"><label>Reason for rejection *</label><select name="reason" required><option value="">Select reason</option><option value="incorrect_details">Incorrect beneficiary details</option><option value="insufficient_funds">Insufficient funds</option><option value="compliance">Compliance / KYC</option><option value="duplicate">Duplicate</option><option value="other">Other</option></select></div>
                <div class="field"><label>Notes</label><textarea name="notes" rows="3" placeholder="Explain for audit trail" required></textarea></div>
            </div>
            <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('rejectModal')">Cancel</button><button type="submit" class="btn btn-danger">Reject Payout</button></div>
        </form>
    </div>
</div>

{{-- Limits Modal --}}
<div class="modal-backdrop" id="payoutLimitsModal">
    <div class="modal">
        <div class="modal-head"><h3>Payout Limits & Controls</h3><button class="modal-close" onclick="closeModal('payoutLimitsModal')">✕</button></div>
        <div class="modal-body">
            <div class="kv-grid" style="grid-template-columns:1fr 1fr;gap:12px;">
                <div style="background:var(--white);border:1px solid var(--line);border-radius:12px;padding:14px;"><div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-soft);">Daily limit</div><b style="font-size:16px;color:var(--coffee-900);">@money($limits['daily'] ?? 50000000)</b><div class="limit-bar" style="margin-top:8px;"><i style="width:62%;"></i></div><div style="font-size:11px;color:var(--ink-soft);margin-top:4px;">Used @money($limits['used_today'] ?? 31000000) today</div></div>
                <div style="background:var(--white);border:1px solid var(--line);border-radius:12px;padding:14px;"><div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-soft);">Per-transaction max</div><b style="font-size:16px;color:var(--coffee-900);">@money($limits['per_txn'] ?? 10000000)</b><div style="font-size:11px;color:var(--ink-soft);margin-top:8px;">Min @money($limits['per_txn_min'] ?? 1000)</div></div>
            </div>
            <div style="margin-top:14px;background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;font-size:12.5px;color:var(--ink-soft);">
                Approval required above: <strong style="color:var(--coffee-800);">@money($limits['approval_threshold'] ?? 1000000)</strong> · Dual approval enabled · 2FA required for payouts &gt; @money(5000000)
            </div>
        </div>
        <div class="modal-foot"><button class="btn btn-primary" onclick="closeModal('payoutLimitsModal')">Close</button></div>
    </div>
</div>
@endsection

@php
    $payoutsJson = ($payouts ?? collect())->map(function($p){
        return [
            'id'=>$p->id,
            'routeKey'=>$p->getRouteKey(),
            'reference'=>$p->reference ?? 'PO-'.str_pad($p->id,6,'0',STR_PAD_LEFT),
            'beneficiary_name'=>$p->beneficiary_name,
            'beneficiary_account'=>$p->beneficiary_account,
            'method'=>$p->method,
            'amount'=>(float)$p->amount,
            'fee'=>(float)($p->fee ?? 0),
            'status'=>$p->status,
            'gateway'=>$p->gateway,
            'created_at'=>$p->created_at->format('d M Y H:i'),
        ];
    })->values();
@endphp
@section('scripts')
<script>
    const payoutsData = @json($payoutsJson);

    function setPayoutStatus(s){ document.getElementById('fStatus').value=s; document.getElementById('filterForm').submit(); }
    function filterPayoutRows(q){
        q=q.toLowerCase();
        document.querySelectorAll('#payoutsBody tr[data-id]').forEach(tr=>{
            tr.style.display=(!q || tr.dataset.search.includes(q)) ? 'table-row' : 'none';
        });
    }
    function openApproveModal(id, ref){
        document.getElementById('approveRef').textContent=ref;
        document.getElementById('approveForm').action='/payouts/'+id+'/approve';
        openModal('approveModal');
    }
    function openRejectModal(id){
        document.getElementById('rejectForm').action='/payouts/'+id+'/reject';
        openModal('rejectModal');
    }
    async function retryPayout(id){
        if(!confirm('Retry this failed payout via gateway API?')) return;
        try{
            const r=await fetch('/payouts/'+id+'/retry',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const d=await r.json().catch(()=>({}));
            if(r.ok && d.success){ toast(d.message||'Payout retried','success'); setTimeout(()=>location.reload(),700); }
            else toast(d.message||'Retry failed','error');
        }catch(e){ toast('Network error','error'); }
    }
    // Click row -> single payout page (encrypted, full details from API/DB)
    document.querySelectorAll('#payoutsBody tr[data-href]').forEach(tr=>{
        tr.addEventListener('click', (e)=>{
            if(e.target.closest('a, button')) return;
            window.location = tr.dataset.href;
        });
    });
    function fmt(n){ return 'TZS ' + Number(n).toLocaleString('en-US',{maximumFractionDigits:2}); }
    document.querySelectorAll('#approveForm,#rejectForm').forEach(f=>{
        f.addEventListener('submit', e=>{
            e.preventDefault();
            const method=f.id==='approveForm' ? 'POST' : 'POST';
            submitForm(f,{method, done:()=>{ closeModal(f.closest('.modal-backdrop').id); toast('Action completed','success'); setTimeout(()=>location.reload(),700); }});
        });
    });
</script>
@endsection
