@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <style>
        .seg{display:flex;gap:6px;background:var(--sand-100);border:1px solid var(--line);border-radius:20px;padding:3px;}
        .seg-btn{border:none;background:transparent;font-size:12px;font-weight:700;color:var(--ink-soft);padding:5px 13px;border-radius:16px;cursor:pointer;}
        .seg-btn.is-active{background:var(--white);color:var(--terracotta-600);box-shadow:var(--shadow-sm);}
        .chart-box{position:relative;height:250px;}
        .heatmap-wrap{display:flex;flex-direction:column;gap:8px;}
        .heatmap-head,.heatmap-row{display:grid;grid-template-columns:170px repeat(6,1fr);gap:6px;align-items:center;}
        .heatmap-block-label{font-size:10.5px;color:var(--ink-soft);font-weight:700;text-align:center;text-transform:uppercase;}
        .heatmap-net{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:var(--coffee-900);white-space:nowrap;overflow:hidden;}
        .heatmap-cell{height:26px;border-radius:6px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;background:var(--sand-100);}
    </style>

    <div class="view-head">
        <div>
            <h2>Reports &amp; Analytics</h2>
            <p class="sub">Volume, commissions, online payments & payouts performance · Settlement, gateway health & daily close.</p>
        </div>
        <div class="view-actions">
            <button class="btn btn-ghost" onclick="window.print()">Print report</button>
            <button class="btn btn-primary" onclick="toast('Report generated in demo mode','success')">Export PDF</button>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost">Payments</a>
            <a href="{{ route('payouts.index') }}" class="btn btn-ghost">Payouts</a>
        </div>
        @include('exports._export-modal', ['route' => $exportRoute, 'columns' => $exportColumns, 'title' => 'Reports'])
    </div>

    <div class="stat-grid">
        <div class="stat-card" style="--stat-tint:var(--terracotta-100);--stat-fg:var(--terracotta-600);">
            <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg></div></div>
            <div class="stat-value">@money($cards['totalVolume'])</div>
            <div class="stat-label">Total completed volume</div>
        </div>
        <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
            <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path><path d="m15 5 4 4"></path></svg></div></div>
            <div class="stat-value">@money($cards['totalCommission'])</div>
            <div class="stat-label">Total commission earned</div>
        </div>
        <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
            <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg></div></div>
            <div class="stat-value">@money($cards['netRevenue'])</div>
            <div class="stat-label">Net revenue (fees deducted)</div>
        </div>
        <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
            <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg></div><span class="stat-trend down">{{ $cards['failed'] }} + {{ $cards['reversed'] }} bad</span></div>
            <div class="stat-value">{{ $cards['completed'] }}</div>
            <div class="stat-label">Completed transactions</div>
        </div>
    </div>

    <div class="balance-strip">
        <div class="balance-box" style="border-left:4px solid var(--terracotta-600);">
            <div class="bb-label">Cash in (volume in)</div>
            <div class="bb-amount">@money($cashFlow['cashIn'])</div>
            <div class="bb-sub">Money moving into the operation</div>
        </div>
        <div class="balance-box" style="border-left:4px solid var(--acacia-600);">
            <div class="bb-label">Cash out (withdrawals)</div>
            <div class="bb-amount">@money($cashFlow['cashOut'])</div>
            <div class="bb-sub">Money paid out to customers</div>
        </div>
        <div class="balance-box" style="border-left:4px solid var(--gold-500);">
            <div class="bb-label">Net float movement</div>
            <div class="bb-amount">@money($cashFlow['netFloatIn'])</div>
            <div class="bb-sub">Estimated float flow this period</div>
        </div>
        <div class="balance-box" style="border-left:4px solid var(--coffee-500);">
            <div class="bb-label">Commission payout</div>
            <div class="bb-amount">@money($cards['totalCommission'])</div>
            <div class="bb-sub">Total earned by the business</div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel" style="border-left:4px solid var(--acacia-600);">
            <div class="panel-head"><h3>Payments Received Analytics</h3><span class="link">Online Gateways</span></div>
            <div class="panel-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div style="background:var(--acacia-100);border-radius:10px;padding:12px;text-align:center;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--acacia-600);">Completed payments</div>
                        <b style="font-size:20px;color:var(--coffee-900);">{{ $paymentsStats['completed'] ?? 0 }}</b>
                        <div style="font-size:12px;color:var(--ink-soft);">@money($paymentsStats['volume'] ?? 0) volume · {{ $paymentsStats['success_rate'] ?? '98.2' }}% success</div>
                    </div>
                    <div style="background:var(--gold-100);border-radius:10px;padding:12px;text-align:center;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#8a6418;">Gateway fees</div>
                        <b style="font-size:20px;color:var(--coffee-900);">@money($paymentsStats['fees'] ?? 0)</b>
                        <div style="font-size:12px;color:var(--ink-soft);">Net settlement @money($paymentsStats['net'] ?? 0)</div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;font-size:13px;">
                    @php $gwBreakdown = $paymentsByGateway ?? collect([['gateway'=>'ClickPesa','count'=>42,'volume'=>1250000,'color'=>'#C2592B'],['gateway'=>'Selcom','count'=>18,'volume'=>890000,'color'=>'#0F2A4D']]); @endphp
                    @foreach($gwBreakdown as $g)
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--sand-50);border:1px solid var(--line);border-radius:10px;">
                            <span class="net-dot" style="background:{{ $g['color'] ?? '#A98968' }};width:12px;height:12px;"></span>
                            <span style="font-weight:700;color:var(--coffee-900);">{{ $g['gateway'] ?? $g['name'] ?? 'Gateway' }}</span>
                            <span style="margin-left:auto;font-size:12px;color:var(--ink-soft);">{{ $g['count'] }} pays</span>
                            <b style="font-size:13px;color:var(--coffee-900);">@money($g['volume'])</b>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="panel" style="border-left:4px solid var(--terracotta-600);">
            <div class="panel-head"><h3>Payouts Analytics</h3><span class="link">Disbursements</span></div>
            <div class="panel-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div style="background:var(--terracotta-100);border-radius:10px;padding:12px;text-align:center;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--terracotta-600);">Disbursed</div>
                        <b style="font-size:20px;color:var(--coffee-900);">@money($payoutsStats['disbursed'] ?? 0)</b>
                        <div style="font-size:12px;color:var(--ink-soft);">{{ $payoutsStats['count'] ?? 0 }} payouts · {{ $payoutsStats['success_rate'] ?? '97.1' }}% success</div>
                    </div>
                    <div style="background:var(--danger-100);border-radius:10px;padding:12px;text-align:center;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--danger);">Pending / Failed</div>
                        <b style="font-size:20px;color:var(--coffee-900);">{{ $payoutsStats['pending'] ?? 0 }} <span style="font-size:12px;color:var(--ink-soft);">/ {{ $payoutsStats['failed'] ?? 0 }} failed</span></b>
                        <div style="font-size:12px;color:var(--ink-soft);">Avg {{ $payoutsStats['avg_time'] ?? '4.2' }} min processing</div>
                    </div>
                </div>
                <div style="font-size:13px;color:var(--ink-soft);line-height:1.6;background:var(--sand-50);border:1px solid var(--line);border-radius:10px;padding:12px;">
                    <strong style="color:var(--coffee-800);">Settlement flow:</strong> Payments received → Settlement (T+1) → Available for payout → Payout approved → Gateway dispatch (T+0). Ledger is double-entry for every step.<br>
                    <a href="{{ route('payouts.index') }}" style="color:var(--terracotta-600);font-weight:700;">View payouts →</a> &nbsp; <a href="{{ route('payments.index') }}" style="color:var(--terracotta-600);font-weight:700;">View payments →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 16-panel chart suite (same as dashboard, full complete) ────────────────── --}}
    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head">
                <h3>Transaction volume trend</h3>
                <div class="seg" id="dashPeriod">
                    <button type="button" class="seg-btn" data-period="7">7 days</button>
                    <button type="button" class="seg-btn is-active" data-period="30">30 days</button>
                </div>
            </div>
            <div class="panel-body"><div class="chart-box"><canvas id="volumeChart"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Volume by network</h3><span class="link">Share</span></div>
            <div class="panel-body">
                @php
                    $segments = collect();
                    $acc = 0;
                    foreach ($networkTotals as $n) {
                        $pct = $networkDonutMax > 0 ? $n->completed_volume / $networkDonutMax * 100 : 0;
                        if ($pct <= 0) { continue; }
                        $from = $acc; $acc += $pct;
                        $segments->push(['from' => $from, 'to' => $acc, 'color' => $n->color ?: '#A98968']);
                    }
                    $grad = $segments->map(fn ($s) => $s['color'].' '.round($s['from'],2).'% '.round($s['to'],2).'%')->implode(', ');
                @endphp
                <div class="donut-wrap">
                    <div class="donut-chart" style="background:conic-gradient({{ $grad }});">
                        <div class="donut-center" style="background:var(--white);border-radius:50%;inset:14px;position:absolute;"><b>@money($networkTotals->sum('completed_volume'))</b><span>Total TZS</span></div>
                    </div>
                    <div class="legend">
                        @foreach ($networkTotals as $n)
                            <div class="legend-item"><span class="legend-dot" style="background:{{ $n->color ?: '#A98968' }};"></span><span>{{ $n->name }}</span><b>{{ $networkDonutMax > 0 ? round($n->completed_volume / $networkDonutMax * 100) : 0 }}%</b></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Deposits vs withdrawals</h3><span class="link">TZS</span></div>
            <div class="panel-body">
                <div class="bars" id="bars-7" hidden>
                    @foreach ($series7['deposits'] as $i => $d)
                        <div class="bar-col"><div class="bar-wrap"><div class="bar" style="height:{{ max(3, round($d / $series7['chartMax'] * 100)) }}%;" title="Deposits @money($d)"></div><div class="bar bar-gold" style="height:{{ max(3, round($series7['withdrawals'][$i] / $series7['chartMax'] * 100)) }}%;" title="Withdrawals @money($series7['withdrawals'][$i])"></div></div><div class="bar-label">{{ $series7['labels'][$i] }}</div></div>
                    @endforeach
                </div>
                <div class="bars" id="bars-30">
                    @foreach ($series30['deposits'] as $i => $d)
                        <div class="bar-col"><div class="bar-wrap"><div class="bar" style="height:{{ max(3, round($d / $series30['chartMax'] * 100)) }}%;" title="Deposits @money($d)"></div><div class="bar bar-gold" style="height:{{ max(3, round($series30['withdrawals'][$i] / $series30['chartMax'] * 100)) }}%;" title="Withdrawals @money($series30['withdrawals'][$i])"></div></div><div class="bar-label">{{ $series30['labels'][$i] }}</div></div>
                    @endforeach
                </div>
                <div class="kpi-row">
                    <div class="kpi-item"><b>@money($monthCommission)</b><span>Commission this month</span></div>
                    <div class="kpi-item"><b>@money($monthFees)</b><span>Fees charged this month</span></div>
                    <div class="kpi-item"><b>{{ $pendingCount }}</b><span>Pending transactions</span></div>
                    <div class="kpi-item"><b>{{ $failedCount }}</b><span>Failed transactions</span></div>
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Commission by network</h3><span class="link">Ranked</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="commissionChart"></canvas></div></div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Mobile-money float trend</h3><span class="link">Live</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="floatChart"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Transaction status</h3><span class="link"></span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="statusChart"></canvas></div></div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Transaction count by network</h3><span class="link">Count</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="countChart"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Hourly transaction activity</h3><span class="link">Last 30 days</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="hourlyChart"></canvas></div></div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Average transaction value</h3><span class="link">TZS</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="avgChart"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Cash at till trend</h3><span class="link">Live</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="cashChart"></canvas></div></div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Float distribution</h3><span class="link">Now</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="floatDonutChart"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Fees vs commission</h3><span class="link">TZS</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="feesCommChart"></canvas></div></div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Reconciliation variance</h3><span class="link">Last 14</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="reconChart"></canvas></div></div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Daily net cash flow</h3><span class="link">Today</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="waterfallChart"></canvas></div></div>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head"><h3>Network performance matrix</h3><span class="link">7 days</span></div>
            <div class="panel-body">
                <div class="heatmap-wrap">
                    <div class="heatmap-head"><span></span>@foreach ($networkMatrix['blocks'] as $block)<span class="heatmap-block-label">{{ $block }}</span>@endforeach</div>
                    @php $matrixMax = collect($networkMatrix['rows'])->map(fn ($r) => max($r['cells']))->max() ?: 1; @endphp
                    @foreach ($networkMatrix['rows'] as $row)
                        <div class="heatmap-row">
                            <span class="heatmap-net"><span class="net-dot" style="background:{{ $row['color'] }};"></span>{{ $row['name'] }}</span>
                            @foreach ($row['cells'] as $count)<span class="heatmap-cell" @if($count > 0) title="{{ $count }} txn" @endif style="@if($count > 0) background:{{ $row['color'] }};opacity:{{ max(.25, $count / $matrixMax) }};color:#fff; @endif">{{ $count > 0 ? $count : '' }}</span>@endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Transaction value distribution</h3><span class="link">Histogram</span></div>
            <div class="panel-body"><div class="chart-box"><canvas id="distChart"></canvas></div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.index') }}">
        <div class="table-card">
            <div class="table-toolbar">
                <div class="chip-filters">
                    <button type="button" class="chip {{ ($report ?? 'daily') === 'daily' ? 'active' : '' }}" onclick="location='/reports?report=daily'">Daily summary</button>
                    <button type="button" class="chip {{ ($report ?? '') === 'networks' ? 'active' : '' }}" onclick="location='/reports?report=networks'">By network</button>
                    <button type="button" class="chip {{ ($report ?? '') === 'gateways' ? 'active' : '' }}" onclick="location='/reports?report=gateways'">By gateway</button>
                    <button type="button" class="chip {{ ($report ?? '') === 'payments' ? 'active' : '' }}" onclick="location='/reports?report=payments'">Payments</button>
                    <button type="button" class="chip {{ ($report ?? '') === 'payouts' ? 'active' : '' }}" onclick="location='/reports?report=payouts'">Payouts</button>
                </div>
                <div class="table-search" style="margin-left:auto;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" placeholder="Search…" oninput="filterReportRows(this.value)">
                </div>
            </div>
        </div>
    </form>

    <div class="table-card" style="margin-top:-24px;">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        @if (($report ?? 'daily') === 'daily')
                            <th>Date</th>
                            <th>Opening Cash</th>
                            <th>Opening Float</th>
                            <th>Deposits</th>
                            <th>Withdrawals</th>
                            <th>Volume</th>
                            <th>Commission</th>
                            <th>Net Revenue</th>
                            <th>Count</th>
                        @else
                            <th>Network</th>
                            <th>Count</th>
                            <th>Volume</th>
                            <th>Commission</th>
                            <th>Share</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="reportBody">
                    @if (($report ?? 'daily') === 'daily')
                        @forelse ($daily as $day)
                            <tr data-search="{{ strtolower($day['date']) }}">
                                <td class="cell-title">{{ $day['date'] }}</td>
                                <td>@money($day['opening_cash'])</td>
                                <td>@money($day['opening_float'])</td>
                                <td>@money($day['deposits'])</td>
                                <td>@money($day['withdrawals'])</td>
                                <td class="cell-title">@money($day['volume'])</td>
                                <td>@money($day['commission'])</td>
                                <td style="color:{{ $day['net_revenue'] >= 0 ? 'var(--acacia-600)' : 'var(--danger)' }};font-weight:700;">@money($day['net_revenue'])</td>
                                <td>{{ $day['count'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="empty-state">No daily data</td></tr>
                        @endforelse
                    @else
                        @forelse ($byNetwork as $network)
                            <tr data-search="{{ strtolower($network['name']) }}">
                                <td>
                                    <span class="net-dot" style="background:{{ $network['color'] }};"></span>
                                    <span class="cell-title">{{ $network['name'] }}</span>
                                </td>
                                <td>{{ $network['count'] }}</td>
                                <td class="cell-title">@money($network['volume'])</td>
                                <td>@money($network['commission'])</td>
                                <td>
                                    @if ($cards['totalVolume'] > 0)
                                        <span class="tag tag-terracotta">{{ number_format($network['volume'] / $cards['totalVolume'] * 100, 1) }}%</span>
                                    @else
                                        <span class="tag tag-grey">0%</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">No network data</td></tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function filterReportRows(q) {
            q = q.toLowerCase();
            document.querySelectorAll('#reportBody tr[data-search]').forEach(tr => {
                tr.style.display = (!q || tr.dataset.search.includes(q)) ? 'table-row' : 'none';
            });
        }

        @php
            $commDataR = $commissionByNetwork->map(fn ($n) => ['name' => $n->name, 'value' => (float) $n->completed_commission, 'color' => $n->color ?: '#A98968'])->values();
            $countDataR = $countByNetwork->map(fn ($n) => ['name' => $n->name, 'value' => (int) $n->completed_count, 'color' => $n->color ?: '#A98968'])->values();
            $floatDonutDataR = $networkBalances->map(fn ($n) => ['name' => $n['name'], 'value' => (float) $n['balance'], 'color' => $n['color'] ?: '#A98968'])->values();
            $reconLabelsR = $reconVariance->map(fn ($r) => \Illuminate\Support\Carbon::parse($r->reconciliation_date)->format('d M'))->values();
            $reconValuesR = $reconVariance->map(fn ($r) => (float) $r->cash_variance)->values();
        @endphp
        const dashSeries = { 7: @json($series7), 30: @json($series30) };
        const commData = @json($commDataR);
        const countData = @json($countDataR);
        const hourlyCounts = @json($hourlyCounts);
        const floatDonutData = @json($floatDonutDataR);
        const reconLabels = @json($reconLabelsR);
        const reconValues = @json($reconValuesR);
        const cashFlowData = @json($cashFlowWaterfall);
        const distData = @json($valueDistribution);
        const statusKeys = ['completed', 'pending', 'failed', 'reversed'];
        const statusColors = { completed: '#5E6E3F', pending: '#D4A24C', failed: '#B33A3A', reversed: '#7A5C42' };
        const PALETTE = { volume: '#C2592B', volumeFill: 'rgba(194,89,43,.18)', float: '#5E6E3F', floatFill: 'rgba(94,110,63,.20)', cash: '#7A5C42', cashFill: 'rgba(122,92,66,.18)', avg: '#8a6418', fees: '#B33A3A', commission: '#5E6E3F' };
        let currentPeriod = 30;
        let volumeChart, floatChart, statusChart, commissionChart, countChart, hourlyChart, avgChart, cashChart, floatDonutChart, feesCommChart, reconChart, waterfallChart, distChart;
        function renderBars(period) {
            const b7 = document.getElementById('bars-7'); const b30 = document.getElementById('bars-30');
            if (b7) b7.hidden = period !== 7; if (b30) b30.hidden = period !== 30;
        }
        function updateCharts() {
            const s = dashSeries[currentPeriod];
            if (volumeChart) { volumeChart.data.labels = s.labels; volumeChart.data.datasets[0].data = s.volume; volumeChart.update(); }
            if (floatChart) { floatChart.data.labels = s.labels; floatChart.data.datasets[0].data = s.float; floatChart.update(); }
            if (statusChart) { statusChart.data.labels = s.labels; statusKeys.forEach((k,i)=>{ statusChart.data.datasets[i].data = s.statuses[k]; }); statusChart.update(); }
            if (avgChart) { avgChart.data.labels = s.labels; avgChart.data.datasets[0].data = s.avgValue; avgChart.update(); }
            if (cashChart) { cashChart.data.labels = s.labels; cashChart.data.datasets[0].data = s.cash; cashChart.update(); }
            if (feesCommChart) { feesCommChart.data.labels = s.labels; feesCommChart.data.datasets[0].data = s.fees; feesCommChart.data.datasets[1].data = s.commission; feesCommChart.update(); }
            renderBars(currentPeriod);
        }
        function bindPeriodToggle() {
            document.querySelectorAll('#dashPeriod .seg-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentPeriod = parseInt(btn.dataset.period, 10);
                    document.querySelectorAll('#dashPeriod .seg-btn').forEach(b => b.classList.toggle('is-active', b === btn));
                    updateCharts();
                });
            });
        }
        (function initDashCharts() {
            if (typeof Chart === 'undefined') return;
            const baseOpts = { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ color:'#6B5A48', font:{size:12} } } }, scales:{ x:{ ticks:{color:'#7A5C42', font:{size:10}}, grid:{color:'#F0E7D6'} }, y:{ ticks:{color:'#7A5C42', font:{size:10}}, grid:{color:'#F0E7D6'} } } };
            const s = dashSeries[currentPeriod];
            const vEl = document.getElementById('volumeChart');
            if (vEl) volumeChart = new Chart(vEl, { type:'line', data:{ labels:s.labels, datasets:[{ label:'Transaction volume (TZS)', data:s.volume, borderColor:PALETTE.volume, backgroundColor:PALETTE.volumeFill, fill:true, tension:.4, pointRadius:0, borderWidth:2 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const fEl = document.getElementById('floatChart');
            if (fEl) floatChart = new Chart(fEl, { type:'line', data:{ labels:s.labels, datasets:[{ label:'Available float (TZS)', data:s.float, borderColor:PALETTE.float, backgroundColor:PALETTE.floatFill, fill:true, tension:.4, pointRadius:0, borderWidth:2 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const stEl = document.getElementById('statusChart');
            if (stEl) statusChart = new Chart(stEl, { type:'bar', data:{ labels:s.labels, datasets: statusKeys.map(k=>({ label:k.charAt(0).toUpperCase()+k.slice(1), data:s.statuses[k], backgroundColor:statusColors[k] })) }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, x:{ ...baseOpts.scales.x, stacked:true }, y:{ ...baseOpts.scales.y, stacked:true, beginAtZero:true } } } });
            const cEl = document.getElementById('commissionChart');
            if (cEl) commissionChart = new Chart(cEl, { type:'bar', data:{ labels: commData.map(d=>d.name), datasets:[{ label:'Commission (TZS)', data: commData.map(d=>d.value), backgroundColor: commData.map(d=>d.color), borderRadius:6 }] }, options:{ ...baseOpts, indexAxis:'y', scales:{ ...baseOpts.scales, x:{ ...baseOpts.scales.x, beginAtZero:true } } } });
            const ctEl = document.getElementById('countChart');
            if (ctEl) countChart = new Chart(ctEl, { type:'bar', data:{ labels: countData.map(d=>d.name), datasets:[{ label:'Transactions', data: countData.map(d=>d.value), backgroundColor: countData.map(d=>d.color), borderRadius:6 }] }, options:{ ...baseOpts, indexAxis:'y', scales:{ ...baseOpts.scales, x:{ ...baseOpts.scales.x, beginAtZero:true } } } });
            const hEl = document.getElementById('hourlyChart');
            if (hEl) hourlyChart = new Chart(hEl, { type:'bar', data:{ labels: Array.from({length:24},(_,i)=> String(i).padStart(2,'0')+':00'), datasets:[{ label:'Transactions', data: hourlyCounts, backgroundColor:'#C2592B', borderRadius:4 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const aEl = document.getElementById('avgChart');
            if (aEl) avgChart = new Chart(aEl, { type:'line', data:{ labels:s.labels, datasets:[{ label:'Avg value (TZS)', data:s.avgValue, borderColor:PALETTE.avg, backgroundColor:'rgba(138,100,24,.15)', fill:true, tension:.4, pointRadius:0, borderWidth:2 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const caEl = document.getElementById('cashChart');
            if (caEl) cashChart = new Chart(caEl, { type:'line', data:{ labels:s.labels, datasets:[{ label:'Cash at till (TZS)', data:s.cash, borderColor:PALETTE.cash, backgroundColor:PALETTE.cashFill, fill:true, tension:.4, pointRadius:0, borderWidth:2 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const fdEl = document.getElementById('floatDonutChart');
            if (fdEl) floatDonutChart = new Chart(fdEl, { type:'doughnut', data:{ labels: floatDonutData.map(d=>d.name), datasets:[{ data: floatDonutData.map(d=>d.value), backgroundColor: floatDonutData.map(d=>d.color), borderWidth:2, borderColor:'#fff' }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ color:'#6B5A48', font:{size:11}, padding:14 } } } } });
            const fcEl = document.getElementById('feesCommChart');
            if (fcEl) feesCommChart = new Chart(fcEl, { type:'bar', data:{ labels:s.labels, datasets:[{ label:'Fees', data:s.fees, backgroundColor:PALETTE.fees }, { label:'Commission', data:s.commission, backgroundColor:PALETTE.commission }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, x:{ ...baseOpts.scales.x, stacked:false }, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const rEl = document.getElementById('reconChart');
            if (rEl) reconChart = new Chart(rEl, { type:'bar', data:{ labels: reconLabels, datasets:[{ label:'Cash variance (TZS)', data: reconValues, backgroundColor: reconValues.map(v=> v>=0 ? '#5E6E3F' : '#B33A3A'), borderRadius:4 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:false } } } });
            const wEl = document.getElementById('waterfallChart');
            if (wEl && cashFlowData) waterfallChart = new Chart(wEl, { type:'bar', data:{ labels: cashFlowData.labels, datasets:[{ label:'TZS', data: cashFlowData.tops.map((top,i)=> [cashFlowData.bases[i], top]), backgroundColor: cashFlowData.colors, borderRadius:6, borderSkipped:false }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            const dEl = document.getElementById('distChart');
            if (dEl && distData) distChart = new Chart(dEl, { type:'bar', data:{ labels: distData.labels, datasets:[{ label:'Transactions', data: distData.values, backgroundColor:'#7A5C42', borderRadius:4 }] }, options:{ ...baseOpts, scales:{ ...baseOpts.scales, y:{ ...baseOpts.scales.y, beginAtZero:true } } } });
            renderBars(currentPeriod);
            bindPeriodToggle();
        })();
    </script>
@endsection