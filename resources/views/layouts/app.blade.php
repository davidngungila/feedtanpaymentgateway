<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" type="image/png" href="/favicon-64.png">
    <title>@yield('title', 'Dashboard') · FeedTan Collections</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        :root{
            --sand-50:#FBF7EF;
            --sand-100:#F4ECDC;
            --sand-200:#E9DCC0;
            --coffee-900:#2A1B10;
            --coffee-800:#3B2718;
            --coffee-700:#4D3422;
            --coffee-500:#7A5C42;
            --coffee-300:#A98968;
            --terracotta-600:#C2592B;
            --terracotta-500:#D06B3A;
            --terracotta-100:#F6E1D3;
            --acacia-600:#5E6E3F;
            --acacia-500:#7A8450;
            --acacia-100:#E2E7D4;
            --gold-500:#D4A24C;
            --gold-100:#F7E9CB;
            --ink:#241408;
            --ink-soft:#6B5A48;
            --line:#E4D7C2;
            --white:#FFFFFF;
            --danger:#B33A3A;
            --danger-100:#F6DCDA;
            --success:#3F6B3F;
            --radius-sm:8px;
            --radius-md:14px;
            --radius-lg:20px;
            --shadow-sm:0 1px 2px rgba(42,27,16,.08);
            --shadow-md:0 8px 24px rgba(42,27,16,.10);
            --shadow-lg:0 20px 48px rgba(42,27,16,.18);
            --sidebar-w:264px;
            --sidebar-w-collapsed:76px;
            --topbar-h:72px;
        }

        *{box-sizing:border-box;}
        html,body{height:100%;}
        body{
            margin:0;
            font-family:'Raleway',sans-serif;
            background:var(--sand-50);
            color:var(--ink);
            -webkit-font-smoothing:antialiased;
            overflow-x:hidden;
        }
        ::selection{background:var(--terracotta-100);color:var(--coffee-900);}
        h1,h2,h3,h4{font-family:'Raleway',sans-serif;margin:0;color:var(--coffee-900);letter-spacing:-0.01em;}
        p{margin:0;}
        a{color:inherit;text-decoration:none;}
        button{font-family:inherit;cursor:pointer;}
        input,select,textarea{font-family:inherit;}
        ::-webkit-scrollbar{width:9px;height:9px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--coffee-300);border-radius:10px;}
        ::-webkit-scrollbar-thumb:hover{background:var(--coffee-500);}

        /* Sidebar */
        .sidebar{
            position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);z-index:200;
            background:var(--coffee-900);
            background-image:radial-gradient(circle at 0% 0%, rgba(212,162,76,.10), transparent 55%);
            display:flex;flex-direction:column;
            transition:width .25s ease, transform .25s ease;
            border-right:1px solid rgba(255,255,255,.06);
        }
        .sidebar.collapsed{width:var(--sidebar-w-collapsed);}
        .sb-brand{
            display:flex;align-items:center;gap:12px;padding:22px 20px;
            border-bottom:1px solid rgba(255,255,255,.08);min-height:var(--topbar-h);
        }
        .sb-mark{
            width:38px;height:38px;border-radius:10px;flex:none;
            background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));
            display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);color:#fff;
        }
        .sb-mark svg{width:21px;height:21px;}
        .sb-brand-text{overflow:hidden;white-space:nowrap;}
        .sb-brand-text strong{display:block;color:#fff;font-family:'Raleway',sans-serif;font-size:15.5px;line-height:1.2;}
        .sb-brand-text span{display:block;color:var(--gold-500);font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;}
        .sidebar.collapsed .sb-brand-text{display:none;}
        .sb-nav{flex:1;overflow-y:auto;padding:16px 12px;}
        .sb-section-label{
            color:rgba(255,255,255,.32);font-size:10.5px;font-weight:700;letter-spacing:.09em;
            text-transform:uppercase;padding:14px 12px 8px;
        }
        .sidebar.collapsed .sb-section-label{display:none;}
        .sb-item{
            display:flex;align-items:center;gap:13px;padding:11px 12px;border-radius:10px;
            color:rgba(255,255,255,.62);font-size:14px;font-weight:500;margin-bottom:2px;
            position:relative;transition:background .15s,color .15s;white-space:nowrap;
        }
        .sb-item:hover{background:rgba(255,255,255,.06);color:#fff;}
        .sb-item.active{background:rgba(212,162,76,.16);color:var(--gold-500);}
        .sb-item.active::before{
            content:"";position:absolute;left:-12px;top:8px;bottom:8px;width:3px;border-radius:3px;
            background:var(--gold-500);
        }
        .sb-item svg{width:19px;height:19px;flex:none;}
        .sb-item .badge{
            margin-left:auto;background:var(--terracotta-600);color:#fff;font-size:11px;font-weight:700;
            padding:1px 7px;border-radius:20px;
        }
        .sidebar.collapsed .sb-item span:not(.badge){display:none;}
        .sidebar.collapsed .sb-item .badge{display:none;}
        .sidebar.collapsed .sb-item{justify-content:center;}
        .sb-drop{position:relative;}
        .sb-drop-toggle{width:100%;cursor:pointer;background:none;border:none;font-family:inherit;display:flex;align-items:center;gap:13px;color:rgba(255,255,255,.62);font-size:14px;font-weight:500;}
        .sb-drop-toggle .chev{margin-left:auto;opacity:.55;transition:transform .25s ease;width:15px;height:15px;flex:none;}
        .sb-drop.open .sb-drop-toggle .chev{transform:rotate(180deg);}
        .sb-drop.open .sb-drop-toggle{color:var(--gold-500);}
        .sb-drop-menu{display:none;margin:2px 0 4px;padding-left:12px;}
        .sb-drop.open .sb-drop-menu{display:block;}
        .sidebar.collapsed .sb-drop-menu{display:none;}
        /* nested provider dropdowns */
        .sb-drop .sb-drop .sb-drop-toggle{font-size:13px;padding:9px 12px;color:rgba(255,255,255,.55);}
        .sb-drop .sb-drop .sb-drop-toggle .chev{width:13px;height:13px;}
        .sb-drop .sb-drop .sb-drop-menu{padding-left:10px;}
        .sb-drop .sb-drop.open .sb-drop-toggle{color:var(--gold-500);}
        .sb-drop-sub{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:8px;margin-bottom:1px;color:rgba(255,255,255,.55);font-size:13px;font-weight:500;text-decoration:none;transition:background .15s ease,color .15s ease;}
        .sb-drop-sub:hover{color:#fff;background:rgba(255,255,255,.06);}
        .sb-drop-sub.active{color:var(--gold-500);background:rgba(212,162,76,.12);}
        .sb-drop-sub svg{width:14px;height:14px;flex:none;}
        .sb-footer{padding:14px 20px 20px;border-top:1px solid rgba(255,255,255,.08);}
        .sb-user{display:flex;align-items:center;gap:11px;}
        .sb-avatar{
            width:36px;height:36px;border-radius:50%;flex:none;
            background:linear-gradient(155deg,var(--acacia-500),var(--acacia-600));
            display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;
        }
        .sb-user-text{overflow:hidden;white-space:nowrap;}
        .sb-user-text strong{display:block;color:#fff;font-size:13.5px;}
        .sb-user-text span{display:block;color:rgba(255,255,255,.5);font-size:11.5px;}
        .sidebar.collapsed .sb-user-text{display:none;}

        /* Main */
        .main{margin-left:var(--sidebar-w);transition:margin-left .25s ease;min-height:100vh;display:flex;flex-direction:column;}
        .sidebar.collapsed ~ .main{margin-left:var(--sidebar-w-collapsed);}
        .topbar{
            position:sticky;top:0;z-index:100;height:var(--topbar-h);
            background:rgba(251,247,239,.86);backdrop-filter:blur(10px);
            border-bottom:1px solid var(--line);
            display:flex;align-items:center;gap:16px;padding:0 28px;
        }
        .tb-toggle{
            width:38px;height:38px;border-radius:10px;border:1.5px solid var(--line);background:var(--white);
            display:flex;align-items:center;justify-content:center;flex:none;color:var(--coffee-700);
        }
        .tb-toggle svg{width:18px;height:18px;color:var(--coffee-700);}
        .tb-toggle:hover{background:var(--sand-100);}
        .tb-search{
            flex:1;max-width:420px;display:flex;align-items:center;gap:10px;
            background:var(--white);border:1.5px solid var(--line);border-radius:11px;padding:9px 14px;
        }
        .tb-search svg{width:17px;height:17px;color:var(--ink-soft);flex:none;}
        .tb-search input{border:none;outline:none;background:transparent;font-size:14px;width:100%;color:var(--ink);}
        .tb-right{margin-left:auto;display:flex;align-items:center;gap:10px;}
        .tb-iconbtn{
            width:40px;height:40px;border-radius:11px;border:1.5px solid var(--line);background:var(--white);
            display:flex;align-items:center;justify-content:center;position:relative;color:var(--coffee-700);
        }
        .tb-iconbtn:hover{background:var(--sand-100);}
        .tb-iconbtn svg{width:18px;height:18px;}
        .tb-dot{
            position:absolute;top:7px;right:7px;width:8px;height:8px;border-radius:50%;
            background:var(--terracotta-600);border:2px solid var(--sand-50);
        }
        .tb-live{
            display:flex;align-items:center;gap:7px;background:var(--acacia-100);color:var(--acacia-600);
            padding:7px 13px;border-radius:20px;font-size:12.5px;font-weight:700;
        }
        .tb-live::before{
            content:"";width:7px;height:7px;border-radius:50%;background:var(--acacia-600);box-shadow:0 0 0 0 rgba(94,110,63,.5);animation:pulse 2s infinite;
        }
        @keyframes pulse{
            0%{box-shadow:0 0 0 0 rgba(94,110,63,.45);}
            70%{box-shadow:0 0 0 7px rgba(94,110,63,0);}
            100%{box-shadow:0 0 0 0 rgba(94,110,63,0);}
        }
        .tb-user{
            display:flex;align-items:center;gap:9px;
            background:var(--white);border:1.5px solid var(--line);border-radius:11px;padding:5px 10px 5px 5px;
            transition:background .15s;cursor:pointer;font-family:inherit;
        }
        .tb-user:hover{background:var(--sand-100);}
        .tb-user-avatar{
            width:30px;height:30px;border-radius:50%;flex:none;
            background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));
            display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;
        }
        .tb-user-avatar.acacia{background:linear-gradient(155deg,var(--acacia-500),var(--acacia-600));}
        .tb-user-avatar.gold{background:linear-gradient(155deg,#C2912F,var(--gold-500));}
        .tb-user-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
        .tb-user-text{overflow:hidden;white-space:nowrap;}
        .tb-user-text b{display:block;color:var(--coffee-900);font-size:12.5px;line-height:1.25;}
        .tb-user-text span{display:block;color:var(--ink-soft);font-size:11px;}
        .tb-user-wrap{position:relative;}
        .tb-user-chev{transition:transform .2s;}
        .tb-user-wrap.open .tb-user-chev{transform:rotate(180deg);}
        .tb-user-menu{
            position:absolute;top:calc(100% + 8px);right:0;min-width:215px;
            background:var(--white);border:1px solid var(--line);border-radius:12px;
            box-shadow:var(--shadow-lg);padding:6px;display:none;z-index:300;
        }
        .tb-user-wrap.open .tb-user-menu{display:block;}
        .tb-user-menu a,.tb-user-menu button{
            display:flex;align-items:center;gap:10px;width:100%;text-align:left;
            padding:10px 12px;border-radius:9px;border:none;background:none;cursor:pointer;
            font-size:13.5px;font-weight:600;color:var(--coffee-700);text-decoration:none;font-family:inherit;
        }
        .tb-user-menu a svg,.tb-user-menu button svg{width:16px;height:16px;color:var(--ink-soft);}
        .tb-user-menu a:hover,.tb-user-menu button:hover{background:var(--sand-100);}
        .tb-user-menu button.danger{color:var(--danger);}
        .tb-user-menu button.danger svg{color:var(--danger);}
        .tb-notif-wrap{position:relative;}
        .tb-notif-wrap::after{content:"";position:absolute;top:100%;left:0;right:0;height:10px;}
        .tb-notif-menu{
            position:absolute;top:calc(100% + 10px);right:0;width:345px;
            background:var(--white);border:1px solid var(--line);border-radius:12px;
            box-shadow:var(--shadow-lg);padding:10px;display:none;z-index:320;
        }
        .tb-notif-wrap:hover .tb-notif-menu,
        .tb-notif-wrap:focus-within .tb-notif-menu{display:block;}
        .tb-notif-head{display:flex;align-items:center;justify-content:space-between;padding:6px 6px 10px;}
        .tb-notif-head b{font-size:15px;color:var(--coffee-900);}
        .tb-notif-count{background:var(--terracotta-100);color:var(--terracotta-600);font-size:11px;font-weight:800;padding:2px 8px;border-radius:20px;}
        .tb-notif-tabs{display:flex;gap:6px;background:var(--sand-100);border-radius:10px;padding:4px;margin-bottom:8px;}
        .tb-notif-tabs button{flex:1;border:none;background:transparent;padding:7px 0;border-radius:8px;font-size:12.5px;font-weight:700;color:var(--ink-soft);cursor:pointer;font-family:inherit;}
        .tb-notif-tabs button.active{background:var(--white);color:var(--coffee-900);box-shadow:0 1px 2px rgba(0,0,0,.08);}
        .tb-notif-panel{max-height:340px;overflow-y:auto;overscroll-behavior:contain;}
        .tb-notif-item{display:flex;gap:10px;padding:9px;border-radius:10px;text-decoration:none;}
        .tb-notif-item:hover{background:var(--sand-100);}
        .tb-notif-ico{width:32px;height:32px;border-radius:9px;flex:none;display:flex;align-items:center;justify-content:center;background:var(--sand-100);color:var(--coffee-700);}
        .tb-notif-ico svg{width:15px;height:15px;}
        .tb-notif-body{min-width:0;display:flex;flex-direction:column;gap:1px;}
        .tb-notif-body b{font-size:13px;color:var(--coffee-900);}
        .tb-notif-meta{font-size:12px;color:var(--ink-soft);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .tb-notif-time{display:flex;align-items:center;gap:7px;font-size:11px;color:var(--ink-soft);}
        .tb-notif-empty{color:var(--ink-soft);font-size:13px;text-align:center;padding:22px 10px;}
        .tb-notif-foot{display:flex;justify-content:space-between;gap:8px;border-top:1px solid var(--line);margin-top:8px;padding:10px 6px 4px;}
        .tb-notif-foot a{font-size:12.5px;font-weight:700;color:var(--terracotta-600);text-decoration:none;}
        .tb-notif-foot a:hover{text-decoration:underline;}
        .menu-sep{height:1px;background:var(--line);margin:5px 4px;}
        .view-wrap{padding:28px;flex:1;}
        .view{animation:fadeUp .35s ease;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
        .view-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
        .view-head h2{font-size:27px;}
        .view-head .sub{color:var(--ink-soft);font-size:14px;margin-top:5px;}
        .view-actions{display:flex;gap:10px;flex-wrap:wrap;}


        /* Stats */
        .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:18px;margin-bottom:24px;}
        .stat-card{
            background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);
            padding:20px 20px 18px;box-shadow:var(--shadow-sm);position:relative;overflow:hidden;
        }
        .stat-card::after{content:"";position:absolute;right:-20px;top:-20px;width:90px;height:90px;border-radius:50%;background:var(--stat-tint,var(--terracotta-100));opacity:.5;}
        .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
        .stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:var(--stat-tint,var(--terracotta-100));color:var(--stat-fg,var(--terracotta-600));position:relative;}
        .stat-icon svg{width:20px;height:20px;}
        .stat-trend{font-size:12px;font-weight:700;padding:3px 8px;border-radius:20px;}
        .stat-trend.up{color:var(--success);background:var(--acacia-100);}
        .stat-trend.down{color:var(--danger);background:var(--danger-100);}
        .stat-value{font-family:'Raleway',sans-serif;font-size:30px;color:var(--coffee-900);position:relative;}
        .stat-label{font-size:13px;color:var(--ink-soft);margin-top:4px;position:relative;}


        .panel-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:18px;margin-bottom:24px;}
        .panel{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden;}
        .panel-head{display:flex;align-items:center;justify-content:space-between;padding:18px 20px 14px;border-bottom:1px solid var(--line);}
        .panel-head h3{font-size:16.5px;}
        .panel-head .link{font-size:12.5px;font-weight:700;color:var(--terracotta-600);cursor:pointer;}
        .panel-body{padding:18px 20px 20px;}

        /* Bar Chart */
        .bars{display:flex;align-items:flex-end;gap:10px;height:170px;padding-top:10px;}
        .bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end;}
        .bar-wrap{width:100%;max-width:30px;height:100%;display:flex;align-items:flex-end;gap:3px;}
        .bar{width:100%;flex:1;border-radius:6px 6px 2px 2px;min-height:3px;background:linear-gradient(180deg,var(--terracotta-500),var(--terracotta-600));transition:height .6s cubic-bezier(.2,.8,.2,1);position:relative;}
        .bar.bar-gold{background:linear-gradient(180deg,var(--gold-500),#bb8636);}
        .bar-label{font-size:11px;color:var(--ink-soft);font-weight:600;display:flex;align-items:center;gap:6px;}
        .bar-label .dot{width:7px;height:7px;border-radius:50%;flex:none;}

        .donut-wrap{display:flex;align-items:center;gap:18px;}
        .donut-chart{width:120px;height:120px;border-radius:50%;flex:none;position:relative;}
        .donut-chart .donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;}
        .donut-center b{font-size:14px;color:var(--coffee-900);}
        .donut-center span{font-size:10.5px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.05em;}
        .legend{display:flex;flex-direction:column;gap:10px;flex:1;}
        .legend-item{display:flex;align-items:center;gap:9px;font-size:13px;}
        .legend-dot{width:10px;height:10px;border-radius:3px;flex:none;}
        .legend-item b{margin-left:auto;color:var(--coffee-900);}

        .activity-list{display:flex;flex-direction:column;gap:3px;}
        .activity-row{display:flex;gap:12px;padding:11px 0;border-bottom:1px dashed var(--line);}
        .activity-row:last-child{border-bottom:none;}
        .activity-ico{width:34px;height:34px;border-radius:9px;flex:none;display:flex;align-items:center;justify-content:center;background:var(--sand-100);color:var(--coffee-700);}
        .activity-ico svg{width:16px;height:16px;}
        .activity-text{font-size:13.5px;line-height:1.4;}
        .activity-text b{color:var(--coffee-900);}
        .activity-time{font-size:11.5px;color:var(--ink-soft);margin-top:2px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;}


        /* Tables */
        .table-card{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:24px;}
        .table-toolbar{display:flex;align-items:center;gap:10px;padding:16px 18px;border-bottom:1px solid var(--line);flex-wrap:wrap;}
        .chip-filters{display:flex;gap:8px;flex-wrap:wrap;}
        .chip{padding:7px 14px;border-radius:20px;font-size:12.5px;font-weight:600;background:var(--sand-100);color:var(--coffee-700);border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;text-decoration:none;}
        .chip:hover{background:var(--sand-200);}
        .chip.active{background:var(--coffee-900);color:#fff;}
        .table-search{display:flex;align-items:center;gap:8px;background:var(--sand-50);border:1.5px solid var(--line);border-radius:10px;padding:8px 12px;margin-left:auto;min-width:200px;}
        .table-search svg{width:15px;height:15px;color:var(--ink-soft);}
        .table-search input{border:none;background:transparent;outline:none;font-size:13.5px;width:100%;}
        /* Tabs (page-level tab bars) */
        .tabs{display:flex;gap:4px;background:var(--sand-100);border:1px solid var(--line);border-radius:12px;padding:4px;margin:0 0 24px;flex-wrap:wrap;}
        .tabs .tab-btn{border:none;background:transparent;padding:9px 18px;border-radius:9px;font-size:13.5px;font-weight:700;color:var(--ink-soft);cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:background .15s,color .15s;}
        .tabs .tab-btn:hover{background:rgba(255,255,255,.6);color:var(--coffee-900);}
        .tabs .tab-btn.active{background:var(--white);color:var(--coffee-900);box-shadow:0 1px 2px rgba(0,0,0,.08);}
        .tabs .tab-count{font-size:11px;font-weight:800;background:var(--terracotta-100);color:var(--terracotta-600);padding:1px 8px;border-radius:20px;}
        .tabs .tab-btn.active .tab-count{background:var(--terracotta-600);color:#fff;}

        .led{width:10px;height:10px;border-radius:50%;display:inline-block;flex:none;vertical-align:middle;}
        .led-on{background:var(--acacia-600);box-shadow:0 0 0 0 rgba(31,157,85,.45);animation:ledPulse 1.6s infinite;}
        .led-off{background:#c8c3b8;}
        @keyframes ledPulse{0%{box-shadow:0 0 0 0 rgba(31,157,85,.45);}70%{box-shadow:0 0 0 9px rgba(31,157,85,0);}100%{box-shadow:0 0 0 0 rgba(31,157,85,0);}}
        .tab-panel.hidden{display:none;}
        .table-scroll{overflow-x:auto;}
        .table-pager{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-top:1px solid var(--line);flex-wrap:wrap;}
        .pager-pages{display:flex;gap:6px;align-items:center;flex-wrap:wrap;}
        .pager-pages a,.pager-pages span{min-width:36px;padding:7px 12px;border-radius:8px;font-size:12.5px;font-weight:600;text-align:center;text-decoration:none;border:1.5px solid var(--line);background:var(--white);color:var(--coffee-700);transition:background .15s,border-color .15s;font-family:'Raleway',sans-serif;}
        .pager-pages a:hover{background:var(--sand-100);border-color:var(--coffee-300);}
        .pager-pages span.active{background:var(--coffee-900);color:#fff;border-color:var(--coffee-900);}
        .pager-pages span.disabled{background:var(--sand-100);color:var(--ink-soft);border-color:var(--line);opacity:.6;cursor:not-allowed;}
        .pager-info{font-size:12.5px;color:var(--ink-soft);font-weight:600;}
        table{width:100%;border-collapse:collapse;min-width:680px;}
        thead th{
            text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-soft);
            padding:12px 18px;border-bottom:1px solid var(--line);background:var(--sand-50);font-weight:700;white-space:nowrap;
        }
        tbody td{padding:14px 18px;border-bottom:1px solid var(--line);font-size:13.5px;color:var(--coffee-900);}
        tbody tr:last-child td{border-bottom:none;}
        tbody tr{transition:background .12s;}
        tbody tr:hover{background:var(--sand-50);}
        .cell-main{display:flex;align-items:center;gap:11px;}
        .thumb{width:42px;height:42px;border-radius:9px;object-fit:cover;flex:none;background:var(--sand-200);}
        .cell-title{font-weight:600;color:var(--coffee-900);}
        .cell-sub{font-size:12px;color:var(--ink-soft);margin-top:2px;}
        .tag{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11.5px;font-weight:700;white-space:nowrap;}
        .tag-green{background:var(--acacia-100);color:var(--acacia-600);}
        .tag-gold{background:var(--gold-100);color:#8a6418;}
        .tag-red{background:var(--danger-100);color:var(--danger);}
        .tag-grey{background:var(--sand-200);color:var(--ink-soft);}
        .tag-terracotta{background:var(--terracotta-100);color:var(--terracotta-600);}
        .row-actions{display:flex;gap:6px;justify-content:flex-end;}
        .row-actions button{width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;color:var(--coffee-700);}
        .row-actions button:hover{background:var(--sand-100);}
        .row-actions button svg{width:14.5px;height:14.5px;}
        .row-actions .danger:hover{background:var(--danger-100);color:var(--danger);border-color:var(--danger-100);}
        .row-actions .warn:hover{background:var(--gold-100);color:#8a6418;border-color:var(--gold-100);}
        .pwd-wrap{position:relative;}
        .pwd-wrap input{padding-right:42px;}
        .pwd-toggle{position:absolute;right:4px;top:50%;transform:translateY(-50%);width:32px;height:32px;border:none;background:transparent;cursor:pointer;font-size:14px;opacity:.7;border-radius:8px;}
        .pwd-toggle:hover{opacity:1;background:var(--sand-200);}
        .input-icon-wrap{position:relative;}
        .input-icon-wrap .input-icon{position:absolute;left:14px;top:0;bottom:0;margin:auto 0;width:16px;height:16px;color:var(--coffee-300);pointer-events:none;}
        .field .input-icon-wrap input,.field .input-icon-wrap textarea{padding-left:42px;}
        .settings-section{display:flex;align-items:center;gap:9px;margin:24px 0 4px;padding-top:8px;}
        .settings-section:first-of-type{margin-top:0;padding-top:0;}
        .settings-section::before{content:'';width:22px;height:3px;border-radius:2px;background:var(--gold-500);flex:none;}
        .settings-section h4{margin:0;font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--coffee-700);}
        .empty-state{padding:60px 20px;text-align:center;color:var(--ink-soft);}
        .empty-state svg{width:46px;height:46px;color:var(--coffee-300);margin-bottom:12px;}
        .empty-state h4{margin-bottom:5px;color:var(--coffee-800);}
        .empty-state p{font-size:13.5px;}


        /* Buttons */
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            padding:12px 20px;border-radius:var(--radius-sm);border:none;
            font-weight:600;font-size:14.5px;transition:transform .12s, box-shadow .12s, background .15s;text-decoration:none;
        }
        .btn:active{transform:translateY(1px);}
        .btn-primary{background:var(--terracotta-600);color:#fff;box-shadow:0 6px 16px rgba(194,89,43,.32);}
        .btn-primary:hover{background:var(--terracotta-500);}
        .btn-ghost{background:transparent;color:var(--coffee-700);border:1.5px solid var(--line);}
        .btn-ghost:hover{background:var(--sand-100);}
        .btn-soft{background:var(--sand-100);color:var(--coffee-800);}
        .btn-soft:hover{background:var(--sand-200);}
        .btn-danger{background:var(--danger-100);color:var(--danger);}
        .btn-danger:hover{background:#efc6c2;}
        .btn-sm{padding:8px 13px;font-size:13px;}


        /* Cards / misc layout */
        .card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;}
        .mini-card{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);padding:18px;box-shadow:var(--shadow-sm);}
        .mini-card .mc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
        .mini-card .mc-name{font-weight:700;color:var(--coffee-900);font-size:14.5px;display:flex;align-items:center;gap:8px;}
        .net-dot{width:10px;height:10px;border-radius:50%;flex:none;display:inline-block;}
        .mini-card .mc-big{font-size:22px;color:var(--coffee-900);font-weight:700;}
        .mini-card .mc-label{font-size:12px;color:var(--ink-soft);margin-top:3px;}
        .kpi-row{display:flex;gap:14px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px dashed var(--line);}
        .kpi-item{flex:1;min-width:90px;}
        .kpi-item b{display:block;font-size:14px;color:var(--coffee-900);}
        .kpi-item span{font-size:11.5px;color:var(--ink-soft);}

        .balance-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;}
        .balance-box{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);padding:16px 18px;box-shadow:var(--shadow-sm);}
        .balance-box .bb-label{font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-soft);font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:7px;}
        .balance-box .bb-amount{font-size:21px;font-weight:700;color:var(--coffee-900);}
        .balance-box .bb-sub{font-size:12px;color:var(--ink-soft);margin-top:3px;}


        /* Detail rows / receipts */
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 20px;}
        .detail-item .dk{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-soft);font-weight:700;}
        .detail-item .dv{font-size:14px;color:var(--coffee-900);font-weight:600;margin-top:2px;}
        .receipt{background:var(--sand-100);border:1px dashed var(--coffee-300);border-radius:var(--radius-sm);padding:14px;margin-top:14px;font-size:13px;color:var(--coffee-800);}
        .receipt .receipt-row{display:flex;justify-content:space-between;gap:10px;padding:4px 0;border-bottom:1px dashed var(--line);}
        .receipt .receipt-row:last-child{border-bottom:none;}
        .receipt b{color:var(--coffee-900);}

        .avatar{width:38px;height:38px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;background:linear-gradient(155deg,var(--terracotta-600),var(--gold-500));}
        .avatar.acacia{background:linear-gradient(155deg,var(--acacia-500),var(--acacia-600));}
        .avatar.gold{background:linear-gradient(155deg,#C2912F,var(--gold-500));}
        .avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;}


        /* Settings */
        .settings-layout{display:grid;grid-template-columns:240px 1fr;gap:24px;align-items:start;}
        .settings-nav{display:flex;flex-direction:column;gap:3px;background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);padding:10px;box-shadow:var(--shadow-sm);}
        .settings-nav a{display:flex;align-items:center;gap:10px;text-align:left;padding:11px 13px;border-radius:9px;font-size:13.8px;font-weight:600;color:var(--coffee-700);cursor:pointer;text-decoration:none;}
        .settings-nav a.active{background:var(--sand-100);color:var(--terracotta-600);}
        .settings-nav a svg{width:17px;height:17px;}
        .settings-panel{background:var(--white);border:1px solid var(--line);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);padding:26px;}
        .settings-panel h3{font-size:17px;margin-bottom:18px;}
        .field{margin-bottom:16px;}
        .field label{display:block;font-size:12.5px;font-weight:600;color:var(--coffee-700);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em;}
        .field input,.field select,.field textarea{
            width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:var(--radius-sm);
            background:var(--white);font-size:14.5px;color:var(--ink);transition:border-color .15s, box-shadow .15s;
        }
        .field input:focus,.field select:focus,.field textarea:focus{
            outline:none;border-color:var(--terracotta-500);box-shadow:0 0 0 3px var(--terracotta-100);
        }
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .net-picker{position:relative;}
        .net-picker-btn{
            width:100%;display:flex;align-items:center;justify-content:space-between;gap:10px;
            padding:12px 14px;border:1.5px solid var(--line);border-radius:var(--radius-sm);
            background:var(--white);font-size:14.5px;font-weight:600;color:var(--coffee-700);
            cursor:pointer;transition:border-color .15s,box-shadow .15s;
        }
        .net-picker-btn:hover{border-color:var(--terracotta-500);}
        .net-picker-btn:focus{outline:none;border-color:var(--terracotta-500);box-shadow:0 0 0 3px var(--terracotta-100);}
        .net-picker-menu{
            position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:60;
            background:var(--white);border:1.5px solid var(--line);border-radius:var(--radius-sm);
            box-shadow:var(--shadow-md);padding:8px;max-height:220px;overflow-y:auto;
        }
        .net-picker-menu:not([hidden]){
            display:flex;flex-direction:column;gap:2px;
        }
        .net-option{
            display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;
            font-size:13.5px;font-weight:600;color:var(--coffee-700);cursor:pointer;
        }
        .net-option:hover{background:var(--sand-100);}
        .net-option input{accent-color:var(--terracotta-600);width:16px;height:16px;flex:none;}
        .net-picker-tag{
            display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;
            background:var(--sand-100);color:var(--coffee-700);font-size:12.5px;font-weight:700;white-space:nowrap;
        }
        .net-picker-tag .net-dot{width:8px;height:8px;}
        .toggle-row{display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid var(--line);}
        .toggle-row:last-child{border-bottom:none;}
        .toggle-text strong{display:block;font-size:14px;color:var(--coffee-900);margin-bottom:2px;}
        .toggle-text span{font-size:12.5px;color:var(--ink-soft);}
        .switch{width:42px;height:24px;border-radius:20px;background:var(--sand-200);position:relative;flex:none;border:none;cursor:pointer;transition:background .2s;}
        .switch::after{content:"";position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:transform .2s;}
        .switch.on{background:var(--acacia-500);}
        .switch.on::after{transform:translateX(18px);}


        /* Right-side drawers (replaces centered modals) */
        .modal-backdrop{
            position:fixed;inset:0;background:rgba(36,20,8,.5);backdrop-filter:blur(2px);
            display:none;z-index:400;
        }
        .modal-backdrop.show{display:flex;align-items:flex-start;justify-content:center;padding:40px 20px;overflow-y:auto;}
        .modal{
            position:fixed;top:0;right:0;bottom:0;width:100%;max-width:540px;
            background:var(--sand-50);box-shadow:var(--shadow-lg);
            display:flex;flex-direction:column;
            transform:translateX(105%);transition:transform .32s cubic-bezier(.2,.8,.2,1);
            overflow:hidden;
        }
        .modal-backdrop.show .modal{transform:translateX(0);}
        .modal-head{flex:none;display:flex;align-items:center;justify-content:space-between;padding:22px 26px;border-bottom:1px solid var(--line);}
        .modal-head h3{font-size:19px;}
        .modal-close{width:34px;height:34px;border-radius:9px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;}
        .modal-close:hover{background:var(--sand-100);}
        .modal-body{padding:24px 26px;flex:1 1 auto;min-height:0;overflow-y:auto;max-height:calc(100vh - 170px);overscroll-behavior:contain;}
        .modal-body::-webkit-scrollbar{width:8px;}
        .modal-body::-webkit-scrollbar-thumb{background:var(--coffee-300);border-radius:4px;}
        .modal-body::-webkit-scrollbar-track{background:transparent;}
        .modal-foot{flex:none;display:flex;justify-content:flex-end;gap:10px;padding:18px 26px;border-top:1px solid var(--line);}

        /* Compatibility aliases: older views used .drawer-* / .drawer-backdrop names */
        .drawer-backdrop,.modal-backdrop{position:fixed;inset:0;background:rgba(36,20,8,.5);backdrop-filter:blur(2px);display:none;z-index:400;}
        .drawer-backdrop.show,.modal-backdrop.show{display:flex;align-items:flex-start;justify-content:center;padding:40px 20px;overflow-y:auto;}
        .drawer-backdrop.show .drawer,.modal-backdrop.show .modal{transform:translateX(0);}
        .drawer,.modal{position:fixed;top:0;right:0;bottom:0;width:100%;max-width:540px;background:var(--sand-50);box-shadow:var(--shadow-lg);display:flex;flex-direction:column;transform:translateX(105%);transition:transform .32s cubic-bezier(.2,.8,.2,1);overflow:hidden;}
        /* Centered popup for confirmation dialogs */
        .popup{margin:auto;background:var(--sand-50);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);width:100%;max-width:440px;animation:popupIn .28s cubic-bezier(.2,.8,.2,1);}
        @keyframes popupIn{from{opacity:0;transform:scale(.96) translateY(10px);}to{opacity:1;transform:scale(1) translateY(0);}}
        .drawer-head,.modal-head{flex:none;display:flex;align-items:center;justify-content:space-between;padding:22px 26px;border-bottom:1px solid var(--line);}
        .drawer-head h3,.modal-head h3{font-size:19px;}
        .drawer-close,.modal-close{width:34px;height:34px;border-radius:9px;border:1px solid var(--line);background:var(--white);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;}
        .drawer-close:hover,.modal-close:hover{background:var(--sand-100);}
        .drawer-body,.modal-body{padding:24px 26px;flex:1 1 auto;min-height:0;overflow-y:auto;max-height:calc(100vh - 170px);overscroll-behavior:contain;}
        .drawer-body::-webkit-scrollbar,.modal-body::-webkit-scrollbar{width:8px;}
        .drawer-body::-webkit-scrollbar-thumb,.modal-body::-webkit-scrollbar-thumb{background:var(--coffee-300);border-radius:4px;}
        .drawer-body::-webkit-scrollbar-track,.modal-body::-webkit-scrollbar-track{background:transparent;}
        .drawer-foot,.modal-foot{flex:none;display:flex;justify-content:flex-end;gap:10px;padding:18px 26px;border-top:1px solid var(--line);}


        /* Toast */
        #toastHost{position:fixed;bottom:24px;right:24px;z-index:600;display:flex;flex-direction:column;gap:10px;}
        .toast{
            background:var(--coffee-900);color:#fff;padding:13px 18px;border-radius:11px;font-size:13.5px;font-weight:600;
            box-shadow:var(--shadow-lg);display:flex;align-items:center;gap:10px;min-width:240px;animation:toastIn .3s ease;
        }
        .toast.success{background:var(--acacia-600);}
        .toast.error{background:var(--danger);}
        .toast svg{width:17px;height:17px;flex:none;}
        @keyframes toastIn{from{opacity:0;transform:translateX(20px);}to{opacity:1;transform:translateX(0);}}


        /* Responsive */
        .mobile-overlay{position:fixed;inset:0;background:rgba(36,20,8,.45);z-index:190;display:none;}
        .mobile-overlay.show{display:block;}
        @media (max-width:1180px){
            .panel-grid{grid-template-columns:1fr;}
            .settings-layout{grid-template-columns:1fr;}
        }
        @media (max-width:900px){
            .sidebar{transform:translateX(-100%);width:var(--sidebar-w);z-index:300;}
            .sidebar.mobile-open{transform:translateX(0);}
            .main{margin-left:0 !important;}
            .tb-search{display:none;}
        }
@media (max-width:640px){
            .view-wrap{padding:16px;}
            .topbar{padding:0 14px;gap:10px;}
            .form-row,.detail-grid{grid-template-columns:1fr;}
            .tb-search{display:none;}
            .tb-live span{display:none;}
            .tb-user-text{display:none;}
            .view-head h2{font-size:22px;}
        }
        @media (prefers-reduced-motion:reduce){
            *{animation-duration:.001ms !important;transition-duration:.001ms !important;}
        }
        /* Global loading overlay - shown while pages respond / requests run */
        .global-loader{position:fixed;inset:0;z-index:2000;background:rgba(42,27,16,.45);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;}
        .global-loader.show{display:flex;}
        .global-loader-card{background:var(--sand-50);border-radius:16px;padding:26px 36px;text-align:center;box-shadow:var(--shadow-lg);animation:gloaderPop .25s ease;}
        @keyframes gloaderPop{from{opacity:0;transform:scale(.94);}to{opacity:1;transform:scale(1);}}
        .global-loader-spinner{width:44px;height:44px;border-radius:50%;margin:0 auto 12px;border:4px solid var(--sand-200);border-top-color:var(--terracotta-600);animation:gloaderSpin .8s linear infinite;}
        @keyframes gloaderSpin{to{transform:rotate(360deg);}}
        .global-loader-text{font-size:13.5px;font-weight:700;color:var(--coffee-900);}
        .global-loader-sub{font-size:12px;color:var(--ink-soft);margin-top:4px;}
    </style>
    @yield('head')
</head>
<body>
    <div class="global-loader" id="globalLoader" aria-hidden="true">
        <div class="global-loader-card" role="status" aria-live="polite">
            <div class="global-loader-spinner"></div>
            <div class="global-loader-text">Loading…</div>
            <div class="global-loader-sub">Please wait while the page responds.</div>
        </div>
    </div>
    @php
        $routeName = request()->route() ? request()->route()->getName() : '';
        $isCashPointArea = str_starts_with($routeName, 'cash-point');
        $isTransactionArea = str_starts_with($routeName, 'transactions');
        $isNetworkArea = str_starts_with($routeName, 'networks');
        $isDeviceArea = str_starts_with($routeName, 'devices');
        $isMessageArea = str_starts_with($routeName, 'sms');
        $isFloatArea = str_starts_with($routeName, 'float');
        $isReconArea = str_starts_with($routeName, 'reconciliation');
        $isReportArea = str_starts_with($routeName, 'reports');
        $isFinanceArea = str_starts_with($routeName, 'finance');
        $isUserArea = str_starts_with($routeName, 'users');
        $isAuditArea = str_starts_with($routeName, 'audit');
        $isSettingArea = str_starts_with($routeName, 'settings');
        $isProfileArea = str_starts_with($routeName, 'profile');
        $isAccountArea = str_starts_with($routeName, 'account');
        $isPaymentArea = str_starts_with($routeName, 'payments');
        $isPayoutArea = str_starts_with($routeName, 'payouts');
        $currentUser = auth()->user();
        if (!$currentUser) {
            $currentUser = new class {
                public $name = 'Demo Admin';
                public $role = 'admin';
                public $email = 'admin@feedtan.local';
                public function avatarUrl(){ return ''; }
            };
        }
        try {
            $recentTxns = class_exists(\App\Models\Transaction::class) ? \App\Models\Transaction::query()->latest()->limit(6)->get() : collect();
        } catch (\Throwable $e) { $recentTxns = collect(); }
        $canAccessAudit = in_array($currentUser->role ?? null, ['supervisor', 'admin'], true);
        try {
            $recentAudit = $canAccessAudit && class_exists(\App\Models\AuditLog::class) ? \App\Models\AuditLog::query()->with('user')->latest()->limit(6)->get() : collect();
        } catch (\Throwable $e) { $recentAudit = collect(); }
        $initials = $currentUser && $currentUser->name ? strtoupper(implode('', array_map(fn ($w) => $w[0] ?? '', preg_split('/\s+/', $currentUser->name)))) : 'CP';
        // Payments & Payouts pending counts for sidebar badges (graceful fallback if tables don't exist yet)
        $pendingPayoutsCount = 0;
        $pendingPaymentsCount = 0;
        $pendingCollectionsCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('payouts')) {
                $pendingPayoutsCount = \Illuminate\Support\Facades\DB::table('payouts')->whereIn('status', ['pending', 'awaiting_approval'])->count();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                $pendingPaymentsCount = \Illuminate\Support\Facades\DB::table('payments')->where('status', 'pending')->count();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('provider_transactions')) {
                $pendingCollectionsCount = \Illuminate\Support\Facades\DB::table('provider_transactions')->whereIn('status', ['PENDING', 'PROCESSING'])->count();
            }
        } catch (\Throwable $e) {
            $pendingPayoutsCount = 0;
            $pendingPaymentsCount = 0;
            $pendingCollectionsCount = 0;
        }
    @endphp
    <div id="app">
        <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileSidebar()"></div>

        <aside class="sidebar" id="sidebar">
            <div class="sb-brand">
                <div class="sb-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                </div>
                <div class="sb-brand-text">
                    <strong>Feedtan Payments</strong>
                    <span>Collections · Mobile Money</span>
                </div>
            </div>
            <nav class="sb-nav">
              
                <a href="{{ route('dashboard') }}" class="sb-item {{ $routeName === 'dashboard' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"></rect><rect x="14" y="3" width="7" height="5" rx="1.5"></rect><rect x="14" y="12" width="7" height="9" rx="1.5"></rect><rect x="3" y="16" width="7" height="5" rx="1.5"></rect></svg>
                    <span>Dashboard</span>
                </a>

                
                <div class="sb-drop {{ request()->routeIs('collections.payments.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        <span>Payments</span>
                        @if($pendingCollectionsCount > 0)<span class="badge">{{ $pendingCollectionsCount }}</span>@endif
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('collections.payments.index') }}" class="sb-drop-sub {{ request()->routeIs('collections.payments.index') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>All Transactions</a>
                        <a href="{{ route('collections.payments.create') }}" class="sb-drop-sub {{ request()->routeIs('collections.payments.create') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>Initiate Payment</a>
                    </div>
                </div>

                <div class="sb-drop {{ request()->routeIs('collections.requests*', 'collections.links*', 'collections.invoices*', 'collections.recurring*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>Collections</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('collections.requests') }}" class="sb-drop-sub {{ request()->routeIs('collections.requests*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Payment Requests</a>
                        <a href="{{ route('collections.links') }}" class="sb-drop-sub {{ request()->routeIs('collections.links*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>Payment Links</a>
                        <a href="{{ route('collections.invoices') }}" class="sb-drop-sub {{ request()->routeIs('collections.invoices*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Invoices</a>
                        <a href="{{ route('collections.recurring') }}" class="sb-drop-sub {{ request()->routeIs('collections.recurring*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>Recurring</a>
                    </div>
                </div>
                <div class="sb-drop {{ request()->routeIs('customers.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span>Customers</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('customers.index') }}" class="sb-drop-sub {{ request()->routeIs('customers.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>Customers</a>
                        <a href="{{ route('mm.reports.transactions') }}" class="sb-drop-sub {{ request()->routeIs('mm.reports.transactions') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>Payment History</a>
                    </div>
                </div>

                <a href="{{ route('providers.index') }}" class="sb-item {{ request()->routeIs('providers.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    <span>Mobile Money</span>
                </a>
                <div class="sb-drop {{ request()->routeIs('reconciliation.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                        <span>Reconciliation</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('reconciliation.index') }}" class="sb-drop-sub {{ request()->routeIs('reconciliation.index') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>Reconciliation</a>
                        <a href="{{ route('reconciliation.unmatched') }}" class="sb-drop-sub {{ request()->routeIs('reconciliation.unmatched') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>Unmatched Transactions</a>
                        <a href="{{ route('reconciliation.provider') }}" class="sb-drop-sub {{ request()->routeIs('reconciliation.provider') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>Provider Transactions</a>
                        <a href="{{ route('reconciliation.exceptions') }}" class="sb-drop-sub {{ request()->routeIs('reconciliation.exceptions') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>Exceptions</a>
                    </div>
                </div>

                <div class="sb-drop {{ request()->routeIs('settlements.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"></path><path d="M1 14h6M9 8h6M17 16h6"></path></svg>
                        <span>Settlements</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('settlements.index') }}" class="sb-drop-sub {{ request()->routeIs('settlements.index') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"></path><path d="M1 14h6M9 8h6M17 16h6"></path></svg>Settlements</a>
                        <a href="{{ route('settlements.transactions') }}" class="sb-drop-sub {{ request()->routeIs('settlements.transactions') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>Settlement Transactions</a>
                        <a href="{{ route('settlements.reports') }}" class="sb-drop-sub {{ request()->routeIs('settlements.reports') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Settlement Reports</a>
                    </div>
                </div>
                <div class="sb-drop {{ request()->routeIs('mm.reports.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                        <span>Reports</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('mm.reports.daily') }}" class="sb-drop-sub {{ request()->routeIs('mm.reports.daily') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>Daily Collections</a>
                        <a href="{{ route('mm.reports.providers') }}" class="sb-drop-sub {{ request()->routeIs('mm.reports.providers') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>Provider Reports</a>
                        <a href="{{ route('mm.reports.transactions') }}" class="sb-drop-sub {{ request()->routeIs('mm.reports.transactions') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>Transaction Reports</a>
                        <a href="{{ route('mm.reports.fees') }}" class="sb-drop-sub {{ request()->routeIs('mm.reports.fees') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>Fees</a>
                        <a href="{{ route('mm.reports.export') }}" class="sb-drop-sub"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>Export</a>
                    </div>
                </div>

                @if (auth()->check() && auth()->user()->hasPermission('developers.keys'))
                <div class="sb-drop {{ request()->routeIs('developers.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                        <span>Developers</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                        <a href="{{ route('developers.keys') }}" class="sb-drop-sub {{ request()->routeIs('developers.keys*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>API Keys</a>
                        <a href="{{ route('developers.webhooks') }}" class="sb-drop-sub {{ request()->routeIs('developers.webhooks') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>Webhooks</a>
                        <a href="{{ route('developers.logs') }}" class="sb-drop-sub {{ request()->routeIs('developers.logs') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>API Logs</a>
                        <a href="{{ route('developers.docs') }}" class="sb-drop-sub {{ request()->routeIs('developers.docs') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>Documentation</a>
                    </div>
                </div>
                @endif

                <div class="sb-drop {{ request()->routeIs('users.*', 'roles.*', 'security.events', 'audit.*') ? 'open' : '' }}">
                    <button type="button" class="sb-drop-toggle" onclick="toggleSbDrop(this)" style="width:100%;padding:11px 12px;border-radius:10px;background:none;cursor:pointer;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;flex:none;display:inline;"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.2 4.2l2.8 2.8M17 17l2.8 2.8M1 12h4M19 12h4M4.2 19.8 7 17M17 7l2.8-2.8"></path></svg>
                        <span>Administration</span>
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sb-drop-menu">
                @if (auth()->check() && auth()->user()->hasPermission('users.manage'))
                        <a href="{{ route('users.index') }}" class="sb-drop-sub {{ $isUserArea ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>Users</a>
                @endif
                @if (auth()->check() && auth()->user()->hasPermission('roles.manage'))
                        <a href="{{ route('roles.index') }}" class="sb-drop-sub {{ request()->routeIs('roles.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.2 4.2l2.8 2.8M17 17l2.8 2.8M1 12h4M19 12h4M4.2 19.8 7 17M17 7l2.8-2.8"></path></svg>Roles &amp; Permissions</a>
                @endif
                @if (auth()->check() && auth()->user()->hasPermission('audit.view'))
                        <a href="{{ route('audit.index') }}" class="sb-drop-sub {{ $isAuditArea ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>Audit Logs</a>
                        <a href="{{ route('security.events') }}" class="sb-drop-sub {{ request()->routeIs('security.events') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>Security Events</a>
                @endif
                        <a href="{{ route('account.index') }}" class="sb-drop-sub {{ $isAccountArea ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>Account &amp; Security</a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main -->
        <div class="main" id="mainArea">
            <header class="topbar">
                <button class="tb-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg>
                </button>
                <div class="tb-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" placeholder="Search payments, payouts, references…" onkeydown="if(event.key==='Enter'){event.preventDefault();location='/payments?q='+this.value}">
                </div>
                <div class="tb-right">
                    <div class="tb-live"><span>APIs Connected</span></div>
                    <div class="tb-notif-wrap">
                        <a href="{{ route('payments.index') }}" class="tb-iconbtn" aria-label="Notifications" title="Notifications">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            <span class="tb-dot"></span>
                        </a>
                        <div class="tb-notif-menu">
                            <div class="tb-notif-head"><b>Notifications</b><span class="tb-notif-count">{{ $recentTxns->count() }}</span></div>
                            @if ($canAccessAudit)
                                <div class="tb-notif-tabs">
                                    <button type="button" class="active" onclick="switchNotifTab(this, 'txns')">Payments</button>
                                    <button type="button" onclick="switchNotifTab(this, 'activity')">Activity</button>
                                </div>
                            @endif
                            <div class="tb-notif-panel" data-panel="txns">
                                @forelse ($recentTxns as $item)
                                    <a class="tb-notif-item" href="{{ route('payments.index', ['q' => $item->reference]) }}">
                                        <span class="tb-notif-ico">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                        </span>
                                        <span class="tb-notif-body">
                                            <b>{{ txn_type_label($item->type) }}</b>
                                            <span class="tb-notif-meta">{{ money($item->amount) }} · {{ $item->customer_name ?? '—' }} · {{ $item->reference }}</span>
                                            <span class="tb-notif-time">{{ $item->created_at->diffForHumans() }}<span class="tag {{ status_badge($item->status) }}">{{ ucfirst($item->status) }}</span></span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="tb-notif-empty">No payments yet.</div>
                                @endforelse
                            </div>
                            @if ($canAccessAudit)
                                <div class="tb-notif-panel" data-panel="activity" style="display:none;">
                                    @forelse ($recentAudit as $item)
                                        <a class="tb-notif-item" href="{{ route('audit.index') }}">
                                            <span class="tb-notif-ico">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </span>
                                            <span class="tb-notif-body">
                                                <b>{{ $item->action }}</b>
                                                <span class="tb-notif-meta">{{ $item->user?->name ?? 'System' }}</span>
                                                <span class="tb-notif-time">{{ $item->created_at->diffForHumans() }}</span>
                                            </span>
                                        </a>
                                    @empty
                                        <div class="tb-notif-empty">No recent activity.</div>
                                    @endforelse
                                </div>
                            @endif
                            <div class="tb-notif-foot">
                                <a href="{{ route('payments.index') }}">View all payments</a>
                                @if ($canAccessAudit)
                                    <a href="{{ route('audit.index') }}">View all activity</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tb-user-wrap">
                        <button type="button" class="tb-user" onclick="toggleUserMenu(this)" title="Account">
                            @if ($currentUser->avatarUrl())
                                <div class="tb-user-avatar"><img src="{{ $currentUser->avatarUrl() }}" alt=""></div>
                            @else
                                <div class="tb-user-avatar {{ $currentUser->role === 'admin' ? 'gold' : ($currentUser->role === 'supervisor' ? 'acacia' : '') }}">{{ $initials }}</div>
                            @endif
                            <div class="tb-user-text">
                                <b>{{ $currentUser->name }}</b>
                                <span>{{ ucfirst($currentUser->role) }}</span>
                            </div>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tb-user-chev" style="width:14px;height:14px;color:var(--ink-soft);flex:none;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="tb-user-menu">
                            <a href="{{ route('profile.index') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                My Profile
                            </a>
                            <a href="{{ route('account.index') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"></path><path d="M1 14h6M9 8h6M17 16h6"></path></svg>
                                Account &amp; Security
                            </a>
                            @if (($currentUser->role ?? null) === 'admin')
                                <a href="{{ route('settings.index') }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect></svg>
                                    System Settings
                                </a>
                            @endif
                            <div class="menu-sep"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="danger">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="view-wrap">
                @if (session('status'))
                    <div style="background:var(--acacia-100);color:var(--acacia-600);border-radius:10px;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:20px;">{{ session('status') }}</div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div style="background:var(--danger-100);color:var(--danger);border-radius:10px;padding:12px 16px;font-size:13.5px;font-weight:600;margin-bottom:20px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="view">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <div id="toastHost"></div>

    <!-- Generic row details drawer -->
    <div class="modal-backdrop" id="rowDetailsModal">
        <div class="modal">
            <div class="modal-head">
                <h3 id="rowDetailsTitle">Details</h3>
                <button class="modal-close" onclick="closeModal('rowDetailsModal')">✕</button>
            </div>
            <div class="modal-body" id="rowDetailsBody"></div>
            <div class="modal-foot" id="rowDetailsFoot">
                <button class="btn btn-primary" onclick="closeModal('rowDetailsModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';

        function toggleSidebar(){
            if(window.innerWidth <= 900){
                document.getElementById('sidebar').classList.toggle('mobile-open');
                document.getElementById('mobileOverlay').classList.toggle('show');
            } else {
                document.getElementById('sidebar').classList.toggle('collapsed');
            }
        }
        function closeMobileSidebar(){
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('mobileOverlay').classList.remove('show');
        }
        function toggleSbDrop(el){
            const drop = el.closest('.sb-drop');
            if(!drop) return;
            const wasOpen = drop.classList.contains('open');
            // Only one open per level: close siblings, keep ancestors open.
            const scope = drop.parentElement;
            if(scope) scope.querySelectorAll(':scope > .sb-drop.open').forEach(d => { if(d !== drop) d.classList.remove('open'); });
            drop.classList.toggle('open', !wasOpen);
        }

        function toast(msg, type='default'){
            const host = document.getElementById('toastHost');
            const el = document.createElement('div');
            el.className = 'toast ' + (type==='success'?'success':type==='error'?'error':'');
            el.innerHTML = (type==='success' ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>' : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>') + '<span>'+msg+'</span>';
            host.appendChild(el);
            setTimeout(()=>{ el.style.opacity='0'; el.style.transform='translateX(20px)'; el.style.transition='all .25s'; setTimeout(()=>el.remove(),250); }, 3200);
        }

        let modalStack = 400;
        function openModal(id){ const el = document.getElementById(id); if(el){ el.style.zIndex = ++modalStack; el.classList.add('show'); } }
        function closeModal(id){ const el = document.getElementById(id); if(el) el.classList.remove('show'); }
        function openDrawer(id){ const el = document.getElementById(id); if(el){ el.style.zIndex = ++modalStack; el.classList.add('show'); } }
        function closeDrawer(id){ const el = document.getElementById(id); if(el) el.classList.remove('show'); }
        document.addEventListener('click', (e) => {
            if(e.target.classList && (e.target.classList.contains('modal-backdrop') || e.target.classList.contains('drawer-backdrop')) && e.target.classList.contains('show')) {
                e.target.classList.remove('show');
            }
        });

        function toggleUserMenu(btn) {
            const wrap = btn.closest('.tb-user-wrap');
            const wasOpen = wrap.classList.contains('open');
            document.querySelectorAll('.tb-user-wrap.open').forEach(w => w.classList.remove('open'));
            if (!wasOpen) wrap.classList.add('open');
        }
        function switchNotifTab(btn, tab) {
            btn.closest('.tb-notif-tabs').querySelectorAll('button').forEach(b => b.classList.toggle('active', b === btn));
            btn.closest('.tb-notif-menu').querySelectorAll('.tb-notif-panel').forEach(p => {
                p.style.display = p.dataset.panel === tab ? 'block' : 'none';
            });
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.tb-user-wrap')) {
                document.querySelectorAll('.tb-user-wrap.open').forEach(w => w.classList.remove('open'));
            }
        });

        function openRowDetails(entries, title = 'Details', actions = []) {
            const body = document.getElementById('rowDetailsBody');
            const foot = document.getElementById('rowDetailsFoot');
            document.getElementById('rowDetailsTitle').textContent = title;
            body.innerHTML = '';
            foot.innerHTML = '';
            
            const receipt = document.createElement('div');
            receipt.className = 'receipt';
            if (!entries || !entries.length) {
                const p = document.createElement('p');
                p.className = 'empty-state';
                p.textContent = 'Nothing to show for this row.';
                receipt.appendChild(p);
            } else {
                entries.forEach(([label, value]) => {
                    const row = document.createElement('div');
                    row.className = 'receipt-row';
                    const s = document.createElement('span');
                    s.textContent = label;
                    const b = document.createElement('b');
                    if (value && value.__html) {
                        b.innerHTML = value.__html;
                    } else {
                        b.textContent = value == null || value === '' ? '—' : value;
                    }
                    row.appendChild(s);
                    row.appendChild(b);
                    receipt.appendChild(row);
                });
            }
            body.appendChild(receipt);
            
            // Add action buttons if provided
            if (actions && actions.length > 0) {
                actions.forEach(action => {
                    const btn = document.createElement('button');
                    btn.className = `btn ${action.class || 'btn-ghost'}`;
                    btn.textContent = action.label;
                    btn.onclick = action.action;
                    foot.appendChild(btn);
                });
            }
            
            // Always add close button
            const closeBtn = document.createElement('button');
            closeBtn.className = 'btn btn-primary';
            closeBtn.textContent = 'Close';
            closeBtn.onclick = () => closeModal('rowDetailsModal');
            foot.appendChild(closeBtn);
            
            openModal('rowDetailsModal');
        }

        function bindRowClick(selector, extractor, title = 'Details', actions = []) {
            document.querySelectorAll(selector).forEach(tr => {
                tr.style.cursor = 'pointer';
                tr.addEventListener('click', (e) => {
                    if (e.target.closest('a, button, input, select, textarea, label')) return;
                    const entries = extractor(tr);
                    const actionList = typeof actions === 'function' ? actions(tr) : actions;
                    if (entries) openRowDetails(entries, typeof title === 'function' ? title(tr) : title, actionList);
                });
            });
        }

        function statusBadgeHtml(status) {
            const cls = status === 'completed' || status === 'active' || status === 'reconciled' ? 'tag-green'
                : (status === 'reversed' || status === 'inactive' ? 'tag-grey'
                : (status === 'failed' || status === 'suspended' ? 'tag-red' : 'tag-gold'));
            return '<span class="tag ' + cls + '">' + status[0].toUpperCase() + status.slice(1) + '</span>';
        }

        /* ---- Global loading overlay: page transitions, native posts, slow fetch ---- */
        let __loaderCount = 0;
        let __loaderTimer = null;
        let __loaderSafety = null;
        function showGlobalLoader(msg){
            const el = document.getElementById('globalLoader');
            if(!el) return;
            if(msg){ const t = el.querySelector('.global-loader-text'); if(t) t.textContent = msg; }
            __loaderCount++;
            clearTimeout(__loaderTimer);
            __loaderTimer = setTimeout(()=> el.classList.add('show'), 250);
            clearTimeout(__loaderSafety);
            __loaderSafety = setTimeout(hideGlobalLoaderForce, 20000);
        }
        function hideGlobalLoader(){
            __loaderCount = Math.max(0, __loaderCount - 1);
            if(__loaderCount > 0) return;
            clearTimeout(__loaderTimer);
            const el = document.getElementById('globalLoader');
            if(el) el.classList.remove('show');
            clearTimeout(__loaderSafety);
        }
        function hideGlobalLoaderForce(){
            __loaderCount = 0;
            clearTimeout(__loaderTimer);
            clearTimeout(__loaderSafety);
            const el = document.getElementById('globalLoader');
            if(el) el.classList.remove('show');
        }
        window.showGlobalLoader = showGlobalLoader;
        window.hideGlobalLoader = hideGlobalLoader;

        // Full-page link navigation (skip downloads, exports, anchors, new tabs, JS-handled clicks)
        document.addEventListener('click', function(e){
            const a = e.target.closest('a[href]');
            if(!a || e.defaultPrevented) return;
            if(e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            const href = a.getAttribute('href');
            if(!href || href.startsWith('#') || href.startsWith('javascript:') || a.target === '_blank' || a.hasAttribute('download')) return;
            let url;
            try { url = new URL(href, window.location.href); } catch(_){ return; }
            if(url.origin !== window.location.origin) return;
            if(/export/i.test(url.pathname) || /\.(csv|pdf|xlsx?)$/i.test(url.pathname)) return;
            setTimeout(function(){ if(!e.defaultPrevented) showGlobalLoader(); }, 0);
        }, true);

        // Native (non-AJAX) form posts
        document.addEventListener('submit', function(e){
            const form = e.target;
            if(!(form instanceof HTMLFormElement)) return;
            if(form.target === '_blank' || form.hasAttribute('data-loader-skip')) return;
            setTimeout(function(){ if(!e.defaultPrevented) showGlobalLoader('Submitting…'); }, 0);
        }, true);

        // Slow fetch/XHR: delayed, ref-counted overlay
        (function(){
            if(window.fetch.__loaderWrapped) return;
            const origFetch = window.fetch.bind(window);
            window.fetch = function(...args){
                showGlobalLoader();
                return origFetch(...args).finally(hideGlobalLoader);
            };
            window.fetch.__loaderWrapped = true;
        })();

        window.addEventListener('pageshow', function(){ hideGlobalLoaderForce(); });
        document.addEventListener('DOMContentLoaded', function(){ hideGlobalLoaderForce(); });

        async function submitForm(form, { method = 'POST', done = null, csrf = true } = {}) {
            const url = form.getAttribute('action') || window.location.href;
            const formData = new FormData(form);
            const btn = form.querySelector('[type="submit"]');
            if (btn) { btn.disabled = true; }
            try {
                const response = await fetch(url, {
                    method,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf ? CSRF_TOKEN : '', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    toast(data.message || 'Saved successfully.', 'success');
                    done && done(data);
                } else {
                    toast(data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Something went wrong!'), 'error');
                }
            } catch (err) {
                console.error(err);
                toast('Something went wrong! Please check the console.', 'error');
            } finally {
                if (btn) { btn.disabled = false; }
            }
        }
    </script>
    @yield('scripts')
</body>
</html>