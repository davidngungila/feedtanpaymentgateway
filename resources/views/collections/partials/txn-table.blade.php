{{-- Expects: $txns (paginator), $showSettle (bool, default true) --}}
<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>TXN ID</th>
                    <th>Provider</th>
                    <th>Customer</th>
                    <th class="num">Amount</th>
                    <th class="num">Fee</th>
                    <th class="num">Net</th>
                    <th>Status</th>
                    @if ($showSettle ?? true)<th>Settlement</th>@endif
                    <th>Initiated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($txns as $t)
                    <tr>
                        <td><span class="mono">{{ $t->txn_id }}</span><div class="cell-sub mono">{{ $t->reference }}</div></td>
                        <td><span class="prov-dot" style="background:{{ $providers[$t->provider]['color'] ?? '#999' }}"></span>{{ $providers[$t->provider]['name'] ?? $t->provider }}</td>
                        <td>{{ $t->customer?->name ?? '—' }}<div class="cell-sub mono">{{ $t->customer?->maskedPhone() ?? '—' }}</div></td>
                        <td class="num">{{ number_format($t->amount, 0) }}</td>
                        <td class="num">{{ number_format($t->provider_fee, 0) }}</td>
                        <td class="num">{{ number_format($t->net_amount, 0) }}</td>
                        <td><span class="pill pill-{{ strtolower($t->status) }}">{{ $t->status }}</span></td>
                        @if ($showSettle ?? true)<td><span class="pill pill-{{ strtolower($t->settlement_status) }}">{{ str_replace('_', ' ', $t->settlement_status) }}</span></td>@endif
                        <td>{{ $t->initiated_at?->format('d M H:i') ?? $t->created_at->format('d M H:i') }}</td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ $t->detailsUrl() }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="10"><div class="empty-state">No transactions found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $txns->links() }}</div>
