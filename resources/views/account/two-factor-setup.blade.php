@extends('layouts.app')

@section('title', 'Set up two-factor authentication')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Set up two-factor authentication</h2>
        <p class="sub">Link your authenticator app, then verify — every future sign-in will ask for a code.</p>
    </div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('account.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel" id="setupPanel">
    <div class="settings-section"><h4>1 · Scan the QR code</h4></div>
    <div style="background:var(--sand-100);border:1px dashed var(--line);border-radius:12px;padding:22px;text-align:center;margin-bottom:18px;">
        <div id="otpauthQr" data-uri="{{ $uri }}" style="display:inline-block;margin:0 auto 10px;"></div>
        <p class="prov-hint">Scan with Google Authenticator, Authy, Microsoft Authenticator or 1Password.</p>
    </div>

    <div class="settings-section"><h4>2 · Or enter the key manually</h4></div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
        <code id="setupKey" style="font-family:ui-monospace,monospace;background:var(--sand-100);border:1px solid var(--line);border-radius:8px;padding:10px 14px;font-size:14px;font-weight:700;letter-spacing:.05em;user-select:all;">{{ chunk_split($secret, 4, ' ') }}</code>
        <button type="button" class="btn btn-ghost btn-sm" onclick="copySetupKey(this)">Copy key</button>
    </div>

    <div class="settings-section"><h4>3 · Verify &amp; enable</h4></div>
    <form id="confirm2faForm" method="POST" action="{{ route('account.two-factor.confirm') }}">
        @csrf
        <div class="field">
            <label>6-digit code from your app *</label>
            <input type="text" name="code" inputmode="numeric" maxlength="6" placeholder="••••••" required autocomplete="one-time-code" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;text-align:center;letter-spacing:.3em;font-size:18px;font-weight:700;">
        </div>
        <button type="submit" class="btn btn-primary" id="confirm2faBtn" style="width:100%;">Verify &amp; enable</button>
    </form>

    <div id="codesResult" style="display:none;margin-top:18px;">
        <div class="settings-section"><h4>Recovery codes — save them now</h4></div>
        <p class="prov-hint">Each code works once if you lose your device. They will never be shown again.</p>
        <div id="codesGrid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin:12px 0;"></div>
        <a href="{{ route('account.index') }}" class="btn btn-primary" style="width:100%;text-decoration:none;">I've saved my codes</a>
    </div>
</div>

<div class="settings-panel">
    <h3 style="margin-top:0;">How it works</h3>
    <ol class="detail-list">
        <li><strong>Install</strong> an authenticator app on your phone.</li>
        <li><strong>Scan</strong> the QR code — or type the setup key.</li>
        <li><strong>Enter</strong> the 6-digit code to prove it works.</li>
        <li>From then on, <strong>every sign-in</strong> needs password + code.</li>
    </ol>
    <div class="kv"><span class="k">Account</span><span class="v">{{ $user->email }}</span></div>
    <div class="kv"><span class="k">Code refresh</span><span class="v">every 30 seconds</span></div>
    <div class="kv"><span class="k">Recovery codes</span><span class="v">8 single-use backups</span></div>
    <div class="kv"><span class="k">Disable anytime</span><span class="v">with your password</span></div>
</div>
</div>
@endsection

@section('scripts')
<script src="/vendor/qrcode/qrcode.js"></script>
<script>
(function(){
    var host = document.getElementById('otpauthQr');
    if(host && typeof qrcode !== 'undefined'){
        try {
            var qr = qrcode(0, 'M');
            qr.addData(host.dataset.uri);
            qr.make();
            host.innerHTML = qr.createSvgTag(4, 2);
        } catch(e){ console.error('QR failed:', e); }
    }
    window.copySetupKey = function(btn){
        navigator.clipboard.writeText('{{ $secret }}').then(function(){
            var t = btn.textContent; btn.textContent = 'Copied ✓';
            setTimeout(function(){ btn.textContent = t; }, 1600);
        });
    };
    var form = document.getElementById('confirm2faForm');
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var btn = document.getElementById('confirm2faBtn');
        btn.disabled = true;
        showGlobalLoader && showGlobalLoader('Verifying…');
        fetch(form.action, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json'},
            body: new FormData(form)
        }).then(function(r){ return r.json().then(function(d){ return {ok: r.ok, d: d}; }); })
        .then(function(res){
            hideGlobalLoader && hideGlobalLoader();
            btn.disabled = false;
            if(res.ok && res.d.success){
                toast('Two-factor enabled.', 'success');
                document.getElementById('codesResult').style.display = 'block';
                var grid = document.getElementById('codesGrid');
                grid.innerHTML = '';
                (res.d.recovery_codes || []).forEach(function(c){
                    var box = document.createElement('div');
                    box.style.cssText = 'background:var(--sand-100);border:1px solid var(--line);border-radius:8px;padding:10px;font-family:ui-monospace,monospace;font-size:13px;font-weight:700;text-align:center;';
                    box.textContent = c;
                    grid.appendChild(box);
                });
                form.style.display = 'none';
                document.getElementById('codesResult').scrollIntoView({behavior: 'smooth'});
            } else {
                toast((res.d && res.d.message) || 'Verification failed.', 'error');
            }
        }).catch(function(err){
            hideGlobalLoader && hideGlobalLoader();
            btn.disabled = false;
            console.error(err);
            toast('Something went wrong.', 'error');
        });
    });
})();
</script>
@endsection
