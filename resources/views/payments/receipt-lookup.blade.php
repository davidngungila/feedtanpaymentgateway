<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tafuta Risiti — FeedTan CMG</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--sand-50:#FBF7EF;--sand-100:#F4ECDC;--line:#E4D7C2;--coffee-900:#2A1B10;--coffee-700:#4D3422;--terracotta-600:#C2592B;--terracotta-100:#F6E1D3;--acacia-600:#5E6E3F;--gold-500:#D4A24C;--ink-soft:#6B5A48;--white:#fff;--danger:#B33A3A;--radius-md:14px;--shadow-lg:0 20px 48px rgba(42,27,16,.18)}
        *{box-sizing:border-box}body{margin:0;font-family:'Raleway',sans-serif;background:var(--sand-50);color:var(--coffee-900)}
        .topbar{position:sticky;top:0;z-index:100;height:56px;background:rgba(251,247,239,.86);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px;padding:0 14px}
        .sb-mark{width:32px;height:32px;border-radius:8px;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));display:flex;align-items:center;justify-content:center;color:#fff}
        .view-wrap{padding:20px;min-height:calc(100dvh - 56px);display:flex;align-items:center;justify-content:center}
        .card{width:100%;max-width:460px;background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-lg);overflow:hidden}
        .card-head{padding:18px 20px;text-align:center;background:linear-gradient(155deg,var(--coffee-900),var(--coffee-700));color:#fff}
        .card-body{padding:20px;display:flex;flex-direction:column;gap:14px}
        .field label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--coffee-700);margin-bottom:6px;display:block}
        .field input{width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-size:14px}
        .field input:focus{outline:none;border-color:var(--terracotta-600);box-shadow:0 0 0 3px var(--terracotta-100)}
        .btn{padding:12px 18px;border-radius:8px;border:none;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px}
        .btn-primary{background:var(--terracotta-600);color:#fff;width:100%}
        .btn-ghost{background:transparent;border:1.5px solid var(--line);color:var(--coffee-700);width:100%}
        .recent{font-size:12px;color:var(--ink-soft);text-align:center}
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;height:64px;background:var(--white);border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-around;gap:4px;padding:4px 6px calc(4px + env(safe-area-inset-bottom));z-index:150}
        .bnav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 4px;border-radius:10px;color:var(--ink-soft);font-size:9.5px;font-weight:600;text-decoration:none;border:none;background:none}
        .bnav-item.active{color:var(--terracotta-600);background:var(--terracotta-100)}
        .bnav-fab{width:52px;height:52px;border-radius:14px;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(194,89,43,.32);flex:none;margin-top:-14px;border:2px solid var(--white)}
        @media(min-width:641px){.bottom-nav{display:none}.topbar{height:64px}}
    </style>
</head>
<body>
    <header class="topbar">
        <div class="sb-mark"><i class="fa-solid fa-leaf"></i></div>
        <div style="line-height:1.2"><strong style="font-size:14px;color:var(--coffee-900)">FeedTan CMG</strong><span style="font-size:10px;color:var(--ink-soft);letter-spacing:.06em;text-transform:uppercase;font-weight:600">Risiti za Malipo</span></div>
        <a href="{{ route('payments.public') }}" style="margin-left:auto;font-size:12px;font-weight:600;color:var(--terracotta-600)">Nyumbani</a>
    </header>
    <div class="view-wrap">
        <div class="card">
            <div class="card-head">
                <div style="width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><i class="fa-solid fa-receipt"></i></div>
                <h2 style="margin:0;font-size:16px;color:#fff">Tafuta Risiti</h2>
                <p style="margin:6px 0 0;font-size:12px;opacity:.9">Weka Reference ya malipo (mf. PAY...) kuona risiti — kila ukurasa utaomba reference</p>
            </div>
            <div class="card-body">
                <div class="field">
                    <label for="refInput">Reference ya Risiti *</label>
                    <input type="text" id="refInput" placeholder="mf. PAY20260924083403818" autocomplete="off" maxlength="30" style="text-transform:uppercase;letter-spacing:.04em;font-family:ui-monospace,monospace">
                </div>
                <button type="button" id="viewBtn" class="btn btn-primary"><i class="fa-solid fa-eye"></i> Angalia Risiti</button>
                <a href="{{ route('payments.public') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Rudi Kulipa</a>
                <p class="recent">Baada ya kulipa, Reference hutumwa kwa SMS papo hapo. Weka hapa kuona na kupakua risiti PDF.</p>
            </div>
        </div>
    </div>

    <nav class="bottom-nav" aria-label="Bottom nav">
        <a href="{{ route('payments.public') }}" class="bnav-item"><i class="fa-solid fa-house"></i><span>Nyumbani</span></a>
        <a href="{{ route('payments.status.page') }}" class="bnav-item"><i class="fa-solid fa-eye"></i><span>Hali</span></a>
        <a href="{{ route('payments.receipt.lookup') }}" class="bnav-item active"><i class="fa-solid fa-receipt"></i><span>Risiti</span></a>
        <a href="{{ route('payments.msaada') }}" class="bnav-item"><i class="fa-solid fa-headset"></i><span>Msaada</span></a>
    </nav>

<script>
document.getElementById('viewBtn').addEventListener('click', function(){
    const v=document.getElementById('refInput').value.trim();
    if(!v){ alert('Weka Reference ya risiti'); return; }
    const clean=v.replace(/[^A-Za-z0-9\-]/g,'');
    window.location.href='/payments/receipt/'+encodeURIComponent(clean);
});
document.getElementById('refInput').addEventListener('keydown', e=>{ if(e.key==='Enter') document.getElementById('viewBtn').click(); });
</script>
</body>
</html>
