@if (session('success'))
    <div class="flash ok">{{ session('success') }}</div>
@endif
@if (session('plain_key'))
    <div class="flash info">Full API key (shown once): <code class="mono">{{ session('plain_key') }}</code></div>
@endif
@if (session('plain_secret'))
    <div class="flash info">New webhook secret (shown once): <code class="mono">{{ session('plain_secret') }}</code></div>
@endif
@if ($errors->any())
    <div class="flash err">{{ $errors->first() }}</div>
@endif
