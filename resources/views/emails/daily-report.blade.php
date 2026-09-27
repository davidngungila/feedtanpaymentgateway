@extends('emails.layout')

@section('title', 'Daily report')

@section('content')
    <h2 style="margin:0 0 4px;">Daily report</h2>
    <p style="margin:0 0 20px;color:#6B5A48;">Summary for {{ $summary['date'] }}.</p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;">
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;width:45%;">Transactions</td>
            <td style="padding:8px 12px;font-weight:700;">{{ $summary['count'] }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Volume</td>
            <td style="padding:8px 12px;font-size:16px;font-weight:700;color:#C2592B;">{{ money($summary['volume']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Deposits in</td>
            <td style="padding:8px 12px;">{{ money($summary['deposits']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Withdrawals out</td>
            <td style="padding:8px 12px;">{{ money($summary['withdrawals']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Commission earned</td>
            <td style="padding:8px 12px;">{{ money($summary['commission']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Fees</td>
            <td style="padding:8px 12px;">{{ money($summary['fees']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Net revenue</td>
            <td style="padding:8px 12px;font-weight:700;color:{{ $summary['net_revenue'] >= 0 ? '#5E6E3F' : '#B33A3A' }};">{{ money($summary['net_revenue']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Opening cash</td>
            <td style="padding:8px 12px;">{{ money($summary['opening_cash']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Opening float</td>
            <td style="padding:8px 12px;">{{ money($summary['opening_float']) }}</td>
        </tr>
        @if($summary['closing_cash'] !== null)
            <tr>
                <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Closing cash</td>
                <td style="padding:8px 12px;">{{ money($summary['closing_cash']) }}</td>
            </tr>
        @endif
        @if($summary['closing_float'] !== null)
            <tr>
                <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Closing float</td>
                <td style="padding:8px 12px;">{{ money($summary['closing_float']) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Current cash at till</td>
            <td style="padding:8px 12px;">{{ money($summary['cash_available']) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px;background:#F5EFE4;color:#6B5A48;">Current float available</td>
            <td style="padding:8px 12px;">{{ money($summary['float_available']) }}</td>
        </tr>
    </table>
@endsection