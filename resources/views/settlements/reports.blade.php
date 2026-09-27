@extends('layouts.app')

@section('title', 'Settlement Reports')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Settlement Reports</h2><p class="sub">Latest batches with gross, fees and net.</p></div></div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Reference</th><th>Provider</th><th>Period</th><th class="num">Gross</th><th class="num">Fees</th><th class="num">Net</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($settlements as $s)
                    <tr>
                        <td><a class="mono" href="{{ route('settlements.show', $s) }}">{{ $s->reference }}</a></td>
                        <td>{{ $s->provider ? \App\Payments\ProviderRegistry::name($s->provider) : 'All' }}</td>
                        <td>{{ $s->period_start?->format('d M') ?? '—' }} → {{ $s->period_end?->format('d M Y') ?? '—' }}</td>
                        <td class="num">{{ number_format($s->total_amount, 0) }}</td>
                        <td class="num">{{ number_format($s->total_fee, 0) }}</td>
                        <td class="num">{{ number_format($s->net_amount, 0) }}</td>
                        <td><span class="pill pill-{{ strtolower($s->status) }}">{{ str_replace('_', ' ', $s->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">No settlements yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
