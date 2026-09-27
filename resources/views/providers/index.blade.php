@extends('layouts.app')

@section('title', 'Mobile Money Providers')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>Mobile Money</h2><p class="sub">Five adapters, one engine. Open a provider for its collections, status queries, webhooks, logs, configuration and credentials.</p></div></div>

@include('collections.partials.flash')

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Provider</th><th class="num">Today</th><th class="num">Total</th><th class="num">Pending</th><th class="num">Success</th><th class="num">Failed</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($providers as $p)
                    <tr>
                        <td><span class="prov-dot" style="background:{{ $p->color }}"></span><strong>{{ $p->name }}</strong><div class="cell-sub mono">{{ $p->code }} · {{ $p->driver }}</div></td>
                        <td class="num">{{ number_format($stats[$p->code]['today'] ?? 0, 0) }}</td>
                        <td class="num">{{ number_format($stats[$p->code]['total'] ?? 0, 0) }}</td>
                        <td class="num">{{ $stats[$p->code]['pending'] ?? 0 }}</td>
                        <td class="num">{{ $stats[$p->code]['success'] ?? 0 }}</td>
                        <td class="num">{{ $stats[$p->code]['failed'] ?? 0 }}</td>
                        <td><span class="pill pill-{{ $p->is_active ? 'active' : 'failed' }}">{{ $p->is_active ? 'active' : 'off' }}</span></td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('providers.collections', $p->code) }}" aria-label="View {{ $p->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state">No providers configured.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
