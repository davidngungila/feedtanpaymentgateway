@extends('layouts.app')

@section('title', 'Users & Roles')

@section('head')
<style>
    .role-matrix{display:grid;grid-template-columns:180px repeat(3,1fr);gap:1px;background:var(--line);border:1px solid var(--line);border-radius:12px;overflow:hidden;font-size:12.5px;}
    .role-matrix .rm-head{background:var(--sand-50);padding:10px 12px;font-weight:800;color:var(--coffee-900);text-transform:uppercase;letter-spacing:.05em;font-size:11px;}
    .role-matrix .rm-cell{background:var(--white);padding:9px 12px;display:flex;align-items:center;gap:8px;}
    .role-matrix .rm-cell.feat{background:var(--sand-50);font-weight:700;color:var(--coffee-700);}
    .perm-dot{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex:none;font-size:11px;font-weight:800;}
    .perm-dot.yes{background:var(--acacia-100);color:var(--acacia-600);}
    .perm-dot.no{background:var(--sand-200);color:var(--ink-soft);}
    .perm-dot.limit{background:var(--gold-100);color:#8a6418;}
</style>
@endsection

@section('content')
<div class="view-head">
    <div>
        <h2>Users &amp; Roles</h2>
        <p class="sub">Manage team access · Role-based permissions, 2FA enforcement, session control & audit trail · ClickPesa Feedtan Online.</p>
    </div>
    <div class="view-actions">
        <button class="btn btn-ghost" onclick="openModal('roleMatrixModal')">Permission Matrix</button>
        <a class="btn btn-primary" href="{{ route('users.create') }}" style="text-decoration:none;">+ Add user</a>
        @include('exports._export-modal', ['route' => $exportRoute, 'columns' => $exportColumns, 'title' => 'Users'])
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card" style="--stat-tint:var(--terracotta-100);--stat-fg:var(--terracotta-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div><span class="stat-trend up">{{ $users->count() }} total</span></div>
        <div class="stat-value">{{ $users->where('is_active',true)->count() }}</div>
        <div class="stat-label">Active users · {{ $users->where('is_active',false)->count() }} disabled</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--acacia-100);--stat-fg:var(--acacia-600);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg></div><span class="stat-trend up">{{ $users->where('two_factor_enabled',true)->count() }} secured</span></div>
        <div class="stat-value">{{ $users->where('role','admin')->count() }} <span style="font-size:14px;color:var(--ink-soft);">admins · {{ $users->where('role','supervisor')->count() }} supervisors · {{ $users->where('role','cashier')->count() }} cashiers</span></div>
        <div class="stat-label">Role distribution</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--gold-100);--stat-fg:#8a6418;">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div><span class="stat-trend up">Live</span></div>
        <div class="stat-value">{{ $users->filter(fn($u)=> $u->last_login_at && $u->last_login_at->isToday())->count() }}</div>
        <div class="stat-label">Active today · Last 7d: {{ $users->filter(fn($u)=> $u->last_login_at && $u->last_login_at->gt(now()->subDays(7)))->count() }}</div>
    </div>
    <div class="stat-card" style="--stat-tint:var(--danger-100);--stat-fg:var(--danger);">
        <div class="stat-top"><div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div><span class="stat-trend down">Security</span></div>
        <div class="stat-value">{{ $users->where('two_factor_enabled',false)->where('role','admin')->count() }} <span style="font-size:14px;color:var(--ink-soft);">admins without 2FA</span></div>
        <div class="stat-label">Enforce 2FA for admins & supervisors</div>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head"><h3>Role Permissions Overview</h3><span class="link">RBAC</span></div>
    <div class="panel-body">
        <div class="role-matrix">
            <div class="rm-head">Capability</div><div class="rm-head" style="text-align:center;">Cashier</div><div class="rm-head" style="text-align:center;">Supervisor</div><div class="rm-head" style="text-align:center;">Admin</div>

            <div class="rm-cell feat">Record transactions</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">Verify payments (API)</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">Create payout</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot limit">•</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">Approve payout (&gt;1M)</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">Manage users</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">System settings & API keys</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">View audit logs</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            <div class="rm-cell feat">Reports & finance</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
        </div>
        <p style="font-size:12px;color:var(--ink-soft);margin:12px 0 0;">Cashiers can create payouts below approval threshold; supervisors approve; admins have full system control. 2FA is required for payout approval & settings changes.</p>
    </div>
</div>

<form method="GET" action="{{ route('users.index') }}">
    <div class="table-card">
        <div class="table-toolbar">
            <div class="chip-filters" id="roleChips">
                <button type="button" class="chip {{ $activeRole === 'all' ? 'active' : '' }}" data-role="all" onclick="setRoleFilter('all')">All ({{ $users->count() }})</button>
                <button type="button" class="chip {{ $activeRole === 'cashier' ? 'active' : '' }}" data-role="cashier" onclick="setRoleFilter('cashier')">Cashiers ({{ $users->where('role','cashier')->count() }})</button>
                <button type="button" class="chip {{ $activeRole === 'supervisor' ? 'active' : '' }}" data-role="supervisor" onclick="setRoleFilter('supervisor')">Supervisors ({{ $users->where('role','supervisor')->count() }})</button>
                <button type="button" class="chip {{ $activeRole === 'admin' ? 'active' : '' }}" data-role="admin" onclick="setRoleFilter('admin')">Admins ({{ $users->where('role','admin')->count() }})</button>
                <input type="hidden" name="role" id="fRole" value="{{ $activeRole }}">
            </div>
            <div class="table-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" placeholder="Search name, email, phone…" oninput="filterUserRows(this.value)">
            </div>
        </div>
    </div>
</form>

<div class="table-card" style="margin-top:-24px;">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Cash point</th>
                    <th>Phone</th>
                    <th>2FA</th>
                    <th>Status</th>
                    <th>Last login</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="usersBody">
                @forelse ($users as $user)
                    <tr data-id="{{ $user->getRouteKey() }}" data-name="{{ strtolower($user->name.' '.$user->email.' '.($user->phone ?? '')) }}" data-role="{{ $user->role }}"
                        data-email="{{ $user->email }}" data-phone="{{ $user->phone ?? '' }}"
                        data-agent="{{ $user->agent?->name ?? '' }}" data-agentcode="{{ $user->agent?->code ?? '' }}"
                        data-active="{{ $user->is_active ? '1' : '0' }}"
                        data-2fa="{{ $user->two_factor_enabled ? '1' : '0' }}"
                        data-lastlogin="{{ $user->last_login_at?->format('d M Y H:i') ?? '' }}">
                        <td>
                            <div class="cell-main">
                                <div class="avatar {{ $user->role === 'admin' ? 'gold' : ($user->role === 'supervisor' ? 'acacia' : '') }}">@if ($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="">@else{{ strtoupper(substr($user->name, 0, 2)) }}@endif</div>
                                <div>
                                    <div class="cell-title">{{ $user->name }} @if($user->id===auth()->id())<span class="tag tag-grey" style="font-size:10px;padding:2px 6px;margin-left:4px;">You</span>@endif</div>
                                    <div class="cell-sub">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="tag {{ $user->role==='admin' ? 'tag-gold' : ($user->role==='supervisor' ? 'tag-green' : 'tag-terracotta') }}">{{ ucfirst($user->role) }}</span></td>
                        <td>
                            <div class="cell-title">{{ $user->agent?->name ?? '—' }}</div>
                            <div class="cell-sub">{{ $user->agent?->code ?? 'No link' }}</div>
                        </td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            @if($user->two_factor_enabled)
                                <span class="tag tag-green">On</span>
                            @else
                                <span class="tag tag-red">Off</span>
                            @endif
                        </td>
                        <td><span class="tag {{ $user->is_active ? 'tag-green' : 'tag-grey' }}">{{ $user->is_active ? 'Active' : 'Disabled' }}</span></td>
                        <td>
                            <div class="cell-title" style="font-size:12.5px;">{{ $user->last_login_at?->format('d M Y') ?? 'Never' }}</div>
                            <div class="cell-sub">{{ $user->last_login_at?->format('H:i') ?? '' }} · {{ $user->last_login_at?->diffForHumans() ?? '' }}</div>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('users.show', $user->getRouteKey()) }}" title="View" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <button onclick="openUserModal('{{ $user->getRouteKey() }}')" title="Edit" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"></path></svg>
                                </button>
                                <button class="danger" onclick="confirmDeleteUser('{{ $user->getRouteKey() }}', '{{ $user->name }}')" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state"><h4>No users found</h4><p>Add your first team member. Assign cashier for daily ops, supervisor for approvals.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add / edit user modal - Enhanced with 2FA & permissions -->
<div class="modal-backdrop" id="userModal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-head">
            <h3 id="userModalTitle">Add user</h3>
            <button class="modal-close" onclick="closeModal('userModal')">✕</button>
        </div>
        <form id="userForm" data-user-form>
            <input type="hidden" name="_method" value="" id="userMethod">
            <input type="hidden" name="id" id="userId">
            <div class="modal-body">
                <div class="form-row">
                    <div class="field">
                        <label>Full name *</label>
                        <input type="text" name="name" id="userName" placeholder="e.g. Baraka Mushi" required>
                    </div>
                    <div class="field">
                        <label>Email *</label>
                        <input type="email" name="email" id="userEmail" placeholder="name@company.com" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phone" id="userPhone" placeholder="07xxxxxxxx">
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <div class="pwd-wrap"><input type="password" name="password" id="userPassword" placeholder="Min 6 characters" autocomplete="new-password"><button type="button" class="pwd-toggle" onclick="this.previousElementSibling.type=this.previousElementSibling.type==='password'?'text':'password'">👁</button></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Role *</label>
                        <select name="role" id="userRole" onchange="toggleAgentField();togglePermHint();">
                            <option value="cashier">Cashier — Daily operations</option>
                            <option value="supervisor">Supervisor — Approvals & reports</option>
                            <option value="admin">Administrator — Full access</option>
                        </select>
                        <div id="permHint" style="font-size:11.5px;color:var(--ink-soft);margin-top:6px;"></div>
                    </div>
                    <div class="field">
                        <label>Status</label>
                        <select name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="field" id="agentFieldWrap">
                    <label>Linked cash point</label>
                    <select name="agent_id" id="userAgentId">
                        <option value="">— Not linked —</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--sand-50);border:1px solid var(--line);border-radius:10px;margin-top:4px;">
                    <div>
                        <strong style="font-size:13px;color:var(--coffee-900);">Enforce 2FA on next login</strong>
                        <div style="font-size:12px;color:var(--ink-soft);">User will be prompted to set up authenticator</div>
                    </div>
                    <label style="position:relative;display:inline-flex;align-items:center;cursor:pointer;">
                        <input type="checkbox" name="require_2fa" value="1" style="width:18px;height:18px;accent-color:var(--terracotta-600);">
                    </label>
                </div>
                <p style="font-size:12px;color:var(--ink-soft);margin:12px 0 0;" id="agentFieldHint">Leave password blank when editing to keep the current password. Password is bcrypt hashed.</p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('userModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="userSubmitBtn">Save user</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm delete modal -->
<div class="modal-backdrop" id="confirmModalBackdrop">
    <div class="modal" style="max-width:400px;">
        <div class="modal-head">
            <h3>Delete user?</h3>
            <button class="modal-close" onclick="closeModal('confirmModalBackdrop')">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--ink-soft);line-height:1.6;" id="confirmText">This user will lose access to the system. Their audit history is retained.</p>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" onclick="closeModal('confirmModalBackdrop')">Cancel</button>
            <button class="btn btn-danger" onclick="executeDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Role matrix modal -->
<div class="modal-backdrop" id="roleMatrixModal">
    <div class="modal" style="max-width:680px;">
        <div class="modal-head"><h3>Detailed Permission Matrix</h3><button class="modal-close" onclick="closeModal('roleMatrixModal')">✕</button></div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:14px;">Full breakdown of what each role can do. Permissions are enforced at middleware and UI level.</p>
            <div class="role-matrix">
                <div class="rm-head">Feature</div><div class="rm-head" style="text-align:center;">Cashier</div><div class="rm-head" style="text-align:center;">Supervisor</div><div class="rm-head" style="text-align:center;">Admin</div>
                <div class="rm-cell feat">Dashboard & cash point</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Transactions (create)</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Reverse / mark unusual</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Payments: verify via API</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Payments: refund</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Payouts: create</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot limit">≤ limit</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Payouts: approve/reject</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Float & reconciliation</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Networks & devices</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Reports & finance</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
                <div class="rm-cell feat">Users & audit logs</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">view</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">manage</span></div>
                <div class="rm-cell feat">System settings & API keys</div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot no">—</span></div><div class="rm-cell" style="justify-content:center;"><span class="perm-dot yes">✓</span></div>
            </div>
        </div>
        <div class="modal-foot"><button class="btn btn-primary" onclick="closeModal('roleMatrixModal')">Close</button></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    @php
        $jsonUsers = $users->map(fn ($u) => [
            'id' => $u->id,
            'routeKey' => $u->getRouteKey(),
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'role' => $u->role,
            'agent_id' => $u->agent_id,
            'is_active' => (bool) $u->is_active,
        ])->values();
    @endphp
    const usersData = @json($jsonUsers);
    let pendingDeleteFunc = null;

    function setRoleFilter(role) {
        document.getElementById('fRole').value = role;
        document.getElementById('fRole').closest('form').submit();
    }
    function filterUserRows(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#usersBody tr[data-id]').forEach(tr => {
            tr.style.display = (!q || tr.dataset.name.includes(q)) ? 'table-row' : 'none';
        });
    }
    function toggleAgentField() {
        const role = document.getElementById('userRole').value;
        document.getElementById('agentFieldWrap').style.display = role === 'cashier' ? '' : 'none';
    }
    function togglePermHint(){
        const r=document.getElementById('userRole').value;
        const h=document.getElementById('permHint');
        const map={
            cashier:'Can record transactions, manage float, view own payouts. Cannot approve or manage users.',
            supervisor:'Can approve payouts, refund payments, view reports & audit logs. Cannot manage system settings.',
            admin:'Full access: users, settings, API keys, finance, audit.'
        };
        h.textContent=map[r]||'';
    }
    function openUserModal(routeKey = null) {
        const form = document.getElementById('userForm');
        if (routeKey) {
            const u = usersData.find(x => x.routeKey === routeKey || String(x.id) === String(routeKey));
            if (!u) return;
            document.getElementById('userModalTitle').textContent = 'Edit user';
            document.getElementById('userMethod').value = 'PUT';
            document.getElementById('userId').value = u.routeKey;
            form.action = `/users/${u.routeKey}`;
            document.getElementById('userName').value = u.name;
            document.getElementById('userEmail').value = u.email;
            document.getElementById('userPhone').value = u.phone || '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').placeholder = 'Leave blank to keep current';
            document.getElementById('userRole').value = u.role;
            document.getElementById('userAgentId').value = u.agent_id || '';
            form.querySelector('[name="is_active"]').value = u.is_active ? '1' : '0';
            document.getElementById('userSubmitBtn').textContent = 'Update user';
        } else {
            document.getElementById('userModalTitle').textContent = 'Add user';
            document.getElementById('userMethod').value = '';
            document.getElementById('userId').value = '';
            form.action = '{{ route("users.store") }}';
            document.getElementById('userName').value = '';
            document.getElementById('userEmail').value = '';
            document.getElementById('userPhone').value = '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').placeholder = 'Min 6 characters';
            document.getElementById('userRole').value = 'cashier';
            document.getElementById('userAgentId').value = '';
            form.querySelector('[name="is_active"]').value = '1';
            document.getElementById('userSubmitBtn').textContent = 'Save user';
        }
        toggleAgentField(); togglePermHint();
        openModal('userModal');
    }
    function editUser(id) { openUserModal(id); }
    function confirmDeleteUser(id, name) {
        document.getElementById('confirmText').textContent = 'Delete "' + name + '"? They will lose access immediately. Audit history is retained for compliance.';
        pendingDeleteFunc = async () => {
            try {
                const response = await fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                });
                const data = await response.json();
                if (data.success) { toast(data.message, 'success'); setTimeout(() => location.reload(), 600); }
                else { toast(data.message, 'error'); }
            } catch (err) { console.error(err); toast('Something went wrong!', 'error'); }
        };
        openModal('confirmModalBackdrop');
    }
    function executeDelete() {
        if (pendingDeleteFunc) pendingDeleteFunc();
        closeModal('confirmModalBackdrop');
    }
    bindRowClick('#usersBody tr[data-id]', tr => {
        const roleTag = tr.dataset.role === 'admin'
            ? '<span class="tag tag-gold">Admin</span>'
            : (tr.dataset.role === 'supervisor'
                ? '<span class="tag tag-green">Supervisor</span>'
                : '<span class="tag tag-terracotta">Cashier</span>');
        return [
            ['Name', tr.dataset.name],
            ['Email', tr.dataset.email],
            ['Role', { __html: roleTag }],
            ['Cash point', tr.dataset.agent ? tr.dataset.agent + ' (' + tr.dataset.agentcode + ')' : '—'],
            ['Phone', tr.dataset.phone || '—'],
            ['2FA', {__html: tr.dataset['2fa']==='1' ? '<span class="tag tag-green">On</span>' : '<span class="tag tag-red">Off</span>'}],
            ['Status', { __html: tr.dataset.active === '1' ? '<span class="tag tag-green">Active</span>' : '<span class="tag tag-grey">Disabled</span>' }],
            ['Last login', tr.dataset.lastlogin || 'Never'],
        ];
    }, 'User details');
    document.querySelectorAll('[data-user-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const method = document.getElementById('userMethod').value || 'POST';
            submitForm(form, { method, done: () => setTimeout(() => location.reload(), 600) });
        });
    });
</script>
@endsection
