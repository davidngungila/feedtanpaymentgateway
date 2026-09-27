@extends('layouts.app')

@php
    use App\Support\TwoFactor;
@endphp

@section('title', 'Account & Security')

@section('head')
<style>
    /* Centered popups on this page - no right drawer */
    .modal-backdrop.show{align-items:center;justify-content:center;padding:20px;}
    .drawer,.modal{position:static;top:auto;right:auto;bottom:auto;width:100%;max-width:440px;max-height:calc(100vh - 40px);border-radius:20px;transform:none;transition:none;}
    .drawer-backdrop.show .drawer,.modal-backdrop.show .drawer,.modal-backdrop.show .modal{transform:none;}
</style>
@endsection

@section('content')
    <div class="view-head">
        <div>
            <h2>Account &amp; Security</h2>
            <p class="sub">Manage your sign-in security: password, two-factor authentication and active sessions.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="status-banner" style="background:var(--acacia-100);color:var(--acacia-600);border-radius:10px;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:20px;">{{ session('status') }}</div>
    @endif

    <div class="panel" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:20px;padding:24px clamp(20px,4vw,32px);flex-wrap:wrap;">
            <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;flex:none;display:flex;align-items:center;justify-content:center;background:var(--sand-100);color:#fff;font-weight:700;font-size:22px;border:3px solid var(--sand-50);box-shadow:var(--shadow-md);">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <span style="background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));width:100%;height:100%;display:flex;align-items:center;justify-content:center;">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                @endif
            </div>
            <div style="flex:1;min-width:200px;">
                <h3 style="font-size:19px;margin:0 0 4px;">{{ $user->name }}</h3>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="tag {{ $user->role === 'admin' ? 'tag-gold' : ($user->role === 'supervisor' ? 'tag-green' : 'tag-terracotta') }}">{{ ucfirst($user->role) }}</span>
                    <span class="tag {{ $user->is_active ? 'tag-green' : 'tag-grey' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="tag {{ $user->two_factor_enabled ? 'tag-green' : 'tag-grey' }}">{{ $user->two_factor_enabled ? '2FA enabled' : '2FA off' }}</span>
                </div>
            </div>
            <a class="btn btn-ghost" href="{{ route('profile.index') }}" style="text-decoration:none;">Edit profile</a>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head">
                <h3>Account</h3>
            </div>
            <div class="panel-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="dk">Name</div><div class="dv">{{ $user->name }}</div></div>
                    <div class="detail-item"><div class="dk">Email</div><div class="dv">{{ $user->email }}</div></div>
                    <div class="detail-item"><div class="dk">Role</div><div class="dv">{{ ucfirst($user->role) }}</div></div>
                    <div class="detail-item"><div class="dk">Cash point</div><div class="dv">{{ $user->agent?->name ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Phone</div><div class="dv">{{ $user->phone ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Member since</div><div class="dv">{{ $user->created_at->format('d M Y') }}</div></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Change password</h3>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('account.password') }}" data-password-form>
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="field"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
                    <div class="field"><label>New password</label><input type="password" name="password" required minlength="6" autocomplete="new-password"></div>
                    <div class="field"><label>Confirm new password</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>
                    <button type="submit" class="btn btn-primary">Update password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Two-factor authentication</h3>
            @if ($user->two_factor_enabled)
                <span class="tag tag-green">Enabled</span>
            @else
                <span class="tag tag-gold">Off</span>
            @endif
        </div>
        <div class="panel-body">
            @if ($user->two_factor_enabled)
                <p style="margin:0 0 18px;color:var(--ink-soft);font-size:14px;line-height:1.7;">
                    Two-factor authentication is on. Every sign-in now requires a code from your authenticator app.
                    Keep your recovery codes somewhere safe in case you lose access to your device.
                </p>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="button" class="btn btn-primary" onclick="openPasswordModal('Regenerate recovery codes', '{{ route('account.recovery-codes') }}', 'Generate codes')">Regenerate recovery codes</button>
                    <button type="button" class="btn btn-danger" onclick="openPasswordModal('Disable two-factor authentication', '{{ route('account.two-factor.disable') }}', 'Disable two-factor')">Disable two-factor</button>
                </div>
            @else
                <p style="margin:0 0 16px;color:var(--ink-soft);font-size:14px;line-height:1.7;">
                    Add an extra layer of security. Once enabled, every sign-in will also require a six-digit code from an authenticator app.
                </p>
                <a href="{{ route('account.two-factor.setup') }}" class="btn btn-primary" style="text-decoration:none;">Set up two-factor</a>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Active sessions</h3>
            <span class="link">{{ $sessions->count() }} other {{ $sessions->count() === 1 ? 'device' : 'devices' }}</span>
        </div>
        <div class="panel-body" style="padding:0;">
            @if ($currentSession)
                <div style="display:flex;align-items:center;gap:14px;padding:18px 20px;background:var(--acacia-100);">
                    <div style="width:38px;height:38px;border-radius:10px;background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--acacia-600);flex:none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <b style="display:block;font-size:14px;color:var(--coffee-900);">{{ $currentSession['device'] }}</b>
                        <span style="font-size:12.5px;color:var(--ink-soft);">{{ $currentSession['browser'] }} · IP {{ $currentSession['ip'] }} · Active now</span>
                    </div>
                    <span class="tag tag-green">This device</span>
                </div>
            @endif
            @if ($sessions->isEmpty())
                <p class="empty-state">No other active sessions.</p>
            @else
                @foreach ($sessions as $session)
                    <div style="display:flex;align-items:center;gap:14px;padding:16px 20px;border-top:1px solid var(--line);">
                        <div style="width:38px;height:38px;border-radius:10px;background:var(--sand-100);display:flex;align-items:center;justify-content:center;color:var(--coffee-500);flex:none;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><rect x="2" y="6" width="20" height="12" rx="2"></rect><line x1="6" y1="10" x2="10" y2="10"></line></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <b style="display:block;font-size:14px;color:var(--coffee-900);">{{ $session['device'] }} · {{ $session['browser'] }}</b>
                            <span style="font-size:12.5px;color:var(--ink-soft);">IP {{ $session['ip'] }} · Active {{ $session['last_seen']->diffForHumans() }}</span>
                        </div>
                        <form method="POST" action="{{ route('account.sessions.destroy', ['session' => $session['id']]) }}" data-revoke-form>
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn" style="padding:8px 14px;font-size:12.5px;background:var(--coffee-900);color:#fff;">Revoke</button>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/vendor/qrcode/qrcode.js"></script>
    <div class="modal-backdrop" id="passwordConfirmModal">
        <div class="modal">
            <div class="modal-head">
                <h3 id="passwordModalTitle">Confirm</h3>
                <button class="modal-close" onclick="closeModal('passwordConfirmModal')">✕</button>
            </div>
            <div class="modal-body">
                <p style="color:var(--ink-soft);font-size:14px;line-height:1.7;margin:0 0 16px;">Enter your current password to continue. This action is recorded in the audit trail.</p>
                <form method="POST" id="passwordConfirmForm">
                    @csrf
                    <input type="hidden" id="passwordModalAction" name="action">
                    <div class="field"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
                </form>
            </div>
            <div class="modal-foot">
                <button class="btn" onclick="closeModal('passwordConfirmModal')">Cancel</button>
                <button class="btn btn-primary" onclick="document.getElementById('passwordConfirmForm').requestSubmit()" id="passwordModalSubmit">Continue</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="recoveryCodesModal">
        <div class="modal">
            <div class="modal-head">
                <h3>Recovery codes</h3>
                <button class="modal-close" onclick="closeModal('recoveryCodesModal')">✕</button>
            </div>
            <div class="modal-body">
                <p style="color:var(--ink-soft);font-size:14px;line-height:1.7;margin:0 0 6px;">
                    Store these codes somewhere safe. Each code can be used once to sign in if you lose access to your authenticator app.
                    They will not be shown again.
                </p>
                <div id="recoveryCodesList" style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin:14px 0;"></div>
            </div>
            <div class="modal-foot">
                <button class="btn btn-primary" onclick="closeModal('recoveryCodesModal'); location.reload();">I've saved my codes</button>
            </div>
        </div>
    </div>

    @if ($pendingSecret)
    <div class="modal-backdrop" id="enableTwoFactorModal">
        <div class="modal" style="max-width:520px;">
            <div class="modal-head">
                <h3>Set up two-factor authentication</h3>
                <button class="modal-close" onclick="closeModal('enableTwoFactorModal')">✕</button>
            </div>
            <div class="modal-body">
                <ol style="margin:0 0 18px;padding-left:20px;color:var(--ink-soft);font-size:13.5px;line-height:1.8;">
                    <li>Install an authenticator app (Google Authenticator, Authy, Microsoft Authenticator, 1Password, etc.).</li>
                    <li>Add a new account by scanning the QR code below or manually entering the setup key.</li>
                    <li>Enter the 6-digit code from the app to verify and enable two-factor authentication.</li>
                </ol>

                <div style="background:var(--sand-100);border:1px dashed var(--line);border-radius:12px;padding:24px;text-align:center;margin-bottom:20px;">
                    <div id="otpauthQr" data-uri="{{ \App\Support\Security\TwoFactor::otpauthUri($pendingSecret, $user->email) }}" style="display:inline-block;margin:0 auto 12px;"></div>
                    <p style="margin:0 0 18px;color:var(--ink-soft);font-size:13px;">Scan this code with your authenticator app.</p>

                    <div style="margin-bottom:18px;">
                        <strong style="display:block;margin-bottom:8px;color:var(--coffee-900);font-size:13px;">Setup key (Manual entry)</strong>
                        <div style="display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;">
                            <code style="font-family:ui-monospace,monospace;background:var(--white);border:1px solid var(--line);border-radius:8px;padding:10px 14px;font-size:14px;font-weight:700;letter-spacing:.05em;color:var(--coffee-900);user-select:all;"
                                id="setupKey">{{ chunk_split($pendingSecret, 4, ' ') }}</code>
                            <button type="button" class="btn btn-ghost" onclick="copyText('{{ $pendingSecret }}', this)" style="padding:8px 14px;font-size:12.5px;">Copy key</button>
                        </div>
                    </div>

                    <div style="margin-top:14px;">
                        <strong style="display:block;margin-bottom:8px;color:var(--coffee-900);font-size:13px;">otpauth URI (apps that offer "Scan with camera")</strong>
                        <code style="font-family:ui-monospace,monospace;background:var(--white);border:1px solid var(--line);border-radius:8px;padding:10px 12px;font-size:11.5px;color:var(--ink-soft);word-break:break-all;display:block;max-height:80px;overflow:auto;">
                            {{ \App\Support\Security\TwoFactor::otpauthUri($pendingSecret, $user->email) }}
                        </code>
                    </div>
                </div>

                <form method="POST" action="{{ route('account.two-factor.confirm') }}" data-2fa-confirm-form>
                    @csrf
                    <div class="field">
                        <label>Verification code</label>
                        <input type="text" name="code" inputmode="numeric" maxlength="6" placeholder="6-digit code" required autocomplete="one-time-code" style="text-align:center;letter-spacing:.3em;font-size:18px;font-weight:700;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Verify & enable</button>
                </form>
            </div>
            <div class="modal-foot">
                <button class="btn btn-ghost" onclick="closeModal('enableTwoFactorModal')">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    <script>
        @if ($pendingSecret)
        document.addEventListener('DOMContentLoaded', function(){ openModal('enableTwoFactorModal'); });
        @endif
        const qrHost = document.getElementById('otpauthQr');
        if (qrHost && typeof qrcode !== 'undefined') {
            try {
                const qr = qrcode(0, 'M');
                qr.addData(qrHost.dataset.uri);
                qr.make();
                qrHost.innerHTML = qr.createSvgTag(4, 2);
            } catch (err) {
                console.error('QR generation failed:', err);
            }
        }

        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const original = btn.textContent;
                btn.textContent = 'Copied ✓';
                setTimeout(() => { btn.textContent = original; }, 1600);
            });
        }

        let pendingModalEndpoint = null;
        let pendingModalTitle = '';
        let pendingModalSubmitLabel = '';

        function openPasswordModal(title, endpoint, submitLabel) {
            pendingModalTitle = title;
            pendingModalEndpoint = endpoint;
            pendingModalSubmitLabel = submitLabel;
            document.getElementById('passwordModalTitle').textContent = title;
            document.getElementById('passwordModalSubmit').textContent = submitLabel;
            document.getElementById('passwordConfirmForm').reset();
            openModal('passwordConfirmModal');
        }

        function showRecoveryCodes(codes) {
            const list = document.getElementById('recoveryCodesList');
            list.innerHTML = '';
            codes.forEach(code => {
                const box = document.createElement('div');
                box.style.cssText = 'background:var(--sand-100);border:1px solid var(--line);border-radius:8px;padding:10px;font-family:ui-monospace,monospace;font-size:13px;font-weight:700;letter-spacing:.05em;color:var(--coffee-900);text-align:center;';
                box.textContent = code;
                list.appendChild(box);
            });
            openModal('recoveryCodesModal');
        }

        const passwordModalForm = document.getElementById('passwordConfirmForm');

        passwordModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!pendingModalEndpoint) return;

            const done = (data) => {
                if (data.recovery_codes) {
                    closeModal('passwordConfirmModal');
                    setTimeout(() => showRecoveryCodes(data.recovery_codes), 150);
                } else {
                    closeModal('passwordConfirmModal');
                    setTimeout(() => location.reload(), 600);
                }
            };

            const btn = passwordModalForm.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;

            try {
                const response = await fetch(pendingModalEndpoint, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: new FormData(passwordModalForm),
                });
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    toast(data.message || 'Saved successfully.', 'success');
                    done(data);
                } else {
                    toast(data.message || 'Something went wrong!', 'error');
                }
            } catch (err) {
                console.error(err);
                toast('Something went wrong! Please check the console.', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        });

        document.querySelectorAll('[data-password-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                submitForm(form, { method: 'PUT' });
            });
        });

        document.querySelectorAll('[data-2fa-confirm-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                submitForm(form, { method: 'POST', done: (data) => {
                    if (data.recovery_codes) {
                        closeModal('enableTwoFactorModal');
                        setTimeout(() => showRecoveryCodes(data.recovery_codes), 150);
                    }
                } });
            });
        });

        document.querySelectorAll('[data-revoke-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                submitForm(form, { method: 'DELETE', done: () => {
                    setTimeout(() => location.reload(), 600);
                } });
            });
        });
    </script>
@endsection