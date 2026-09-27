@extends('layouts.app')

@section('title', 'Edit '.$user->name)

@section('content')
    <div class="view-head">
        <div>
            <h2>Edit User</h2>
            <p class="sub">{{ $user->name }} · {{ $user->email }}</p>
        </div>
        <div class="view-actions">
            <a href="{{ route('users.show', $user->getRouteKey()) }}" class="btn btn-ghost">← View user</a>
            <a href="{{ route('users.index') }}" class="btn btn-ghost">Back to users</a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>User Information</h3>
            <span class="link">{{ ucfirst($user->role) }}</span>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('users.update', $user->getRouteKey()) }}" data-user-form>
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="field"><label>Full name</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
                    <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
                </div>
                <div class="form-row">
                    <div class="field"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="07xxxxxxxx"></div>
                    <div class="field"><label>Role</label>
                        <select name="role" required>
                            @foreach(\App\Models\Role::orderBy('id')->get(['code','name']) as $r)
                                <option value="{{ $r->code }}" {{ $user->role === $r->code ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                            @if(!\App\Models\Role::where('code',$user->role)->exists())
                                <option value="{{ $user->role }}" selected>{{ ucfirst($user->role) }} (legacy)</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field"><label>Cash Point</label>
                        <select name="agent_id">
                            <option value="">— No cash point —</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ (string)$user->agent_id === (string)$agent->id ? 'selected' : '' }}>{{ $agent->name }} ({{ $agent->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Status</label>
                        <select name="is_active">
                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="field"><label>New password (leave blank to keep current)</label><input type="password" name="password" placeholder="Min 6 characters" autocomplete="new-password"></div>
                <div style="display:flex; gap:10px; margin-top:18px;">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('users.show', $user->getRouteKey()) }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Danger Zone</h3>
        </div>
        <div class="panel-body">
            <p style="font-size:13px; color:var(--ink-soft); margin-bottom:12px;">Delete this user account. This action cannot be undone.</p>
            <form method="POST" action="{{ route('users.destroy', $user->getRouteKey()) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete user</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-user-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                // Use POST with _method=PUT for FormData compatibility (fixes "The name field is required" on PUT + FormData)
                submitForm(form, { method: 'POST', done: () => { toast('User updated successfully.', 'success'); setTimeout(() => window.location.href = '{{ route('users.show', $user->getRouteKey()) }}', 600); } });
            });
        });
    </script>
@endsection
