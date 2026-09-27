@props([
    'route' => null,
    'columns' => [],
    'title' => 'Export',
    'subtitle' => null,
    'filters' => [],
])

@php
    $modalId = 'exportModal_'.md5($route ?? $title);
    $formId = 'exportForm_'.md5($route ?? $title);
@endphp

<button type="button" class="btn btn-ghost" onclick="openModal('{{ $modalId }}')" style="display:inline-flex;align-items:center;gap:6px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
    Export
</button>

<div class="modal-backdrop" id="{{ $modalId }}">
    <div class="modal" style="max-width:560px;">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">Export {{ $title }}</h3>
                @if($subtitle)<p class="cell-sub" style="margin:4px 0 0;">{{ $subtitle }}</p>@endif
            </div>
            <button type="button" class="modal-close" onclick="closeModal('{{ $modalId }}')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="{{ $formId }}" method="GET" action="{{ $route }}" class="modal-body" style="padding-top:12px;">
            {{-- Preserve current filters as hidden inputs --}}
            @foreach($filters as $key => $value)
                @if($value !== null && $value !== '' && $value !== 'all')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <div class="field">
                <label style="display:flex;align-items:center;justify-content:space-between;">
                    <span>Columns to include</span>
                    <span style="display:flex;gap:8px;">
                        <button type="button" class="btn btn-ghost" style="padding:4px 8px;font-size:11px;" onclick="exportSelectAll('{{ $formId }}', true)">Select all</button>
                        <button type="button" class="btn btn-ghost" style="padding:4px 8px;font-size:11px;" onclick="exportSelectAll('{{ $formId }}', false)">None</button>
                    </span>
                </label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;max-height:240px;overflow-y:auto;padding:8px;background:var(--sand-100);border:1px solid var(--line);border-radius:8px;">
                    @foreach($columns as $col)
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--coffee-700);cursor:pointer;padding:4px 6px;border-radius:6px;background:var(--white);border:1px solid var(--line);">
                            <input type="checkbox" name="columns[]" value="{{ $col['key'] }}" checked style="width:14px;height:14px;">
                            <span>{{ $col['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="cell-sub" style="margin-top:6px;">Uncheck to hide columns from the exported file. At least one column must be selected.</div>
            </div>

            <div class="field">
                <label>Export format</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:6px;">
                    <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--line);border-radius:10px;cursor:pointer;background:var(--white);">
                        <input type="radio" name="format" value="pdf" checked style="width:16px;height:16px;">
                        <span style="display:flex;align-items:center;gap:8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:#B33A3A;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                            <span>
                                <strong style="display:block;font-size:13px;">PDF Document</strong>
                                <span class="cell-sub" style="font-size:11px;">Printable, styled report</span>
                            </span>
                        </span>
                    </label>
                    <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1.5px solid var(--line);border-radius:10px;cursor:pointer;background:var(--white);">
                        <input type="radio" name="format" value="excel" style="width:16px;height:16px;">
                        <span style="display:flex;align-items:center;gap:8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:#5E6E3F;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M8 13h2a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H8v6z"></path><path d="M15 13a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2v-6h2z"></path></svg>
                            <span>
                                <strong style="display:block;font-size:13px;">Excel Sheet</strong>
                                <span class="cell-sub" style="font-size:11px;">.xlsx, editable</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="modal-foot" style="padding:8px 0 0;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('{{ $modalId }}')">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="return exportValidate('{{ $formId }}')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline></svg>
                    Download
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function exportSelectAll(formId, checked) {
        document.querySelectorAll('#' + formId + ' input[name="columns[]"]').forEach(cb => cb.checked = checked);
    }
    function exportValidate(formId) {
        const checked = document.querySelectorAll('#' + formId + ' input[name="columns[]"]:checked');
        if (checked.length === 0) {
            if (typeof toast === 'function') toast('Select at least one column', 'error'); else alert('Select at least one column');
            return false;
        }
        const modal = document.getElementById(formId).closest('.modal-backdrop');
        if (modal) modal.classList.remove('show');
        if (typeof toast === 'function') toast('Preparing download…', 'success');
        return true;
    }
</script>
