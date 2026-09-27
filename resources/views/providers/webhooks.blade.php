@extends('layouts.app')

@section('title', $provider->name.' · Webhooks')

@section('head')
@include('collections.partials.head')
<style>
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0;}
    .tab{padding:8px 14px;border-radius:20px;font-size:13px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);text-decoration:none;}
    .tab.active{background:var(--coffee-900);color:#fff;}
</style>
@endsection

@section('content')
@include('providers.partials.header', ['active' => 'webhooks'])

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
    </div>
</div>
@endsection
