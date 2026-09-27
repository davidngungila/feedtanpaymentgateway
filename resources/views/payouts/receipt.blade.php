@extends('layouts.app')

@section('title', 'Payout Receipt '.$payout->reference)

@section('content')
    <div class="view-head">
        <div>
            <h2>Payout Receipt</h2>
            <p class="sub">{{ $payout->reference }} · {{ $payout->created_at->format('d M Y H:i') }} · {{ ucfirst($payout->gateway ?? 'ClickPesa') }} · {{ ucwords(str_replace('_',' ', $payout->method ?? 'mobile_money')) }}</p>
        </div>
        <div class="view-actions">
            <a href="{{ route('payouts.show', $payout->getRouteKey()) }}" class="btn btn-ghost">← Back to payout</a>
            <a href="{{ route('payouts.index') }}" class="btn btn-ghost">All payouts</a>
            <a href="{{ route('payouts.receipt.pdf', $payout->getRouteKey()) }}" class="btn btn-ghost">Export PDF</a>
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="panel" id="receiptPanel" style="width:100%; max-width:none; margin:0;">
        <div class="panel-body" style="padding:28px;">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--acacia-600),var(--acacia-500));color:#fff;box-shadow:var(--shadow-sm);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:26px;height:26px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 style="margin:12px 0 4px; font-size:20px;">ClickPesa Feedtan Online</h3>
                <div style="font-size:12px; color:var(--ink-soft); letter-spacing:.06em; text-transform:uppercase;">Payout Disbursement Receipt</div>
                <div style="margin-top:10px; display:inline-flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:center;">
                    <span class="tag {{ status_badge($payout->status) }}">{{ ucfirst(str_replace('_',' ', $payout->status)) }}</span>
                    <span class="tag tag-terracotta">{{ ucwords(str_replace('_',' ', $payout->method ?? 'mobile_money')) }} · {{ $payout->gateway ?? 'ClickPesa' }}</span>
                    @if($payout->gateway_reference)<span class="tag tag-grey" style="font-family:monospace; font-size:11px;">{{ $payout->gateway_reference }}</span>@endif
                </div>
            </div>

            <div style="border:1px dashed var(--line); border-radius:12px; padding:18px; background:var(--sand-50); margin-bottom:18px;">
                <div style="text-align:center;">
                    <div style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-soft);">Amount Disbursed</div>
                    <div style="font-size:32px; font-weight:800; color:var(--coffee-900); margin-top:4px;">@money($payout->amount)</div>
                    <div style="font-size:13px; color:var(--ink-soft); margin-top:6px;">Fee @money($payout->fee ?? 0) · Total debit @money(($payout->amount ?? 0) + ($payout->fee ?? 0)) · {{ $payout->currency ?? 'TZS' }}</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:18px; padding-top:14px; border-top:1px dashed var(--line);">
                    <div class="detail-item"><div class="dk">Payout Reference</div><div class="dv" style="font-family:monospace; font-size:13px;">{{ $payout->reference }}</div></div>
                    <div class="detail-item"><div class="dk">Gateway Reference</div><div class="dv" style="font-family:monospace; font-size:11px; word-break:break-all;">{{ $payout->gateway_reference ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Beneficiary</div><div class="dv">{{ $payout->beneficiary_name ?? '—' }} · {{ $payout->beneficiary_account ?? $payout->beneficiary_phone ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Date & Time</div><div class="dv">{{ $payout->created_at->format('d M Y H:i:s') }}</div></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                <div class="panel" style="margin:0; box-shadow:none; border:1px solid var(--line);">
                    <div class="panel-head"><h3>Beneficiary</h3></div>
                    <div class="panel-body">
                        <div class="detail-grid">
                            <div class="detail-item"><div class="dk">Name</div><div class="dv">{{ $payout->beneficiary_name ?? '—' }}</div></div>
                            <div class="detail-item"><div class="dk">Account / Phone</div><div class="dv" style="font-family:monospace; font-size:12px;">{{ $payout->beneficiary_account ?? $payout->beneficiary_phone ?? '—' }}</div></div>
                            <div class="detail-item"><div class="dk">Bank</div><div class="dv">{{ $payout->bank_name ?? '—' }}</div></div>
                            <div class="detail-item"><div class="dk">Method</div><div class="dv">{{ ucwords(str_replace('_',' ', $payout->method ?? 'mobile_money')) }}</div></div>
                            <div class="detail-item"><div class="dk">Gateway</div><div class="dv">{{ $payout->gateway ?? 'ClickPesa' }}</div></div>
                        </div>
                    </div>
                </div>
                <div class="panel" style="margin:0; box-shadow:none; border:1px solid var(--line);">
                    <div class="panel-head"><h3>Disbursement</h3></div>
                    <div class="panel-body">
                        <div class="detail-grid">
                            <div class="detail-item"><div class="dk">Amount</div><div class="dv" style="font-weight:700; color:var(--coffee-900);">@money($payout->amount)</div></div>
                            <div class="detail-item"><div class="dk">Fee</div><div class="dv">@money($payout->fee ?? 0)</div></div>
                            <div class="detail-item"><div class="dk">Total Debit</div><div class="dv" style="font-weight:700;">@money(($payout->amount ?? 0)+($payout->fee ?? 0))</div></div>
                            <div class="detail-item"><div class="dk">Status</div><div class="dv"><span class="tag {{ status_badge($payout->status) }}">{{ strtoupper(str_replace('_',' ', $payout->status)) }}</span></div></div>
                            <div class="detail-item"><div class="dk">Purpose</div><div class="dv">{{ ucwords(str_replace('_',' ', $payout->purpose ?? 'other')) }}</div></div>
                            <div class="detail-item"><div class="dk">Requested by</div><div class="dv">{{ $payout->requester?->name ?? $payout->created_by_name ?? 'System' }} · {{ $payout->created_at->format('d M Y H:i') }}</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel" style="margin:0; box-shadow:none; border:1px solid var(--line);">
                <div class="panel-head"><h3>Approval & Ledger</h3></div>
                <div class="panel-body">
                    <div class="detail-grid">
                        <div class="detail-item"><div class="dk">Approved by</div><div class="dv">{{ $payout->approver?->name ?? '—' }} @if($payout->approved_at)· {{ $payout->approved_at->format('d M Y H:i') }}@endif</div></div>
                        <div class="detail-item"><div class="dk">Source</div><div class="dv">{{ ucfirst($payout->source_account ?? 'settlement') }}</div></div>
                        <div class="detail-item"><div class="dk">Journal Entry</div><div class="dv" style="font-family:monospace;">JE-{{ str_pad($payout->id,6,'0',STR_PAD_LEFT) }}</div></div>
                        <div class="detail-item"><div class="dk">Notes</div><div class="dv">{{ $payout->notes ?? '—' }}</div></div>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px; text-align:center; font-size:11px; color:var(--ink-soft); border-top:1px dashed var(--line); padding-top:14px;">
                Thank you for using ClickPesa Feedtan Online · {{ config('app.name', 'ClickPesa') }} · {{ now()->format('d M Y H:i') }}<br>
                <span style="font-family:monospace;">Receipt ID: {{ $payout->reference }}-{{ $payout->id }}</span> · Encrypted: {{ substr($payout->getRouteKey(),0,16) }}...
            </div>
        </div>
    </div>

    <style>
        @media print {
            .topbar, .sidebar, .view-head .view-actions, .sb-footer { display:none !important; }
            .main { margin-left:0 !important; }
            .view-wrap { padding:0 !important; }
            .panel { box-shadow:none !important; border:1px solid #ddd !important; }
        }
    </style>
@endsection
