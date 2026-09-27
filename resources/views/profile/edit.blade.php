@extends('layouts.app')

@section('title', 'Edit Profile')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Edit Profile</h2><p class="sub">Update your personal information and password.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('profile.index') }}">Back</a></div>
</div>

@if (session('status'))
    <div class="flash ok">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="flash err">{{ $errors->first() }}</div>
@endif

<div class="create-grid">
    <div class="settings-panel">
        <h3 style="margin-top:0;">Profile information</h3>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div class="field"><label>Full name *</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Email *</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="120" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="07xxxxxxxx" maxlength="30" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Profile photo</label><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="width:100%;padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;background:var(--white);">
                <div class="prov-hint">JPG, PNG, WebP or GIF · max 2 MB. Uploading replaces the current photo.</div>
            </div>
            @if ($user->avatarUrl())
                <div class="field"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;"><input type="checkbox" name="remove_avatar" value="1" style="width:auto;"> Remove current photo</label></div>
            @endif
            <button type="submit" class="btn btn-primary" style="width:100%;">Save profile</button>
        </form>
    </div>
    <div>
        <div class="settings-panel" style="margin-bottom:18px;">
            <h3 style="margin-top:0;">Change password</h3>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="field"><label>Current password *</label><input type="password" name="current_password" required autocomplete="current-password" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
                <div class="field"><label>New password *</label><input type="password" name="password" required minlength="6" autocomplete="new-password" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
                <div class="field"><label>Confirm new password *</label><input type="password" name="password_confirmation" required autocomplete="new-password" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Update password</button>
            </form>
        </div>
        <div class="settings-panel">
            <h3 style="margin-top:0;">Good to know</h3>
            <ol class="detail-list">
                <li>Forms submit <strong>normally</strong> — no JavaScript needed.</li>
                <li>Your photo shows across the system once saved.</li>
                <li>Use a <strong>strong, unique password</strong> (6+ characters).</li>
                <li>Protect sign-in further with <strong>two-factor</strong> under Account &amp; Security.</li>
            </ol>
            <div class="kv"><span class="k">Role</span><span class="v">{{ ucfirst($user->role) }}</span></div>
            <div class="kv"><span class="k">2FA</span><span class="v">{{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</span></div>
        </div>
    </div>
</div>
@endsection
