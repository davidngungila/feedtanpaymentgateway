@extends('layouts.app')

@section('title', 'API Logs')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>API Logs</h2><p class="sub">Authenticated API-key usage. Keys are identified by prefix only.</p></div></div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Key</th><th>Method</th><th>Path</th><th>Status</th><th>IP</th><th>At</th></tr></thead>
            <tbody>
                @forelse ($logs as $l)
                    <tr>
                        <td class="mono">{{ $l->key?->prefix ?? '—' }}_••••</td>
                        <td>{{ $l->method }}</td>
                        <td class="mono">{{ $l->path }}</td>
                        <td>{{ $l->status ?? '—' }}</td>
                        <td class="mono">{{ $l->ip_address ?? '—' }}</td>
                        <td>{{ $l->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No API usage logged yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $logs->links() }}</div>
@endsection
