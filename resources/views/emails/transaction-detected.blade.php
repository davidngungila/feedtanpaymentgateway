@extends('emails.layout')

@section('title', 'Transaction detected')

@section('content')
    <h2 style="margin:0 0 4px;">Transaction detected</h2>
    <p style="margin:0 0 20px;color:#6B5A48;">A new transaction was recorded on {{ $transaction->created_at->format('d M Y H:i:s') }}.</p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;">
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;width:45%;">Reference</td>
            <td style="padding:8px 12px;font-weight:700;">{{ $transaction->reference }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Provider reference</td>
            <td style="padding:8px 12px;">{{ $transaction->provider_reference }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Type</td>
            <td style="padding:8px 12px;">{{ txn_type_label($transaction->type) }} ({{ $transaction->type }})</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Network</td>
            <td style="padding:8px 12px;">{{ $transaction->network?->name }} ({{ $transaction->network?->code }})</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Customer</td>
            <td style="padding:8px 12px;">{{ $transaction->customer_name ?: '—' }} · {{ $transaction->customer_phone }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Amount</td>
            <td style="padding:8px 12px;font-size:16px;font-weight:700;color:#C2592B;">{{ money($transaction->amount) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Commission</td>
            <td style="padding:8px 12px;">{{ money($transaction->commission) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Fee</td>
            <td style="padding:8px 12px;">{{ money($transaction->fee) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Status</td>
            <td style="padding:8px 12px;">{{ strtoupper($transaction->status) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Running cash</td>
            <td style="padding:8px 12px;">{{ $transaction->running_cash_balance !== null ? money($transaction->running_cash_balance) : '—' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Running float</td>
            <td style="padding:8px 12px;">{{ $transaction->running_float_balance !== null ? money($transaction->running_float_balance) : '—' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Notes</td>
            <td style="padding:8px 12px;">{{ $transaction->notes ?: '—' }}</td>
        </tr>
    </table>
@endsection