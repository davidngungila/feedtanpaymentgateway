@extends('layouts.app')

@section('title', $provider->name.' · Configuration')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
@include('providers.partials.header', ['active' => 'config'])

<div class="create-grid">
<div class="settings-panel">
    <h3 style="margin-top:0;">Configuration</h3>
    <form method="POST" action="{{ route('providers.config.update', $provider->code) }}">
        @csrf @method('PUT')
        <div class="field"><label>Active</label><input type="checkbox" name="is_active" value="1" {{ $provider->is_active ? 'checked' : '' }} style="width:auto;"> Enabled</div>
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
<div class="settings-panel">
    <h3 style="margin-top:0;">About these settings</h3>
    <ol class="detail-list">
        <li><strong>Prefixes</strong> drive auto-detection — a 255{{ implode(', 255', $provider->prefixes()) }} number routes here.</li>
        <li><strong>Fees</strong> are assessed on every collection: percent of amount plus flat TZS.</li>
        <li><strong>Direct transport</strong> is this provider's own API — no aggregator in between.</li>
        <li>Until base URL + initiate path are set, collections <strong>fail closed</strong> with a clear message.</li>
        <li><strong>Status path</strong> supports a <code class="mono">{reference}</code> placeholder for polling.</li>
        <li>Use <strong>Test connection</strong> on Credentials after saving.</li>
    </ol>
    <div class="kv"><span class="k">Driver</span><span class="v mono">{{ $provider->driver }}</span></div>
    <div class="kv"><span class="k">Status</span><span class="v">{{ $provider->is_active ? 'Active' : 'Disabled' }}</span></div>
    <div class="kv"><span class="k">Webhook endpoint</span><span class="v mono" style="font-size:11px;">POST /webhooks/{{ $provider->code }}</span></div>
</div>
</div>
    </div>
</div>
@endsection
