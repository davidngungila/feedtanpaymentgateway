<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt {{ $payment->reference }} · ClickPesa Feedtan Online</title>
<style>
    @page { margin: 12mm; }
    *{ box-sizing:border-box; }
    body{ margin:0; font-family:'Helvetica','Arial',sans-serif; color:#241408; background:#fff; -webkit-print-color-adjust:exact; }
    .header{ text-align:center; padding:18px 0 14px; border-bottom:2px solid #E4D7C2; }
    .brand{ display:inline-flex; align-items:center; justify-content:center; width:52px; height:52px; border-radius:12px; background:linear-gradient(135deg,#C2592B,#D4A24C); color:#fff; font-weight:800; }
    .h1{ margin:10px 0 2px; font-size:20px; color:#2A1B10; }
    .sub{ font-size:11px; color:#6B5A48; letter-spacing:.06em; text-transform:uppercase; }
    .badge{ display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; }
    .badge-green{ background:#E2E7D4; color:#5E6E3F; }
    .badge-terracotta{ background:#F6E1D3; color:#C2592B; }
    .amount-box{ text-align:center; border:1px dashed #E4D7C2; border-radius:12px; padding:16px; background:#FBF7EF; margin:18px 0; }
    .amount-label{ font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#6B5A48; }
    .amount{ font-size:32px; font-weight:800; color:#2A1B10; margin-top:4px; }
    .meta{ font-size:12px; color:#6B5A48; margin-top:6px; }
    .grid{ display:table; width:100%; border-collapse:collapse; margin-top:14px; }
    .row{ display:table-row; }
    .cell{ display:table-cell; padding:8px 10px; border-bottom:1px solid #E4D7C2; vertical-align:top; }
    .dk{ font-size:10px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#6B5A48; }
    .dv{ font-size:12px; font-weight:600; color:#2A1B10; margin-top:2px; word-break:break-all; }
    .mono{ font-family:'Courier New',monospace; }
    .footer{ text-align:center; font-size:10px; color:#6B5A48; border-top:1px dashed #E4D7C2; padding-top:12px; margin-top:18px; }
</style>
</head>
<body>
    <div class="header">
        <div class="brand">CP</div>
        <div class="h1">ClickPesa Feedtan Online</div>
        <div class="sub">Online Payment Receipt</div>
        <div style="margin-top:8px;">
            <span class="badge badge-green">{{ ucfirst($payment->status) }}</span>
            <span class="badge badge-terracotta" style="font-family:monospace;">{{ $payment->gateway ?? 'ClickPesa' }} · {{ $payment->method ?? 'mobile_money' }}</span>
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-label">Amount Paid</div>
        <div class="amount">TZS {{ number_format($payment->amount ?? 0, 0, '.', ',') }}</div>
        <div class="meta">Fee TZS {{ number_format($payment->fee ?? 0, 0, '.', ',') }} · Net TZS {{ number_format(($payment->amount ?? 0) - ($payment->fee ?? 0), 0, '.', ',') }} · {{ $payment->currency ?? 'TZS' }}</div>
        <div class="grid" style="margin-top:14px; text-align:left;">
            <div class="row"><div class="cell"><div class="dk">Payment Reference</div><div class="dv mono">{{ $payment->reference }}</div></div><div class="cell"><div class="dk">Gateway Reference</div><div class="dv mono" style="font-size:10px;">{{ $payment->gateway_reference ?? '—' }}</div></div></div>
            <div class="row"><div class="cell"><div class="dk">Provider Reference</div><div class="dv mono" style="font-size:10px;">{{ $payment->provider_reference ?? '—' }}</div></div><div class="cell"><div class="dk">Date & Time</div><div class="dv">{{ $payment->created_at->format('d M Y H:i:s') }}</div></div></div>
        </div>
    </div>

    <div class="grid">
        <div class="row"><div class="cell"><div class="dk">Customer</div><div class="dv">{{ $payment->customer_name ?? '—' }} @if($payment->customer_phone)<span style="color:#6B5A48;">· {{ $payment->customer_phone }}</span>@endif</div></div><div class="cell"><div class="dk">Email</div><div class="dv">{{ $payment->customer_email ?? '—' }}</div></div></div>
        <div class="row"><div class="cell"><div class="dk">Gateway</div><div class="dv">{{ ucfirst($payment->gateway ?? 'ClickPesa') }} · {{ ucwords(str_replace('_',' ', $payment->method ?? 'mobile_money')) }} · {{ $payment->channel ?? 'API' }}</div></div><div class="cell"><div class="dk">Status</div><div class="dv">{{ strtoupper($payment->status) }}</div></div></div>
        <div class="row"><div class="cell"><div class="dk">Settlement</div><div class="dv">{{ $payment->settlement_status ?? 'Settled' }} @if($payment->settlement_date)· {{ $payment->settlement_date->format('d M Y') }}@endif</div></div><div class="cell"><div class="dk">Operator</div><div class="dv">{{ $payment->operator?->name ?? 'System' }}</div></div></div>
    </div>

    @if($payment->billing_address)
        <div style="margin-top:12px; padding:10px; background:#F4ECDC; border-radius:8px; font-size:12px;">
            <strong>Billing Address:</strong> {{ $payment->billing_address }} @if($payment->customer_country)· {{ $payment->customer_country }}@endif
        </div>
    @endif

    <div class="footer">
        Thank you for using ClickPesa Feedtan Online · {{ config('app.name', 'ClickPesa') }} · {{ now()->format('d M Y H:i') }}<br>
        <span class="mono">Receipt ID: {{ $payment->reference }}-{{ $payment->id }}</span> · Encrypted: {{ substr($payment->getRouteKey(),0,16) }}...<br>
        <span style="font-size:9px;">This is a computer-generated receipt. No signature required.</span>
    </div>
</body>
</html>
