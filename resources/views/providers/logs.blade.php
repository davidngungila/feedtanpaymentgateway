@extends('layouts.app')

@section('title', $provider->name.' · Logs')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
@include('providers.partials.header', ['active' => 'logs'])

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
    </div>
</div>
@endsection
