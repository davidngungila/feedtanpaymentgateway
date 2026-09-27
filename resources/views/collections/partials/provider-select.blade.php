{{-- Expects: $providers (meta), $selected (default 'auto'), $name (default 'provider'), $allowAuto (default false) --}}
<select name="{{ $name ?? 'provider' }}" data-provider-select required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:8px;background:var(--white);font-size:14px;font-family:inherit">
    @if ($allowAuto ?? false)
        <option value="auto" {{ ($selected ?? 'auto') === 'auto' ? 'selected' : '' }}>Auto-detect from phone number</option>
    @endif
    @foreach ($providers as $code => $meta)
        <option value="{{ $code }}" {{ ($selected ?? '') === $code ? 'selected' : '' }}
            data-prefixes="{{ implode(',', $meta['prefixes'] ?? []) }}"
            data-fee-percent="{{ $meta['fee']['percent'] ?? 0 }}"
            data-fee-flat="{{ $meta['fee']['flat'] ?? 0 }}">● {{ $meta['name'] }}</option>
    @endforeach
</select>
<div class="prov-hint" data-provider-hint></div>
<script>
(function(){
    function fmt(n){ return 'TZS ' + Number(n || 0).toLocaleString('en-US', {maximumFractionDigits: 0}); }
    function update(form){
        var sel = form.querySelector('select[data-provider-select]');
        var hint = form.querySelector('[data-provider-hint]');
        if(!sel || !hint) return;
        var opt = sel.options[sel.selectedIndex];
        var prefixes = opt ? (opt.getAttribute('data-prefixes') || '') : '';
        var feeP = parseFloat(opt ? (opt.getAttribute('data-fee-percent') || '0') : '0') || 0;
        var feeF = parseFloat(opt ? (opt.getAttribute('data-fee-flat') || '0') : '0') || 0;
        var amtInput = form.querySelector('input[name="amount"]');
        var amt = amtInput ? parseFloat(amtInput.value) || 0 : 0;
        var html = '';
        if(sel.value === 'auto'){
            html += 'Network is detected from the phone prefix (74/75/76 M-Pesa · 68/69/78 Airtel · 65/67/71 Mixx · 62 HaloPesa · 73 T-Pesa).';
        } else if(prefixes){
            html += 'Prefixes: <strong>' + prefixes.split(',').join(', ') + '</strong>. ';
        }
        if(amt > 0){
            var fee = Math.round((amt * feeP / 100 + feeF) * 100) / 100;
            html += 'Fee ≈ <strong>' + fmt(fee) + '</strong> · Net ≈ <strong>' + fmt(amt - fee) + '</strong>.';
        } else {
            html += 'Enter an amount to preview fee & net.';
        }
        hint.innerHTML = html;
    }
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('form').forEach(function(form){
            var sel = form.querySelector('select[data-provider-select]');
            if(!sel) return;
            update(form);
            sel.addEventListener('change', function(){ update(form); });
            var amt = form.querySelector('input[name="amount"]');
            if(amt) amt.addEventListener('input', function(){ update(form); });
        });
    });
})();
</script>
