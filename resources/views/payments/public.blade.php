<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Malipo ya Wanachama — FeedTan CMG · ClickPesa Feedtan Online</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <meta name="description" content="Fanya malipo ya wanachama wa FeedTan Community Microfinance Group kwa urahisi kupitia Tigo Pesa, M-Pesa, Airtel Money na Halopesa. Lipia kwa urahisi zaidi.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{
            --sand-50:#FBF7EF; --sand-100:#F4ECDC; --sand-200:#E9DCC0;
            --coffee-900:#2A1B10; --coffee-800:#3B2718; --coffee-700:#4D3422; --coffee-500:#7A5C42; --coffee-300:#A98968;
            --terracotta-600:#C2592B; --terracotta-500:#D06B3A; --terracotta-100:#F6E1D3;
            --acacia-600:#5E6E3F; --acacia-500:#7A8450; --acacia-100:#E2E7D4;
            --gold-500:#D4A24C; --gold-100:#F7E9CB;
            --ink:#241408; --ink-soft:#6B5A48; --line:#E4D7C2; --white:#FFFFFF; --danger:#B33A3A; --danger-100:#F6DCDA;
            --radius-sm:8px; --radius-md:14px; --radius-lg:20px;
            --shadow-sm:0 1px 2px rgba(42,27,16,.08); --shadow-md:0 8px 24px rgba(42,27,16,.10); --shadow-lg:0 20px 48px rgba(42,27,16,.18);
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{margin:0;font-family:'Raleway',sans-serif;background:var(--sand-50);color:var(--ink);-webkit-font-smoothing:antialiased;overflow-x:hidden}
        ::selection{background:var(--terracotta-100);color:var(--coffee-900)}
        h1,h2,h3{font-family:'Raleway',sans-serif;color:var(--coffee-900);letter-spacing:-.01em}
        a{color:inherit;text-decoration:none}
        button{font-family:inherit;cursor:pointer}
        input,select,textarea{font-family:inherit}
        /* mesh - sand warm */
        .mesh-bg{background-color:var(--sand-50);background-image:radial-gradient(at 0% 0%, rgba(194,89,43,.08) 0, transparent 50%),radial-gradient(at 100% 0%, rgba(212,162,76,.10) 0, transparent 45%),radial-gradient(at 50% 100%, rgba(94,110,63,.06) 0, transparent 50%)}
        /* topbar like dashboard */
        .topbar{position:sticky;top:0;z-index:100;height:72px;background:rgba(251,247,239,.86);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);display:flex;align-items:center;gap:16px;padding:0 28px}
        .sb-mark{width:38px;height:38px;border-radius:10px;flex:none;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);color:#fff}
        .sb-mark i{font-size:15px}
        .tb-live{display:flex;align-items:center;gap:7px;background:var(--acacia-100);color:var(--acacia-600);padding:7px 13px;border-radius:20px;font-size:12.5px;font-weight:700}
        .tb-live::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--acacia-600);box-shadow:0 0 0 0 rgba(94,110,63,.5);animation:pulse 2s infinite}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(94,110,63,.45)}70%{box-shadow:0 0 0 7px rgba(94,110,63,0)}100%{box-shadow:0 0 0 0 rgba(94,110,63,0)}}
        /* view */
        .view-wrap{padding:28px;flex:1}
        .panel-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:18px;margin-bottom:24px}
        .settings-panel{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);padding:26px;overflow:hidden}
        .settings-section{display:flex;align-items:center;gap:9px;margin:24px 0 4px;padding-top:8px}
        .settings-section:first-of-type{margin-top:0;padding-top:0}
        .settings-section::before{content:'';width:22px;height:3px;border-radius:2px;background:var(--gold-500);flex:none}
        .settings-section h4{margin:0;font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--coffee-700)}
        .field{margin-bottom:16px}
        .field label{display:block;font-size:12.5px;font-weight:600;color:var(--coffee-700);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em}
        .field input,.field select,.field textarea{width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:var(--radius-sm);background:var(--white);font-size:14.5px;color:var(--ink);transition:border-color .15s,box-shadow .15s}
        .field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--terracotta-500);box-shadow:0 0 0 3px var(--terracotta-100)}
        .field input::placeholder{color:var(--ink-soft);opacity:.7}
        .input-icon-wrap{position:relative}
        .input-icon-wrap .input-icon{position:absolute;left:14px;top:0;bottom:0;margin:auto 0;width:16px;height:16px;color:var(--coffee-300);pointer-events:none;display:flex;align-items:center;justify-content:center}
        .field .input-icon-wrap input{padding-left:42px}
        .chip{padding:7px 14px;border-radius:20px;font-size:12.5px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;text-decoration:none;transition:background .15s,color .15s,border-color .15s}
        .chip:hover{background:var(--sand-200)}
        .chip.active{background:var(--coffee-900);color:#fff}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:var(--radius-sm);border:none;font-weight:600;font-size:14.5px;transition:transform .12s,box-shadow .12s,background .15s;text-decoration:none}
        .btn:active{transform:translateY(1px)}
        .btn-primary{background:var(--terracotta-600);color:#fff;box-shadow:0 6px 16px rgba(194,89,43,.32)}
        .btn-primary:hover{background:var(--terracotta-500)}
        .btn-ghost{background:transparent;color:var(--coffee-700);border:1.5px solid var(--line)}
        .btn-ghost:hover{background:var(--sand-100)}
        .btn-sm{padding:8px 13px;font-size:13px}
        .balance-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px}
        .balance-box{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);padding:16px 18px;box-shadow:var(--shadow-sm)}
        .balance-box .bb-label{font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-soft);font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:7px}
        .balance-box .bb-amount{font-size:21px;font-weight:700;color:var(--coffee-900)}
        .balance-box .bb-sub{font-size:12px;color:var(--ink-soft);margin-top:3px}
        .table-card{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:24px}
        .table-toolbar{display:flex;align-items:center;gap:10px;padding:16px 18px;border-bottom:1px solid var(--line);flex-wrap:wrap}
        /* popups - centered like dashboard popup */
        .modal-backdrop{position:fixed;inset:0;background:rgba(36,20,8,.5);backdrop-filter:blur(2px);display:none;z-index:400}
        .modal-backdrop.show{display:flex;align-items:center;justify-content:center;padding:20px;overflow-y:auto}
        .popup{margin:auto;background:var(--sand-50);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);width:100%;max-width:440px;animation:popupIn .28s cubic-bezier(.2,.8,.2,1);overflow:hidden;border:1px solid var(--line)}
        @keyframes popupIn{from{opacity:0;transform:scale(.96) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
        .toast{position:fixed;bottom:24px;right:24px;z-index:600;background:var(--coffee-900);color:#fff;padding:13px 18px;border-radius:11px;font-size:13.5px;font-weight:600;box-shadow:var(--shadow-lg);display:flex;align-items:center;gap:10px;min-width:240px;animation:toastIn .3s ease}
        .toast.success{background:var(--acacia-600)}
        .toast.error{background:var(--danger)}
        @keyframes toastIn{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
        .animate-fade-up{animation:fadeUp .35s ease}
        .notice{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:700;width:min(92vw,680px);border-radius:14px;border:1px solid var(--line);padding:14px 16px;box-shadow:var(--shadow-lg);backdrop-filter:blur(8px);display:none;align-items:flex-start;gap:12px}
        .notice.show{display:flex}
        .notice.amber{background:var(--gold-100);border-color:var(--gold-500);color:#8a6418}
        .notice.red{background:var(--danger-100);border-color:var(--danger);color:var(--danger)}
        .notice.green{background:var(--acacia-100);border-color:var(--acacia-600);color:var(--acacia-600)}
        .public-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:18px;align-items:start}
        /* hero info panel like dashboard balance but dark gradient matching sb */
        .hero-info{background:var(--coffee-900);background-image:radial-gradient(circle at 0% 0%, rgba(212,162,76,.10), transparent 55%);color:#fff;border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-md);padding:26px;position:relative;overflow:hidden;box-shadow:var(--shadow-sm)}
        .hero-info::after{content:"";position:absolute;right:-20px;top:-20px;width:90px;height:90px;border-radius:50%;background:rgba(212,162,76,.12)}
        .hero-kicker{color:var(--gold-500);font-size:10.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;gap:8px}
        .hero-kicker::before{content:"";width:22px;height:3px;border-radius:2px;background:var(--gold-500);flex:none}
        .amount-card{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);padding:16px 18px;box-shadow:var(--shadow-sm);display:flex;align-items:center;justify-content:space-between;gap:14px}
        .amount-card .ac-label{font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-soft);font-weight:700}
        .amount-card .ac-value{font-size:22px;font-weight:700;color:var(--coffee-900);margin-top:2px}
        @media (max-width:1180px){.public-grid{grid-template-columns:1fr}}
        @media (max-width:900px){.topbar{padding:0 14px}}
        /* Compact mobile - small details, page scrolls normally */
        @media (max-width:640px){
            html,body{height:auto;min-height:100%;overflow-x:hidden;overflow-y:auto}
            .mesh-bg{min-height:100dvh;height:auto;overflow:visible}
            .min-h-screen{min-height:100dvh;height:auto;overflow:visible}
            .view-wrap{padding:10px 12px 16px;height:auto;min-height:0;overflow:visible;display:flex;flex-direction:column}
            .view-wrap > div[style*="max-width:1080px"]{flex:1;display:flex;flex-direction:column;overflow:visible;min-height:0}
            .topbar{height:50px;min-height:50px;padding:0 12px;gap:8px}
            .sb-mark{width:32px;height:32px;border-radius:8px}
            .sb-mark i{font-size:13px}
            .topbar strong{font-size:13px !important}
            .topbar span[style*="font-size:11px"]{font-size:9px !important}
            .tb-live{padding:4px 8px;font-size:10px;gap:4px}
            .tb-live span{display:inline !important;font-size:10px}
            .view-head{margin-bottom:8px !important;gap:8px !important;align-items:center !important}
            .view-head h2{font-size:16px !important;line-height:1.2}
            .view-head .sub{font-size:10.5px !important;line-height:1.3;margin-top:2px !important}
            .view-head .view-actions span{font-size:10px !important}
            .public-grid{flex:1;gap:8px;overflow:visible;min-height:0;display:flex;flex-direction:column}
            /* hide all extra info on small - only form */
            .view-head{display:none !important}
            .public-grid > div:first-child{display:none !important}
            .view-wrap > div > div:last-child[style*="text-align:center"]{display:none !important}
            .public-grid{gap:0 !important}
            .public-grid > div:first-child{gap:8px !important}
            .hero-info{display:none !important}
            /* secondary Tunakubali panel stays hidden on mobile (form only) */
            .public-grid > div:first-child > .settings-panel{display:none !important}
            .hero-info::after{width:60px;height:60px;right:-10px;top:-10px}
            .hero-kicker{font-size:9px !important;margin-bottom:4px !important;gap:6px !important}
            .hero-kicker::before{width:14px;height:2px}
            .hero-info h1{font-size:15px !important;line-height:1.2 !important;margin-bottom:6px !important}
            .hero-info p{font-size:10.5px !important;line-height:1.35 !important;margin-bottom:8px !important;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
            .hero-info div[style*="flex-direction:column"]{gap:6px !important}
            .hero-info div[style*="flex-direction:column"] > div{font-size:10.5px !important;gap:6px !important}
            .hero-info span[style*="width:32px"]{width:24px !important;height:24px !important;border-radius:7px !important}
            .hero-info span[style*="width:32px"] i{font-size:11px !important}
            .settings-panel{padding:10px 12px !important;border-radius:10px}
            .settings-panel[style*="padding:0"]{display:flex;flex-direction:column;overflow:visible;min-height:0;border-radius:10px}
            .settings-panel[style*="padding:0"] > div:first-child{padding:10px 12px !important}
            .settings-panel[style*="padding:0"] > div:first-child h3{font-size:13px !important}
            .settings-panel[style*="padding:0"] > div:first-child div{font-size:10.5px !important}
            #paymentForm{padding:10px 12px !important;gap:8px !important;overflow:visible;min-height:0}
            .field{margin-bottom:6px !important}
            .field label{font-size:10px !important;margin-bottom:3px !important;letter-spacing:.03em}
            .field input{padding:8px 10px !important;font-size:12.5px !important;border-radius:6px}
            .field .input-icon-wrap input{padding-left:32px !important}
            .input-icon{left:10px !important;width:14px !important;height:14px !important;font-size:12px !important}
            .chip{padding:4px 8px !important;font-size:10.5px !important;border-radius:14px}
            .chip-filters{gap:5px !important;margin-bottom:6px !important}
            .amount-card{padding:8px 10px !important;border-radius:8px;gap:8px !important}
            .amount-card .ac-label{font-size:9px !important}
            .amount-card .ac-value{font-size:15px !important}
            .amount-card div[style*="width:38px"]{width:30px !important;height:30px !important;border-radius:7px !important}
            .btn{padding:9px 12px !important;font-size:12.5px !important;border-radius:6px}
            #submitBtn{padding:10px !important;font-size:13px !important;gap:6px !important}
            #paymentForm > div[style*="font-size:11px"]{font-size:9.5px !important;line-height:1.3 !important;margin-top:2px !important}
            div[style*="text-align:center;font-size:11px"]{font-size:9.5px !important;margin-top:6px !important}
            /* hide secondary Tunakubali detail text on small to save space */
            .settings-panel[style*="padding:16px 18px"]{padding:8px 10px !important}
            .settings-panel[style*="padding:16px 18px"] div[style*="font-size:11px"]{font-size:9px !important}
            .notice{top:8px;padding:10px 12px;border-radius:10px;width:min(94vw,640px)}
            .notice #globalNoticeTitle{font-size:12px !important}
            .notice #globalNoticeMessage{font-size:11px !important}
            .popup{max-width:92vw !important;border-radius:14px !important}
            .popup h3{font-size:14px !important}
        }
        @media (max-width:375px){
            .view-wrap{padding:8px 10px}
            .topbar{height:48px}
            .view-head{margin-bottom:6px !important}
            .view-head h2{font-size:14px !important}
            .hero-info{padding:8px 10px !important}
            .hero-info h1{font-size:13px !important}
            .field input{padding:7px 9px !important;font-size:12px !important}
            .chip{padding:3px 7px !important;font-size:10px !important}
            #paymentForm{gap:6px !important}
            .btn{padding:8px 10px !important;font-size:12px !important}
        }
        @media (max-height:700px) and (max-width:640px){
            .hero-info p{-webkit-line-clamp:1}
            #paymentForm{gap:6px !important}
            .field{margin-bottom:5px !important}
            .amount-card{padding:6px 10px !important}
        }
    </style>
</head>
<body class="mesh-bg min-h-screen">
    <div id="globalNotice" class="notice">
        <div id="globalNoticeIcon" style="margin-top:1px"></div>
        <div style="min-width:0;flex:1">
            <div id="globalNoticeTitle" style="font-size:13.5px;font-weight:700"></div>
            <div id="globalNoticeMessage" style="font-size:12.5px;margin-top:2px;opacity:.9"></div>
        </div>
        <button type="button" id="globalNoticeClose" style="border:none;background:transparent;font-size:16px;opacity:.6;cursor:pointer">✕</button>
    </div>

    <div class="min-h-screen flex flex-col">
        <header class="topbar">
            <div class="sb-mark"><i class="fa-solid fa-leaf"></i></div>
            <div style="line-height:1.2">
                <strong style="display:block;color:var(--coffee-900);font-size:15.5px">FeedTan CMG</strong>
                <span style="display:block;color:var(--ink-soft);font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600">Member Payments · Online</span>
            </div>
            <div style="margin-left:auto;display:flex;align-items:center;gap:10px">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm" style="display:none" id="adminLink">Admin</a>
                <span class="tb-live"><span>Malipo Salama</span></span>
            </div>
        </header>

        <div class="view-wrap">
            <div style="max-width:1080px;margin:0 auto">
                <div class="view-head" style="margin-bottom:18px">
                    <div>
                        <h2 style="font-size:24px">Malipo ya Wanachama</h2>
                        <p class="sub">Lipia kwa Urahisi zaidi · ClickPesa USSD moja kwa moja · SMS papo hapo</p>
                    </div>
                    <div class="view-actions">
                        <span style="font-size:12.5px;color:var(--ink-soft);font-weight:600;display:flex;align-items:center;gap:6px"><i class="fa-solid fa-shield-halved" style="color:var(--acacia-600)"></i> Encrypted · 256-bit</span>
                    </div>
                </div>

                <div class="public-grid">
                    <!-- Info - desktop like hero -->
                    <div style="display:flex;flex-direction:column;gap:18px">
                        <div class="hero-info">
                            <div class="hero-kicker">ClickPesa Feedtan Online</div>
                            <h1 style="font-size:26px;line-height:1.2;color:#fff;margin-bottom:10px">Lipa kwa urahisi<br><span style="color:var(--gold-500)">kupitia simu yako</span></h1>
                            <p style="font-size:13.5px;line-height:1.6;color:rgba(255,255,255,.78);margin-bottom:16px">Jaza fomu, thibitisha USSD kwenye simu yako, na upokee uthibitisho kwa SMS mara malipo yanapokamilika. Lipia kwa urahisi zaidi.</p>
                            <div style="display:flex;flex-direction:column;gap:10px;font-size:13.5px">
                                <div style="display:flex;gap:10px;align-items:center"><span style="width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,.10);display:flex;align-items:center;justify-content:center;flex:none"><i class="fa-solid fa-bolt" style="font-size:13px;color:var(--gold-500)"></i></span><span style="color:rgba(255,255,255,.9)">Haraka — USSD inatumwa moja kwa moja</span></div>
                                <div style="display:flex;gap:10px;align-items:center"><span style="width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,.10);display:flex;align-items:center;justify-content:center;flex:none"><i class="fa-solid fa-lock" style="font-size:13px;color:var(--gold-500)"></i></span><span style="color:rgba(255,255,255,.9)">Salama — ClickPesa & mobile money</span></div>
                                <div style="display:flex;gap:10px;align-items:center"><span style="width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,.10);display:flex;align-items:center;justify-content:center;flex:none"><i class="fa-solid fa-paper-plane" style="font-size:13px;color:var(--gold-500)"></i></span><span style="color:rgba(255,255,255,.9)">SMS papo hapo baada ya malipo</span></div>
                            </div>
                        </div>
                        <div class="settings-panel" style="padding:16px 18px">
                            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--ink-soft);margin-bottom:10px">Tunakubali</div>
                            <div style="display:flex;flex-wrap:wrap;gap:8px">
                                <span class="chip" style="background:var(--white);border:1px solid var(--line);padding:6px 10px;font-size:12px"><i class="fa-solid fa-mobile-screen" style="color:var(--terracotta-600);margin-right:6px"></i>M-Pesa</span>
                                <span class="chip" style="background:var(--white);border:1px solid var(--line);padding:6px 10px;font-size:12px"><i class="fa-solid fa-mobile-screen" style="color:var(--terracotta-600);margin-right:6px"></i>Tigo Pesa</span>
                                <span class="chip" style="background:var(--white);border:1px solid var(--line);padding:6px 10px;font-size:12px"><i class="fa-solid fa-mobile-screen" style="color:var(--terracotta-600);margin-right:6px"></i>Airtel Money</span>
                                <span class="chip" style="background:var(--white);border:1px solid var(--line);padding:6px 10px;font-size:12px"><i class="fa-solid fa-mobile-screen" style="color:var(--terracotta-600);margin-right:6px"></i>Halopesa</span>
                            </div>
                            <div style="font-size:11px;color:var(--ink-soft);margin-top:10px;text-align:center">Let's Grow Together · Pay → USSD → SMS</div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="settings-panel animate-fade-up" style="padding:0;overflow:hidden">
                        <div style="padding:18px 22px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <div>
                                <h3 style="font-size:16px;margin:0">Malipo ya Wanachama</h3>
                                <div style="font-size:12.5px;color:var(--ink-soft);margin-top:3px">Sehemu zenye <span style="color:var(--danger)">*</span> ni lazima · Lipia kwa Urahisi zaidi</div>
                            </div>
                            <span class="tb-live" style="padding:5px 10px;font-size:11px"><i class="fa-solid fa-shield-halved"></i> Secure</span>
                        </div>
                        <form action="{{ route('payments.store') }}" method="POST" id="paymentForm" style="padding:18px 22px;display:flex;flex-direction:column;gap:14px">
                            @csrf
                            <div class="field" style="margin-bottom:0">
                                <label for="payer_name">Jina la Mwanachama <span style="color:var(--danger)">*</span></label>
                                <div class="input-icon-wrap">
                                    <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" id="payer_name" name="payer_name" placeholder="Mfano: Jane Mwanza" maxlength="100" required autocomplete="name">
                                </div>
                            </div>
                            <div class="field" style="margin-bottom:0">
                                <label for="phone_number">Namba ya Simu <span style="color:var(--danger)">*</span></label>
                                <div class="input-icon-wrap">
                                    <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" id="phone_number" name="phone_number" placeholder="255712345678" maxlength="12" required inputmode="numeric" autocomplete="tel" style="font-family:ui-monospace,monospace">
                                </div>
                            </div>
                            <div class="field" style="margin-bottom:0">
                                <label for="amount">Kiasi (TZS) <span style="color:var(--danger)">*</span></label>
                                <div class="input-icon-wrap">
                                    <span class="input-icon" style="font-size:10.5px;font-weight:800;color:var(--terracotta-600)">TZS</span>
                                    <input type="number" id="amount" name="amount" placeholder="5,000" min="500" max="5000000" required inputmode="numeric" style="font-weight:700">
                                </div>
                                <div style="font-size:11.5px;color:var(--ink-soft);margin-top:6px">Kiwango: TZS 500 — 5,000,000</div>
                            </div>
                            <div class="field" style="margin-bottom:0">
                                <label for="description">Malipo Kwaajili Ya <span style="color:var(--danger)">*</span></label>
                                <div class="chip-filters" id="purposeChips" style="margin-bottom:10px">
                                    <button type="button" data-purpose="Akiba" class="chip">Akiba</button>
                                    <button type="button" data-purpose="Uwekezaji" class="chip">Uwekezaji</button>
                                    <button type="button" data-purpose="Malipo ya mkopo" class="chip">Malipo ya mkopo</button>
                                    <button type="button" data-purpose="Ada ya Uanachama" class="chip">Ada ya Uanachama</button>
                                    <button type="button" data-purpose="Hisa" class="chip">Hisa</button>
                                    <button type="button" data-purpose="SWF Contribution" class="chip">SWF Contribution</button>
                                    <button type="button" data-purpose="Malipo ya Bidhaa" class="chip">Malipo ya Bidhaa</button>
                                    <button type="button" data-purpose="Nyingine" class="chip">Nyingine</button>
                                </div>
                                <input type="text" id="description" name="description" required readonly placeholder="Chagua malipo kwaajili ya…" style="background:var(--sand-100);cursor:not-allowed">
                            </div>
                            <input type="hidden" id="akiba_type" name="akiba_type">
                            <input type="hidden" id="uwekezaji_type" name="uwekezaji_type">
                            <input type="hidden" id="hisa_type" name="hisa_type">

                            <div class="amount-card">
                                <div>
                                    <div class="ac-label">Jumla ya Malipo</div>
                                    <div class="ac-value">TZS <span id="btnAmount">0</span></div>
                                </div>
                                <div style="width:38px;height:38px;border-radius:10px;background:var(--terracotta-100);color:var(--terracotta-600);display:flex;align-items:center;justify-content:center;flex:none">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px">
                                <i class="fa-solid fa-lock" style="font-size:12px"></i>
                                <span>Lipa Sasa</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div style="text-align:center;font-size:11px;color:var(--ink-soft);margin-top:12px;display:flex;align-items:center;justify-content:center;gap:6px"><i class="fa-solid fa-lock" style="color:var(--acacia-600)"></i> Powered by FeedTan Team · ClickPesa · SMS papo hapo</div>
            </div>
        </div>
    </div>

    <!-- sub-option modal using dashboard modal-backdrop + popup -->
    <div id="subOptionModal" class="modal-backdrop" style="backdrop-filter:blur(4px)">
        <div class="popup" style="padding:22px">
            <div style="text-align:center;margin-bottom:12px"><h3 id="subOptionModalTitle" style="font-size:16px;margin:0"></h3></div>
            <div id="subOptionModalContent" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:16px"></div>
            <button type="button" id="subOptionModalClose" class="btn btn-ghost" style="width:100%">Funga</button>
        </div>
    </div>
    <div id="customInputModal" class="modal-backdrop" style="backdrop-filter:blur(4px)">
        <div class="popup" style="padding:22px">
            <div style="text-align:center;margin-bottom:14px"><h3 style="font-size:16px;margin:0">Andika Malipo Kwaajili Ya</h3></div>
            <div class="field"><input type="text" id="customPurposeInput" placeholder="Andika maelezo ya malipo…" maxlength="100"></div>
            <div style="display:flex;gap:10px;margin-top:12px">
                <button type="button" id="customInputModalClose" class="btn btn-ghost" style="flex:1">Funga</button>
                <button type="button" id="customInputModalSave" class="btn btn-primary" style="flex:1">Hifadhi</button>
            </div>
        </div>
    </div>

    <div id="modalRoot"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const noticeEl = document.getElementById('globalNotice');
        const noticeTitleEl = document.getElementById('globalNoticeTitle');
        const noticeMessageEl = document.getElementById('globalNoticeMessage');
        const noticeIconEl = document.getElementById('globalNoticeIcon');
        const noticeCloseEl = document.getElementById('globalNoticeClose');
        function showGlobalNotice(type, title, message) {
            const map = {
                warning:{cls:'amber',icon:'<i class="fa-solid fa-triangle-exclamation" style="color:#8a6418"></i>'},
                error:{cls:'red',icon:'<i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i>'},
                success:{cls:'green',icon:'<i class="fa-solid fa-circle-check" style="color:var(--acacia-600)"></i>'},
                info:{cls:'amber',icon:'<i class="fa-solid fa-circle-info" style="color:var(--coffee-500)"></i>'}
            };
            const s = map[type]||map.info;
            noticeEl.className='notice show '+s.cls;
            noticeTitleEl.textContent=title; noticeMessageEl.textContent=message; noticeIconEl.innerHTML=s.icon;
            clearTimeout(window.__noticeT); window.__noticeT=setTimeout(()=>noticeEl.classList.remove('show'),5000);
        }
        noticeCloseEl.addEventListener('click',()=>noticeEl.classList.remove('show'));
        // show admin link if cookie? just check /dashboard reachable? keep hidden
        try{ if(document.cookie.includes('laravel_session')) document.getElementById('adminLink').style.display='inline-flex'; }catch(e){}

        const form = document.getElementById('paymentForm');
        const submitBtn = document.getElementById('submitBtn');
        const amountInput = document.getElementById('amount');
        const btnAmount = document.getElementById('btnAmount');
        const descriptionInput = document.getElementById('description');
        const phoneInput = document.getElementById('phone_number');
        const modalRoot = document.getElementById('modalRoot');
        const akibaTypeInput = document.getElementById('akiba_type');
        const uwekezajiTypeInput = document.getElementById('uwekezaji_type');
        const hisaTypeInput = document.getElementById('hisa_type');
        const subOptionModal = document.getElementById('subOptionModal');
        const subOptionModalTitle = document.getElementById('subOptionModalTitle');
        const subOptionModalContent = document.getElementById('subOptionModalContent');
        const subOptionModalClose = document.getElementById('subOptionModalClose');
        const customInputModal = document.getElementById('customInputModal');
        const customPurposeInput = document.getElementById('customPurposeInput');
        const customInputModalClose = document.getElementById('customInputModalClose');
        const customInputModalSave = document.getElementById('customInputModalSave');

        const subOptions = {
            'Akiba': { title: 'Chagua Aina ya Akiba', options: ['RDA', 'FLEX', 'EMERGENCE'], inputField: akibaTypeInput, descriptionPrefix: 'Akiba' },
            'Uwekezaji': { title: 'Chagua Aina ya Uwekezaji', options: ['2Year FIA', '4Years FIA', '6 Years FIA'], inputField: uwekezajiTypeInput, descriptionPrefix: 'Uwekezaji' },
            'Hisa': { title: 'Chagua Aina ya Hisa', options: ['Hisa za duka', 'Hisa za Feedtan CMG'], inputField: hisaTypeInput, descriptionPrefix: 'Hisa' }
        };
        let currentPurpose = null;
        let currentSubOptionConfig = null;
        let pollingInterval = null;
        let pollingStartTime = null;
        const POLLING_DURATION = 180000;
        const POLLING_INTERVAL = 3000;

        function formatAmountDisplay(value) { const n = Number(value) || 0; return n.toLocaleString('en-TZ'); }
        amountInput.addEventListener('input', function () { btnAmount.textContent = formatAmountDisplay(this.value); });

        function openSubOptionModal(purpose) {
            currentPurpose = purpose; currentSubOptionConfig = subOptions[purpose];
            subOptionModalTitle.textContent = currentSubOptionConfig.title; subOptionModalContent.innerHTML = '';
            const currentAmount = Number(amountInput.value) || 0;
            let optionsToShow = currentSubOptionConfig.options;
            if (purpose === 'Akiba') {
                optionsToShow = optionsToShow.filter(option => { if (option === 'RDA') return currentAmount > 100000; return true; });
                if (optionsToShow.length === 0) { showGlobalNotice('warning','Kiasi kidogo','RDA inahitaji zaidi ya TZS 100,000. Chagua FLEX au EMERGENCE.'); return; }
            }
            optionsToShow.forEach(option => {
                const button = document.createElement('button'); button.type = 'button'; button.textContent = option; button.className = 'chip';
                button.addEventListener('click', () => selectSubOption(option));
                subOptionModalContent.appendChild(button);
            });
            subOptionModal.classList.add('show');
        }
        function closeSubOptionModal() { subOptionModal.classList.remove('show'); currentPurpose = null; currentSubOptionConfig = null; }
        function openCustomInputModal() { customPurposeInput.value = ''; customInputModal.classList.add('show'); customPurposeInput.focus(); }
        function closeCustomInputModal() { customInputModal.classList.remove('show'); }
        function saveCustomPurpose() {
            const customValue = customPurposeInput.value.trim();
            if (customValue) { descriptionInput.value = customValue; closeCustomInputModal(); }
            else { showGlobalNotice('warning','Jaza maelezo','Tafadhali andika maelezo ya malipo.'); }
        }
        function selectSubOption(option) {
            if (!currentSubOptionConfig) return;
            currentSubOptionConfig.inputField.value = option;
            descriptionInput.value = `${currentSubOptionConfig.descriptionPrefix} - ${option}`;
            closeSubOptionModal();
        }
        document.querySelectorAll('.chip').forEach(function (chip) {
            if(!chip.dataset.purpose) return;
            chip.addEventListener('click', function () {
                const purpose = this.dataset.purpose;
                akibaTypeInput.value = ''; uwekezajiTypeInput.value=''; hisaTypeInput.value='';
                document.querySelectorAll('[data-purpose]').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                if (subOptions[purpose]) { openSubOptionModal(purpose); }
                else if (purpose === 'Nyingine') { openCustomInputModal(); }
                else { descriptionInput.value = purpose; }
            });
        });
        subOptionModalClose.addEventListener('click', closeSubOptionModal);
        subOptionModal.addEventListener('click', function (e) { if (e.target === subOptionModal) closeSubOptionModal(); });
        customInputModalClose.addEventListener('click', closeCustomInputModal);
        customInputModalSave.addEventListener('click', saveCustomPurpose);
        customInputModal.addEventListener('click', function (e) { if (e.target === customInputModal) closeCustomInputModal(); });
        customPurposeInput.addEventListener('keypress', function (e) { if (e.key === 'Enter') saveCustomPurpose(); });
        phoneInput.addEventListener('blur', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length === 10 && value.startsWith('0')) { this.value = '255' + value.substring(1); }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            const data = {
                amount: formData.get('amount'),
                phone_number: String(formData.get('phone_number') || '').replace(/\D/g, ''),
                payer_name: String(formData.get('payer_name') || '').trim(),
                description: String(formData.get('description') || '').trim(),
                akiba_type: String(formData.get('akiba_type') || '').trim(),
                uwekezaji_type: String(formData.get('uwekezaji_type') || '').trim(),
                hisa_type: String(formData.get('hisa_type') || '').trim(),
                _token: formData.get('_token')
            };
            if (!data.payer_name) { showGlobalNotice('error','Jina linahitajika','Tafadhali ingiza jina lako kamili.'); return; }
            if (!data.description) { showGlobalNotice('error','Chagua kusudi','Tafadhali chagua malipo kwaajili ya.'); return; }
            if (data.description === 'Akiba' && !data.akiba_type) { showGlobalNotice('error','Chagua Akiba','Tafadhali chagua aina ya Akiba (RDA, FLEX, au EMERGENCE).'); return; }
            if (data.description === 'Akiba - RDA' && Number(data.amount) <= 100000) { showGlobalNotice('error','Kiasi kidogo','RDA inahitaji zaidi ya TZS 100,000.'); return; }
            if (data.description === 'Uwekezaji' && !data.uwekezaji_type) { showGlobalNotice('error','Chagua Uwekezaji','Tafadhali chagua aina ya Uwekezaji.'); return; }
            if (data.description.startsWith('Hisa') && !data.hisa_type) { showGlobalNotice('error','Chagua Hisa','Tafadhali chagua aina ya Hisa.'); return; }
            if (!data.phone_number.match(/^255[67]\d{8}$/)) { showGlobalNotice('error','Namba si sahihi','Namba ya simu si sahihi. Mfano: 255712345678'); return; }
            if (!data.amount || data.amount < 500) { showGlobalNotice('error','Kiasi kidogo','Kiasi cha chini ni TZS 500.'); return; }
            if (data.amount > 5000000) { showGlobalNotice('error','Kiasi kikubwa','Kiasi cha juu ni TZS 5,000,000.'); return; }

            submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Inatuma…</span>';
            showProcessingModal();
            fetch('{{ route('payments.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(data)
            })
            .then(res => res.json().then(j=>({ok:res.ok, json:j})))
            .then(({ok, json}) => {
                closeModal('processingModal');
                if (ok && json.success) { showUSSDNotification(json); }
                else { showGlobalNotice('error','Imeshindikana', json.message || 'Imeshindikwa kutuma malipo. Jaribu tena.'); resetButton(); }
            })
            .catch(() => {
                closeModal('processingModal');
                showGlobalNotice('warning','Mtandao','Tatizo la mtandao. Tafadhali jaribu tena.');
                resetButton();
            });
        });

        function resetButton() { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa-solid fa-lock" style="font-size:12px"></i><span>Lipa Sasa</span>'; }
        function closeModal(id) { const el = document.getElementById(id); if (el) el.remove(); }
        function showProcessingModal() {
            modalRoot.innerHTML = `
                <div id="processingModal" class="modal-backdrop show" style="display:flex">
                    <div class="popup" style="padding:26px;text-align:center">
                        <div style="width:56px;height:56px;border-radius:12px;background:var(--terracotta-100);color:var(--terracotta-600);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-solid fa-spinner fa-spin" style="font-size:22px"></i></div>
                        <h3 style="font-size:17px;margin:0">Tunaandaa Malipo Yako</h3>
                        <p style="font-size:13px;color:var(--ink-soft);margin-top:6px">USSD inatumwa kwenye simu yako. Subiri kidogo…</p>
                        <div style="margin-top:16px;text-align:left;font-size:13px;background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:14px;display:flex;flex-direction:column;gap:8px">
                            <div style="display:flex;gap:8px"><span style="color:var(--terracotta-600);font-weight:700">1.</span><span>Angalia simu yako</span></div>
                            <div style="display:flex;gap:8px"><span style="color:var(--terracotta-600);font-weight:700">2.</span><span>Thibitisha USSD PUSH</span></div>
                            <div style="display:flex;gap:8px"><span style="color:var(--terracotta-600);font-weight:700">3.</span><span>Weka PIN — SMS itatumwa papo hapo!</span></div>
                        </div>
                    </div>
                </div>`;
        }
        function showUSSDNotification(data) {
            const phone = data.phone_number || data.phone || '—';
            const amount = formatAmountDisplay(data.amount);
            const orderReference = data.order_reference || data.orderReference;
            modalRoot.innerHTML = `
                <div id="ussdNotification" class="modal-backdrop show" style="display:flex">
                    <div class="popup" style="padding:0;overflow:hidden">
                        <div style="background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));color:#fff;padding:18px 22px;text-align:center">
                            <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><i class="fa-solid fa-check"></i></div>
                            <h3 style="font-size:16px;color:#fff;margin:0">Malipo Yameanzishwa</h3>
                            <p style="font-size:12.5px;color:rgba(255,255,255,.9);margin-top:4px">USSD imetumwa — thibitisha kwenye simu yako</p>
                        </div>
                        <div style="padding:16px 22px;display:flex;flex-direction:column;gap:10px">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:var(--sand-100);border:1px solid var(--line)"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--ink-soft)">Simu</span><span style="font-size:13px;font-family:ui-monospace,monospace;font-weight:700;color:var(--coffee-900)">${phone}</span></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:var(--terracotta-100);border:1px solid var(--terracotta-600)"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--terracotta-600)">Kiasi</span><span style="font-size:13px;font-weight:700;color:var(--coffee-900)">TZS ${amount}</span></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:var(--sand-100);border:1px solid var(--line)"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--ink-soft)">Reference</span><span style="font-size:12px;font-family:ui-monospace,monospace;font-weight:700;color:var(--coffee-900)">${orderReference}</span></div>
                            <p style="font-size:11.5px;color:var(--ink-soft);text-align:center">Thibitisha na PIN. Tunaangalia hali — SMS itatumwa mara moja ukilipia!</p>
                            <div id="statusIndicator" style="text-align:center;font-size:12.5px;color:var(--coffee-700)"><i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;color:var(--terracotta-600)"></i>Inaangalia hali ya malipo…</div>
                            <div style="height:6px;background:var(--sand-100);border-radius:20px;overflow:hidden;border:1px solid var(--line)"><div id="progressBar" style="height:100%;background:linear-gradient(90deg,var(--terracotta-600),var(--gold-500));width:0%;transition:width .3s"></div></div>
                        </div>
                    </div>
                </div>`;
            startPolling(orderReference);
        }
        window.closeUSSDNotification = function () { stopPolling(); const el=document.getElementById('ussdNotification'); if(el) el.remove(); form.reset(); btnAmount.textContent='0'; resetButton(); };
        function startPolling(orderReference) {
            pollingStartTime = Date.now(); updateProgressBar();
            pollingInterval = setInterval(() => {
                const elapsed = Date.now() - pollingStartTime; updateProgressBar();
                if (elapsed >= POLLING_DURATION) { stopPolling(); showPollingTimeout(orderReference); return; }
                checkPaymentStatus(orderReference);
            }, POLLING_INTERVAL);
            checkPaymentStatus(orderReference);
        }
        function stopPolling() { if (pollingInterval) { clearInterval(pollingInterval); pollingInterval = null; } }
        function updateProgressBar() {
            const progressBar = document.getElementById('progressBar'); if (!progressBar) return;
            const elapsed = Date.now() - pollingStartTime; const percentage = Math.min((elapsed / POLLING_DURATION) * 100, 100);
            progressBar.style.width = `${percentage}%`;
        }
        async function checkPaymentStatus(orderReference) {
            try {
                const response = await fetch('{{ route('payments.api.status') }}', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' },
                    body: JSON.stringify({ order_reference: orderReference })
                });
                const result = await response.json(); if (!result.success) return;
                const data = result.data; const transaction = result.transaction; let status = null;
                if (transaction && transaction.status) status = transaction.status;
                else if (data && data.status) status = data.status;
                else if (Array.isArray(data) && data[0] && data[0].status) status = data[0].status;
                if (!status) return; status = String(status).toUpperCase();
                const isSuccessful = ['SUCCESS', 'SETTLED', 'COMPLETED','SUCCESSFUL'].includes(status);
                const isFailed = ['FAILED', 'DECLINED', 'CANCELLED', 'ERROR', 'REVERSED'].includes(status);
                if (isSuccessful) { stopPolling(); showSuccessModal(orderReference, data || transaction); }
                else if (isFailed) { stopPolling(); showFailureModal(status, data || transaction); }
            } catch (error) { console.error(error); }
        }
        function showSuccessModal(orderReference, paymentData) {
            const amount = formatAmountDisplay(paymentData.amount || paymentData.collectedAmount || 0);
            modalRoot.innerHTML = `
                <div id="successModal" class="modal-backdrop show" style="display:flex">
                    <div class="popup" style="padding:0;overflow:hidden">
                        <div style="background:linear-gradient(155deg,var(--acacia-600),var(--acacia-500));color:#fff;padding:18px 22px;text-align:center">
                            <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><i class="fa-solid fa-circle-check" style="font-size:22px"></i></div>
                            <h3 style="font-size:16px;color:#fff;margin:0">Hongera! Malipo Yamekamilika</h3>
                            <p style="font-size:12.5px;color:rgba(255,255,255,.9);margin-top:4px">SMS ya uthibitisho imetumwa papo hapo ✓</p>
                        </div>
                        <div style="padding:18px 22px;display:flex;flex-direction:column;gap:12px">
                            <p style="font-size:13px;color:var(--ink-soft);text-align:center">Asante! Malipo yako yamekamilika na SMS imetumwa.</p>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:var(--acacia-100);border:1px solid var(--acacia-600)"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--acacia-600)">Kiasi</span><span style="font-size:13px;font-weight:700;color:var(--coffee-900)">TZS ${amount}</span></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:var(--sand-100);border:1px solid var(--line)"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--ink-soft)">Reference</span><span style="font-size:12px;font-family:ui-monospace,monospace;font-weight:700;color:var(--coffee-900)">${orderReference}</span></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                                <a href="/payments/receipt/${orderReference}" target="_blank" class="btn btn-primary" style="font-size:13px;padding:10px"><i class="fa-solid fa-download"></i> Pakua Risiti</a>
                                <a href="/payments/status?reference=${orderReference}" class="btn btn-ghost" style="font-size:13px;padding:10px"><i class="fa-solid fa-eye"></i> Angalia</a>
                            </div>
                            <button type="button" onclick="closeSuccessModal()" class="btn btn-ghost" style="width:100%">Funga</button>
                        </div>
                    </div>
                </div>`;
        }
        window.closeSuccessModal = function () { const el=document.getElementById('successModal'); if(el) el.remove(); form.reset(); btnAmount.textContent='0'; resetButton(); };
        function swStatus(status){
            const m={FAILED:'IMESHINDWA',DECLINED:'IMEKAT ALIWA',CANCELLED:'IMEGHAIRIWA',ERROR:'HITILAFU',REVERSED:'IMERUDISHWA',PENDING:'INASUBIRI',PROCESSING:'INASHUGHULIKIWA',SUCCESS:'IMEFANIKIWA',SETTLED:'IMETATULIWA',COMPLETED:'IMEKAMILIKA'};
            const up=String(status||'').toUpperCase(); return m[up]||up;
        }
        function swReason(msg){
            if(!msg) return 'Hakuna maelezo.';
            const low=String(msg).toLowerCase();
            if(low.includes('overdraf') && low.includes('unsubsc')){
                const balM=String(msg).match(/source balance:\s*([\d.,]+)/i);
                const amtM=String(msg).match(/amount with fee:\s*([\d.,]+)/i);
                const fmt=n=>{ const v=Number(String(n).replace(/,/g,'')); return isNaN(v)? n : v.toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2}); };
                const bal=balM? fmt(balM[1]) : '—';
                const amt=amtM? fmt(amtM[1]) : '—';
                return `Akaunti ya mkopo wa Overdraft haijasajiliwa. Salio halitoshi: TZS ${bal}, kiasi pamoja na tozo: TZS ${amt}. Tafadhali weka akiba ya kutosha au wasiliana na msimamizi wa FeedTan.`;
            }
            if(low.includes('customer_canceled_pin') || low.includes('customer_cancelled_pin')){
                return 'Umeighairi kuweka PIN — ulipokea USSD lakini ukaghairi kabla ya kuweka PIN yako ya siri. Tafadhali jaribu tena na uweke PIN sahihi kuthibitisha malipo.';
            }
            if(low.includes('customer_canceled') || low.includes('customer_cancelled') || low.includes('user_canceled') || low.includes('transaction_canceled')){
                return 'Mteja ameghairi muamala — ulighairi ombi la malipo kwenye simu yako. Tafadhali jaribu tena.';
            }
            if(low.includes('canceled_pin') || low.includes('cancelled_pin')){
                return 'Umeighairi — hukuthibitisha malipo kwa PIN.';
            }
            if(low.includes('wrong pin') || low.includes('incorrect pin') || low.includes('invalid pin')){
                return 'PIN uliyoingiza si sahihi. Tafadhali jaribu tena kwa PIN sahihi.';
            }
            if(low.includes('timeout') || low.includes('timed out')){
                return 'Muda umeisha — muamala haukuthibitishwa kwa wakati. Tafadhali jaribu tena.';
            }
            if(low.includes('insufficient')){
                let out=String(msg).replace(/Insufficient source balance/gi,'Salio la chanzo halitoshi').replace(/Insufficient balance/gi,'Salio halitoshi').replace(/amount with fee/gi,'kiasi pamoja na tozo').replace(/source balance/gi,'salio la chanzo');
                if(out===msg) out=msg.replace(/Insufficient/gi,'Halitoshi').replace(/balance/gi,'salio').replace(/amount/gi,'kiasi').replace(/fee/gi,'tozo');
                return out + ' · Tafadhali ongeza salio.';
            }
            // generic quick map for other cases
            let out=String(msg);
            const dict=[[/\bunsubscribed\b/gi,'haijasajiliwa'],[/\boverdraft\b/gi,'mkopo wa ziada (Overdraft)'],[/\boverdraf\b/gi,'mkopo wa ziada'],[/\bloan\b/gi,'mkopo'],[/\bfailed\b/gi,'imeshindwa'],[/\bdeclined\b/gi,'imekataliwa'],[/\bcancelled\b/gi,'imeghairiwa'],[/\breversed\b/gi,'imerudishwa']];
            let changed=false; dict.forEach(([re,rep])=>{ if(re.test(out)){ out=out.replace(re,rep); changed=true; }});
            if(changed) return out;
            return msg;
        }
        function showFailureModal(status, transactionData) {
            let failureReason = 'Hakuna maelezo.';
            if (transactionData) { failureReason = transactionData.reason || transactionData.description || transactionData.message || transactionData.error || transactionData.failure_reason || failureReason; }
            const failureSw = swReason(failureReason);
            const statusSw = swStatus(status);
            const statusLine = statusSw!==String(status).toUpperCase() ? `${status} (${statusSw})` : `${status} · ${statusSw}`;
            const reasonBlock = failureSw!==failureReason
                ? `<p style="font-size:13px;margin:0;color:var(--danger)">${failureSw}</p><p style="font-size:10.5px;margin:6px 0 0;color:var(--ink-soft);border-top:1px dashed rgba(179,58,58,.25);padding-top:6px"><i>Asili (EN):</i> ${failureReason}</p>`
                : `<p style="font-size:13px;margin:0">${failureSw}</p>`;
            modalRoot.innerHTML = `
                <div id="failureModal" class="modal-backdrop show" style="display:flex">
                    <div class="popup" style="padding:0;overflow:hidden">
                        <div style="background:var(--danger);color:#fff;padding:18px 22px;text-align:center">
                            <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><i class="fa-solid fa-circle-xmark" style="font-size:22px"></i></div>
                            <h3 style="font-size:16px;color:#fff;margin:0">Malipo Haujaweza</h3>
                            <p style="font-size:11px;color:rgba(255,255,255,.85);margin-top:4px">Payment Failed — Imefeli</p>
                        </div>
                        <div style="padding:18px 22px;display:flex;flex-direction:column;gap:12px">
                            <p style="font-size:13px;color:var(--ink-soft);text-align:center">Malipo haujaweza kukamilika. / Payment could not be completed.</p>
                            <div style="padding:12px;border-radius:10px;background:var(--danger-100);border:1px solid var(--danger);color:var(--danger)">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700">Hali / Status</span><span style="font-size:12px;font-weight:700;text-align:right">${statusLine}</span></div>
                                <div style="border-top:1px solid rgba(179,58,58,.2);padding-top:8px"><span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;margin-bottom:4px">Sababu / Reason</span>${reasonBlock}</div>
                            </div>
                            <button type="button" onclick="closeFailureModal()" class="btn" style="background:var(--danger);color:#fff;width:100%">Jaribu Tena — Try Again</button>
                        </div>
                    </div>
                </div>`;
        }
        window.closeFailureModal = function () { const el=document.getElementById('failureModal'); if(el) el.remove(); resetButton(); };
        function showPollingTimeout(orderReference) {
            modalRoot.innerHTML = `
                <div id="timeoutModal" class="modal-backdrop show" style="display:flex">
                    <div class="popup" style="padding:0;overflow:hidden">
                        <div style="background:var(--gold-500);color:#fff;padding:18px 22px;text-align:center">
                            <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><i class="fa-solid fa-clock" style="font-size:22px"></i></div>
                            <h3 style="font-size:16px;color:#fff;margin:0">Malipo Bado Yanaendelea</h3>
                        </div>
                        <div style="padding:18px 22px;display:flex;flex-direction:column;gap:12px">
                            <p style="font-size:13px;color:var(--ink-soft);text-align:center">Malipo yako bado yanaendelea. Kwa M-Pesa huweza kuchukua muda. Tafadhali angalia hali ya malipo.</p>
                            <a href="/payments/status?reference=${orderReference}" class="btn btn-primary" style="width:100%"><i class="fa-solid fa-eye"></i> Angalia Hali ya Malipo</a>
                            <button type="button" onclick="closeTimeoutModal()" class="btn btn-ghost" style="width:100%">Funga</button>
                        </div>
                    </div>
                </div>`;
        }
        window.closeTimeoutModal = function () { const el=document.getElementById('timeoutModal'); if(el) el.remove(); resetButton(); };
    });
    </script>
</body>
</html>
