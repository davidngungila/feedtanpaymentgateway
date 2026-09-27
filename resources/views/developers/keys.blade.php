@extends('layouts.app')

@section('title', 'API Keys')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head"><div><h2>API Keys</h2><p class="sub">Only prefix + hash are stored — a database leak reveals no active keys.</p></div></div>

@include('collections.partials.flash')

<div class="settings-panel" style="max-width:560px;margin-bottom:18px;">
    <h3 style="margin-top:0;">Mint a key</h3>
    <form method="POST" action="{{ route('developers.keys.store') }}" class="filterbar">
        @csrf
        <div class="field" style="flex:1;"><label style="font-size:11px;">Name</label><input type="text" name="name" required maxlength="100" placeholder="POS integration"></div>
        <div><button class="btn btn-primary" type="submit">Create</button></div>
    </form>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Name</th><th>Prefix</th><th>Created by</th><th>Last used</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($keys as $k)
                    <tr>
                        <td>{{ $k->name }}</td>
                        <td class="mono">{{ $k->prefix }}_••••</td>
                        <td>{{ $k->creator?->name ?? '—' }}</td>
                        <td>{{ $k->last_used_at?->format('d M H:i') ?? 'never' }}</td>
                        <td><span class="pill pill-{{ $k->is_active ? 'active' : 'failed' }}">{{ $k->is_active ? 'active' : 'revoked' }}</span></td>
                        <td>
                            @if ($k->is_active)
                                <form method="POST" action="{{ route('developers.keys.revoke', $k) }}" style="display:inline;" onsubmit="return confirm('Revoke this key?');">@csrf<button class="btn btn-sm btn-ghost" type="submit">Revoke</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No API keys yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $keys->links() }}</div>
@endsection
