<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 18mm 12mm 18mm 12mm; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; line-height: 1.4; margin: 0; padding: 0; }
    .header { border-bottom: 3px solid #C2592B; padding-bottom: 10px; margin-bottom: 14px; }
    .header-top { display: table; width: 100%; }
    .header-left { display: table-cell; vertical-align: top; width: 70%; }
    .header-right { display: table-cell; vertical-align: top; width: 30%; text-align: right; }
    .business-name { font-size: 16px; font-weight: 700; color: #C2592B; margin: 0; letter-spacing: 0.02em; }
    .business-meta { font-size: 8px; color: #7A5C42; margin: 2px 0 0; }
    .doc-badge { background: #5E6E3F; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; display: inline-block; }
    .title { font-size: 14px; font-weight: 700; color: #1a1a1a; margin: 0 0 2px; }
    .subtitle { font-size: 9px; color: #6B5A48; margin: 0 0 8px; }
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px; }
    .meta-table td { padding: 3px 6px; vertical-align: top; }
    .meta-label { color: #7A5C42; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; font-size: 7px; width: 90px; }
    .meta-value { color: #1a1a1a; }
    .meta-box { background: #F4ECDC; border: 1px solid #E5DDD0; border-radius: 4px; padding: 6px 8px; margin-bottom: 10px; }
    .table-wrap { margin-top: 8px; }
    table { width: 100%; border-collapse: collapse; font-size: 8px; }
    thead th { background: #5E6E3F; color: #fff; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; font-size: 7px; padding: 7px 5px; text-align: left; border: 1px solid #4a5532; }
    thead th.amount { text-align: right; }
    tbody td { padding: 5px 5px; border: 1px solid #E5DDD0; vertical-align: top; }
    tbody tr:nth-child(even) td { background: #F9F5EB; }
    tbody td.amount { text-align: right; font-family: DejaVu Sans Mono, monospace; }
    tbody td.center { text-align: center; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
    .badge-green { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
    .badge-gold { background: #FFF8E1; color: #8a6418; border: 1px solid #FFECB3; }
    .badge-red { background: #FFEBEE; color: #B33A3A; border: 1px solid #FFCDD2; }
    .badge-grey { background: #F5F5F5; color: #6B5A48; border: 1px solid #E0E0E0; }
    .badge-terracotta { background: #FBE9E7; color: #C2592B; border: 1px solid #FFCCBC; }
    tfoot td { background: #5E6E3F; color: #fff; font-weight: 700; padding: 7px 5px; border: 1px solid #4a5532; font-size: 8px; }
    tfoot td.amount { text-align: right; }
    .footer { position: fixed; bottom: -12mm; left: 0; right: 0; text-align: center; font-size: 7px; color: #7A5C42; border-top: 1px solid #E5DDD0; padding-top: 6px; }
    .footer span { margin: 0 8px; }
    .pagenum:before { content: counter(page); }
    .empty { text-align: center; padding: 30px 10px; color: #7A5C42; font-style: italic; border: 1px dashed #E5DDD0; border-radius: 6px; background: #FFFBF5; }
    .summary { margin-top: 10px; background: #F4ECDC; border: 1px solid #E5DDD0; border-radius: 4px; padding: 8px 10px; font-size: 8px; }
    .summary strong { color: #5E6E3F; }
    .filter-tag { display: inline-block; background: #fff; border: 1px solid #E5DDD0; border-radius: 3px; padding: 2px 6px; margin: 2px 4px 2px 0; font-size: 7px; }
</style>
</head>
<body>

<div class="header">
    <div class="header-top">
        <div class="header-left">
            <div class="business-name">{{ $business['name'] }}</div>
            <div class="business-meta">{{ $business['address'] }} &nbsp;|&nbsp; {{ $business['phone'] }} &nbsp;|&nbsp; {{ $business['email'] }}</div>
        </div>
        <div class="header-right">
            <div class="doc-badge">{{ $title }}</div>
            <div style="font-size:7px;color:#7A5C42;margin-top:4px;">Generated: {{ $generatedAt }}</div>
            <div style="font-size:7px;color:#7A5C42;">By: {{ $generatedBy }}</div>
        </div>
    </div>
</div>

<div>
    <div class="title">{{ $title }}</div>
    @if($subtitle)<div class="subtitle">{{ $subtitle }}</div>@endif
</div>

<div class="meta-box">
    <table class="meta-table" style="margin:0;">
        <tr>
            <td class="meta-label">Records</td>
            <td class="meta-value"><strong>{{ $rows->count() }}</strong> record(s) &nbsp;|&nbsp; Columns: {{ count($columns) }} &nbsp;|&nbsp; Printed: {{ $generatedAt }}</td>
            <td class="meta-label" style="text-align:right;">User</td>
            <td class="meta-value" style="text-align:right;">{{ $generatedBy }}</td>
        </tr>
        @if(!empty($meta['filters']))
            <tr>
                <td class="meta-label">Filters</td>
                <td class="meta-value" colspan="3">
                    @foreach($meta['filters'] as $k => $v)
                        <span class="filter-tag"><strong>{{ ucfirst($k) }}:</strong> {{ is_array($v) ? implode(', ', $v) : $v }}</span>
                    @endforeach
                </td>
            </tr>
        @endif
        @if(!empty($meta['period']))
            <tr>
                <td class="meta-label">Period</td>
                <td class="meta-value" colspan="3">{{ $meta['period'] }}</td>
            </tr>
        @endif
    </table>
</div>

<div class="table-wrap">
    @if($rows->isEmpty())
        <div class="empty">No records found for the selected filters and columns.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:28px;text-align:center;">#</th>
                    @foreach($columns as $col)
                        @php $isAmount = in_array($col['key'], ['amount','fee','commission','balance','total_volume','total_commission','volume','debits','credits','opening','closing','total','deposits','withdrawals','net','count']); @endphp
                        <th @if($isAmount) class="amount" @endif>{{ $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $idx => $row)
                    <tr>
                        <td style="text-align:center;color:#7A5C42;">{{ $idx + 1 }}</td>
                        @foreach($columns as $col)
                            @php
                                $key = $col['key'];
                                $val = $row[$key] ?? '';
                                $isAmount = in_array($key, ['amount','fee','commission','balance','total_volume','total_commission','volume','debits','credits','opening','closing','total','deposits','withdrawals','net','count','total debits','total credits']);
                                // Handle badges for status
                                $isStatus = in_array($key, ['status','processing_status','state']);
                            @endphp
                            @if($isStatus && $val !== '')
                                <td class="center"><span class="badge badge-green">{{ $val }}</span></td>
                            @else
                                <td @if($isAmount) class="amount" @endif>{{ $val }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if(!empty($meta['totals']))
                <tfoot>
                    <tr>
                        <td colspan="1" style="text-align:right;">Totals</td>
                        @foreach($columns as $col)
                            @php $key = $col['key']; $isAmount = in_array($key, ['amount','fee','commission','balance','total_volume','total_commission','volume','debits','credits']); @endphp
                            <td @if($isAmount) class="amount" @endif>
                                @if(isset($meta['totals'][$key])){{ $meta['totals'][$key] }}@elseif($loop->first) {{ $rows->count() }} records @else — @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>

        @if(!empty($meta['summary']))
            <div class="summary">{{ $meta['summary'] }}</div>
        @endif
    @endif
</div>

<div class="footer">
    <span>{{ $business['name'] }} — {{ $business['address'] }}</span>
    <span>Page <span class="pagenum"></span></span>
    <span>{{ $title }} • {{ $generatedAt }}</span>
</div>

</body>
</html>
