@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Roles &amp; Permissions</h2><p class="sub">Create roles, tick their permissions, assign them to users. System roles can't be deleted.</p></div>
    <div class="view-actions"><button class="btn btn-primary" onclick="document.getElementById('newRolePanel').style.display = document.getElementById('newRolePanel').style.display === 'none' ? 'block' : 'none';">+ New role</button></div>
</div>

@include('collections.partials.flash')

<div class="settings-panel" id="newRolePanel" style="display:none;margin-bottom:18px;">
    <h3 style="margin-top:0;">New role</h3>
    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="field"><label>Code * <span class="prov-hint">lowercase, no spaces</span></label><input type="text" name="code" value="{{ old('code') }}" required maxlength="30" placeholder="finance" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;font-family:ui-monospace,monospace;"></div>
            <div class="field"><label>Name *</label><input type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Finance Manager" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
            <div class="field"><label>Description</label><input type="text" name="description" value="{{ old('description') }}" maxlength="500" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;"></div>
        </div>
        <div class="field"><label>Permissions</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:8px;">
                @foreach ($catalogue as $code => $desc)
                    <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px;background:var(--sand-50);border:1px solid var(--line);border-radius:8px;padding:8px 10px;cursor:pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $code }}" style="margin-top:2px;">
                        <span><strong class="mono" style="font-size:11.5px;">{{ $code }}</strong><br><span style="color:var(--ink-soft);">{{ $desc }}</span></span>
                    </label>
                @endforeach
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Create role</button>
    </form>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Role</th><th class="num">Users</th><th class="num">Permissions</th><th>Updated</th><th></th></tr></thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td><strong>{{ $role->name }}</strong> <span class="mono" style="font-size:11px;color:var(--ink-soft);">{{ $role->code }}</span>
                            @if (in_array($role->code, ['admin', 'supervisor', 'cashier']))<span class="pill pill-neutral" style="margin-left:6px;">system</span>@endif
                        </td>
                        <td class="num">{{ $userCounts[$role->code] ?? 0 }}</td>
                        <td class="num">{{ $role->permissions->count() }}</td>
                        <td>{{ $role->updated_at->format('d M H:i') }}</td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('roles.show', $role) }}" aria-label="View details"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No roles yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
