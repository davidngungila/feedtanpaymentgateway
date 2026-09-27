@extends('layouts.app')

@section('title', $provider->name.' · Credentials')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
@include('providers.partials.header', ['active' => 'credentials'])

<div class="settings-panel" style="max-width:640px;">
    <h3 style="margin-top:0;">Credentials ({{ $credential?->environment ?? 'live' }})</h3>
    @if ($credential)
        <div class="detail-grid" style="display:grid;gap:10px;margin-bottom:14px;">
            <div><div class="dk">Client ID</div><div class="dv mono">{{ $credential->client_id ?? '—' }}</div></div>
            <div><div class="dk">Client secret</div><div class="dv mono">•••••••• (stored encrypted, never displayed)</div></div>
            <div><div class="dk">API key</div><div class="dv mono">•••••••• (stored encrypted, never displayed)</div></div>
            <div><div class="dk">Webhook secret</div><div class="dv mono">•••••••• (stored encrypted, never displayed)</div></div>
        </div>
        <form method="POST" action="{{ route('providers.credentials.test', $provider->code) }}" style="margin-bottom:14px;">
            @csrf
            <button class="btn btn-ghost" type="submit">Test connection</button>
        </form>
    @else
        <p style="font-size:13px;color:var(--ink-soft);">No credentials stored yet.</p>
    @endif
    @if (auth()->user()->hasPermission('credentials.manage'))
        <h3>Store / rotate credentials</h3>
        <form method="POST" action="{{ route('providers.credentials.store', $provider->code) }}">
            @csrf
            <div class="field"><label>Environment</label><select name="environment" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"><option value="live">live</option><option value="sandbox">sandbox</option></select></div>
            <div class="field"><label>Label</label><input type="text" name="label" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;" placeholder="Primary"></div>
            <div class="field"><label>Client ID</label><input type="text" name="client_id" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Client secret</label><input type="password" name="client_secret" autocomplete="new-password" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>API key</label><input type="password" name="api_key" autocomplete="new-password" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Webhook secret</label><input type="password" name="webhook_secret" autocomplete="new-password" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <button class="btn btn-primary" type="submit" style="width:100%;">Save encrypted</button>
        </form>
    @endif
</div>
    </div>
</div>
@endsection
