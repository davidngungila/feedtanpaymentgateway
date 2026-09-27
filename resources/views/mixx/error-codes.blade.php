@extends('layouts.app')

@section('title', 'Mixx error codes')

@section('head')
@include('collections.partials.head')
@endsection

@section('content')
<div class="view-head">
    <div><h2>Mixx error engine</h2><p class="sub">Every provider code maps to one internal status. error111 → UNKNOWN, TXNSTATUS 100 → HOLD.</p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('mixx.dashboard') }}">Back</a></div>
</div>

<div class="prov-layout">
@include('mixx.partials.rail', ['code' => 'mixx', 'active' => null, 'mixxActive' => 'error-codes'])
<div class="prov-content">
<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>Code</th><th>Description</th><th>Internal</th><th>Retryable</th><th>Manual review</th></tr></thead>
            <tbody>
                @forelse ($codes as $c)
                    <tr>
                        <td class="mono">{{ $c->code }}</td>
                        <td>{{ $c->description }}</td>
                        <td><span class="pill pill-{{ strtolower($c->internal_status) }}">{{ $c->internal_status }}</span></td>
                        <td>{{ $c->retryable ? 'yes' : 'no' }}</td>
                        <td>{{ $c->requires_manual_review ? 'yes' : 'no' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No error codes seeded. Run the MixxErrorCodesSeeder.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $codes->links() }}</div>
</div>
</div>
@endsection
