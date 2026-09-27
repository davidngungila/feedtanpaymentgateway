@extends('layouts.app')

@section('title', 'Security Events')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Security Events</h2><p class="sub">Append-only trail: credential access, reveals, refunds, approvals, rejections.</p></div></div>

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('security.events') }}" class="filterbar" style="width:100%">
            <div class="field" style="flex:1;"><label style="font-size:11px;">Search action</label><input type="text" name="q" value="{{ $q }}" placeholder="webhook.rejected…"></div>
            <div><button class="btn btn-ghost" type="submit">Search</button></div>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Action</th><th>User</th><th>Entity</th><th>Result</th><th>Severity</th><th>IP</th><th>At</th></tr></thead>
            <tbody>
                @forelse ($events as $e)
                    <tr>
                        <td class="mono">{{ $e->action }}</td>
                        <td>{{ $e->user?->email ?? 'system' }}</td>
                        <td>{{ $e->entity_type ?? '—' }}</td>
                        <td><span class="pill pill-{{ $e->result === 'SUCCESS' ? 'success' : 'failed' }}">{{ $e->result }}</span></td>
                        <td>{{ $e->severity }}</td>
                        <td class="mono">{{ $e->ip_address ?? '—' }}</td>
                        <td>{{ $e->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">No security events yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $events->links() }}</div>
@endsection
