@extends('layouts.app')

@section('title', 'Add User')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Add User</h2><p class="sub">Create a sign-in account — role decides what they can see and do.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('users.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="create-grid">
<div class="settings-panel">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Full name *</label><input type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Mfano: Neema Kimaro" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Email *</label><input type="email" name="email" value="{{ old('email') }}" required maxlength="150" placeholder="neema@company.co.tz" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Phone</label><input type="text" name="phone" value="{{ old('phone') }}" maxlength="30" placeholder="2557xxxxxxxx" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            <div class="field"><label>Role *</label>
                <select name="role" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;">
                    @foreach ($roles as $r)
                        <option value="{{ $r->code }}" {{ old('role') === $r->code ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field"><label>Temporary password *</label><input type="text" name="password" required minlength="6" maxlength="100" placeholder="min. 6 characters" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            <div class="field"><label>&nbsp;</label><label style="text-transform:none;letter-spacing:0;"><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active — can sign in immediately</label></div>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;">Create user</button>
    </form>
</div>
<div class="settings-panel">
    <h3 style="margin-top:0;">Good to know</h3>
    <ol class="detail-list">
        <li><strong>Roles</strong> come from Roles &amp; Permissions — create custom ones there first.</li>
        <li>Passwords are <strong>hashed</strong> (never stored readable); share the temporary one securely.</li>
        <li>New users should enable <strong>two-factor</strong> from Account &amp; Security.</li>
        <li><strong>Cashiers</strong> see masked customer data; <strong>admins</strong> manage credentials.</li>
        <li>Creation is written to the <strong>audit trail</strong> with your name and IP.</li>
    </ol>
    <div class="kv"><span class="k">Sign-in</span><span class="v">email + password</span></div>
    <div class="kv"><span class="k">2FA</span><span class="v">optional per user</span></div>
    <div class="kv"><span class="k">Deactivate</span><span class="v"> anytime, keeps history</span></div>
</div>
</div>
@endsection
