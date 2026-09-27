{{-- Expects: $provider, $endpoint, $active. Opens .prov-layout + content div (views must close both). --}}
<div class="view-head">
    <div><h2><span class="prov-dot" style="background:{{ $provider->color }};width:12px;height:12px;"></span>{{ $provider->name }}</h2>
    <p class="sub">Driver: {{ $provider->driver }} · Webhook endpoint: <code class="mono">{{ $endpoint }}</code></p></div>
    <div class="view-actions"><a class="btn btn-ghost" href="{{ route('providers.index') }}">All providers</a></div>
</div>

@include('collections.partials.flash')

<div class="prov-layout">
    @if ($provider->code === 'mixx')
        @include('mixx.partials.rail', ['code' => 'mixx', 'active' => $active, 'mixxActive' => null])
    @else
    <nav class="prov-tabs" aria-label="{{ $provider->name }} sections">
        <a href="{{ route('providers.collections', $provider->code) }}" class="{{ ($active ?? '') === 'collections' ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>Collections</a>
        <a href="{{ route('providers.status', $provider->code) }}" class="{{ ($active ?? '') === 'status' ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>Status Queries</a>
        <a href="{{ route('providers.webhooks', $provider->code) }}" class="{{ ($active ?? '') === 'webhooks' ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>Webhooks</a>
        <a href="{{ route('providers.logs', $provider->code) }}" class="{{ ($active ?? '') === 'logs' ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Logs</a>
        <a href="{{ route('providers.config.page', $provider->code) }}" class="{{ ($active ?? '') === 'config' ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.2 4.2l2.8 2.8M17 17l2.8 2.8M1 12h4M19 12h4M4.2 19.8 7 17M17 7l2.8-2.8"></path></svg>Configuration</a>
        <a href="{{ route('providers.credentials.page', $provider->code) }}" class="{{ ($active ?? '') === 'credentials' ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>Credentials</a>
    </nav>
    @endif
    <div class="prov-content">
