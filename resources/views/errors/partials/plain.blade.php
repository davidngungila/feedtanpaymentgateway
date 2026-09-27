@php
    $code = $code ?? '500';
    $title = $title ?? 'Something went wrong';
    $message = $message ?? '';
    $tone = $tone ?? 'terracotta';

    $tones = [
        'terracotta' => ['fg' => 'var(--terracotta-600)', 'bg' => 'var(--terracotta-100)'],
        'danger' => ['fg' => 'var(--danger)', 'bg' => 'var(--danger-100)'],
        'gold' => ['fg' => '#8a6418', 'bg' => 'var(--gold-100)'],
        'acacia' => ['fg' => 'var(--acacia-600)', 'bg' => 'var(--acacia-100)'],
    ];
    $t = $tones[$tone] ?? $tones['terracotta'];

    $icons = [
        '400' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>',
        '401' => '<rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
        '403' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>',
        '404' => '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>',
        '405' => '<circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>',
        '408' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
        '419' => '<polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>',
        '422' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
        '429' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
        '500' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
        '501' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.3 2.3-2.7-2.7 2.3-2.3z"></path>',
        '502' => '<rect x="2" y="2" width="20" height="8" rx="2"></rect><rect x="2" y="14" width="20" height="8" rx="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line>',
        '503' => '<rect x="2" y="2" width="20" height="8" rx="2"></rect><rect x="2" y="14" width="20" height="8" rx="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line>',
        '504' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
    ];
    $iconSvg = $icons[$code] ?? '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>';
@endphp
<div style="max-width:520px;margin:0 auto;padding:48px 24px;text-align:center;">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;border-radius:50%;background:{{ $t['bg'] }};color:{{ $t['fg'] }};margin-bottom:20px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:36px;height:36px;">{!! $iconSvg !!}</svg>
    </div>
    <h1 style="margin:0 0 8px;font-size:22px;color:var(--coffee-800);">{{ $code }} &mdash; {{ $title }}</h1>
    <p style="margin:0;color:var(--ink-soft);font-size:14px;line-height:1.6;">{{ $message }}</p>
    <div style="margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn btn-primary">Go to dashboard</a>
        <a href="javascript:history.length > 1 ? history.back() : window.location.href = '/'" class="btn btn-ghost">Go back</a>
    </div>
</div>
