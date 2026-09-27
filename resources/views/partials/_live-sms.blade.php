@php
    // Shared live SMS client used by every listening page (sms.index,
    // devices.show, networks.show). Configures itself from page data:
    //
    //   smsLiveStart({
    //     stream: url, since: maxSmsId, updated: isoWatermark,
    //     body: '#tbodySelector', maxRows: 300,
    //     filter(data) -> bool,   // gate rendering per page
    //     onMessage(data, prev)->bool, // side effects (counts); return filter result
    //     cells(data) -> [tdHtml, ...],
    //     rowClick(data), openBody(data),
    //   });
@endphp
<script>
(function () {
    if (window.smsLiveStart) return;

    window.smsCache = window.smsCache || new Map();

    window.smsLiveStart = function (cfg) {
        let lastId = Number(cfg.since || 0);
        let lastUpdated = cfg.updated || new Date().toISOString();
        let openTimer = null;

        function bindRow(tr, data) {
            const view = tr.querySelector('[data-sms-view]');
            if (view) {
                view.onclick = (e) => {
                    e.stopPropagation();
                    if (cfg.openBody) cfg.openBody(data);
                };
            }
            tr.onclick = null;
            if (cfg.rowClick) {
                tr.style.cursor = 'pointer';
                tr.onclick = () => cfg.rowClick(data);
            }
        }

        function renderRow(data) {
            const tr = document.createElement('tr');
            tr.dataset.smsId = data.sms_id;
            tr.innerHTML = cfg.cells(data).join('');
            bindRow(tr, data);
            return tr;
        }

        function upsert(data) {
            const body = document.querySelector(cfg.body);
            if (!body) return;
            const existing = body.querySelector('tr[data-sms-id="' + data.sms_id + '"]');

            if (!existing) {
                const empty = body.querySelector('.empty-state');
                const emptyTr = empty ? empty.closest('tr') : null;
                if (emptyTr) emptyTr.remove();

                body.prepend(renderRow(data));

                if (cfg.maxRows) {
                    while (body.rows.length > cfg.maxRows) {
                        const last = body.lastElementChild;
                        if (last) last.remove();
                    }
                }
                return;
            }
            existing.innerHTML = cfg.cells(data).join('');
            bindRow(existing, data);
        }

        function open() {
            if (openTimer) clearTimeout(openTimer);
            let url;
            try {
                url = new URL(cfg.stream, window.location.origin);
            } catch (err) {
                return;
            }
            url.searchParams.set('since', lastId);
            url.searchParams.set('updated', lastUpdated);

            const source = new EventSource(url.href);
            source.onmessage = (e) => {
                if (e.data === 'ping') return;
                let data;
                try {
                    data = JSON.parse(e.data);
                } catch (err) {
                    return;
                }
                if (!data || !data.sms_id) return;
                data.sms_id = Number(data.sms_id);
                if (data.sms_id > lastId) lastId = data.sms_id;
                if (data.updated_at && data.updated_at > lastUpdated) lastUpdated = data.updated_at;
                data.label = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : '';

                const prev = window.smsCache.get(data.sms_id);
                window.smsCache.set(data.sms_id, data);

                if (cfg.onMessage && cfg.onMessage(data, prev) === false) return;
                if (cfg.filter && !cfg.filter(data)) return;

                upsert(data);
            };
            source.onerror = () => {
                source.close();
                openTimer = setTimeout(open, 4000);
            };
        }
        open();
    };

    window.smsEsc = function (s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    };

    window.smsTd = function (content, cls) {
        return '<td' + (cls ? ' class="' + cls + '"' : '') + '>' + content + '</td>';
    };

    window.smsDeviceCell = function (data) {
        const name = window.smsEsc(data.device && data.device !== '—' ? data.device : '—');
        if (data.device && data.device_route) {
            return window.smsTd('<a href="/devices/' + encodeURIComponent(data.device_route) + '" class="cell-title">' + name + '</a>');
        }
        return window.smsTd('<span class="cell-title">' + name + '</span>');
    };

    window.smsNetworkCell = function (data) {
        if (!data.network || data.network === '—') return window.smsTd('—');
        return window.smsTd('<span style="display:inline-flex;align-items:center;gap:7px;"><span style="width:9px;height:9px;border-radius:50%;background:' + window.smsEsc(data.network_color) + ';display:inline-block;"></span>' + window.smsEsc(data.network) + '</span>');
    };

    window.smsCustomerCell = function (data) {
        let html = '<div class="cell-title">' + window.smsEsc(data.customer && data.customer !== '—' ? data.customer : '—') + '</div>';
        if (data.customer_phone) html += '<div class="cell-sub">' + window.smsEsc(data.customer_phone) + '</div>';
        return window.smsTd(html);
    };

    window.smsRefCell = function (data) {
        let html = '<div class="cell-title">' + window.smsEsc(data.reference || '—') + '</div>';
        if (data.txn_reference) {
            html += '<div class="cell-sub">→ <a href="/transactions?q=' + encodeURIComponent(data.txn_reference) + '">' + window.smsEsc(data.txn_reference) + '</a></div>';
        }
        return window.smsTd(html);
    };

    window.smsStatusCell = function (data) {
        return window.smsTd('<span class="tag ' + window.smsEsc(data.badge) + '">' + window.smsEsc(data.label || data.status) + '</span>');
    };

    window.smsViewCell = function () {
        return window.smsTd('<button type="button" class="btn btn-ghost btn-sm" data-sms-view>View</button>');
    };
})();
</script>