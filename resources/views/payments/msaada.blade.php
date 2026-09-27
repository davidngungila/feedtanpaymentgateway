<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Msaada — FeedTan CMG</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--sand-50:#FBF7EF;--sand-100:#F4ECDC;--line:#E4D7C2;--coffee-900:#2A1B10;--coffee-700:#4D3422;--terracotta-600:#C2592B;--terracotta-100:#F6E1D3;--acacia-600:#5E6E3F;--acacia-100:#E2E7D4;--gold-500:#D4A24C;--ink-soft:#6B5A48;--white:#fff;--radius-md:14px;--shadow-sm:0 1px 2px rgba(42,27,16,.08);--shadow-lg:0 20px 48px rgba(42,27,16,.18)}
        *{box-sizing:border-box}html,body{height:100%}body{margin:0;font-family:'Raleway',sans-serif;background:var(--sand-50);color:var(--coffee-900);-webkit-font-smoothing:antialiased}
        .topbar{position:sticky;top:0;z-index:100;height:56px;background:rgba(251,247,239,.86);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px;padding:0 14px}
        .sb-mark{width:32px;height:32px;border-radius:8px;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));display:flex;align-items:center;justify-content:center;color:#fff}
        .view-wrap{padding:16px;min-height:calc(100dvh - 56px);padding-bottom:80px}
        .container{max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:16px}
        .card{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden}
        .card-head{padding:16px 18px;background:linear-gradient(155deg,var(--coffee-900),var(--coffee-700));color:#fff;text-align:center}
        .card-body{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
        .section-title{font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--coffee-700);display:flex;align-items:center;gap:8px;margin:4px 0}
        .section-title::before{content:"";width:18px;height:2px;background:var(--gold-500);border-radius:2px}
        .contact-row{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--sand-100);border:1px solid var(--line);border-radius:8px}
        .contact-ico{width:32px;height:32px;border-radius:8px;background:var(--white);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:var(--terracotta-600);flex:none}
        .contact-row b{font-size:13px}
        .contact-row span{font-size:12px;color:var(--ink-soft)}
        .step{display:flex;gap:10px;align-items:flex-start}
        .step-num{width:26px;height:26px;border-radius:50%;background:var(--terracotta-600);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex:none}
        .step p{margin:0;font-size:12.5px;line-height:1.5}
        .step p strong{color:var(--coffee-900)}
        .faq{border:1px solid var(--line);border-radius:8px;background:var(--white);overflow:hidden}
        .faq details{border-bottom:1px solid var(--line)}
        .faq details:last-child{border-bottom:none}
        .faq summary{padding:12px 14px;font-size:12.5px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
        .faq summary::-webkit-details-marker{display:none}
        .faq summary i{color:var(--ink-soft);font-size:11px;transition:transform .2s}
        .faq details[open] summary i{transform:rotate(180deg)}
        .faq .answer{padding:0 14px 12px;font-size:12px;color:var(--ink-soft);line-height:1.6}
        .btn{padding:10px 14px;border-radius:8px;border:none;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:13px}
        .btn-primary{background:var(--terracotta-600);color:#fff}
        .btn-ghost{background:transparent;border:1.5px solid var(--line);color:var(--coffee-700)}
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;height:64px;background:var(--white);border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-around;gap:4px;padding:4px 6px calc(4px + env(safe-area-inset-bottom));z-index:150}
        .bnav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 4px;border-radius:10px;color:var(--ink-soft);font-size:9.5px;font-weight:600;text-decoration:none;border:none;background:none}
        .bnav-item.active{color:var(--terracotta-600);background:var(--terracotta-100)}
        @media(min-width:641px){.view-wrap{padding:20px}.topbar{height:64px;padding:0 18px}}
    </style>
</head>
<body>
    <header class="topbar">
        <div class="sb-mark"><i class="fa-solid fa-leaf"></i></div>
        <div style="line-height:1.2"><strong style="font-size:14px;color:var(--coffee-900)">FeedTan CMG</strong><span style="font-size:10px;color:var(--ink-soft);letter-spacing:.06em;text-transform:uppercase;font-weight:600">Msaada & Mawasiliano</span></div>
        <a href="{{ route('payments.public') }}" style="margin-left:auto;font-size:12px;font-weight:600;color:var(--terracotta-600)">Lipa</a>
    </header>

    <div class="view-wrap">
        <div class="container">
            <div class="card">
                <div class="card-head">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><i class="fa-solid fa-headset"></i></div>
                    <h2 style="margin:0;font-size:16px;color:#fff">Tunakusaidiaje?</h2>
                    <p style="margin:6px 0 0;font-size:12px;opacity:.9">Wasiliana nasi moja kwa moja — hakuna login inahitajika</p>
                </div>
                <div class="card-body">
                    <div class="section-title">Wasiliana Nasi</div>
                    <a href="tel:+255714000001" class="contact-row" style="text-decoration:none;color:inherit">
                        <div class="contact-ico"><i class="fa-solid fa-phone"></i></div>
                        <div style="flex:1"><b>+255 714 000 001</b><br><span>Piga simu — Saa 08:00 - 17:00</span></div>
                        <i class="fa-solid fa-chevron-right" style="color:var(--ink-soft);font-size:11px"></i>
                    </a>
                    <a href="https://wa.me/255756123456" target="_blank" class="contact-row" style="text-decoration:none;color:inherit">
                        <div class="contact-ico" style="color:var(--acacia-600)"><i class="fa-brands fa-whatsapp"></i></div>
                        <div style="flex:1"><b>WhatsApp: +255 756 123 456</b><br><span>Chat moja kwa moja</span></div>
                        <i class="fa-solid fa-chevron-right" style="color:var(--ink-soft);font-size:11px"></i>
                    </a>
                    <a href="mailto:hello@feedtancmg.org" class="contact-row" style="text-decoration:none;color:inherit">
                        <div class="contact-ico"><i class="fa-solid fa-envelope"></i></div>
                        <div style="flex:1"><b>hello@feedtancmg.org</b><br><span>Barua pepe</span></div>
                        <i class="fa-solid fa-chevron-right" style="color:var(--ink-soft);font-size:11px"></i>
                    </a>
                    <div class="contact-row">
                        <div class="contact-ico"><i class="fa-solid fa-location-dot"></i></div>
                        <div><b>Ofisi: Mikocheni, Dar es Salaam</b><br><span style="font-size:12px;color:var(--ink-soft)">FeedTan CMG — Let's Grow Together</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title">Jinsi ya Kulipa</div>
                    <div class="step"><div class="step-num">1</div><p><strong>Jaza fomu</strong> — jina, namba ya simu (255...), kiasi, na chagua <i>Malipo Kwaajili Ya</i></p></div>
                    <div class="step"><div class="step-num">2</div><p><strong>Bonyeza Lipa Sasa</strong> — USSD itatumwa moja kwa moja kwenye simu yako</p></div>
                    <div class="step"><div class="step-num">3</div><p><strong>Thibitisha kwa PIN</strong> — weka PIN yako ya siri kwenye dirisha la USSD</p></div>
                    <div class="step"><div class="step-num" style="background:var(--acacia-600)">4</div><p><strong>Pokea SMS</strong> — uthibitisho utatumwa papo hapo, na risiti utaipata kwa Reference</p></div>
                    <div style="display:flex;gap:8px;margin-top:4px">
                        <a href="{{ route('payments.public') }}" class="btn btn-primary" style="flex:1"><i class="fa-solid fa-wallet"></i> Lipa Sasa</a>
                        <a href="{{ route('payments.status.page') }}" class="btn btn-ghost" style="flex:1"><i class="fa-solid fa-eye"></i> Angalia Hali</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title">Maswali Yanayoulizwa</div>
                    <div class="faq">
                        <details>
                            <summary>Sitapata USSD, nifanye nini? <i class="fa-solid fa-chevron-down"></i></summary>
                            <div class="answer">Hakikisha namba uliyoingiza ni sahihi (255...), simu ina mtandao, na jaribu tena baada ya dakika 1. Ukikosa, wasiliana nasi na Reference yako.</div>
                        </details>
                        <details>
                            <summary>Nimeghairi PIN kimakosa <i class="fa-solid fa-chevron-down"></i></summary>
                            <div class="answer">Ukighairi PIN, malipo hufeli kama <i>CUSTOMER_CANCELED_PIN</i>. Tafsiri: Umeighairi kuweka PIN. Jaribu tena na uweke PIN sahihi.</div>
                        </details>
                        <details>
                            <summary>Salio halitoshi — Overdraft <i class="fa-solid fa-chevron-down"></i></summary>
                            <div class="answer">Kama unaona <i>Overdraft haijasajiliwa / Salio halitoshi</i>, weka akiba ya kutosha au wasiliana na msimamizi wa FeedTan kuongeza salio la chanzo.</div>
                        </details>
                        <details>
                            <summary>Risiti na Reference <i class="fa-solid fa-chevron-down"></i></summary>
                            <div class="answer">Kila malipo una Reference (mf. PAY...). Hutumwa kwa SMS papo hapo. Weka Reference kwenye ukurasa wa <i>Risiti</i> kupakua PDF.</div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="bottom-nav" aria-label="Bottom nav">
        <a href="{{ route('payments.public') }}" class="bnav-item"><i class="fa-solid fa-house"></i><span>Nyumbani</span></a>
        <a href="{{ route('payments.status.page') }}" class="bnav-item"><i class="fa-solid fa-eye"></i><span>Hali</span></a>
        <a href="{{ route('payments.receipt.lookup') }}" class="bnav-item"><i class="fa-solid fa-receipt"></i><span>Risiti</span></a>
        <a href="{{ route('payments.msaada') }}" class="bnav-item active"><i class="fa-solid fa-headset"></i><span>Msaada</span></a>
    </nav>
</body>
</html>
