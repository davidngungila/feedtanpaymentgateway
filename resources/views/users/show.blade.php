@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="view-head">
        <div>
            <h2>{{ $user->name }}</h2>
            <p class="sub">{{ $user->email }} · {{ ucfirst($user->role) }} · {{ $user->is_active ? 'Active' : 'Disabled' }}</p>
        </div>
        <div class="view-actions">
            <a href="{{ route('users.index') }}" class="btn btn-ghost">← Back to users</a>
            <a href="{{ route('users.edit', $user->getRouteKey()) }}" class="btn btn-primary">Edit user</a>
        </div>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <div class="panel-head">
                <h3>User Details</h3>
                <span class="tag {{ $user->is_active ? 'tag-green' : 'tag-grey' }}">{{ $user->is_active ? 'Active' : 'Disabled' }}</span>
            </div>
            <div class="panel-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="dk">Full name</div><div class="dv">{{ $user->name }}</div></div>
                    <div class="detail-item"><div class="dk">Email</div><div class="dv">{{ $user->email }}</div></div>
                    <div class="detail-item"><div class="dk">Phone</div><div class="dv">{{ $user->phone ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Role</div><div class="dv">{{ ucfirst($user->role) }}</div></div>
                    <div class="detail-item"><div class="dk">Cash Point</div><div class="dv">{{ $user->agent?->name ?? '—' }} @if($user->agent) ({{ $user->agent->code }}) @endif</div></div>
                    <div class="detail-item"><div class="dk">Status</div><div class="dv">{{ $user->is_active ? 'Active' : 'Disabled' }}</div></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Account Details</h3>
            </div>
            <div class="panel-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="dk">Member since</div><div class="dv">{{ $user->created_at?->format('d M Y H:i') ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Last login</div><div class="dv">{{ $user->last_login_at?->format('d M Y H:i') ?? 'Never' }}</div></div>
                    <div class="detail-item"><div class="dk">Updated at</div><div class="dv">{{ $user->updated_at?->format('d M Y H:i') ?? '—' }}</div></div>
                    <div class="detail-item"><div class="dk">Two-factor</div><div class="dv">{{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Actions</h3>
        </div>
        <div class="panel-body" style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('users.edit', $user->getRouteKey()) }}" class="btn btn-primary">Edit user</a>
            <a href="{{ route('users.index') }}" class="btn btn-ghost">Back to users</a>
        </div>
    </div>
@endsection
