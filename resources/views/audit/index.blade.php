@extends('layouts.app')

@section('title', 'Audit Logs')

@section('head')
<style>
    .audit-timeline{position:relative;padding-left:26px;}
    .audit-timeline::before{content:"";position:absolute;left:10px;top:8px;bottom:8px;width:2px;background:var(--line);border-radius:2px;}
    .audit-step{position:relative;display:flex;gap:12px;padding:12px 0;border-bottom:1px dashed var(--line);}
    .audit-step:last-child{border-bottom:none;}
    .audit-node{position:absolute;left:-26px;top:14px;width:16px;height:16px;border-radius:50%;border:2px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;flex:none;}
    .audit-node.create{background:var(--acacia-600);border-color:var(--acacia-600);color:#fff;}
    .audit-node.update{background:var(--gold-500);border-color:var(--gold-500);color:#fff;}
    .audit-node.delete{background:var(--danger);border-color:var(--danger);color:#fff;}
    .audit-node.login{background:var(--terracotta-600);border-color:var(--terracotta-600);color:#fff;}
    .audit-node svg{width:9px;height:9px;}
    .severity{font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;padding:2px 7px;border-radius:20px;}
    .severity-info{background:var(--acacia-100);color:var(--acacia-600);}
    .severity-warn{background:var(--gold-100);color:#8a6418;}
    .severity-crit{background:var(--danger-100);color:var(--danger);}
    .diff-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .diff-box{background:var(--sand-50);border:1px solid var(--line);border-radius:10px;padding:12px;font-family:ui-monospace,monospace;font-size:12px;line-height:1.6;}
    .diff-box del{background:var(--danger-100);color:var(--danger);text-decoration:none;padding:1px 4px;border-radius:4px;}
    .diff-box ins{background:var(--acacia-100);color:var(--acacia-600);text-decoration:none;padding:1px 4px;border-radius:4px;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Audit Logs</h2>
        <p class="sub">Immutable trail of every significant action · Who did what, when, from where & what changed · Exportable for compliance.</p>
    </div>
    <div class="view-actions">
        <button class="btn btn-ghost" onclick="toggleView()">Toggle timeline</button>
        @include('exports._export-modal', ['route' => $exportRoute, 'columns' => $exportColumns, 'title' => 'Audit Log'])
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card" style="--stat-tint:var(--terracotta-100);--stat-fg:var(--terracotta-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg></div><span class="stat-trend up">{{ $logs->total() ?? 0 }} events</span></div>
        <div class="stat-value">{{ $todayCount ?? 0 }}</div>
        <div class="stat-label">Events today</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H4a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg></div></div>
        <div class="stat-value">{{ $uniqueUsers ?? 0 }}</div>
        <div class="stat-label">Unique actors (30d)</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div><span class="stat-trend up">Monitor</span></div>
        <div class="stat-value">{{ $failedLogins ?? 0 }}</div>
        <div class="stat-label">Failed logins (7d)</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div></div>
        <div class="stat-value">{{ $criticalCount ?? 0 }}</div>
        <div class="stat-label">Critical actions (payouts, refunds)</div>
    </div>
</div>

<form method="GET" action="{{ route('audit.index') }}">
    <div class="table-card">
        <div class="table-toolbar">
            <div class="chip-filters" style="align-items:center;gap:8px;">
                <span class="link" style="font-size:13px;color:var(--ink-soft);font-weight:600;">Action:</span>
                <select name="action" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);color:var(--coffee-700);font-weight:600;">
                    <option value="all" {{ $activeAction === 'all' ? 'selected' : '' }}>All actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action['value'] }}" {{ $activeAction === $action['value'] ? 'selected' : '' }}>{{ $action['label'] }}</option>
                    @endforeach
                </select>
                <select name="severity" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);color:var(--coffee-700);font-weight:600;">
                    <option value="all" {{ ($activeSeverity ?? 'all')==='all' ? 'selected' : '' }}>All severities</option>
                    <option value="info" {{ ($activeSeverity ?? '')==='info'?'selected':'' }}>Info</option>
                    <option value="warn" {{ ($activeSeverity ?? '')==='warn'?'selected':'' }}>Warning</option>
                    <option value="critical" {{ ($activeSeverity ?? '')==='critical'?'selected':'' }}>Critical</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:center;margin-left:auto;flex-wrap:wrap;">
                <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);">
                <input type="date" name="to" value="{{ request('to') }}" onchange="this.form.submit()" style="padding:9px 12px;border:1.5px solid var(--line);border-radius:10px;font-size:13px;background:var(--white);">
                <div class="table-search" style="margin-left:0;min-width:200px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" placeholder="Search user, entity, IP…" oninput="filterLogs(this.value)">
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Table view --}}
<div class="table-card" id="tableView" style="margin-top:-24px;">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP</th>
                    <th>Severity</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="logsBody">
                @forelse ($logs as $log)
                    <tr data-search="{{ strtolower($log->action.' '.($log->user?->name ?? '').' '.($log->entity_type ?? '').' '.($log->ip_address ?? '')) }}"
                        data-when="{{ $log->created_at->format('d M Y H:i:s') }}"
                        data-user="{{ $log->user?->name ?? 'System' }}"
                        data-useremail="{{ $log->user?->email ?? '' }}"
                        data-action="{{ $log->action }}"
                        data-entity="{{ $log->entity_type ?? '' }}"
                        data-entityid="{{ $log->entity_id ?? '' }}"
                        data-ip="{{ $log->ip_address ?? '' }}"
                        data-details="{{ json_encode($log->details ?? []) }}"
                        data-severity="{{ $log->severity ?? 'info' }}"
                        data-href="{{ route('audit.show', $log->getRouteKey()) }}"
                        style="cursor:pointer;">
                        <td>
                            <div class="cell-title" style="font-size:12.5px;">{{ $log->created_at->format('d M Y') }}</div>
                            <div class="cell-sub">{{ $log->created_at->format('H:i:s') }} · {{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="cell-main">
                                <div class="avatar" style="width:32px;height:32px;font-size:11px;">{{ strtoupper(substr($log->user?->name ?? '?', 0, 2)) }}</div>
                                <div>
                                    <div class="cell-title" style="font-size:13px;">{{ $log->user?->name ?? 'System' }}</div>
                                    <div class="cell-sub">{{ $log->user?->email ?? 'system' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="tag tag-terracotta" style="white-space:normal;font-size:11px;">{{ $log->action }}</span></td>
                        <td><span class="cell-sub" style="font-family:monospace;font-size:11.5px;">{{ $log->ip_address ?? '—' }}</span><div class="cell-sub" style="font-size:11px;">{{ $log->user_agent ? Str::limit($log->user_agent, 24) : '' }}</div></td>
                        <td>
                            @php $sev = strtolower($log->severity ?? 'info'); @endphp
                            <span class="severity {{ $sev==='critical'?'severity-crit':($sev==='warn'?'severity-warn':'severity-info') }}">{{ $sev }}</span>
                        </td>
                        <td>
                            <a href="{{ route('audit.show', $log->getRouteKey()) }}" class="btn btn-ghost btn-sm" style="padding:6px 10px; font-size:12px; white-space:nowrap;">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state"><h4>No audit logs</h4><p>Actions will appear here as they happen. Try adjusting filters.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($logs) && $logs->hasPages())
        <div class="table-pager">
            <div class="pager-info">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</div>
            <div class="pager-pages">{{ $logs->links('pagination.pager') }}</div>
        </div>
    @endif
</div>

{{-- Timeline view (hidden by default) --}}
<div class="table-card" id="timelineView" style="margin-top:-24px;display:none;">
    <div class="panel-body">
        <div class="audit-timeline">
            @forelse ($logs as $log)
                @php
                    $act = strtolower($log->action);
                    $nodeClass = str_contains($act,'create') ? 'create' : (str_contains($act,'delete')||str_contains($act,'remove') ? 'delete' : (str_contains($act,'login') ? 'login' : (str_contains($act,'update')||str_contains($act,'edit') ? 'update' : 'create')));
                @endphp
                <div class="audit-step" data-search="{{ strtolower($log->action.' '.($log->user?->name ?? '').' '.($log->entity_type ?? '')) }}" data-href="{{ route('audit.show', $log->getRouteKey()) }}">
                    <div class="audit-node {{ $nodeClass }}">
                        @if($nodeClass==='create')<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        @elseif($nodeClass==='delete')<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        @elseif($nodeClass==='login')<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        @else<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"></path></svg>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <b style="font-size:13.5px;color:var(--coffee-900);">{{ $log->action }}</b>
                            <span class="tag tag-terracotta" style="font-size:11px;">{{ $log->entity_type ?? 'system' }} #{{ $log->entity_id ?? '—' }}</span>
                            <span class="severity {{ strtolower($log->severity ?? 'info')==='critical'?'severity-crit':(strtolower($log->severity ?? 'info')==='warn'?'severity-warn':'severity-info') }}">{{ $log->severity ?? 'info' }}</span>
                            <span style="margin-left:auto;font-size:11.5px;color:var(--ink-soft);">{{ $log->created_at->format('d M Y H:i:s') }} · {{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div style="font-size:13px;color:var(--coffee-700);margin-top:4px;display:flex;align-items:center;gap:8px;">
                            <span class="avatar" style="width:24px;height:24px;font-size:10px;">{{ strtoupper(substr($log->user?->name ?? '?',0,2)) }}</span>
                            {{ $log->user?->name ?? 'System' }} <span style="color:var(--ink-soft);">({{ $log->user?->email ?? 'system' }})</span> · <span style="font-family:monospace;font-size:11.5px;">{{ $log->ip_address ?? '—' }}</span>
                        </div>
                        @if(!empty($log->details))
                            <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($log->details as $k=>$v)
                                    <span class="tag tag-grey" style="font-size:11px;">{{ $k }}: {{ is_array($v)?implode(',',$v):Str::limit($v,40) }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if(!empty($log->before) || !empty($log->after))
                            <div class="diff-grid" style="margin-top:10px;">
                                <div class="diff-box"><strong style="display:block;color:var(--danger);margin-bottom:6px;">Before</strong>{{ json_encode($log->before ?? [], JSON_PRETTY_PRINT) }}</div>
                                <div class="diff-box"><strong style="display:block;color:var(--acacia-600);margin-bottom:6px;">After</strong>{{ json_encode($log->after ?? [], JSON_PRETTY_PRINT) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state"><h4>No audit logs</h4><p>Actions will appear here as they happen.</p></div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function filterLogs(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#logsBody tr[data-search], #timelineView .audit-step[data-search]').forEach(tr => {
            tr.style.display = (!q || tr.dataset.search.includes(q)) ? '' : 'none';
        });
    }
    let viewMode='table';
    function toggleView(){
        viewMode = viewMode==='table'?'timeline':'table';
        document.getElementById('tableView').style.display = viewMode==='table'?'':'none';
        document.getElementById('timelineView').style.display = viewMode==='timeline'?'':'none';
        toast('Switched to '+viewMode+' view','success');
    }
    // Click row -> go to single page (encrypted ID, full details)
    document.querySelectorAll('#logsBody tr[data-href]').forEach(tr => {
        tr.addEventListener('click', (e) => {
            if (e.target.closest('a, button, input, select')) return;
            window.location = tr.dataset.href;
        });
        tr.style.cursor = 'pointer';
    });
    // Also allow timeline steps to be clickable if they have data-href (optional)
    document.querySelectorAll('#timelineView .audit-step[data-href]').forEach(el => {
        el.style.cursor = 'pointer';
        el.addEventListener('click', () => { window.location = el.dataset.href; });
    });
</script>
@endsection
