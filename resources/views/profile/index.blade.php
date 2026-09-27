@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="view-head">
        <div>
            <h2>My Profile</h2>
            <p class="sub">Update your personal information and change your password.</p>
        </div>
    </div>

    <div class="panel" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:26px;padding:30px clamp(20px,4vw,36px);flex-wrap:wrap;">
            <div style="flex:none;">
                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;" form="profileForm">
                <button type="button" id="avatarButton" class="avatar-ring" title="Change photo" style="border:none;background:none;cursor:pointer;padding:0;border-radius:50%;position:relative;">
                    <span id="avatarPreview" style="width:96px;height:96px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));color:#fff;font-weight:700;font-size:30px;border:4px solid var(--sand-50);box-shadow:var(--shadow-md);">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        @endif
                    </span>
                    <span class="avatar-hover">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        <span>Change</span>
                    </span>
                </button>
            </div>
            <div style="flex:1;min-width:240px;">
                <h3 style="font-size:21px;margin:0 0 4px;">{{ $user->name }}</h3>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                    <span class="tag {{ $user->role === 'admin' ? 'tag-gold' : ($user->role === 'supervisor' ? 'tag-green' : 'tag-terracotta') }}">{{ ucfirst($user->role) }}</span>
                    <span class="tag {{ $user->is_active ? 'tag-green' : 'tag-grey' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    @if ($user->two_factor_enabled)
                        <span class="tag tag-green">2FA on</span>
                    @else
                        <span class="tag tag-grey">2FA off</span>
                    @endif
                </div>
                <p style="margin:0;color:var(--ink-soft);font-size:14px;">{{ $user->email }}</p>
            </div>
            <div style="text-align:right;flex:none;">
                <a class="btn btn-ghost" href="{{ route('account.index') }}" style="padding:10px 18px;text-decoration:none;">Manage security</a>
            </div>
        </div>
    </div>

    <style>
        .avatar-hover{position:absolute;inset:0;border-radius:50%;background:rgba(42,27,16,.55);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;font-size:11px;font-weight:700;opacity:0;transition:opacity .15s;}
        .avatar-ring:hover .avatar-hover{opacity:1;}
    </style>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head">
                <h3>Profile information</h3>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('profile.update') }}" id="profileForm" data-profile-form enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="field"><label>Full name</label><input type="text" name="name" value="{{ $user->name }}" required maxlength="120"></div>
                    <div class="field"><label>Email</label><input type="email" name="email" value="{{ $user->email }}" required maxlength="120"></div>
                    <div class="field"><label>Phone</label><input type="text" name="phone" value="{{ $user->phone ?? '' }}" placeholder="07xxxxxxxx" maxlength="30"></div>

                    @if ($user->avatarUrl())
                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--danger);margin:2px 0 16px;cursor:pointer;">
                            <input type="checkbox" name="remove_avatar" value="1" style="cursor:pointer;"> Remove current photo
                        </label>
                    @endif

                    <button type="submit" class="btn btn-primary">Save profile</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Change password</h3>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('profile.password') }}" data-password-form>
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="field"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
                    <div class="field"><label>New password</label><input type="password" name="password" id="newPassword" required minlength="6" autocomplete="new-password"></div>
                    <div id="pwdStrength" style="display:flex;gap:5px;margin:-6px 0 14px;"></div>
                    <div class="field"><label>Confirm new password</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>
                    <button type="submit" class="btn btn-danger" style="background:var(--coffee-900);color:#fff;">Update password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Account details</h3>
            <span class="link">{{ $activeSessions }} active {{ $activeSessions === 1 ? 'session' : 'sessions' }}</span>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="detail-item"><div class="dk">Role</div><div class="dv">{{ ucfirst($user->role) }}</div></div>
                <div class="detail-item"><div class="dk">Account status</div><div class="dv">{{ $user->is_active ? 'Active' : 'Inactive' }}</div></div>
                <div class="detail-item"><div class="dk">Two-factor auth</div><div class="dv">{{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</div></div>
                <div class="detail-item"><div class="dk">Phone</div><div class="dv">{{ $user->phone ?? '—' }}</div></div>
                <div class="detail-item"><div class="dk">Member since</div><div class="dv">{{ $user->created_at->format('d M Y') }} · {{ $user->created_at->diffForHumans() }}</div></div>
                <div class="detail-item"><div class="dk">Last login</div><div class="dv">{{ $user->last_login_at?->format('d M Y H:i') ?? 'Never' }}@if($user->last_login_at) · {{ $user->last_login_at->diffForHumans() }}@endif</div></div>
                <div class="detail-item"><div class="dk">Current IP</div><div class="dv">{{ $currentIp ?? '—' }}</div></div>
                <div class="detail-item"><div class="dk">Linked cash point</div><div class="dv">{{ $user->agent?->name ?? '—' }}</div></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');

        document.getElementById('avatarButton').addEventListener('click', () => avatarInput.click());

        avatarInput.addEventListener('change', function () {
            const [file] = this.files;
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                avatarPreview.style.background = 'var(--sand-100)';
                avatarPreview.innerHTML = `<img src="${e.target.result}" alt="" style="width:100%;height:100%;object-fit:cover;">`;
            };
            reader.readAsDataURL(file);
        });

        let bars = [];
        for (let i = 0; i < 4; i++) {
            const b = document.createElement('span');
            b.style.cssText = 'flex:1;height:6px;border-radius:3px;background:var(--sand-200);transition:background .15s;';
            document.getElementById('pwdStrength').appendChild(b);
            bars.push(b);
        }

        function updateStrength() {
            const v = document.getElementById('newPassword').value;
            let score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) score++;
            const colors = ['var(--sand-200)', 'var(--danger)', '#e8a13a', 'var(--gold-500)', 'var(--success)'];
            bars.forEach((b, i) => { b.style.background = i < score ? colors[score] : 'var(--sand-200)'; });
        }

        document.getElementById('newPassword').addEventListener('input', updateStrength);

        function resetForm() {
            const form = document.querySelector('[data-password-form]');
            if (!form) return;
            form.reset();
            bars.forEach(b => { b.style.background = 'var(--sand-200)'; });
        }

        document.querySelectorAll('[data-profile-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                submitForm(form, { method: 'PUT', done: () => setTimeout(() => location.reload(), 600) });
            });
        });

        document.querySelectorAll('[data-password-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                submitForm(form, { method: 'PUT', done: () => { toast('Password changed successfully.', 'success'); resetForm(); } });
            });
        });
    </script>
@endsection