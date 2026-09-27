<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') · Wakala Feedtan Store</title>
    <style>
        :root {
            --sand-50: #FBF7EF;
            --sand-100: #F4ECDC;
            --coffee-900: #2A1B10;
            --coffee-800: #3B2718;
            --coffee-700: #4D3422;
            --coffee-500: #7A5C42;
            --coffee-300: #A98968;
            --terracotta-600: #C2592B;
            --terracotta-500: #D06B3A;
            --terracotta-100: #F6E1D3;
            --acacia-600: #5E6E3F;
            --acacia-100: #E2E7D4;
            --ink: #241408;
            --ink-soft: #6B5A48;
            --line: #E4D7C2;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--sand-50);
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .err {
            text-align: center;
            max-width: 560px;
            width: 100%;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 56px 40px;
            box-shadow: 0 20px 48px rgba(42, 27, 16, .12);
        }
        .err-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 92px;
            height: 92px;
            border-radius: 26px;
            background: var(--terracotta-100);
            color: var(--terracotta-600);
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .err h1 {
            margin: 24px 0 8px;
            font-size: 24px;
            color: var(--coffee-800);
        }
        .err p {
            margin: 0;
            color: var(--ink-soft);
            font-size: 15px;
            line-height: 1.6;
        }
        .err-actions {
            margin-top: 32px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            transition: background .15s, border-color .15s;
        }
        .btn-primary { background: var(--terracotta-600); color: #fff; }
        .btn-primary:hover { background: var(--terracotta-500); }
        .btn-ghost { background: transparent; color: var(--coffee-700); border-color: var(--line); }
        .btn-ghost:hover { border-color: var(--coffee-300); }
        .err-src { margin-top: 28px; font-size: 12px; color: var(--coffee-300); }
    </style>
</head>
<body>
    <div class="err">
        <div class="err-code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="err-actions">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn btn-primary">Go to dashboard</a>
            <a href="javascript:history.length > 1 ? history.back() : window.location.href = '/' " class="btn btn-ghost">Go back</a>
        </div>
        <div class="err-src">Wakala Feedtan Store</div>
    </div>
</body>
</html>