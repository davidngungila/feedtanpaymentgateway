@extends('layouts.app')

@section('title', 'Customers')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Customers</h2><p class="sub">Phones are encrypted at rest and masked everywhere — search works on a blind index.</p></div>
    <div class="view-actions"><a class="btn btn-primary" href="{{ route('customers.create') }}">+ Add customer</a></div>
</div>

@include('collections.partials.flash')

<div class="table-card" style="margin-bottom:18px;">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('customers.index') }}" class="filterbar" style="width:100%">
            <div class="field" style="flex:1;"><label style="font-size:11px;">Search name or phone</label><input type="text" name="q" value="{{ $q }}" placeholder="Juma or 255…"></div>
            <div><button class="btn btn-ghost" type="submit">Search</button></div>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Customer</th><th>Phone (masked)</th><th>Transactions</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td class="mono">{{ $c->maskedPhone() }}</td>
                        <td>{{ $c->transactions_count }}</td>
                        <td><span class="pill pill-{{ strtolower($c->status) }}">{{ $c->status }}</span></td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('customers.show', $c) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No customers yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $customers->links() }}</div>
@endsection
