@extends('layouts.app')

@section('title', 'Webhook Deliveries')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Webhooks</h2><p class="sub">Inbound provider callbacks. Endpoints: <code class="mono">POST /webhooks/{mpesa|airtel|mixx|halopesa|tpesa}</code> · HMAC-SHA256 via <code class="mono">X-Provider-Signature</code>.</p></div></div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Provider</th><th>Event</th><th>Signature</th><th>Processing</th><th>Received</th><th>Processed</th></tr></thead>
            <tbody>
                @forelse ($events as $e)
                    <tr>
                        <td>{{ \App\Payments\ProviderRegistry::name($e->provider) }}</td>
                        <td class="mono">{{ $e->event_id ?? substr($e->payload_hash, 0, 12).'…' }}</td>
                        <td><span class="pill pill-{{ $e->signature_status === 'verified' ? 'success' : 'failed' }}">{{ $e->signature_status }}</span></td>
                        <td><span class="pill pill-neutral">{{ $e->processing_status }}</span></td>
                        <td>{{ $e->received_at->format('d M H:i') }}</td>
                        <td>{{ $e->processed_at?->format('d M H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No webhook deliveries yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $events->links() }}</div>
@endsection
