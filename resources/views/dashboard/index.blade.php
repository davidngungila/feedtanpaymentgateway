@extends('layouts.app')

@section('title', 'Dashboard')

@section('head')
@include('collections.partials.head')
<style>
    .chart-bars{display:flex;align-items:flex-end;gap:10px;height:190px;padding-top:10px;}
    .chart-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;min-width:0;}
    .chart-bar{width:100%;max-width:44px;border-radius:6px 6px 3px 3px;background:linear-gradient(180deg,var(--terracotta-500),var(--terracotta-600));min-height:4px;}
    .chart-lbl{font-size:10.5px;color:var(--ink-soft);font-weight:700;white-space:nowrap;}
    .alert-row{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--line);font-size:13.5px;}
    .alert-row:last-child{border-bottom:none;}
    .alert-n{margin-left:auto;font-weight:800;color:var(--coffee-900);}
    .two-col{display:grid;grid-template-columns:1.6fr 1fr;gap:18px;align-items:start;}
    @media (max-width:1100px){.two-col{grid-template-columns:1fr;}}
    .donut-wrap{display:flex;gap:20px;align-items:center;flex-wrap:wrap;}
    .donut{width:150px;height:150px;border-radius:50%;flex:none;position:relative;}
    .donut-hole{position:absolute;inset:26px;background:var(--white);border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .donut-hole b{font-size:22px;color:var(--coffee-900);}
    .donut-hole span{font-size:10px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.06em;}
    .legend{display:flex;flex-direction:column;gap:8px;font-size:13px;flex:1;min-width:180px;}
    .legend-item{display:flex;align-items:center;gap:8px;color:var(--coffee-700);}
    .legend-item b{margin-left:auto;color:var(--coffee-900);}
    .hbar-row{display:grid;grid-template-columns:120px 1fr 90px;gap:10px;align-items:center;padding:7px 0;font-size:13px;}
    .hbar-name{font-weight:700;color:var(--coffee-900);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .hbar-track{background:var(--sand-100);border-radius:6px;height:14px;overflow:hidden;}
    .hbar-fill{height:100%;border-radius:6px;}
    .hbar-val{text-align:right;font-variant-numeric:tabular-nums;font-weight:700;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Dashboard</h2>
        <p class="sub">{{ now()->format('l, j F Y') }} · Live overview of mobile-money collections across all five networks.</p>
    </div>
    <div class="view-actions">
        <a class="btn btn-ghost" href="{{ route('collections.payments.index') }}">All Transactions</a>
        <a href="{{ route('collections.payments.create') }}" class="btn btn-primary">+ Initiate Payment</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1"></rect></svg></div><span class="stat-trend up">{{ $todayCount }} today</span></div>
        <div class="stat-value">{{ number_format($todayVolume, 0) }}</div>
        <div class="stat-label">TZS collected today (SUCCESS)</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div><span class="stat-trend up">Live</span></div>
        <div class="stat-value">{{ $pendingCount }}</div>
        <div class="stat-label">Pending / processing collections</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--terracotta-100);--stat-fg:var(--terracotta-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg></div><span class="stat-trend up">{{ $successCount }} success</span></div>
        <div class="stat-value">{{ $successRate }}%</div>
        <div class="stat-label">Success rate · {{ $failedCount }} failed</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></div><span class="stat-trend up">This month</span></div>
        <div class="stat-value">{{ number_format($monthVolume, 0) }}</div>
        <div class="stat-label">TZS collected · fees {{ number_format($monthFees, 0) }}</div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Networks today</h3><a class="link" href="{{ route('mm.reports.providers') }}">Provider reports →</a></div>
    <div class="panel-body">
        <div class="card-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
            @foreach ($providerStats as $code => $s)
                <a href="{{ route('providers.show', $code) }}" style="text-decoration:none;color:inherit;">
                    <div class="mini-card" style="border-top:4px solid {{ $s['color'] }};">
                        <div class="mc-top"><span class="mc-name">{{ $s['name'] }}</span></div>
                        <div class="mc-big">{{ number_format($s['today'], 0) }}</div>
                        <div class="mc-label">TZS today · {{ $s['success'] }} success · {{ $s['pending'] }} pending · {{ $s['failed'] }} failed</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="two-col" style="margin-bottom:18px;">
    <div class="panel">
        <div class="panel-head"><h3>Status mix · all time</h3><span class="link">{{ $statusTotal }} total</span></div>
        <div class="panel-body">
            @php
            $donutColors = ['SUCCESS'=>'#5E6E3F','PENDING'=>'#D4A24C','PROCESSING'=>'#1D4E89','FAILED'=>'#B33A3A','REVERSED'=>'#5B3E96'];
            $acc = 0; $stops = [];
            foreach($donutColors as $st=>$c){ $v = ($statusCounts[$st] ?? 0)/$statusTotal*100; if($v>0){ $stops[] = $c.' '.round($acc,1).'% '.round($acc+$v,1).'%'; $acc += $v; } }
            $grad = implode(',', $stops) ?: '#E9DCC0 0% 100%';
            @endphp
            <div class="donut-wrap">
                <div class="donut" style="background:conic-gradient({{ $grad }});"><div class="donut-hole"><b>{{ $statusTotal }}</b><span>total</span></div></div>
                <div class="legend">
                    @foreach ($donutColors as $st => $c)
                        <div class="legend-item"><span class="prov-dot" style="background:{{ $c }}"></span>{{ ucfirst(strtolower($st)) }}<b>{{ $statusCounts[$st] ?? 0 }}</b></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Providers · all-time volume</h3><span class="link">TZS SUCCESS</span></div>
        <div class="panel-body">
            @foreach ($providerStats as $code => $s)
                <div class="hbar-row">
                    <span class="hbar-name">{{ $s['name'] }}</span>
                    <div class="hbar-track"><div class="hbar-fill" title="{{ $s['name'] }}: TZS {{ number_format($s['total'], 0) }}" style="width:{{ $providerMax > 0 ? max(2, round($s['total'] / $providerMax * 100)) : 2 }}%;background:{{ $s['color'] }};"></div></div>
                    <span class="hbar-val">{{ number_format($s['total'], 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="two-col" style="margin-bottom:18px;">
    <div class="panel">
        <div class="panel-head"><h3>Last 7 days · successful volume</h3><span class="link">TZS</span></div>
        <div class="panel-body">
            <div class="chart-bars">
                @foreach ($series7 as $d)
                    <div class="chart-col">
                        <div class="chart-bar" title="{{ $d['label'] }}: TZS {{ number_format($d['volume'], 0) }} ({{ $d['count'] }})" style="height:{{ $chartMax > 0 ? max(3, round($d['volume'] / $chartMax * 150)) : 3 }}px;"></div>
                        <div class="chart-lbl">{{ $d['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Needs attention</h3><a class="link" href="{{ route('reconciliation.index') }}">Open →</a></div>
        <div class="panel-body">
            <a class="alert-row" href="{{ route('reconciliation.unmatched') }}" style="color:inherit;text-decoration:none;"><span>Unmatched transactions</span><span class="alert-n">{{ $unmatchedCount }}</span></a>
            <a class="alert-row" href="{{ route('reconciliation.exceptions') }}" style="color:inherit;text-decoration:none;"><span>Webhook exceptions</span><span class="alert-n">{{ $exceptionsCount }}</span></a>
            <a class="alert-row" href="{{ route('settlements.create') }}" style="color:inherit;text-decoration:none;"><span>Eligible for settlement</span><span class="alert-n">{{ $eligibleCount }}</span></a>
            <a class="alert-row" href="{{ route('settlements.index') }}" style="color:inherit;text-decoration:none;"><span>Batches awaiting approval</span><span class="alert-n">{{ $pendingSettlements }}</span></a>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Daily results · success vs failed</h3><span class="link">counts</span></div>
    <div class="panel-body">
        <div class="chart-bars">
            @foreach ($series7 as $d)
                <div class="chart-col">
                    <div style="display:flex;gap:5px;align-items:flex-end;height:150px;">
                        <div class="chart-bar" title="{{ $d['label'] }} success: {{ $d['success'] }}" style="height:{{ $dayCountMax > 0 ? max(3, round($d['success'] / $dayCountMax * 140)) : 3 }}px;background:linear-gradient(180deg,#7A8B4F,#5E6E3F);"></div>
                        <div class="chart-bar" title="{{ $d['label'] }} failed: {{ $d['failed'] }}" style="height:{{ $dayCountMax > 0 ? max(3, round($d['failed'] / $dayCountMax * 140)) : 3 }}px;background:linear-gradient(180deg,#C65B5B,#B33A3A);"></div>
                    </div>
                    <div class="chart-lbl">{{ $d['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div style="display:flex;gap:16px;margin-top:10px;font-size:12px;color:var(--ink-soft);">
            <span><span class="prov-dot" style="background:#5E6E3F"></span>Success</span>
            <span><span class="prov-dot" style="background:#B33A3A"></span>Failed</span>
        </div>
    </div>
</div>

<div class="two-col" style="margin-bottom:18px;">
    <div class="panel">
        <div class="panel-head"><h3>Monthly trend · 6 months</h3><span class="link">TZS SUCCESS</span></div>
        <div class="panel-body">
            <div class="chart-bars">
                @foreach ($seriesMonth as $m)
                    <div class="chart-col">
                        <div class="chart-bar" title="{{ $m['label'] }}: TZS {{ number_format($m['volume'], 0) }}" style="height:{{ $monthMax > 0 ? max(3, round($m['volume'] / $monthMax * 150)) : 3 }}px;background:linear-gradient(180deg,var(--gold-500),var(--terracotta-600));"></div>
                        <div class="chart-lbl">{{ $m['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Today · hourly activity</h3><span class="link">counts / 2h</span></div>
        <div class="panel-body">
            <div class="chart-bars">
                @foreach ($hourly as $h)
                    <div class="chart-col">
                        <div class="chart-bar" title="{{ $h['label'] }}: {{ $h['count'] }} collections" style="height:{{ $hourMax > 0 ? max(3, round($h['count'] / $hourMax * 150)) : 3 }}px;background:linear-gradient(180deg,#8AA0BE,#1D4E89);"></div>
                        <div class="chart-lbl" style="font-size:9px;">{{ $h['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="two-col">
    <div class="panel">
        <div class="panel-head"><h3>Recent transactions</h3><a class="link" href="{{ route('collections.payments.index') }}">View all →</a></div>
        <div class="panel-body" style="padding:0;">
            <div class="table-card" style="box-shadow:none;border:none;margin:0;">
                <div class="table-scroll">
                    <table>
                        <thead><tr><th>TXN ID</th><th>Provider</th><th>Customer</th><th class="num">Amount</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($recentTransactions as $t)
                                <tr>
                                    <td><span class="mono">{{ $t->txn_id }}</span><div class="cell-sub">{{ $t->created_at->diffForHumans() }}</div></td>
                                    <td><span class="prov-dot" style="background:{{ $providers[$t->provider]['color'] ?? '#999' }}"></span>{{ $providers[$t->provider]['name'] ?? $t->provider }}</td>
                                    <td>{{ $t->customer?->name ?? '—' }}</td>
                                    <td class="num">{{ number_format($t->amount, 0) }}</td>
                                    <td><span class="pill pill-{{ strtolower($t->status) }}">{{ $t->status }}</span></td>
                                    <td><a class="btn btn-sm btn-ghost" href="{{ $t->detailsUrl() }}">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state">No transactions yet. <a href="{{ route('collections.payments.create') }}">Initiate the first collection →</a></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Recent activity</h3><a class="link" href="{{ route('audit.index') }}">Audit →</a></div>
        <div class="panel-body">
            <div class="activity-list">
                @forelse ($recentActivity as $item)
                    <div class="activity-row">
                        <div class="activity-text"><b>{{ $item->action }}</b><div class="activity-time">{{ ($item->user?->name ?? 'System') }} · {{ $item->created_at->diffForHumans() }}</div></div>
                    </div>
                @empty
                    <div class="empty-state">No activity yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
