@extends('layouts.app')

@section('title', $provider->name)

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div><h2><span class="prov-dot" style="background:{{ $provider->color }};width:12px;height:12px;"></span>{{ $provider->name }}</h2>
    <p class="sub">Driver: {{ $provider->driver }} · Webhook endpoint: <code class="mono">{{ $endpoint }}</code></p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('providers.index') }}">All providers</a></div>
</div>

@include('collections.partials.flash')

<div class="tabs">
    @foreach (['collections' => 'Collections', 'status' => 'Status Queries', 'webhooks' => 'Webhooks', 'logs' => 'Logs', 'config' => 'Configuration', 'credentials' => 'Credentials'] as $key => $label)
        <a class="tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('providers.show', ['provider' => $provider->code, 'tab' => $key]) }}">{{ $label }}</a>
    @endforeach
</div>

@if ($tab === 'collections')
    @include('collections.partials.txn-table', ['txns' => $collections, 'providers' => [$provider->code => ['name' => $provider->name, 'color' => $provider->color]]])
@endif

@if ($tab === 'status')
    <div class="settings-panel" style="max-width:640px;margin-bottom:18px;">
        <h3 style="margin-top:0;">Query collection status</h3>
        <form method="POST" action="{{ route('providers.status.query', $provider->code) }}" class="filterbar">
            @csrf
            <div class="field" style="flex:1;"><label style="font-size:11px;">Order reference</label><input type="text" name="reference" required placeholder="PAY…"></div>
            <div><button class="btn btn-primary" type="submit">Query</button></div>
        </form>
        @if (session('status_result'))
            @php $sr = session('status_result'); @endphp
            <div class="flash info" style="margin-top:12px;">
                {{ $sr['reference'] }} → <strong>{{ $sr['status'] }}</strong>
                @if ($sr['amount']) · TZS {{ number_format($sr['amount'], 0) }} @endif
                @if ($sr['error']) · {{ $sr['error'] }} @endif
            </div>
        @endif
    </div>
@endif

@if ($tab === 'webhooks')
    <div class="panel" style="margin-bottom:18px;">
        <div class="panel-head"><h3>Inbound endpoint</h3></div>
        <div class="panel-body">
            <div class="dk">POST</div><div class="dv mono">{{ $endpoint }}</div>
            <p style="font-size:13px;color:var(--ink-soft);">Headers: <code class="mono">X-Provider-Signature</code> (HMAC-SHA256 of raw body), optional <code class="mono">X-Timestamp</code>. Replays are rejected, raw payloads stored encrypted, duplicates acknowledged without reprocessing.</p>
            @if (auth()->user()->hasPermission('credentials.manage'))
                <form method="POST" action="{{ route('providers.webhook.rotate', $provider->code) }}" onsubmit="return confirm('Rotate the webhook secret? Update the provider side immediately.');">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Rotate webhook secret</button>
                </form>
            @endif
        </div>
    </div>
    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Event</th><th>Signature</th><th>Processing</th><th>Received</th></tr></thead>
                <tbody>
                    @forelse ($webhooks as $w)
                        <tr>
                            <td class="mono">{{ $w->event_id ?? substr($w->payload_hash, 0, 12).'…' }}</td>
                            <td><span class="pill pill-{{ $w->signature_status === 'verified' ? 'success' : 'failed' }}">{{ $w->signature_status }}</span></td>
                            <td><span class="pill pill-neutral">{{ $w->processing_status }}</span></td>
                            <td>{{ $w->received_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state">No webhook deliveries yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px">{{ $webhooks->links() }}</div>
@endif

@if ($tab === 'logs')
    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Event</th><th>Signature</th><th>Processing</th><th>Error</th><th>Received</th></tr></thead>
                <tbody>
                    @forelse ($webhooks as $w)
                        <tr>
                            <td class="mono">{{ $w->event_id ?? substr($w->payload_hash, 0, 12).'…' }}</td>
                            <td>{{ $w->signature_status }}</td>
                            <td>{{ $w->processing_status }}</td>
                            <td style="font-size:12px;">{{ $w->error ?? '—' }}</td>
                            <td>{{ $w->received_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">No logs yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px">{{ $webhooks->links() }}</div>
@endif

@if ($tab === 'config')
    <div class="settings-panel" style="max-width:640px;">
        <h3 style="margin-top:0;">Configuration</h3>
        <form method="POST" action="{{ route('providers.config', $provider->code) }}">
            @csrf @method('PUT')
            <div class="field"><label>Active</label><input type="checkbox" name="is_active" value="1" {{ $provider->is_active ? 'checked' : '' }}> Enabled</div>
            <div class="field"><label>Phone prefixes (comma separated)</label><input type="text" name="prefixes" value="{{ old('prefixes', implode(',', $provider->prefixes())) }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field"><label>Fee %</label><input type="number" step="0.01" min="0" max="100" name="fee_percent" value="{{ old('fee_percent', $provider->config['fee']['percent'] ?? 0) }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
                <div class="field"><label>Fee flat (TZS)</label><input type="number" step="0.01" min="0" name="fee_flat" value="{{ old('fee_flat', $provider->config['fee']['flat'] ?? 0) }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            </div>
            <h3 style="margin-top:18px;">Direct API transport</h3>
            <p style="font-size:13px;color:var(--ink-soft);">This provider's own API. Collections fail closed until a base URL + initiate path are set.</p>
            <div class="field"><label>Base URL</label><input type="url" name="base_url" value="{{ old('base_url', $provider->config['direct']['base_url'] ?? '') }}" placeholder="https://api.provider.co.tz" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field"><label>Initiate path</label><input type="text" name="initiate_path" value="{{ old('initiate_path', $provider->config['direct']['initiate_path'] ?? '') }}" placeholder="/v1/collections/ussd-push" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
                <div class="field"><label>Status path ({reference})</label><input type="text" name="status_path" value="{{ old('status_path', $provider->config['direct']['status_path'] ?? '') }}" placeholder="/v1/collections/{reference}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="field"><label>Test path</label><input type="text" name="test_path" value="{{ old('test_path', $provider->config['direct']['test_path'] ?? '/') }}" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
                <div class="field"><label>Auth type</label><select name="auth_type" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">@foreach (['api-key', 'bearer', 'none'] as $t)<option value="{{ $t }}" {{ old('auth_type', $provider->config['direct']['auth_type'] ?? 'api-key') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></div>
            </div>
            <button class="btn btn-primary" type="submit" style="width:100%;">Save configuration</button>
        </form>
    </div>
@endif

@if ($tab === 'credentials')
    <div class="settings-panel" style="max-width:640px;margin-bottom:18px;">
        <h3 style="margin-top:0;">Credentials ({{ $credential?->environment ?? 'live' }})</h3>
        @if ($credential)
            <div class="detail-grid" style="display:grid;gap:10px;margin-bottom:14px;">
                <div><div class="dk">Client ID</div><div class="dv mono">{{ $credential->client_id ?? '—' }}</div></div>
                <div><div class="dk">Client secret</div><div class="dv mono">{{ \App\Support\Security\Crypto::maskSecret('••••') }} (stored encrypted, never displayed)</div></div>
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
@endif
@endsection
