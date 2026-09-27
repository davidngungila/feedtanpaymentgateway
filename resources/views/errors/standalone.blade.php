<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>@yield('title', 'Error') · Wakala Feedtan Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        :root{
            --sand-50:#FBF7EF;
            --sand-100:#F4ECDC;
            --sand-200:#E9DCC0;
            --coffee-900:#2A1B10;
            --coffee-800:#3B2718;
            --coffee-700:#4D3422;
            --coffee-500:#7A5C42;
            --coffee-300:#A98968;
            --terracotta-600:#C2592B;
            --terracotta-500:#D06B3A;
            --terracotta-100:#F6E1D3;
            --acacia-600:#5E6E3F;
            --gold-500:#D4A24C;
            --gold-100:#F7E9CB;
            --ink:#241408;
            --ink-soft:#6B5A48;
            --line:#E4D7C2;
            --white:#FFFFFF;
            --danger:#B33A3A;
            --danger-100:#F6DCDA;
        }
        *{box-sizing:border-box;}
        html,body{height:100%;}
        body{
            margin:0;font-family:'Raleway',sans-serif;background:var(--sand-50);color:var(--ink);
            display:flex;align-items:center;justify-content:center;padding:24px;-webkit-font-smoothing:antialiased;
        }
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:8px;border:none;font-weight:600;font-size:14.5px;text-decoration:none;transition:background .15s;}
        .btn-primary{background:var(--terracotta-600);color:#fff;}
        .btn-primary:hover{background:var(--terracotta-500);}
        .btn-ghost{background:transparent;color:var(--coffee-700);border:1.5px solid var(--line);}
        .btn-ghost:hover{background:var(--sand-100);}
    </style>
</head>
<body>
    <div style="width:100%;">
        @yield('content')
    </div>
</body>
</html>
