<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<body style="margin:0;padding:0;background:#F7F2E9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F2E9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px;width:100%;background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(76,52,28,.08);">
                    <tr>
                        <td style="background:#C2592B;padding:18px 28px;">
                            <span style="color:#FFFFFF;font-size:18px;font-weight:700;letter-spacing:.3px;">
                                @php
                                    $gen = \App\Models\Setting::where('key', 'general')->first();
                                @endphp
                                {{ $gen['business_name'] ?? 'Wakala Platform' }}
                            </span>
                            <span style="display:block;color:#F5D9C8;font-size:12px;margin-top:2px;">{{ $gen['address'] ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;color:#3A2E22;">
                            @yield('content')
                            <p style="margin:24px 0 0;padding-top:16px;border-top:1px solid #EDE3D3;color:#9A8B79;font-size:12px;">
                                This is an automated message from the {{ $gen['business_name'] ?? 'Wakala Platform' }} system. No reply is required.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>