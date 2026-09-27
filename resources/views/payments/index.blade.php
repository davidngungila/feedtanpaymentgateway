@extends('layouts.app')

@section('title', 'Payments Received')

@section('head')
<style>
    .gateway-ico{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px;letter-spacing:.04em;flex:none;}
    .gateway-ico.clickpesa{background:linear-gradient(135deg,#C2592B,#D4A24C);color:#fff;}
    .gateway-ico.selcom{background:#0F2A4D;color:#fff;}
    .gateway-ico.dpo{background:#E30613;color:#fff;}
    .gateway-ico.stripe{background:#635BFF;color:#fff;}
    .gateway-ico.mpesa{background:#ED1C24;color:#fff;}
    .gateway-ico.tigo{background:#00377B;color:#FFD700;}
    .api-dot{width:8px;height:8px;border-radius:50%;flex:none;display:inline-block;}
    .api-dot.ok{background:var(--acacia-600);box-shadow:0 0 0 0 rgba(94,110,63,.5);animation:ledPulse 1.6s infinite;}
    .api-dot.warn{background:#D4A24C;}
    .api-dot.down{background:var(--danger);}
    .webhook-timeline{position:relative;padding-left:22px;}
    .webhook-timeline::before{content:"";position:absolute;left:7px;top:6px;bottom:6px;width:2px;background:var(--line);border-radius:2px;}
    .wh-step{position:relative;padding:8px 0 14px;display:flex;gap:10px;}
    .wh-step:last-child{padding-bottom:0;}
    .wh-node{position:absolute;left:-22px;top:10px;width:14px;height:14px;border-radius:50%;border:2px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;}
    .wh-node.ok{background:var(--acacia-600);border-color:var(--acacia-600);color:#fff;}
    .wh-node.pending{background:var(--gold-500);border-color:var(--gold-500);}
    .wh-node.failed{background:var(--danger);border-color:var(--danger);color:#fff;}
    .wh-node svg{width:8px;height:8px;}
    .kv-grid{display:grid;grid-template-columns:140px 1fr;gap:8px 14px;font-size:13px;}
    .kv-grid dt{color:var(--ink-soft);font-weight:600;text-transform:uppercase;letter-spacing:.04em;font-size:11px;padding-top:2px;}
    .kv-grid dd{margin:0;color:var(--coffee-900);font-weight:600;word-break:break-all;}
    .json-block{background:#1A120B;color:#F4ECDC;border-radius:10px;padding:14px;font-family:ui-monospace,monospace;font-size:12px;line-height:1.6;overflow:auto;max-height:240px;white-space:pre-wrap;word-break:break-word;}
    .health-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;}
    .health-card{background:var(--white);border:1px solid var(--line);border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;box-shadow:var(--shadow-sm);}
    .health-card .hg-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex:none;}
    .health-card b{display:block;font-size:13px;color:var(--coffee-900);}
    .health-card span{font-size:11.5px;color:var(--ink-soft);}
    .method-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;background:var(--sand-100);color:var(--coffee-700);font-size:11.5px;font-weight:700;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Payments Received</h2>
        <p class="sub">ClickPesa only · Full lifecycle via ClickPesa API · Webhooks, settlement & reconciliation.</p>
    </div>
    <div class="view-actions">
        <button class="btn btn-ghost" onclick="openModal('apiHealthModal')">API Health</button>
        <button class="btn btn-ghost" onclick="openModal('webhookLogModal')">Webhook Logs</button>
        <button class="btn btn-primary" onclick="openModal('verifyPaymentModal')">+ Verify Payment</button>
        @include('exports._export-modal', ['route' => $exportRoute ?? '#', 'columns' => $exportColumns ?? [], 'title' => 'Payments Received', 'subtitle' => 'All filtered payments', 'filters' => $filters ?? []])
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div><span class="stat-trend up">Today</span></div>
        <div class="stat-value">@money($todayTotals['received'] ?? 0)</div>
        <div class="stat-label">Total received today</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div><span class="stat-trend up">{{ $todayTotals['pending'] ?? 0 }} pending</span></div>
        <div class="stat-value">{{ $todayTotals['count'] ?? 0 }}</div>
        <div class="stat-label">Payments today · {{ $todayTotals['success_rate'] ?? '98.2' }}% success</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div><span class="stat-trend down">{{ $todayTotals['failed'] ?? 0 }} failed</span></div>
        <div class="stat-value">@money($todayTotals['fees'] ?? 0)</div>
        <div class="stat-label">Gateway fees today</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--terracotta-100);--stat-fg:var(--terracotta-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-7a6 6 0 0 1-6-6v-1"></path><path d="M12 8a6 6 0 0 1 6 6"></path></svg></div><span class="stat-trend up">T+1</span></div>
        <div class="stat-value">@money($todayTotals['net_settlement'] ?? 0)</div>
        <div class="stat-label">Net settlement (after fees)</div>
    </div>
</div>

<div class="balance-strip">
    <div class="balance-box" style="border-left:4px solid var(--acacia-600);">
        <div class="bb-label"><span class="api-dot ok"></span> ClickPesa Balance</div>
        <div class="bb-amount">@money($gatewayBalances['clickpesa'] ?? 1250000)</div>
        <div class="bb-sub">Settled · Available for payout · ClickPesa only</div>
    </div>
</div>

<form method="GET" action="{{ route('payments.index') }}" id="filterForm">
    <div class="table-card">
        <div class="table-toolbar">
            <input type="hidden" name="status" id="fStatus" value="{{ $filters['status'] ?? 'all' }}">
            <input type="hidden" name="gateway" id="fGateway" value="{{ $filters['gateway'] ?? 'all' }}">
            <div class="chip-filters" id="statusChips">
                <button type="button" class="chip {{ ($filters['status'] ?? 'all')==='all' ? 'active' : '' }}" onclick="setPaymentStatus('all')">All</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='completed' ? 'active' : '' }}" onclick="setPaymentStatus('completed')">Completed</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='pending' ? 'active' : '' }}" onclick="setPaymentStatus('pending')">Pending</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='failed' ? 'active' : '' }}" onclick="setPaymentStatus('failed')">Failed</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='refunded' ? 'active' : '' }}" onclick="setPaymentStatus('refunded')">Refunded</button>
                <button type="button" class="chip {{ ($filters['status'] ?? '')==='disputed' ? 'active' : '' }}" onclick="setPaymentStatus('disputed')">Disputed</button>
            </div>
            <div class="table-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search ref, customer, phone, API id…" oninput="filterPaymentRows(this.value)">
            </div>
        </div>
        <div class="table-toolbar" style="border-bottom:none;padding-top:6px;">
            <select name="gateway" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);color:var(--coffee-700);font-weight:600;">
                <option value="all" {{ ($filters['gateway'] ?? 'all')==='all' ? 'selected' : '' }}>All gateways</option>
                <option value="clickpesa" {{ ($filters['gateway'] ?? '')==='clickpesa' ? 'selected' : '' }}>ClickPesa</option>
            </select>
            <select name="method" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);color:var(--coffee-700);font-weight:600;">
                <option value="all" {{ ($filters['method'] ?? 'all')==='all' ? 'selected' : '' }}>All methods</option>
                <option value="card" {{ ($filters['method'] ?? '')==='card' ? 'selected' : '' }}>Card</option>
                <option value="mobile_money" {{ ($filters['method'] ?? '')==='mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                <option value="bank" {{ ($filters['method'] ?? '')==='bank' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="wallet" {{ ($filters['method'] ?? '')==='wallet' ? 'selected' : '' }}>Wallet</option>
            </select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);">
            <button type="submit" class="btn btn-ghost btn-sm">Apply</button>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            <span style="margin-left:auto;font-size:12.5px;color:var(--ink-soft);font-weight:600;">{{ $payments->total() ?? 0 }} payments · Page {{ $payments->currentPage() ?? 1 }}</span>
        </div>
    </div>
</form>

<div class="table-card" style="margin-top:-24px;">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Payment Ref</th>
                    <th>Customer</th>
                    <th>Gateway / Channel</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="paymentsBody">
                @forelse ($payments as $pay)
                    <tr data-id="{{ $pay->getRouteKey() }}" data-search="{{ strtolower(($pay->reference ?? '').' '.($pay->customer_name ?? '').' '.($pay->customer_phone ?? '').' '.($pay->gateway_reference ?? '')) }}" data-status="{{ $pay->status }}" data-gateway="{{ $pay->gateway }}">
                        <td>
                            <div class="cell-title">{{ $pay->reference }}</div>
                            <div class="cell-sub">{{ $pay->gateway_reference ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="cell-title">{{ $pay->customer_name ?? '—' }}</div>
                            <div class="cell-sub">{{ $pay->customer_phone ?? $pay->customer_email ?? '—' }}</div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @php $gw = strtolower($pay->gateway ?? 'clickpesa'); @endphp
                                <span class="gateway-ico {{ $gw }}">{{ substr(strtoupper($pay->gateway ?? 'CP'),0,2) }}</span>
                                <div>
                                    <div class="cell-title" style="font-size:13px;">{{ ucfirst($pay->gateway ?? 'ClickPesa') }}</div>
                                    <div class="cell-sub">{{ ucwords(str_replace('_',' ', $pay->method ?? 'mobile_money')) }} · {{ $pay->channel ?? 'API' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="cell-title">@money($pay->amount)</td>
                        <td><span class="tag {{ status_badge($pay->status) }}">{{ ucfirst($pay->status) }}</span></td>
                        <td>
                            <div class="cell-title" style="font-size:12.5px;">{{ $pay->created_at->format('d M Y') }}</div>
                            <div class="cell-sub">{{ $pay->created_at->format('H:i') }} · {{ $pay->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('payments.show', $pay->getRouteKey()) }}" title="View details" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <button title="Webhook log" onclick="openWebhookLog('{{ $pay->getRouteKey() }}')" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                </button>
                                @if(in_array($pay->status, ['completed']))
                                    <button title="Refund" onclick="openRefundModal('{{ $pay->getRouteKey() }}','{{ $pay->reference }}','{{ $pay->amount }}')" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--terracotta-600);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M3 10h10a8 8 0 0 1 8 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                        <h4>No payments found</h4>
                        <p>Payments via ClickPesa will appear here once webhooks are received.<br>Try adjusting filters or <a href="#" onclick="openModal('verifyPaymentModal');return false;" style="color:var(--terracotta-600);font-weight:700;">verify a payment manually</a>.</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($payments) && $payments->hasPages())
        <div class="table-pager">
            <div class="pager-info">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</div>
            <div class="pager-pages">{{ $payments->links('pagination.pager') }}</div>
        </div>
    @endif
</div>

{{-- Verify Payment Modal --}}
<div class="modal-backdrop" id="verifyPaymentModal">
    <div class="modal">
        <div class="modal-head">
            <h3>Verify Payment via API</h3>
            <button class="modal-close" onclick="closeModal('verifyPaymentModal')">✕</button>
        </div>
        <form id="verifyForm" method="POST" action="{{ route('payments.verify') ?? '#' }}" data-verify-form>
            @csrf
            <div class="modal-body">
                <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px;">Lookup a transaction directly from the gateway API using its reference. Useful when webhook was delayed.</p>
                <div class="field">
                    <label>Gateway</label>
                    <select name="gateway" required>
                        <option value="clickpesa">ClickPesa</option>
                        <option value="selcom">Selcom</option>
                        <option value="dpo">DPO Group</option>
                        <option value="stripe">Stripe</option>
                        <option value="mpesa">M-Pesa Global</option>
                    </select>
                </div>
                <div class="field">
                    <label>Gateway Reference / API ID</label>
                    <input type="text" name="gateway_reference" placeholder="e.g. CP_9F3A2B1C or pi_3Q..." required>
                </div>
                <div class="field">
                    <label>Amount (TZS) <span style="color:var(--ink-soft);font-weight:500;text-transform:none;letter-spacing:0;">optional – verify expected amount</span></label>
                    <input type="number" step="0.01" name="amount" placeholder="50000">
                </div>
                <div style="background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;font-size:12.5px;color:var(--ink-soft);line-height:1.6;">
                    <strong style="color:var(--coffee-800);">API will be called:</strong> <code style="background:var(--white);padding:2px 6px;border-radius:6px;border:1px solid var(--line);">GET /v1/payments/{id}</code> · Response is validated, mapped to internal status, webhook replayed and ledger entry created if completed.
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('verifyPaymentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Verify via API</button>
            </div>
        </form>
    </div>
</div>

{{-- Refund Modal --}}
<div class="modal-backdrop" id="refundModal">
    <div class="modal">
        <div class="modal-head">
            <h3>Refund Payment</h3>
            <button class="modal-close" onclick="closeModal('refundModal')">✕</button>
        </div>
        <form id="refundForm" method="POST" action="#" data-refund-form>
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" id="refundPayId">
            <div class="modal-body">
                <p style="font-size:13.5px;color:var(--ink-soft);margin:0 0 14px;">Refund <strong id="refundRef" style="color:var(--coffee-900);"></strong> · Amount <strong id="refundAmt" style="color:var(--coffee-900);"></strong><br><span style="font-size:12px;">Refund is executed via the original gateway API. Partial refunds are supported.</span></p>
                <div class="field">
                    <label>Refund amount (TZS)</label>
                    <input type="number" step="0.01" id="refundAmount" name="amount" required>
                </div>
                <div class="field">
                    <label>Reason</label>
                    <select name="reason" required>
                        <option value="duplicate">Duplicate</option>
                        <option value="fraudulent">Fraudulent</option>
                        <option value="requested_by_customer">Requested by customer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>Notes</label>
                    <textarea name="notes" rows="2" placeholder="Internal notes for audit log"></textarea>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('refundModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Refund</button>
            </div>
        </form>
    </div>
</div>

{{-- API Health Modal --}}
<div class="modal-backdrop" id="apiHealthModal">
    <div class="modal" style="max-width:620px;">
        <div class="modal-head">
            <h3>Gateway API Health</h3>
            <button class="modal-close" onclick="closeModal('apiHealthModal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="display:grid;gap:12px;">
                @php
                    $gateways = [
                        ['name'=>'ClickPesa','status'=>'operational','latency'=>'124ms','uptime'=>'99.98%','last'=>'12s ago','color'=>'var(--acacia-600)'],
                    ];
                @endphp
                @foreach($gateways as $gw)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--white);border:1px solid var(--line);border-radius:12px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span class="api-dot {{ $gw['status']==='operational'?'ok':($gw['status']==='degraded'?'warn':'down') }}"></span>
                            <div>
                                <b style="font-size:14px;color:var(--coffee-900);">{{ $gw['name'] }}</b>
                                <div style="font-size:12px;color:var(--ink-soft);">{{ $gw['status'] }} · {{ $gw['latency'] }} · {{ $gw['uptime'] }}</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:12px;font-weight:700;color:var(--coffee-700);">{{ $gw['last'] }}</div>
                            <div style="font-size:11px;color:var(--ink-soft);">last webhook</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:16px;background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px;font-size:12.5px;color:var(--ink-soft);">
                <strong style="color:var(--coffee-800);">Webhook endpoint:</strong> <code style="background:var(--white);padding:3px 8px;border-radius:6px;border:1px solid var(--line);word-break:break-all;">{{ url('/api/webhooks/payments') }}</code> · Signed with <code style="background:var(--white);padding:2px 6px;border-radius:6px;">X-Webhook-Signature</code>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="closeModal('apiHealthModal')">Close</button>
            <a href="{{ route('settings.index') }}?pane=gateways" class="btn btn-primary">Manage API Keys</a>
        </div>
    </div>
</div>

{{-- Webhook Log Drawer --}}
<div class="modal-backdrop" id="webhookLogModal">
    <div class="modal">
        <div class="modal-head">
            <h3>Recent Webhook Deliveries</h3>
            <button class="modal-close" onclick="closeModal('webhookLogModal')">✕</button>
        </div>
        <div class="modal-body">
            <div class="webhook-timeline">
                <div class="wh-step">
                    <div class="wh-node ok"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                    <div>
                        <b style="font-size:13px;color:var(--coffee-900);">payment.completed</b> <span class="tag tag-green">200</span>
                        <div style="font-size:12px;color:var(--ink-soft);">ClickPesa · CP_9F3A2B · 12s ago · 124ms</div>
                        <div class="json-block" style="margin-top:8px;max-height:120px;">{ "event": "payment.completed", "id": "CP_9F3A2B", "amount": 50000, "currency": "TZS" }</div>
                    </div>
                </div>
                <div class="wh-step">
                    <div class="wh-node ok"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                    <div>
                        <b style="font-size:13px;color:var(--coffee-900);">payment.pending</b> <span class="tag tag-gold">200</span>
                        <div style="font-size:12px;color:var(--ink-soft);">ClickPesa · CP_1120 · 2m ago · 98ms</div>
                    </div>
                </div>
                <div class="wh-step">
                    <div class="wh-node failed"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></div>
                    <div>
                        <b style="font-size:13px;color:var(--coffee-900);">payment.failed</b> <span class="tag tag-red">500</span>
                        <div style="font-size:12px;color:var(--ink-soft);">ClickPesa · CP_7741 · 5m ago · Signature mismatch - retried 2x</div>
                        <button class="btn btn-ghost btn-sm" style="margin-top:6px;">Retry webhook</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-primary" onclick="closeModal('webhookLogModal')">Close</button>
        </div>
    </div>
</div>
@endsection

@php
    $paymentsJson = ($payments ?? collect())->map(function($p){
        return [
            'id'=>$p->id,
            'routeKey'=>$p->getRouteKey(),
            'reference'=>$p->reference,
            'gateway'=>$p->gateway,
            'method'=>$p->method,
            'channel'=>$p->channel,
            'amount'=>(float)$p->amount,
            'fee'=>(float)($p->fee ?? 0),
            'status'=>$p->status,
            'customer_name'=>$p->customer_name,
            'customer_phone'=>$p->customer_phone,
            'customer_email'=>$p->customer_email,
            'gateway_reference'=>$p->gateway_reference,
            'provider_reference'=>$p->provider_reference,
            'currency'=>$p->currency ?? 'TZS',
            'created_at'=>$p->created_at->format('d M Y H:i'),
            'webhooks'=>$p->webhooks ?? [],
            'raw_payload'=>$p->raw_payload ?? null,
        ];
    })->values();
@endphp
@section('scripts')
<script>
    const paymentsData = @json($paymentsJson);

    function setPaymentStatus(s){
        document.getElementById('fStatus').value=s;
        document.getElementById('filterForm').submit();
    }
    function filterPaymentRows(q){
        q=q.toLowerCase();
        document.querySelectorAll('#paymentsBody tr[data-id]').forEach(tr=>{
            tr.style.display=(!q || tr.dataset.search.includes(q)) ? 'table-row' : 'none';
        });
    }
    function openRefundModal(id, ref, amt){
        document.getElementById('refundPayId').value=id;
        document.getElementById('refundRef').textContent=ref;
        document.getElementById('refundAmt').textContent=fmt(amt);
        document.getElementById('refundAmount').value=amt;
        document.getElementById('refundAmount').max=amt;
        document.getElementById('refundForm').action='/payments/'+id+'/refund';
        openModal('refundModal');
    }
    function openWebhookLog(id){
        openModal('webhookLogModal');
    }
    function fmt(n){ return 'TZS ' + Number(n).toLocaleString('en-US',{maximumFractionDigits:2}); }

    bindRowClick('#paymentsBody tr[data-id]', tr=>{
        const p=paymentsData.find(x=> x.routeKey === tr.dataset.id || String(x.id)===String(tr.dataset.id));
        if(!p) return [];
        return [
            ['Reference', p.reference],
            ['Gateway Ref', p.gateway_reference || '—'],
            ['Provider Ref', p.provider_reference || '—'],
            ['Gateway', p.gateway ? {__html:`<span class="gateway-ico ${p.gateway.toLowerCase()}" style="display:inline-flex;width:22px;height:22px;font-size:9px;vertical-align:middle;">${p.gateway.slice(0,2).toUpperCase()}</span> ${p.gateway}`} : '—'],
            ['Method', p.method ? p.method.replace('_',' ') : '—'],
            ['Channel', p.channel || 'API'],
            ['Customer', p.customer_name || '—'],
            ['Phone', p.customer_phone || '—'],
            ['Email', p.customer_email || '—'],
            ['Amount', fmt(p.amount)],
            ['Fee', fmt(p.fee)],
            ['Net', fmt(p.amount - p.fee)],
            ['Currency', p.currency],
            ['Status', {__html:statusBadgeHtml(p.status)}],
            ['Date', p.created_at],
        ];
    }, 'Payment details');

    document.querySelectorAll('[data-verify-form]').forEach(f=>{
        f.addEventListener('submit', e=>{
            e.preventDefault();
            submitForm(f,{method:'POST', done:()=>setTimeout(()=>location.reload(),600)});
        });
    });
    document.querySelectorAll('[data-refund-form]').forEach(f=>{
        f.addEventListener('submit', e=>{
            e.preventDefault();
            submitForm(f,{method:'POST', done:()=>{ closeModal('refundModal'); toast('Refund submitted via gateway','success'); setTimeout(()=>location.reload(),800); }});
        });
    });
</script>
@endsection
