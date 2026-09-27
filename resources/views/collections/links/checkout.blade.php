<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lipa {{ number_format($link->request->amount, 0) }} TZS · FeedTan Pay</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Raleway',sans-serif;background:#2A1B10;background-image:radial-gradient(circle at 85% 15%, rgba(212,162,76,.16), transparent 45%),radial-gradient(circle at 10% 90%, rgba(194,89,43,.18), transparent 45%);padding:20px;color:#241408}
        .card{width:100%;max-width:460px;background:#FBF7EF;border-radius:20px;padding:32px 28px;box-shadow:0 20px 48px rgba(0,0,0,.35)}
        .brand{display:flex;align-items:center;gap:10px;margin-bottom:6px}
        .brand-mark{width:34px;height:34px;border-radius:9px;background:linear-gradient(155deg,#C2592B,#D4A24C);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;flex:none}
        .brand small{display:block;font-size:10px;letter-spacing:.07em;color:#C2592B;font-weight:700;text-transform:uppercase}
        .brand strong{font-size:15px;color:#2A1B10}
        .amt{font-size:36px;font-weight:800;color:#2A1B10;margin:8px 0 2px}
        .kv{display:flex;justify-content:space-between;gap:10px;font-size:12.5px;padding:7px 0;border-bottom:1px dashed #E4D7C2}
        .kv:last-child{border-bottom:none}
        .kv .k{color:#6B5A48}
        .kv .v{font-weight:700;color:#2A1B10;text-align:right}
        .mono{font-family:ui-monospace,monospace}
        .field{margin-bottom:14px;margin-top:14px}
        .field>label{display:block;font-size:12px;font-weight:700;margin-bottom:8px;color:#4D3422;text-transform:uppercase;letter-spacing:.04em}
        .field input[type="text"]{width:100%;padding:12px 14px;border:1.5px solid #E4D7C2;border-radius:8px;font-size:14px;background:#fff}
        .field input:focus{outline:none;border-color:#D06B3A;box-shadow:0 0 0 3px #F6E1D3}
        .nets{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        .net{display:flex;align-items:center;gap:8px;border:1.5px solid #E4D7C2;border-radius:10px;padding:10px 12px;font-size:13px;font-weight:700;cursor:pointer;background:#fff;transition:border-color .15s,box-shadow .15s}
        .net:has(input:checked){border-color:#C2592B;box-shadow:0 0 0 2px #F6E1D3}
        .net input{accent-color:#C2592B}
        .net .dot{width:10px;height:10px;border-radius:50%;flex:none}
        .net small{display:block;font-weight:400;color:#6B5A48;font-size:10.5px}
        .btn{width:100%;padding:14px;border:none;border-radius:8px;background:#C2592B;color:#fff;font-weight:700;font-size:15px;cursor:pointer;margin-top:4px}
        .btn:hover{background:#D06B3A}
        .err{background:#F6DCDA;color:#B33A3A;border-radius:10px;padding:12px;font-size:13px;margin:12px 0}
        .steps{margin:16px 0 0;padding:14px;background:#F4ECDC;border-radius:12px;font-size:12.5px;line-height:1.9;color:#4D3422}
        .steps strong{color:#2A1B10}
        .secure{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;font-size:11.5px;color:#A98968}
        .loader{position:fixed;inset:0;background:rgba(42,27,16,.55);display:none;align-items:center;justify-content:center;z-index:100;padding:20px;}
        .loader.show{display:flex;}
        .loader-card{background:#FBF7EF;border-radius:16px;padding:28px 36px;text-align:center;}
        .spinner{width:44px;height:44px;border-radius:50%;margin:0 auto 12px;border:4px solid #E9DCC0;border-top-color:#C2592B;animation:spin .8s linear infinite;}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="brand-mark">F</div>
            <div><small>FeedTan Pay · {{ $link->code ?? 'link' }}</small><strong>Malipo ya Moja kwa Moja</strong></div>
        </div>

        <div class="amt">TZS {{ number_format($link->request->amount, 0) }}</div>

        <div style="margin:6px 0 4px;">
            <div class="kv"><span class="k">Kwaajili ya</span><span class="v">{{ $link->request->purpose ?? $link->request->description ?? 'Malipo' }}</span></div>
            @if ($link->request->description && $link->request->purpose)
            <div class="kv"><span class="k">Maelezo</span><span class="v">{{ $link->request->description }}</span></div>
            @endif
            <div class="kv"><span class="k">Reference</span><span class="v mono">{{ $link->request->reference }}</span></div>
            <div class="kv"><span class="k">Mteja</span><span class="v">{{ $link->request->customer?->name ?? '—' }}</span></div>
            <div class="kv"><span class="k">Link inaisha</span><span class="v">{{ $link->expires_at ? $link->expires_at->diffForHumans() : 'Haina ukomo' }}</span></div>
        </div>

        @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ $link->code ? route('collections.links.short.pay', $link->code) : route('collections.links.checkout.pay', $link->token) }}">
            @csrf
            <div class="field"><label>Jina lako *</label><input type="text" name="customer_name" value="{{ old('customer_name', $link->request->customer?->name) }}" required autocomplete="name"></div>
            <div class="field"><label>Namba ya simu *</label><input type="text" name="phone" value="{{ old('phone') }}" required placeholder="255712345678" inputmode="numeric" autocomplete="tel"></div>
            <p style="font-size:12px;color:#6B5A48;text-align:center;">Mtandao (M-Pesa · Airtel · Mixx · HaloPesa · T-Pesa) utatambuliwa kiotomatiki kutoka namba yako.</p>
            <button class="btn" type="submit">Lipa TZS {{ number_format($link->request->amount, 0) }} sasa</button>
        </form>

        <div class="steps">
            <strong>Jinsi inavyofanya kazi:</strong><br>
            1. Chagua mtandao na thibitisha namba yako.<br>
            2. Utapokea <strong>USSD push</strong> — weka PIN kuthibitisha.<br>
            3. Utapata <strong>risiti na SMS</strong> mara malipo yakikamilika.
        </div>
        <div class="secure">🔒 Muamala salama · Hakuna malipo bila PIN yako</div>
    </div>

    <div class="loader" id="pageLoader">
        <div class="loader-card" role="status" aria-live="polite">
            <div class="spinner"></div>
            <div style="font-weight:800;color:#2A1B10;">Inatuma…</div>
            <div style="font-size:12px;color:#6B5A48;margin-top:4px;">Tafadhali subiri.</div>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(){
        document.getElementById('pageLoader').classList.add('show');
    });
    </script>
</body>
</html>
