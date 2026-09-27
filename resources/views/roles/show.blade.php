@extends('layouts.app')

@section('title', 'Role · '.$role->name)

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>{{ $role->name }} <span class="mono" style="font-size:13px;color:var(--ink-soft);">{{ $role->code }}</span>
            @if (in_array($role->code, ['admin', 'supervisor', 'cashier']))<span class="pill pill-neutral" style="margin-left:6px;">system</span>@endif
        </h2>
        <p class="sub">{{ $role->description ?? 'No description.' }} · {{ $users->count() }} user(s) · {{ $role->permissions->count() }} permissions</p>
    </div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('roles.index') }}">Back</a></div>
</div>

@include('collections.partials.flash')

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Users with this role</h3><span class="link">{{ $users->count() }}</span></div>
    <div class="panel-body" style="padding:0;">
        <div class="table-card" style="box-shadow:none;border:none;margin:0;">
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Last login</th></tr></thead>
                    <tbody>
                        @forelse ($users as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td><span class="pill pill-{{ $u->is_active ? 'active' : 'failed' }}">{{ $u->is_active ? 'active' : 'inactive' }}</span></td>
                                <td>{{ $u->last_login_at?->format('d M H:i') ?? 'never' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state">No users use this role yet. Assign it from Users → Edit.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Permissions</h3><span class="link">{{ $role->permissions->count() }} / {{ count($catalogue) }}</span></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="field"><label>Description</label><input type="text" name="description" value="{{ $role->description }}" maxlength="500" style="width:100%;padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:8px;margin-bottom:14px;">
                @foreach ($catalogue as $code => $desc)
                    <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px;background:var(--sand-50);border:1px solid var(--line);border-radius:8px;padding:8px 10px;cursor:pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $code }}" {{ $role->permissions->contains('code', $code) ? 'checked' : '' }} style="margin-top:2px;">
                        <span><strong class="mono" style="font-size:11.5px;">{{ $code }}</strong><br><span style="color:var(--ink-soft);">{{ $desc }}</span></span>
                    </label>
                @endforeach
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit">Save {{ $role->name }}</button>
                @if (! in_array($role->code, ['admin', 'supervisor', 'cashier']))
                    <button class="btn btn-danger" type="submit" form="role-del" onclick="return confirm('Delete role {{ $role->name }}?');">Delete</button>
                @endif
            </div>
        </form>
        @if (! in_array($role->code, ['admin', 'supervisor', 'cashier']))
            <form id="role-del" method="POST" action="{{ route('roles.destroy', $role) }}" style="display:none;">@csrf @method('DELETE')</form>
        @endif
    </div>
</div>
@endsection
