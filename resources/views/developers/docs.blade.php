@extends('layouts.app')

@section('title', 'Developer Documentation')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Documentation</h2><p class="sub">How the five providers flow through one engine.</p></div></div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Standard payment flow</h3></div>
    <div class="panel-body" style="font-size:13.5px;line-height:2;">
        Customer → Payment Request → System → Select Mobile Network → Provider API → Customer Authorization → Provider → Webhook / Callback → Verify Transaction → SUCCESS / FAILED / PENDING → Ledger → Reconciliation → Settlement
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Internal transaction (unified)</h3></div>
    <div class="panel-body mono" style="font-size:12.5px;line-height:2;">
        TXN ID · Provider · Provider Transaction ID (encrypted + blind index)<br>
        Customer · Phone (hash only) · Amount · Currency · Reference<br>
        Provider Fee · Net Amount · Status · Initiated At · Completed At<br>
        Webhook Status · Settlement Status
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Inbound webhooks</h3></div>
    <div class="panel-body" style="font-size:13.5px;line-height:1.9;">
        <p><code class="mono">POST /webhooks/{mpesa|airtel|mixx|halopesa|tpesa}</code></p>
        <p>Headers: <code class="mono">X-Provider-Signature</code> = HMAC-SHA256 of the raw body with the provider webhook secret. Optional <code class="mono">X-Timestamp</code> (5-minute tolerance). Replays and duplicates are acknowledged without reprocessing; raw payloads are stored encrypted.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Security rules</h3></div>
    <div class="panel-body" style="font-size:13.5px;line-height:1.9;">
        <ul style="margin:0;padding-left:18px;">
            <li>Secrets are encrypted at rest; only HMAC blind indexes are queryable.</li>
            <li>Phones are masked everywhere (<code class="mono">255******678</code>); reveals are audited.</li>
            <li>SUCCESS records are immutable — corrections via refunds/reversals.</li>
            <li>Status moves forward only; provider + provider-reference is unique.</li>
            <li>API keys: prefix + hash stored; full key shown once at creation.</li>
        </ul>
    </div>
</div>
@endsection
