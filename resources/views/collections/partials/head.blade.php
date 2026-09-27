<style>
    .pill{display:inline-flex;align-items:center;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
    .pill-pending{background:var(--gold-100);color:#8a6418}
    .pill-processing{background:#DCE6F2;color:#1D4E89}
    .pill-success,.pill-completed,.pill-approved,.pill-active,.pill-settled,.pill-paid,.pill-issued{color:var(--acacia-600);background:var(--acacia-100)}
    .pill-failed,.pill-cancelled,.pill-revoked,.pill-expired{background:var(--danger-100);color:var(--danger)}
    .pill-reversed{background:#E8DFF2;color:#5B3E96}
    .pill-unknown{background:#E4D7C2;color:#4D3422}
    .pill-hold{background:#DCE6F2;color:#1D4E89}
    .pill-neutral,.pill-draft,.pill-sent,.pill-unmatched,.pill-eligible,.pill-batched,.pill-pending_approval{background:var(--sand-200);color:var(--coffee-700)}
    .flash{border-radius:10px;padding:12px 14px;font-size:13.5px;font-weight:600;margin-bottom:16px}
    .flash.ok{background:var(--acacia-100);color:var(--acacia-600)}
    .flash.err{background:var(--danger-100);color:var(--danger)}
    .flash.info{background:var(--gold-100);color:#8a6418}
    .mono{font-family:ui-monospace,monospace;font-size:12px}
    .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
    .filterbar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
    .filterbar .field{margin-bottom:0;min-width:160px}
    .filterbar select,.filterbar input[type="text"],.filterbar input[type="date"]{width:100%;padding:9px 12px;border:1.5px solid var(--line);border-radius:8px;background:var(--white);font-size:13px;font-family:inherit}
    .prov-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;vertical-align:baseline}
    .prov-layout{display:grid;grid-template-columns:220px minmax(0,1fr);gap:18px;align-items:start;}
    .prov-tabs{position:sticky;top:88px;background:var(--white);border:1px solid var(--line);border-radius:14px;padding:10px;display:flex;flex-direction:column;gap:3px;}
    .prov-tabs a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;font-size:13.5px;font-weight:600;color:var(--coffee-700);text-decoration:none;}
    .prov-tabs a:hover{background:var(--sand-100);}
    .prov-tabs a.active{background:var(--coffee-900);color:#fff;}
    .prov-tabs a svg{width:16px;height:16px;flex:none;}
    .prov-tabs-label{font-size:10.5px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--ink-soft);padding:10px 12px 2px;}
    @media (max-width:900px){.prov-layout{grid-template-columns:1fr;}.prov-tabs{position:static;flex-direction:row;overflow-x:auto;}.prov-tabs a{white-space:nowrap;}.prov-tabs-label{display:none;}}
    .create-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:18px;align-items:start;max-width:1080px;}
    .create-grid .settings-panel{margin:0;}
    .detail-list{margin:0;padding-left:18px;font-size:13.5px;line-height:1.9;color:var(--coffee-700);}
    .detail-list li{margin-bottom:6px;}
    .kv{display:flex;justify-content:space-between;gap:10px;font-size:13px;padding:7px 0;border-bottom:1px solid var(--line);}
    .kv:last-child{border-bottom:none;}
    .kv .k{color:var(--ink-soft);}
    .kv .v{font-weight:700;color:var(--coffee-900);text-align:right;}
    .prov-hint{font-size:12px;color:var(--ink-soft);margin-top:6px;line-height:1.6;}
    @media (max-width:900px){.create-grid{grid-template-columns:1fr;}}
</style>
