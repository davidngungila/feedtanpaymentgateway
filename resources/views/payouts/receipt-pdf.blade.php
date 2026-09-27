<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payout Receipt {{ $payout->reference }} · ClickPesa Feedtan Online</title>
<style>
    @page { margin: 12mm; }
    *{ box-sizing:border-box; }
    body{ margin:0; font-family:'Helvetica','Arial',sans-serif; color:#241408; background:#fff; -webkit-print-color-adjust:exact; }
    .header{ text-align:center; padding:14px 0 12px; border-bottom:2px solid #E4D7C2; }
    .brand{ display:inline-flex; align-items:center; justify-content:center; width:52px; height:52px; border-radius:12px; background:linear-gradient(135deg,#5E6E3F,#7A8450); color:#fff; font-weight:800; }
    .h1{ margin:10px 0 2px; font-size:20px; color:#2A1B10; }
    .sub{ font-size:11px; color:#6B5A48; letter-spacing:.06em; text-transform:uppercase; }
    .badge{ display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; }
    .badge-green{ background:#E2E7D4; color:#5E6E3F; }
    .badge-terracotta{ background:#F6E1D3; color:#C2592B; }
    .amount-box{ text-align:center; border:1px dashed #E4D7C2; border-radius:12px; padding:16px; background:#FBF7EF; margin:16px 0; }
    .amount-label{ font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#6B5A48; }
    .amount{ font-size:32px; font-weight:800; color:#2A1B10; margin-top:4px; }
    .meta{ font-size:12px; color:#6B5A48; margin-top:6px; }
    .grid{ display:table; width:100%; border-collapse:collapse; margin-top:12px; }
    .row{ display:table-row; }
    .cell{ display:table-cell; padding:7px 10px; border-bottom:1px solid #E4D7C2; vertical-align:top; }
    .dk{ font-size:10px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#6B5A48; }
    .dv{ font-size:12px; font-weight:600; color:#2A1B10; margin-top:2px; word-break:break-all; }
    .mono{ font-family:'Courier New',monospace; }
    .section{ border:1px solid #E4D7C2; border-radius:10px; padding:12px; margin-top:14px; }
    .section h3{ margin:0 0 8px; font-size:13px; color:#2A1B10; }
    .footer{ text-align:center; font-size:10px; color:#6B5A48; border-top:1px dashed #E4D7C2; padding-top:10px; margin-top:16px; }
</style>
</head>
<body>
    <div class="header">
        <div class="brand">CP</div>
        <div class="h1">ClickPesa Feedtan Online</div>
        <div class="sub">Payout Disbursement Receipt</div>
        <div style="margin-top:8px;">
            <span class="badge badge-green">{{ ucfirst(str_replace('_',' ', $payout->status)) }}</span>
            <span class="badge badge-terracotta" style="font-family:monospace;">{{ $payout->gateway ?? 'ClickPesa' }} · {{ ucwords(str_replace('_',' ', $payout->method ?? 'mobile_money')) }}</span>
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-label">Amount Disbursed</div>
        <div class="amount">TZS {{ number_format($payout->amount ?? 0, 0, '.', ',') }}</div>
        <div class="meta">Fee TZS {{ number_format($payout->fee ?? 0, 0, '.', ',') }} · Total debit TZS {{ number_format(($payout->amount ?? 0)+($payout->fee ?? 0), 0, '.', ',') }}</div>
        <div class="grid" style="margin-top:12px; text-align:left;">
            <div class="row"><div class="cell"><div class="dk">Payout Reference</div><div class="dv mono">{{ $payout->reference }}</div></div><div class="cell"><div class="dk">Gateway Reference</div><div class="dv mono" style="font-size:10px;">{{ $payout->gateway_reference ?? '—' }}</div></div></div>
            <div class="row"><div class="cell"><div class="dk">Beneficiary</div><div class="dv">{{ $payout->beneficiary_name ?? '—' }} · {{ $payout->beneficiary_account ?? $payout->beneficiary_phone ?? '—' }}</div></div><div class="cell"><div class="dk">Date & Time</div><div class="dv">{{ $payout->created_at->format('d M Y H:i:s') }}</div></div></div>
        </div>
    </div>

    <div class="section">
        <h3>Beneficiary</h3>
        <div class="grid">
            <div class="row"><div class="cell"><div class="dk">Name</div><div class="dv">{{ $payout->beneficiary_name ?? '—' }}</div></div><div class="cell"><div class="dk">Account / Phone</div><div class="dv mono" style="font-size:11px;">{{ $payout->beneficiary_account ?? $payout->beneficiary_phone ?? '—' }}</div></div></div>
            <div class="row"><div class="cell"><div class="dk">Bank</div><div class="dv">{{ $payout->bank_name ?? '—' }}</div></div><div class="cell"><div class="dk">Method</div><div class="dv">{{ ucwords(str_replace('_',' ', $payout->method ?? 'mobile_money')) }}</div></div></div>
        </div>
    </div>

    <div class="section">
        <h3>Disbursement & Approval</h3>
        <div class="grid">
            <div class="row"><div class="cell"><div class="dk">Amount</div><div class="dv">TZS {{ number_format($payout->amount ?? 0, 0, '.', ',') }}</div></div><div class="cell"><div class="dk">Fee</div><div class="dv">TZS {{ number_format($payout->fee ?? 0, 0, '.', ',') }}</div></div></div>
            <div class="row"><div class="cell"><div class="dk">Total Debit</div><div class="dv">TZS {{ number_format(($payout->amount ?? 0)+($payout->fee ?? 0), 0, '.', ',') }}</div></div><div class="cell"><div class="dk">Status</div><div class="dv">{{ strtoupper(str_replace('_',' ', $payout->status)) }}</div></div></div>
            <div class="row"><div class="cell"><div class="dk">Approved by</div><div class="dv">{{ $payout->approver?->name ?? '—' }} @if($payout->approved_at)· {{ $payout->approved_at->format('d M Y H:i') }}@endif</div></div><div class="cell"><div class="dk">Requested by</div><div class="dv">{{ $payout->requester?->name ?? $payout->created_by_name ?? 'System' }}</div></div></div>
        </div>
    </div>

    <div class="footer">
        Thank you for using ClickPesa Feedtan Online · {{ config('app.name', 'ClickPesa') }} · {{ now()->format('d M Y H:i') }}<br>
        <span class="mono">Receipt ID: {{ $payout->reference }}-{{ $payout->id }}</span> · Encrypted: {{ substr($payout->getRouteKey(),0,16) }}...<br>
        <span style="font-size:9px;">This is a computer-generated receipt. No signature required.</span>
    </div>
</body>
</html>
