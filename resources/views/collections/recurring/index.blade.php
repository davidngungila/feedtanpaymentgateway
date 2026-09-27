@extends('layouts.app')

@section('title', 'Recurring Collections')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Recurring Collections</h2><p class="sub">Daily, weekly or monthly schedules. Each run creates a normal engine collection.</p></div>
    <div class="view-actions"><a class="btn btn-primary" href="{{ route('collections.recurring.create') }}">+ New schedule</a></div>
</div>

@include('collections.partials.flash')

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Reference</th><th>Customer</th><th>Provider</th><th class="num">Amount</th><th>Frequency</th><th>Next run</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($items as $r)
                    <tr>
                        <td class="mono">{{ $r->reference }}</td>
                        <td>{{ $r->customer?->name ?? '—' }}<div class="cell-sub mono">{{ $r->customer?->maskedPhone() ?? '' }}</div></td>
                        <td>{{ \App\Payments\ProviderRegistry::name($r->provider) }}</td>
                        <td class="num">{{ number_format($r->amount, 0) }}</td>
                        <td style="text-transform:capitalize;">{{ $r->frequency }}</td>
                        <td>{{ $r->next_run_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td><span class="pill pill-{{ strtolower($r->status) }}">{{ $r->status }}</span></td>
                        <td>
                            @if ($r->status === 'active')
                                <form method="POST" action="{{ route('collections.recurring.pause', $r) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-ghost" type="submit">Pause</button></form>
                            @else
                                <form method="POST" action="{{ route('collections.recurring.resume', $r) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-ghost" type="submit">Resume</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state">No recurring schedules yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $items->links() }}</div>
@endsection
