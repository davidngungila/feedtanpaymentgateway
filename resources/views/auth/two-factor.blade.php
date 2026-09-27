<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" type="image/png" href="/favicon-64.png">
    <title>Two-factor verification · Feedtan Payments</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        :root{
            --sand-50:#FBF7EF;--sand-100:#F4ECDC;--sand-200:#E9DCC0;
            --coffee-900:#2A1B10;--coffee-800:#3B2718;--coffee-700:#4D3422;--coffee-500:#7A5C42;--coffee-300:#A98968;
            --terracotta-600:#C2592B;--terracotta-500:#D06B3A;--terracotta-100:#F6E1D3;
            --acacia-600:#5E6E3F;--acacia-100:#E2E7D4;
            --gold-500:#D4A24C;--gold-100:#F7E9CB;
            --ink:#241408;--ink-soft:#6B5A48;--line:#E4D7C2;--white:#FFF;
            --danger:#B33A3A;--danger-100:#F6DCDA;
            --radius-sm:8px;--radius-md:14px;--radius-lg:20px;
            --shadow-sm:0 1px 2px rgba(42,27,16,.08);
            --shadow-md:0 8px 24px rgba(42,27,16,.10);
            --shadow-lg:0 20px 48px rgba(42,27,16,.18);
        }
        *{box-sizing:border-box;}
        body{
            margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
            font-family:'Raleway',sans-serif;background:#FFFFFF;
            padding:24px;color:var(--ink);
        }
        .login-card{
            width:100%;max-width:420px;background:var(--white);border:1px solid var(--line);border-radius:var(--radius-lg);
            box-shadow:var(--shadow-lg);padding:40px 38px;animation:riseIn .4s cubic-bezier(.2,.8,.2,1);
        }
        @keyframes riseIn{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
        .brand{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:8px;}
        .brand-mark{
            width:42px;height:42px;border-radius:11px;flex:none;color:#fff;display:flex;align-items:center;justify-content:center;
            background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));box-shadow:var(--shadow-md);
        }
        .brand-mark svg{width:22px;height:22px;}
        .brand-text strong{display:block;font-size:17px;color:var(--coffee-900);letter-spacing:-0.01em;}
        .brand-text span{display:block;font-size:11px;letter-spacing:.07em;text-transform:uppercase;color:var(--terracotta-600);font-weight:700;}
        .shield{
            width:64px;height:64px;border-radius:18px;margin:26px auto 0;display:flex;align-items:center;justify-content:center;
            background:var(--acacia-100);color:var(--acacia-600);
        }
        .shield svg{width:30px;height:30px;}
        h1{font-size:21px;text-align:center;color:var(--coffee-900);margin:16px 0 6px;}
        .sub{text-align:center;color:var(--ink-soft);font-size:13.5px;margin-bottom:24px;line-height:1.6;}
        .sub b{color:var(--coffee-700);}
        .field{margin-bottom:16px;}
        .field label{display:block;font-size:12.5px;font-weight:600;color:var(--coffee-700);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em;}
        .field input{
            width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:var(--radius-sm);
            background:var(--white);font-size:16px;letter-spacing:.35em;text-align:center;color:var(--ink);
            transition:border-color .15s, box-shadow .15s;
        }
        .field input:focus{outline:none;border-color:var(--terracotta-500);box-shadow:0 0 0 3px var(--terracotta-100);}
        .btn{
            width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;
            border:none;border-radius:var(--radius-sm);font-weight:700;font-size:14.5px;color:#fff;cursor:pointer;
            background:var(--terracotta-600);box-shadow:0 6px 16px rgba(194,89,43,.32);transition:background .15s, transform .12s;margin-top:6px;
        }
        .btn:hover{background:var(--terracotta-500);}
        .btn:active{transform:translateY(1px);}
        .btn:disabled{opacity:.7;cursor:not-allowed;}
        .error{background:var(--danger-100);color:var(--danger);border-radius:10px;padding:12px 14px;font-size:13px;font-weight:600;margin-bottom:16px;}
        .modal-backdrop{position:fixed;inset:0;background:rgba(42,27,16,.55);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;padding:20px;z-index:100;}
        .modal-backdrop.show{display:flex;}
        .modal{width:100%;max-width:380px;background:var(--sand-50);border-radius:16px;box-shadow:var(--shadow-lg);padding:28px;text-align:center;animation:popIn .25s cubic-bezier(.2,.8,.2,1);}
        @keyframes popIn{from{opacity:0;transform:scale(.94) translateY(8px);}to{opacity:1;transform:scale(1) translateY(0);}}
        .modal-ico{width:52px;height:52px;border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;background:var(--danger-100);color:var(--danger);}
        .modal-ico svg{width:24px;height:24px;}
        .modal h2{font-size:17px;margin:0 0 8px;color:var(--coffee-900);}
        .modal p{font-size:13.5px;color:var(--ink-soft);margin:0 0 20px;line-height:1.6;}
        .spinner{width:46px;height:46px;border-radius:50%;margin:0 auto 14px;border:4px solid var(--sand-200);border-top-color:var(--terracotta-600);animation:spin 0.8s linear infinite;}
        @keyframes spin{to{transform:rotate(360deg);}}
        .alt{margin-top:18px;text-align:center;}
        .alt a,.alt button{background:none;border:none;color:var(--terracotta-600);font-weight:700;font-size:13px;cursor:pointer;text-decoration:none;padding:0;}
        .alt button:hover{text-decoration:underline;}
        .foot{margin-top:20px;text-align:center;font-size:11.5px;color:var(--coffee-300);}
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <div class="brand-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
            </div>
            <div class="brand-text">
                <strong>Feedtan Payments</strong>
                <span>Collections · Mobile Money</span>
            </div>
        </div>
        <div class="shield">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path><path d="m9 12 2 2 4-4"></path></svg>
        </div>
        <h1>Two-factor verification</h1>
        <p class="sub">Enter the code from your authenticator app to finish signing in as <b>{{ $email }}</b>.</p>

        <form id="otpForm" method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <div class="field">
                <label for="code">Authentication code</label>
                <input type="text" id="code" name="code" inputmode="numeric" maxlength="6" placeholder="••••••" autofocus autocomplete="one-time-code">
            </div>
            <button type="submit" class="btn" id="verifyBtn">Verify &amp; sign in</button>
        </form>

        <div class="alt">
            <a href="#" id="recoveryToggle">Use a recovery code instead</a>
        </div>

        <form method="POST" action="{{ route('two-factor.cancel') }}" class="foot">
            @csrf
            <button type="submit" style="background:none;border:none;color:var(--coffee-300);font-size:11.5px;cursor:pointer;text-decoration:underline;">Cancel and return to sign in</button>
        </form>
    </div>

    @if ($errors->any())
    <div class="modal-backdrop show" id="errorModal">
        <div class="modal" role="alertdialog" aria-modal="true" aria-labelledby="errorTitle">
            <div class="modal-ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
            <h2 id="errorTitle">Verification failed</h2>
            <p>{{ $errors->first() }}</p>
            <button type="button" class="btn" onclick="closeErrorModal()">Try again</button>
        </div>
    </div>
    @endif

    <div class="modal-backdrop" id="loadingModal">
        <div class="modal" role="status" aria-live="polite">
            <div class="spinner"></div>
            <h2>Verifying…</h2>
            <p>Checking your code, please wait.</p>
        </div>
    </div>

    <script>
        const plain = document.querySelector('#otpForm');
        const label = document.querySelector('#otpForm label');
        const input = document.querySelector('#code');
        const btn = document.querySelector('#verifyBtn');
        const toggle = document.querySelector('#recoveryToggle');

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const isRecovery = this.textContent.includes('recovery code');
            if (isRecovery) {
                this.textContent = 'Use an authenticator code instead';
                label.textContent = 'Recovery code';
                input.placeholder = 'XXXX-XXXX-XXXX-XXXX';
                input.maxLength = 20;
                input.inputMode = '';
                input.value = '';
            } else {
                this.textContent = 'Use a recovery code instead';
                label.textContent = 'Authentication code';
                input.placeholder = '••••••';
                input.maxLength = 6;
                input.inputMode = 'numeric';
                input.value = '';
            }
        });

        input.addEventListener('input', function () {
            if (btn.disabled) return;
            const complete = this.maxLength === 6
                ? /^\d{6}$/.test(this.value)
                : /^[A-Z0-9]{4}(?:-[A-Z0-9]{4}){3}$/.test(this.value);
            if (complete) {
                btn.disabled = true;
                btn.textContent = 'Verifying…';
                plain.submit();
            }
        });

        plain.addEventListener('submit', function () {
            btn.disabled = true;
            btn.textContent = 'Verifying…';
            document.getElementById('loadingModal').classList.add('show');
        });

        function closeErrorModal(){
            document.getElementById('errorModal').classList.remove('show');
            input.value = '';
            input.focus();
        }
        document.getElementById('errorModal')?.addEventListener('click', function(e){
            if(e.target === this) closeErrorModal();
        });
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape') closeErrorModal();
        });
    </script>
</body>
</html>