<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Support\Security\Audit;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public const SYSTEM_ROLES = ['admin', 'supervisor', 'cashier'];

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $catalogue = Permission::catalogue();

        // Ensure catalogue rows exist for the matrix.
        foreach ($catalogue as $code => $desc) {
            Permission::firstOrCreate(['code' => $code], ['name' => ucwords(str_replace(['.', '_'], ' ', $code)), 'description' => $desc]);
        }

        $userCounts = User::selectRaw('role, COUNT(*) as c')->groupBy('role')->pluck('c', 'role');

        return view('roles.index', ['roles' => $roles->fresh(), 'catalogue' => $catalogue, 'userCounts' => $userCounts]);
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $catalogue = Permission::catalogue();
        $users = User::where('role', $role->code)->orderBy('name')->get();

        return view('roles.show', ['role' => $role, 'catalogue' => $catalogue, 'users' => $users]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', 'unique:roles,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,code'],
        ]);

        $role = Role::create([
            'code' => strtolower($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $ids = Permission::whereIn('code', $data['permissions'] ?? [])->pluck('id')->all();
        $role->permissions()->sync($ids);

        Audit::log('role.created', $role, ['code' => $role->code, 'permissions' => $data['permissions'] ?? []]);

        return back()->with('success', 'Role '.$role->name.' created. Assign it to users from Users → Edit.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,code',
        ]);

        $role->update(['description' => $data['description'] ?? $role->description]);
        if (array_key_exists('permissions', $data)) {
            $ids = Permission::whereIn('code', $data['permissions'] ?? [])->pluck('id')->all();
            $role->permissions()->sync($ids);
        }

        Audit::log('permission.changed', $role, ['role' => $role->code, 'permissions' => $data['permissions'] ?? []]);

        return back()->with('success', 'Role '.$role->name.' updated.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->code, self::SYSTEM_ROLES, true)) {
            return back()->withErrors(['role' => 'System roles (admin, supervisor, cashier) cannot be deleted.']);
        }

        $assigned = User::where('role', $role->code)->count();
        if ($assigned > 0) {
            return back()->withErrors(['role' => $assigned.' user(s) still use this role. Reassign them first.']);
        }

        $role->permissions()->detach();
        $role->delete();
        Audit::log('role.deleted', 'Role', ['code' => $role->code]);

        return back()->with('success', 'Role deleted.');
    }

    public function securityEvents(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $events = SecurityEvent::with('user')
            ->when($q !== '', fn ($w) => $w->where('action', 'like', "%{$q}%"))
            ->latest()->paginate(20)->withQueryString();

        return view('roles.security', ['events' => $events, 'q' => $q]);
    }
}
