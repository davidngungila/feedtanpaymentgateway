@extends('layouts.app')

@section('title', 'Audit #'.$log->id.' · '.$log->action)

@section('head')
<style>
    .kv-grid{display:grid;grid-template-columns:160px 1fr;gap:10px 16px;font-size:13.5px;}
    .kv-grid dt{color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-size:11px;padding-top:2px;}
    .kv-grid dd{margin:0;color:var(--coffee-900);font-weight:600;word-break:break-word;}
    .json-block{background:#1A120B;color:#F4ECDC;border-radius:12px;padding:16px;font-family:ui-monospace,monospace;font-size:12px;line-height:1.7;overflow:auto;max-height:360px;white-space:pre-wrap;word-break:break-word;border:1px solid #2A1B10;}
    .diff-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .diff-box{background:var(--sand-50);border:1px solid var(--line);border-radius:12px;padding:14px;font-family:ui-monospace,monospace;font-size:12px;line-height:1.6;}
    .timeline{position:relative;padding-left:26px;}
    .timeline::before{content:"";position:absolute;left:10px;top:8px;bottom:8px;width:2px;background:var(--line);border-radius:2px;}
    .tl-step{position:relative;display:flex;gap:12px;padding:10px 0 14px;}
    .tl-step:last-child{padding-bottom:0;}
    .tl-node{position:absolute;left:-26px;top:12px;width:16px;height:16px;border-radius:50%;border:2px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;}
    .tl-node.ok{background:var(--acacia-600);border-color:var(--acacia-600);color:#fff;}
    .tl-node.info{background:var(--terracotta-600);border-color:var(--terracotta-600);color:#fff;}
    .tl-node.crit{background:var(--danger);border-color:var(--danger);color:#fff;}
    .severity{font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;padding:3px 8px;border-radius:20px;}
    .severity-info{background:var(--acacia-100);color:var(--acacia-600);}
    .severity-warn{background:var(--gold-100);color:#8a6418;}
    .severity-crit{background:var(--danger-100);color:var(--danger);}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('audit.index') }}" class="btn btn-ghost btn-sm">← Back to audit</a>
            <span class="severity {{ strtolower($log->severity ?? 'info')==='critical'?'severity-crit':(strtolower($log->severity ?? 'info')==='warn'?'severity-warn':'severity-info') }}">{{ $log->severity ?? 'info' }}</span>
            <span class="tag tag-terracotta" style="font-family:monospace; font-size:11px;">#{{ $log->id }} · {{ $log->action }}</span>
            <span class="tag tag-grey" style="font-family:monospace; font-size:11px;">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
        </div>
        <h2 style="margin-top:10px;">{{ $log->action }}</h2>
        <p class="sub">{{ $log->entity_type ?? 'System' }} @if($log->entity_id)#{{ $log->entity_id }}@endif · by {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</p>
    </div>
    <div class="view-actions">
        <a href="{{ route('audit.index') }}" class="btn btn-ghost">All logs</a>
        <button class="btn btn-ghost" onclick="window.print()">Print</button>
        <button class="btn btn-primary" onclick="navigator.clipboard.writeText(window.location.href);toast('Link copied','success')">Copy link</button>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:18px;margin-bottom:20px;">
    <div class="panel">
        <div class="panel-head"><h3>Event Overview</h3><span class="link">{{ $log->created_at->format('d M Y H:i:s') }}</span></div>
        <div class="panel-body">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div class="avatar" style="width:42px;height:42px;font-size:14px;">{{ strtoupper(substr($log->user?->name ?? 'SY',0,2)) }}</div>
                <div>
                    <b style="font-size:15px;color:var(--coffee-900);">{{ $log->user?->name ?? 'System' }}</b>
                    <div style="font-size:13px;color:var(--ink-soft);">{{ $log->user?->email ?? 'system' }} · {{ ucfirst($log->user?->role ?? 'system') }}</div>
                </div>
                <span class="severity {{ strtolower($log->severity ?? 'info')==='critical'?'severity-crit':(strtolower($log->severity ?? 'info')==='warn'?'severity-warn':'severity-info') }}" style="margin-left:auto;">{{ strtoupper($log->severity ?? 'info') }}</span>
            </div>

            <div class="kv-grid">
                <dt>When</dt><dd>{{ $log->created_at->format('d M Y H:i:s') }} <span style="color:var(--ink-soft);">({{ $log->created_at->diffForHumans() }})</span></dd>
                <dt>Action</dt><dd><span class="tag tag-terracotta">{{ $log->action }}</span></dd>
                <dt>Entity</dt><dd>{{ $log->entity_type ?? '—' }} @if($log->entity_id)<span style="font-family:monospace;">#{{ $log->entity_id }}</span>@endif</dd>
                <dt>Severity</dt><dd><span class="severity {{ strtolower($log->severity ?? 'info')==='critical'?'severity-crit':(strtolower($log->severity ?? 'info')==='warn'?'severity-warn':'severity-info') }}">{{ $log->severity ?? 'info' }}</span></dd>
                <dt>IP Address</dt><dd style="font-family:monospace;">{{ $log->ip_address ?? '—' }}</dd>
                <dt>User Agent</dt><dd style="font-size:12px; word-break:break-all;">{{ $log->user_agent ?? '—' }}</dd>
                <dt>Log ID</dt><dd style="font-family:monospace;">{{ $log->id }} · Encrypted: {{ substr(encrypt_id($log->id),0,16) }}...</dd>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Context</h3><span class="link">Request</span></div>
        <div class="panel-body">
            <div class="kv-grid">
                <dt>User</dt><dd>{{ $log->user?->name ?? 'System' }} ({{ $log->user?->email ?? 'system' }})</dd>
                <dt>Role</dt><dd>{{ ucfirst($log->user?->role ?? 'system') }}</dd>
                <dt>Entity</dt><dd>{{ $log->entity_type ?? '—' }} #{{ $log->entity_id ?? '—' }}</dd>
                <dt>IP</dt><dd style="font-family:monospace;">{{ $log->ip_address ?? '—' }}</dd>
                <dt>Method</dt><dd>{{ $log->method ?? $log->request_method ?? '—' }}</dd>
                <dt>URL</dt><dd style="font-family:monospace; font-size:11px; word-break:break-all;">{{ $log->url ?? $log->request_url ?? '—' }}</dd>
            </div>
            <div style="margin-top:14px; padding:10px; background:var(--sand-100); border-radius:8px; font-size:12px; color:var(--ink-soft);">
                This record is immutable and retained for <strong>{{ $retention ?? 365 }} days</strong> per audit policy. Tampering is logged.
            </div>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Details</h3><span class="link">Payload</span></div>
    <div class="panel-body">
        @if(!empty($log->details))
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
                @foreach($log->details as $k=>$v)
                    <span class="tag tag-grey" style="font-size:12px; padding:6px 10px;">{{ $k }}: <strong style="color:var(--coffee-900);">{{ is_array($v) ? implode(', ', $v) : Str::limit((string)$v, 80) }}</strong></span>
                @endforeach
            </div>
            <div class="json-block">{{ json_encode($log->details, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</div>
        @else
            <p class="empty-state" style="padding:20px;">No additional details for this event.</p>
        @endif

        @if(!empty($log->before) || !empty($log->after))
            <h4 style="margin:18px 0 10px; font-size:13px; color:var(--coffee-900);">Before → After Diff</h4>
            <div class="diff-grid">
                <div class="diff-box"><strong style="display:block; color:var(--danger); margin-bottom:8px;">Before</strong><pre style="margin:0; white-space:pre-wrap; word-break:break-word;">{{ json_encode($log->before ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre></div>
                <div class="diff-box"><strong style="display:block; color:var(--acacia-600); margin-bottom:8px;">After</strong><pre style="margin:0; white-space:pre-wrap; word-break:break-word;">{{ json_encode($log->after ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre></div>
            </div>
        @endif

        @if(!empty($log->request_data))
            <h4 style="margin:18px 0 10px; font-size:13px; color:var(--coffee-900);">Request Data</h4>
            <div class="json-block">{{ json_encode($log->request_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</div>
        @endif
    </div>
</div>

<div class="panel-grid">
    <div class="panel">
        <div class="panel-head"><h3>Timeline</h3><span class="link">Single event</span></div>
        <div class="panel-body">
            <div class="timeline">
                <div class="tl-step">
                    <div class="tl-node {{ strtolower($log->severity ?? 'info')==='critical' ? 'crit' : (strtolower($log->severity ?? 'info')==='warn' ? 'info' : 'ok') }}"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:10px;height:10px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                    <div>
                        <b style="font-size:13px; color:var(--coffee-900);">{{ $log->action }}</b>
                        <div style="font-size:12px; color:var(--ink-soft);">{{ $log->created_at->format('d M Y H:i:s') }} · {{ $log->created_at->diffForHumans() }}</div>
                        <div style="font-size:12.5px; color:var(--coffee-700); margin-top:4px;">Recorded from IP <span style="font-family:monospace;">{{ $log->ip_address ?? '—' }}</span> by {{ $log->user?->name ?? 'System' }}</div>
                    </div>
                </div>
                @if(!empty($related) && $related->count())
                    @foreach($related as $rel)
                        <div class="tl-step">
                            <div class="tl-node info"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:10px;height:10px;"><circle cx="12" cy="12" r="10"></circle></svg></div>
                            <div>
                                <b style="font-size:12px; color:var(--coffee-900);">{{ $rel->action }}</b>
                                <div style="font-size:11px; color:var(--ink-soft);">{{ $rel->created_at->format('d M H:i') }} · {{ $rel->user?->name ?? 'System' }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Raw Log</h3><span class="link">JSON</span></div>
        <div class="panel-body">
            <div class="json-block" style="max-height:320px;">{{ json_encode($log, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</div>
            <div style="margin-top:10px; display:flex; gap:8px;">
                <button class="btn btn-ghost btn-sm" onclick="navigator.clipboard.writeText(document.querySelector('.json-block').innerText); toast('Copied','success')">Copy JSON</button>
                <a href="{{ route('audit.index') }}" class="btn btn-ghost btn-sm">Back to logs</a>
            </div>
        </div>
    </div>
</div>

<div class="panel" style="margin-top:18px;">
    <div class="panel-head"><h3>Compliance</h3><span class="link">Retention</span></div>
    <div class="panel-body" style="font-size:13px; color:var(--ink-soft); line-height:1.7;">
        This audit entry is part of the immutable trail. Any modification attempt is itself logged. Export via <a href="{{ route('audit.export') }}" style="color:var(--terracotta-600); font-weight:700;">Export</a> for external archiving. Retention: <strong style="color:var(--coffee-900);">{{ $retention ?? 365 }} days</strong>.
    </div>
</div>
@endsection
