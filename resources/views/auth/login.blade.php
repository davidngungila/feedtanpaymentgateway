<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" type="image/png" href="/favicon-64.png">
    <title>Sign in · Feedtan Payments</title>
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
            font-family:'Raleway',sans-serif;background:var(--coffee-900);
            background-image:radial-gradient(circle at 85% 15%, rgba(212,162,76,.16), transparent 45%), radial-gradient(circle at 10% 90%, rgba(194,89,43,.18), transparent 45%);
            padding:24px;color:var(--ink);
        }
        .login-card{
            width:100%;max-width:420px;background:var(--sand-50);border-radius:var(--radius-lg);
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
        h1{font-size:23px;text-align:center;color:var(--coffee-900);margin:22px 0 6px;}
        .sub{text-align:center;color:var(--ink-soft);font-size:13.8px;margin-bottom:26px;}
        .field{margin-bottom:16px;}
        .field label{display:block;font-size:12.5px;font-weight:600;color:var(--coffee-700);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em;}
        .field input{
            width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:var(--radius-sm);
            background:var(--white);font-size:14.5px;color:var(--ink);transition:border-color .15s, box-shadow .15s;
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
        .error{
            background:var(--danger-100);color:var(--danger);border-radius:10px;padding:12px 14px;
            font-size:13px;font-weight:600;margin-bottom:16px;
        }
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
        .hint{margin-top:22px;text-align:center;font-size:12px;color:var(--ink-soft);background:var(--sand-100);border:1px dashed var(--line);border-radius:10px;padding:12px;line-height:1.7;}
        .hint code{background:var(--white);border:1px solid var(--line);border-radius:5px;padding:1px 6px;font-size:11.5px;color:var(--coffee-700);}
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
        <h1>Welcome back</h1>
        <p class="sub">Sign in to the collections control room.<br>Protected accounts continue to two-factor verification.</p>

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@company.co.tz" required autofocus autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn" id="loginBtn">Sign in securely</button>
            <label style="display:flex;align-items:center;gap:8px;margin-top:14px;font-size:13px;color:var(--ink-soft);cursor:pointer;">
                <input type="checkbox" name="remember" value="1" style="width:auto;accent-color:var(--terracotta-600);"> Keep me signed in on this device
            </label>
        </form>

        <p class="foot">M-Pesa · Airtel Money · Mixx by Yas · HaloPesa · T-Pesa</p>
    </div>

    @if ($errors->any())
    <div class="modal-backdrop show" id="errorModal">
        <div class="modal" role="alertdialog" aria-modal="true" aria-labelledby="errorTitle">
            <div class="modal-ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
            <h2 id="errorTitle">Sign in failed</h2>
            <p>{{ $errors->first() }}</p>
            <button type="button" class="btn" onclick="closeErrorModal()">Try again</button>
        </div>
    </div>
    @endif

    <div class="modal-backdrop" id="loadingModal">
        <div class="modal" role="status" aria-live="polite">
            <div class="spinner"></div>
            <h2>Signing you in…</h2>
            <p>Verifying your credentials, please wait.</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e){
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'Signing in…';
            document.getElementById('loadingModal').classList.add('show');
        });

        function closeErrorModal(){
            document.getElementById('errorModal').classList.remove('show');
            document.getElementById('email').focus();
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