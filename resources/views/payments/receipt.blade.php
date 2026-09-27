@extends('layouts.app')

@section('title', 'Payment Receipt '.$payment->reference)

@section('content')
    <div class="view-head">
        <div>
            <h2>Payment Receipt</h2>
            <p class="sub">{{ $payment->reference }} · {{ $payment->created_at->format('d M Y H:i') }} · {{ ucfirst($payment->gateway ?? 'ClickPesa') }}</p>
        </div>
        <div class="view-actions">
            <a href="{{ route('payments.show', $payment->getRouteKey()) }}" class="btn btn-ghost">← Back to payment</a>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost">All payments</a>
            <a href="{{ route('payments.receipt.pdf', $payment->getRouteKey()) }}" class="btn btn-ghost">Export PDF</a>
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="panel" id="receiptPanel" style="width:100%; max-width:none; margin:0;">
        <div class="panel-body" style="padding:28px;">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--terracotta-600),var(--gold-500));color:#fff;box-shadow:var(--shadow-sm);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:26px;height:26px;"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </div>
                <h3 style="margin:12px 0 4px; font-size:20px;">ClickPesa Feedtan Online</h3>
                <div style="font-size:12px; color:var(--ink-soft); letter-spacing:.06em; text-transform:uppercase;">Online Payment Receipt</div>
                <div style="margin-top:10px; display:inline-flex; gap:8px; align-items:center;">
                    <span class="tag {{ status_badge($payment->status) }}">{{ ucfirst($payment->status) }}</span>
                    <span class="tag tag-terracotta" style="font-family:monospace; font-size:11px;">{{ $payment->gateway ?? 'ClickPesa' }} · {{ $payment->method ?? 'mobile_money' }}</span>
                </div>
            </div>

            <div style="border:1px dashed var(--line); border-radius:12px; padding:18px; background:var(--sand-50); margin-bottom:18px;">
                <div style="text-align:center;">
                    <div style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-soft);">Amount Paid</div>
                    <div style="font-size:32px; font-weight:800; color:var(--coffee-900); margin-top:4px;">@money($payment->amount)</div>
                    <div style="font-size:13px; color:var(--ink-soft); margin-top:6px;">Fee @money($payment->fee ?? 0) · Net @money(($payment->amount ?? 0) - ($payment->fee ?? 0)) · {{ $payment->currency ?? 'TZS' }}</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:18px; padding-top:14px; border-top:1px dashed var(--line);">
                    <div class="detail-item"><div class="dk">Payment Reference</div><div class="dv" style="font-family:monospace; font-size:13px;">{{ $payment->reference }}</div></div>
                    <div class="detail-item"><div class="dk">Gateway Reference</div><div class="dv" style="font-family:monospace; font-size:11px; word-break:break-all;">{{ $payment->gateway_reference ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Provider Reference</div><div class="dv" style="font-family:monospace; font-size:11px;">{{ $payment->provider_reference ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Date & Time</div><div class="dv">{{ $payment->created_at->format('d M Y H:i:s') }}</div></div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item"><div class="dk">Customer</div><div class="dv">{{ $payment->customer_name ?? '—' }} @if($payment->customer_phone)<span style="color:var(--ink-soft);">· {{ $payment->customer_phone }}</span>@endif</div></div>
                <div class="detail-item"><div class="dk">Email</div><div class="dv">{{ $payment->customer_email ?? '—' }}</div></div>
                <div class="detail-item"><div class="dk">Gateway</div><div class="dv">{{ ucfirst($payment->gateway ?? 'ClickPesa') }} · {{ ucwords(str_replace('_',' ', $payment->method ?? 'mobile_money')) }} · {{ $payment->channel ?? 'API' }}</div></div>
                <div class="detail-item"><div class="dk">Status</div><div class="dv"><span class="tag {{ status_badge($payment->status) }}">{{ strtoupper($payment->status) }}</span></div></div>
                <div class="detail-item"><div class="dk">Settlement</div><div class="dv">{{ $payment->settlement_status ?? 'Settled' }} @if($payment->settlement_date)· {{ $payment->settlement_date->format('d M Y') }}@endif</div></div>
                <div class="detail-item"><div class="dk">Operator</div><div class="dv">{{ $payment->operator?->name ?? auth()->user()?->name ?? 'System' }}</div></div>
            </div>

            @if($payment->billing_address)
                <div style="margin-top:16px; padding:12px; background:var(--sand-100); border-radius:8px; font-size:13px;">
                    <strong>Billing Address:</strong> {{ $payment->billing_address }} @if($payment->customer_country)· {{ $payment->customer_country }}@endif
                </div>
            @endif

            <div style="margin-top:20px; text-align:center; font-size:11px; color:var(--ink-soft); border-top:1px dashed var(--line); padding-top:14px;">
                Thank you for using ClickPesa Feedtan Online · {{ config('app.name', 'ClickPesa') }} · {{ now()->format('d M Y H:i') }}<br>
                <span style="font-family:monospace;">Receipt ID: {{ $payment->reference }}-{{ $payment->id }}</span> · Encrypted: {{ substr($payment->getRouteKey(),0,12) }}...
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
