<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hali ya Malipo — {{ $orderReference }} — FeedTan CMG</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--sand-50:#FBF7EF;--sand-100:#F4ECDC;--sand-200:#E9DCC0;--coffee-900:#2A1B10;--coffee-700:#4D3422;--terracotta-600:#C2592B;--terracotta-100:#F6E1D3;--acacia-600:#5E6E3F;--acacia-100:#E2E7D4;--gold-500:#D4A24C;--ink-soft:#6B5A48;--line:#E4D7C2;--white:#fff;--danger:#B33A3A;--radius-md:14px;--shadow-sm:0 1px 2px rgba(42,27,16,.08);--shadow-lg:0 20px 48px rgba(42,27,16,.18)}
        *{box-sizing:border-box}html,body{height:100%}body{margin:0;font-family:'Raleway',sans-serif;background:var(--sand-50);color:var(--ink-soft);-webkit-font-smoothing:antialiased}
        .topbar{position:sticky;top:0;z-index:100;height:72px;background:rgba(251,247,239,.86);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);display:flex;align-items:center;gap:16px;padding:0 28px}
        .sb-mark{width:38px;height:38px;border-radius:10px;flex:none;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));display:flex;align-items:center;justify-content:center;color:#fff}
        .view-wrap{padding:28px;flex:1;display:flex;align-items:center;justify-content:center;min-height:calc(100vh - 72px)}
        .card{width:100%;max-width:460px;background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-lg);overflow:hidden}
        .card-head{padding:22px;text-align:center;color:#fff}
        .card-head h1{margin:0;font-size:18px;color:#fff}
        .card-body{padding:22px;display:flex;flex-direction:column;gap:14px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 18px;border-radius:8px;border:none;font-weight:600;font-size:14px;text-decoration:none;cursor:pointer}
        .btn-primary{background:var(--terracotta-600);color:#fff}
        .btn-ghost{background:transparent;color:var(--coffee-700);border:1.5px solid var(--line)}
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;height:64px;background:var(--white);border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-around;gap:4px;padding:4px 6px calc(4px + env(safe-area-inset-bottom));z-index:150}
        .bnav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 4px;border-radius:10px;color:var(--ink-soft);font-size:9.5px;font-weight:600;text-decoration:none;border:none;background:none}
        .bnav-item.active{color:var(--terracotta-600);background:var(--terracotta-100)}
        @media(max-width:640px){.view-wrap{padding:16px;padding-bottom:76px}.topbar{padding:0 14px;height:64px}}
        @media(min-width:641px){.bottom-nav{display:none}}
    </style>
</head>
<body>
    <header class="topbar">
        <div class="sb-mark"><i class="fa-solid fa-leaf"></i></div>
        <div style="line-height:1.2"><strong style="color:var(--coffee-900);font-size:15.5px">FeedTan CMG</strong><span style="color:var(--ink-soft);font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600">Member Payments</span></div>
        <a href="{{ route('payments.public') }}" class="btn btn-ghost" style="margin-left:auto">Rudi</a>
    </header>
    <div class="view-wrap">
        <div class="card">
            <div class="card-head" id="headBox" style="background:linear-gradient(155deg,var(--coffee-900),var(--coffee-700))">
                <p style="font-size:10.5px;letter-spacing:.09em;text-transform:uppercase;font-weight:700;opacity:.7;margin:0 0 4px">FeedTan CMG</p>
                <h1>Hali ya Malipo</h1>
                <p style="font-size:12.5px;opacity:.9;margin:6px 0 0">Reference: <span style="font-family:ui-monospace,monospace;font-weight:700">{{ $orderReference ?? '—' }}</span></p>
            </div>
            <div class="card-body">
                <div id="statusBox" style="border:1px solid var(--line);border-radius:12px;padding:16px;text-align:center;background:var(--sand-100)">
                    <div id="statusIcon" style="width:48px;height:48px;border-radius:50%;background:var(--gold-500);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:20px"><i class="fa-solid fa-spinner fa-spin"></i></div>
                    <div id="statusLabel" style="font-size:14px;font-weight:700;color:var(--coffee-900)">Inaangalia…</div>
                    <div id="statusDetail" style="font-size:12.5px;color:var(--ink-soft);margin-top:4px">Tunaangalia hali kutoka ClickPesa. SMS itatumwa papo hapo malipo yakikamilika.</div>
                </div>
                <div style="height:6px;background:var(--sand-100);border-radius:20px;overflow:hidden;border:1px solid var(--line)"><div id="progressBar" style="height:100%;background:linear-gradient(90deg,var(--terracotta-600),var(--gold-500));width:0%;transition:width .3s"></div></div>
                <!-- Weka Reference - always on its page -->
                <div style="background:var(--sand-100);border:1px solid var(--line);border-radius:10px;padding:12px">
                    <label for="refInput" style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:var(--coffee-700);display:block;margin-bottom:6px">Weka Reference ya Malipo / Risiti *</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" id="refInput" value="{{ $orderReference ?? '' }}" placeholder="mf. PAY20260924083403818" style="flex:1;padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;font-size:13px;text-transform:uppercase">
                        <button type="button" id="checkBtn" style="padding:10px 14px;background:var(--terracotta-600);color:#fff;border:none;border-radius:8px;font-weight:600;white-space:nowrap">Angalia</button>
                    </div>
                    <div style="font-size:10.5px;color:var(--ink-soft);margin-top:6px">Kila ukurasa utaomba Reference — weka hapa kisha Angalia / Pakua Risiti</div>
                </div>
                <div style="display:flex;gap:10px">
                    <a href="{{ route('payments.public') }}" class="btn" style="flex:1;background:var(--coffee-900);color:#fff">Rudi Kulipa</a>
                    <a id="receiptLink" href="{{ $orderReference ? route('payments.receipt.order', $orderReference) : '#' }}" target="_blank" class="btn btn-primary" style="flex:1;display:none"><i class="fa-solid fa-download"></i> Risiti</a>
                </div>
            </div>
        </div>
    </div>
<script>
const orderReference = @json($orderReference);
const POLLING_INTERVAL = 3000; const POLLING_DURATION = 180000;
let start = Date.now(); let timer=null;
function updateBar(){ const el=document.getElementById('progressBar'); if(!el) return; const pct=Math.min((Date.now()-start)/POLLING_DURATION*100,100); el.style.width=pct+'%'; }
function swReason(msg){
    if(!msg) return 'Hakuna maelezo.';
    const low=String(msg).toLowerCase();
    if(low.includes('overdraf') && low.includes('unsubsc')){
        const balM=String(msg).match(/source balance:\s*([\d.,]+)/i);
        const amtM=String(msg).match(/amount with fee:\s*([\d.,]+)/i);
        const fmt=n=>{ const v=Number(String(n).replace(/,/g,'')); return isNaN(v)? n : v.toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2}); };
        const bal=balM? fmt(balM[1]) : '—'; const amt=amtM? fmt(amtM[1]) : '—';
        return `Akaunti ya mkopo wa Overdraft haijasajiliwa. Salio halitoshi: TZS ${bal}, kiasi pamoja na tozo: TZS ${amt}.`;
    }
    if(low.includes('customer_canceled_pin') || low.includes('customer_cancelled_pin')){
        return 'Umeighairi kuweka PIN — ulipokea USSD lakini ukaghairi kabla ya kuweka PIN yako ya siri. Tafadhali jaribu tena na uweke PIN sahihi.';
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
        let out=String(msg).replace(/Insufficient source balance/gi,'Salio la chanzo halitoshi').replace(/Insufficient balance/gi,'Salio halitoshi').replace(/amount with fee/gi,'kiasi pamoja na tozo');
        return out;
    }
    return msg;
}
function setStatus(type,label,detail){
    const icon=document.getElementById('statusIcon'); const lbl=document.getElementById('statusLabel'); const det=document.getElementById('statusDetail'); const head=document.getElementById('headBox');
    lbl.textContent=label; det.innerHTML=detail;
    if(type==='success'){ icon.innerHTML='<i class="fa-solid fa-check"></i>'; icon.style.background='var(--acacia-600)'; head.style.background='linear-gradient(155deg,var(--acacia-600),#7A8450)'; document.getElementById('receiptLink').style.display='inline-flex'; }
    else if(type==='failed'){ icon.innerHTML='<i class="fa-solid fa-xmark"></i>'; icon.style.background='var(--danger)'; head.style.background='var(--danger)'; }
    else { icon.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>'; icon.style.background='var(--gold-500)'; }
}
async function check(){
    if(!orderReference){ setStatus('failed','Hakuna reference','Reference haipo kwenye URL.'); return; }
    try{
        const res = await fetch('{{ route('payments.api.status') }}', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({order_reference: orderReference}) });
        const j = await res.json(); if(!j.success){ setStatus('pending','Bado inaendelea','Tunangoja uthibitisho...'); return; }
        const s = String(j.status || j.data?.status || '').toUpperCase();
        if(['SUCCESS','SETTLED','COMPLETED','SUCCESSFUL'].includes(s)){ clearInterval(timer); setStatus('success','Malipo Yamekamilika ✓ — IMEKAMILIKA','Hongera! SMS ya uthibitisho imetumwa papo hapo.'); }
        else if(['FAILED','DECLINED','CANCELLED','ERROR','REVERSED'].includes(s)){
            clearInterval(timer);
            const raw=j.data?.reason || j.data?.message || j.data?.error || s;
            const sw=swReason(raw);
            const detail = sw!==raw ? `${sw}<br><span style="font-size:10.5px;color:var(--ink-soft)">Asili (EN): ${raw}</span>` : sw;
            setStatus('failed','Malipo Haujaweza — IMESHINDWA', detail);
        }
        else { setStatus('pending','Bado inaendelea', s ? s+' · Inasubiri uthibitisho wa USSD' : 'PENDING — Thibitisha USSD'); }
    }catch(e){ setStatus('pending','Bado inaendelea','Mtandao — tunajaribu tena...'); }
}
if(orderReference){ check(); timer=setInterval(()=>{ updateBar(); if(Date.now()-start >= POLLING_DURATION){ clearInterval(timer); setStatus('pending','Muda umeisha','Angalia tena baadaye.'); } else check(); }, POLLING_INTERVAL); setInterval(updateBar,500);} else setStatus('failed','Hakuna reference — Weka hapa chini','Andika Reference ya malipo hapa chini kisha bonyeza Angalia. Kila ukurasa utaomba Reference.');
document.getElementById('checkBtn')?.addEventListener('click', ()=>{ const v=document.getElementById('refInput').value.trim(); if(!v){ alert('Weka Reference ya malipo'); return; } const clean=v.replace(/[^A-Za-z0-9\-]/g,''); location.href='/payments/status?reference='+encodeURIComponent(clean); });
document.getElementById('refInput')?.addEventListener('keydown', e=>{ if(e.key==='Enter') document.getElementById('checkBtn').click(); });
</script>
    <nav class="bottom-nav" aria-label="Bottom nav">
        <a href="{{ route('payments.public') }}" class="bnav-item"><i class="fa-solid fa-house"></i><span>Nyumbani</span></a>
        <a href="{{ route('payments.status.page') }}" class="bnav-item active"><i class="fa-solid fa-eye"></i><span>Hali</span></a>
        <a href="{{ route('payments.receipt.lookup') }}" class="bnav-item"><i class="fa-solid fa-receipt"></i><span>Risiti</span></a>
        <a href="{{ route('payments.msaada') }}" class="bnav-item"><i class="fa-solid fa-headset"></i><span>Msaada</span></a>
    </nav>
</body>
</html>
