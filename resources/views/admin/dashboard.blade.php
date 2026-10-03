<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1020">
    <title>Dashboard Admin — Daylivery</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

<style>
    :root {
        --bg:#f5f7fb;--surface:#fff;--surface-soft:#f8fafc;--border:#e7ebf2;
        --text:#172033;--muted:#7c879b;--primary:#635bff;--primary-dark:#5146e5;
        --success:#16a878;--warning:#f59e0b;--danger:#ef6b67;--info:#4f8df7;
        --sidebar:#0b1020;--shadow:0 10px 35px rgba(15,23,42,.07);--radius:18px;
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{margin:0;background:radial-gradient(circle at 90% 0%,rgba(99,91,255,.06),transparent 25%),var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    a{color:inherit;text-decoration:none}button,input,select{font:inherit}
    svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .app-shell{min-height:100vh;display:flex}
    .sidebar{position:fixed;z-index:100;inset:0 auto 0 0;width:255px;display:flex;flex-direction:column;padding:24px 16px;background:radial-gradient(circle at 20% 0%,rgba(99,91,255,.15),transparent 28%),linear-gradient(180deg,#0b1020,#080d19);color:#fff;border-right:1px solid rgba(255,255,255,.06);box-shadow:12px 0 40px rgba(15,23,42,.08)}
    .sidebar-backdrop{display:none}
    .brand{display:flex;align-items:center;gap:12px;padding:2px 8px 28px}
    .sidebar-navigation{flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;padding-right:5px;margin-right:-5px;scrollbar-width:thin;scrollbar-color:rgba(155,166,188,.4) transparent}
    .sidebar-navigation::-webkit-scrollbar{width:6px}.sidebar-navigation::-webkit-scrollbar-thumb{border-radius:999px;background:rgba(155,166,188,.35)}
    .brand-mark{width:42px;height:42px;flex-shrink:0;display:grid;place-items:center;border-radius:13px;background:linear-gradient(135deg,#8b7cff,#5d55e8);color:#fff;box-shadow:0 10px 25px rgba(99,91,255,.35)}
    .brand-mark svg{width:27px;height:27px}
    .brand-name{display:flex;flex-direction:column;font-size:1.12rem;font-weight:800;letter-spacing:-.03em}
    .brand-name>span{display:inline;color:#8b7cff}
    .brand-name small{margin-top:2px;color:#7f8aa2;font-size:.59rem;font-weight:700;letter-spacing:.13em}
    .nav-label{padding:0 12px;margin:5px 0 10px;color:#64708a;font-size:.65rem;font-weight:800;letter-spacing:.12em}
    .nav-label-spaced{margin-top:28px}
    .nav-menu{display:flex;flex-direction:column;gap:5px}
    .nav-link{min-height:45px;display:flex;align-items:center;gap:12px;padding:0 12px;border-radius:11px;color:#9ba6bc;font-size:.88rem;font-weight:600;transition:background .2s,color .2s,transform .2s}
    .nav-link svg{width:19px;height:19px;flex-shrink:0}
    .nav-link:hover{color:#fff;background:rgba(255,255,255,.055);transform:translateX(2px)}
    .nav-link.active{color:#fff;background:linear-gradient(90deg,rgba(99,91,255,.23),rgba(99,91,255,.08));box-shadow:inset 3px 0 0 #8178ff}
    .nav-link-disabled{cursor:default;opacity:.58}.nav-link-disabled:hover{transform:none}
    .nav-link small{margin-left:auto;padding:3px 7px;border-radius:999px;background:rgba(255,255,255,.06);color:#68748c;font-size:.59rem;font-weight:700}
    .sidebar-bottom{margin-top:auto}
    .support-card{padding:16px;border:1px solid rgba(255,255,255,.07);border-radius:15px;background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.025))}
    .support-icon{width:34px;height:34px;display:grid;place-items:center;margin-bottom:12px;border-radius:10px;background:rgba(99,91,255,.15);color:#9c94ff}
    .support-icon svg{width:18px;height:18px}
    .support-card strong,.support-card>span:not(.support-icon){display:block}
    .support-card strong{margin-bottom:5px;font-size:.82rem}.support-card>span:not(.support-icon){color:#758097;font-size:.72rem;line-height:1.5}
    .support-card a{display:inline-block;margin-top:12px;color:#a8a1ff;font-size:.72rem;font-weight:700}
    .sidebar-footer{padding:16px 5px 0;color:#59647a;font-size:.67rem;text-align:center}
    .main-content{width:calc(100% - 255px);margin-left:255px;min-width:0}
    .topbar{height:72px;display:flex;align-items:center;padding:0 32px;background:rgba(255,255,255,.84);border-bottom:1px solid var(--border);backdrop-filter:blur(15px);position:sticky;top:0;z-index:50}
    .menu-toggle{display:none!important}
    .breadcrumb{display:flex;align-items:center;gap:9px;color:#9aa4b6;font-size:.82rem}.breadcrumb strong{color:var(--text)}.breadcrumb-divider{color:#c4cad5}
    .topbar-actions{display:flex;align-items:center;gap:11px;margin-left:auto}
    .search-box{width:235px;position:relative;display:flex;align-items:center}
    .search-trigger{position:absolute;left:11px;width:28px;height:28px;display:grid;place-items:center;border:0;background:transparent;color:#8c97aa;cursor:pointer}
    .search-trigger svg{width:17px;height:17px}
    .search-box input{width:100%;height:39px;padding:0 48px 0 39px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;color:var(--text);outline:none;font-size:.76rem;transition:.2s}
    .search-box input:focus{border-color:rgba(99,91,255,.45);background:#fff;box-shadow:0 0 0 4px rgba(99,91,255,.07)}
    .search-box kbd{position:absolute;right:9px;padding:3px 6px;border:1px solid #dfe4ec;border-radius:5px;color:#929bad;background:#fff;font-size:.58rem}
    .icon-button{width:39px;height:39px;display:grid;place-items:center;border:1px solid var(--border);border-radius:10px;background:#fff;color:#69758b;cursor:pointer;transition:.2s}
    .icon-button:hover:not(:disabled){color:var(--primary);border-color:rgba(99,91,255,.25);background:#fafaff}
    .icon-button:disabled{opacity:.55;cursor:not-allowed}.icon-button svg{width:18px;height:18px}
    .topbar-divider{width:1px;height:28px;background:var(--border);margin:0 4px}
    .profile-button{display:flex;align-items:center;gap:9px}.profile-copy{display:flex;flex-direction:column;min-width:85px}
    .profile-copy strong{font-size:.75rem;line-height:1.4}.profile-copy small{color:var(--muted);font-size:.62rem}
    .avatar{width:36px;height:36px;display:grid;place-items:center;flex-shrink:0;border-radius:50%;font-size:.68rem;font-weight:800}
    .avatar-admin{color:#fff;background:linear-gradient(135deg,#8b7cff,#5d55e8);box-shadow:0 5px 15px rgba(99,91,255,.25)}
    .avatar-customer{width:34px;height:34px;color:#516073;background:#eef2f7}
    .logout-button{margin-left:8px;padding:7px 10px;border:0;border-radius:8px;background:#fff1f1;color:#e05d5d;font-size:.68rem;font-weight:700;cursor:pointer;transition:.2s}
    .logout-button:hover{background:#ffe2e2}
    .page-content{max-width:1500px;margin:0 auto;padding:32px}
    .flash-message{padding:13px 15px;margin-bottom:22px;border:1px solid rgba(22,168,120,.2);border-radius:12px;background:rgba(22,168,120,.08);color:#087a59;font-size:.82rem;font-weight:600}
    .welcome-row{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:27px}
    .eyebrow{display:flex;align-items:center;gap:7px;margin-bottom:8px;color:#68758c;font-size:.65rem;font-weight:800;letter-spacing:.11em}
    .live-dot{width:7px;height:7px;border-radius:50%;background:var(--success);box-shadow:0 0 0 4px rgba(22,168,120,.09)}
    .welcome-row h1{margin:0 0 7px;font-size:clamp(1.6rem,3vw,2rem);letter-spacing:-.045em}.welcome-row p{margin:0;color:var(--muted);font-size:.82rem}
    .welcome-actions{display:flex;gap:9px}
    .button{min-height:40px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 14px;border-radius:10px;font-size:.74rem;font-weight:750;transition:transform .2s,box-shadow .2s,background .2s}
    .button svg{width:16px;height:16px}.button:hover{transform:translateY(-1px)}
    .button-secondary{border:1px solid var(--border);background:#fff;color:#526076}.button-secondary:hover{border-color:#d5dbe5;box-shadow:var(--shadow)}
    .button-primary{border:1px solid var(--primary);background:var(--primary);color:#fff;box-shadow:0 8px 20px rgba(99,91,255,.18)}
    .button-primary:hover{background:var(--primary-dark);box-shadow:0 11px 25px rgba(99,91,255,.27)}
    .stats-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .stat-card{padding:18px;border:1px solid var(--border);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow);transition:transform .2s,box-shadow .2s}
    .stat-card:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(15,23,42,.10)}
    .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:17px}.stat-label{color:#8994a7;font-size:.69rem;font-weight:700}
    .stat-icon{width:35px;height:35px;display:grid;place-items:center;border-radius:10px}.stat-icon svg{width:17px;height:17px}
    .stat-icon-purple{background:#f0edff;color:#6659dd}.stat-icon-green{background:#e9faf4;color:#16a878}.stat-icon-blue{background:#edf4ff;color:#4f8df7}.stat-icon-orange{background:#fff6e8;color:#e99519}.stat-icon-red{background:#fff0ef;color:#df6b65}
    .stat-value{margin-bottom:7px;color:#172033;font-size:1.35rem;font-weight:800;letter-spacing:-.035em}.stat-note{color:#9aa4b6;font-size:.65rem}
    .overview-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(330px,.9fr);gap:16px;margin-bottom:16px}
    .panel{border:1px solid var(--border);border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;padding:21px 22px 0}
    .panel-heading h2{margin:0 0 5px;font-size:.92rem;letter-spacing:-.02em}.panel-heading p{margin:0;color:#9aa4b6;font-size:.66rem}
    .select-button{padding:7px 10px;border:1px solid var(--border);border-radius:8px;color:#778298;background:#fafbfc;font-size:.62rem;font-weight:650}
    .revenue-total{padding:18px 22px 5px;display:flex;align-items:baseline;gap:9px}.revenue-total strong{font-size:1.35rem;letter-spacing:-.04em}.stat-period{color:#9ca6b7;font-size:.61rem}
    .chart-wrap{display:flex;gap:10px;height:215px;padding:8px 22px 18px}.chart-y-labels{width:65px;display:flex;flex-direction:column;justify-content:space-between;padding:4px 0 25px;color:#a3acba;font-size:.55rem;text-align:right}
    .chart-area{flex:1;min-width:0;position:relative}.chart-gridlines{position:absolute;inset:5px 0 28px;display:flex;flex-direction:column;justify-content:space-between}.chart-gridlines span{width:100%;height:1px;border-top:1px dashed #edf0f4}
    .revenue-chart{position:absolute;left:0;right:0;top:5px;width:100%;height:170px}.chart-x-labels{position:absolute;left:0;right:0;bottom:0;display:flex;justify-content:space-between;color:#9ba5b4;font-size:.55rem}
    .donut-wrap{display:flex;justify-content:center;padding:20px 20px 14px}.donut-chart{width:165px;height:165px;display:grid;place-items:center;border-radius:50%;position:relative;box-shadow:inset 0 0 0 1px rgba(0,0,0,.02)}
    .donut-hole{width:112px;height:112px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:50%;background:#fff;box-shadow:0 2px 10px rgba(15,23,42,.05)}
    .donut-hole strong{font-size:1.45rem;letter-spacing:-.04em}.donut-hole span{margin-top:3px;color:#9ca6b7;font-size:.57rem}
    .status-legend{padding:0 22px 20px}.status-legend>div{display:grid;grid-template-columns:9px 1fr auto 35px;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f1f3f6;font-size:.68rem}.status-legend>div:last-child{border-bottom:0}
    .legend-dot{width:7px;height:7px;border-radius:50%}.status-legend strong{font-size:.68rem}.status-legend small{color:#9aa4b6;text-align:right}
    .deliveries-panel{overflow:hidden}.deliveries-heading{align-items:center;padding-bottom:18px}.table-actions{display:flex;align-items:center}.order-filters{display:flex;align-items:center;gap:7px}
    .order-filters input,.order-filters select{height:36px;border:1px solid var(--border);border-radius:9px;outline:none;background:#fafbfc;color:#526076;padding:0 10px;font-size:.66rem}
    .order-filters input{width:190px}.order-filters select{width:125px}.order-filters input:focus,.order-filters select:focus{border-color:rgba(99,91,255,.4);box-shadow:0 0 0 3px rgba(99,91,255,.06)}
    .table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:850px}thead{background:#fafbfc;border-top:1px solid #f0f2f5;border-bottom:1px solid #edf0f4}
    th{padding:12px 18px;color:#929bad;font-size:.57rem;font-weight:800;letter-spacing:.08em;text-align:left;white-space:nowrap}
    td{padding:14px 18px;border-bottom:1px solid #f0f2f5;color:#59667a;font-size:.7rem;vertical-align:middle}tbody tr{transition:background .15s}tbody tr:hover{background:#fafbff}
    .order-code{color:#4f46c5;font-size:.69rem;font-weight:750}.customer-cell{display:flex;align-items:center;gap:9px;color:#273247;font-weight:650}.customer-cell>span:last-child{display:flex;flex-direction:column}
    .customer-phone{margin-top:2px;color:#9aa4b6;font-size:.58rem;font-weight:400}.restaurant-cell{color:#667287}.amount-cell{color:#283349;font-weight:750;white-space:nowrap}.time-cell{color:#929bad;white-space:nowrap;font-size:.64rem}
    .status-form{margin:0}.status-select{height:30px;padding:0 27px 0 9px;border:0;border-radius:999px;outline:none;font-size:.59rem;font-weight:750;cursor:pointer}
    .status-pending,.status-menunggu{background:#f1f3f5;color:#727c8d}.status-confirmed,.status-assigned{background:#eaf2ff;color:#477fdc}.status-preparing,.status-diproses{background:#fff4df;color:#d48613}.status-ready,.status-picked_up{background:#e7f8f1;color:#12865f}.status-delivering,.status-diantar{background:#eaf2ff;color:#477fdc}.status-completed,.status-selesai{background:#e7f8f1;color:#12865f}.status-cancelled,.status-dibatalkan{background:#fff0ef;color:#d7615b}
    .empty-row td{padding:40px 20px;color:#9aa4b6;text-align:center}.table-footer{min-height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 20px;color:#8d97a9;font-size:.64rem}.table-footer strong{color:#586478}
    .table-footer nav{display:flex;align-items:center;gap:4px}.table-footer nav a,.table-footer nav span{min-width:29px;height:29px;display:grid;place-items:center;padding:0 8px;border:1px solid var(--border);border-radius:7px;background:#fff;color:#68748a;font-size:.62rem}
    .table-footer nav span[aria-current=page]{border-color:var(--primary);background:var(--primary);color:#fff}
    .page-footer{display:flex;justify-content:space-between;padding:22px 3px 5px;color:#a0a9b8;font-size:.62rem}.page-footer a{color:#6259e8;font-weight:700}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    @media(max-width:1250px){.stats-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.overview-grid{grid-template-columns:1fr}}
    @media(max-width:1000px){
        .sidebar-backdrop{display:block;position:fixed;inset:0;background:rgba(15,23,42,.42);opacity:0;pointer-events:none;transition:opacity .25s ease;z-index:90}
        .sidebar-backdrop.visible{opacity:1;pointer-events:auto}
        .sidebar{width:min(270px,82vw);transform:translateX(-110%);transition:transform .25s ease}.sidebar.open{transform:translateX(0)}
        .main-content{width:100%;margin-left:0}.menu-toggle{display:grid!important;margin-right:12px}.topbar{padding:0 20px}.page-content{padding:25px 20px}
        .welcome-row{flex-direction:column;align-items:flex-start}.welcome-actions{width:100%;flex-wrap:wrap}.welcome-actions .button{flex:1 1 180px}
    }
    @media(max-width:760px){
        .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.topbar-actions{gap:8px}.search-box{width:38px}.search-box input,.search-box kbd{display:none}.search-trigger{position:static;width:100%;height:100%}
        .profile-copy,.logout-button,.topbar-divider{display:none}.deliveries-heading{align-items:flex-start;flex-direction:column}.table-actions,.order-filters{width:100%}.order-filters{display:grid;grid-template-columns:1fr 1fr;gap:8px}.order-filters input{width:100%;min-width:0}.order-filters select{width:100%}.order-filters .button{width:100%}
    }
    @media(max-width:560px){
        .page-content{padding:20px 14px}.topbar{height:62px;padding:0 14px}.breadcrumb{font-size:.72rem}.stats-grid{grid-template-columns:1fr}
        .welcome-actions{flex-direction:column}.welcome-actions .button{width:100%}.panel-heading{padding-left:17px;padding-right:17px;flex-direction:column;align-items:flex-start}.chart-wrap{padding-left:15px;padding-right:15px}.revenue-total{padding-left:17px;padding-right:17px}
        .status-legend{padding-left:17px;padding-right:17px}.order-filters{grid-template-columns:1fr}.order-filters input,.order-filters select,.order-filters .button{width:100%;flex:1 1 100%}
        .table-footer{flex-direction:column;align-items:flex-start;gap:10px}.page-footer{flex-direction:column;gap:7px}
    }
    @media(max-width:420px){
        .brand{padding-bottom:20px}.brand-name{font-size:1rem}.brand-name small{display:none}.nav-link{padding-left:10px;padding-right:10px}.support-card{padding:14px}.page-footer{font-size:.6rem}
    }
</style>

</head>
<body>
    <div class="app-shell">
        <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('admin.dashboard') }}" aria-label="Daylivery beranda">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 40 40" fill="none"><path d="M8 12.5h24l-2 19H10l-2-19Z" fill="currentColor"/><path d="M14 14V11a6 6 0 0 1 12 0v3" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="m16 22 3 3 6-6" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="brand-name">daylivery<span>.</span><small>ADMIN CONSOLE</small></span>
            </a>

            <div class="sidebar-navigation">
            <div class="nav-label">MENU UTAMA</div>
            <nav class="nav-menu" aria-label="Menu utama">
                <a class="nav-link active" href="{{ route('admin.dashboard') }}" aria-current="page">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1"/><circle cx="9" cy="7" r="3"/><path d="M17 7h4m-2-2v4"/><path d="M19 14.5c1.4 0 2.5 1.1 2.5 2.5v1H16v-1c0-1.4 1.1-2.5 2.5-2.5Z"/></svg>
                    <span>Mitra</span>
                </a>
                <a class="nav-link" href="{{ route('admin.menus.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10h16l-1 11H5L4 10Z"/><path d="M3 10 5 3h14l2 7M9 10V3m6 7V3M8 15h8"/></svg>
                    <span>Menu Belanja</span>
                </a>
                <a class="nav-link" href="{{ route('admin.categories.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="8" height="7" rx="1"/><rect x="13" y="4" width="8" height="7" rx="1"/><rect x="3" y="13" width="8" height="7" rx="1"/><rect x="13" y="13" width="8" height="7" rx="1"/></svg>
                    <span>Kategori</span>
                </a>
                <a class="nav-link" href="{{ route('admin.ads.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 15-5-5L5 18"/></svg>
                    <span>Sponsor &amp; Iklan</span>
                </a>
                <a class="nav-link" href="{{ route('admin.dashboard') }}#pesanan">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z"/><path d="M8 9h8M8 13h8M8 17h4"/></svg>
                    <span>Pesanan</span>
                </a>
                <a class="nav-link" href="{{ route('admin.dashboard', ['status' => 'assigned']) }}#pesanan">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="7" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M10 17h5l2-8h3l2 5h-4M7 14V7h7l3 7M3 10h6"/></svg>
                    <span>Pengantaran</span>
                </a>
                <a class="nav-link" href="{{ route('admin.users.index', ['role' => 'driver']) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="7" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M10 17h5l2-8h3l2 5h-4M7 14V7h7l3 7M3 10h6"/></svg>
                    <span>Kurir</span>
                </a>
                <a class="nav-link" href="{{ route('admin.users.index', ['role' => 'seller']) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10h16l-1 11H5L4 10Z"/><path d="M3 10 5 3h14l2 7M9 10V3m6 7V3M8 15h8"/></svg>
                    <span>Restoran</span>
                </a>
                <a class="nav-link" href="{{ route('admin.applications.index', 'seller') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg>
                    <span>Pengajuan Mitra</span>
                </a>
                <a class="nav-link" href="{{ route('admin.users.index', ['role' => 'customer']) }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2M17 4a4 4 0 0 1 0 8m2 3a5 5 0 0 1 3 4v2"/></svg>
                    <span>Pelanggan</span>
                </a>
                <a class="nav-link" href="{{ route('admin.dashboard') }}#analitik">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5m0 14h17"/><path d="m7 15 4-4 3 2 6-7"/></svg>
                    <span>Analitik</span>
                </a>
            </nav>

            <div class="nav-label nav-label-spaced">PREFERENSI</div>
            <nav class="nav-menu" aria-label="Preferensi">
                <a class="nav-link" href="{{ route('admin.settings') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.5 2.6-1.7-.7a8 8 0 0 1-1.8 1l-.3 1.9h-3l-.3-1.9a8 8 0 0 1-1.8-1l-1.7.7-1.5-2.6 1.5-1.1a8 8 0 0 1 0-2l-1.5-1.1 1.5-2.6 1.7.7a8 8 0 0 1 1.8-1l.3-1.9h3l.3 1.9a8 8 0 0 1 1.8 1l1.7-.7 1.5 2.6-1.5 1.1a8 8 0 0 1 0 2Z"/></svg>
                    <span>Pengaturan</span>
                </a>
            </nav>
            </div>

            <div class="sidebar-bottom">
                <div class="support-card">
                    <span class="support-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2"/><path d="M4 13h3v6H5a2 2 0 0 1-2-2v-2a2 2 0 0 1 1-2Zm16 0h-3v6h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-1-2ZM17 19a5 5 0 0 1-5 3"/></svg>
                    </span>
                    <strong>Butuh bantuan?</strong>
                    <span>Tim support siap membantu kamu.</span>
                    <a href="mailto:support@daylivery.id">Hubungi support <span aria-hidden="true">↗</span></a>
                </div>
                <div class="sidebar-footer">© 2026 Daylivery Indonesia</div>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="icon-button menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar" data-menu-toggle>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="breadcrumb"><span>Halaman</span><span class="breadcrumb-divider">/</span><strong>Dashboard</strong></div>
                <div class="topbar-actions">
                    <form class="search-box" method="GET" action="{{ route('admin.dashboard') }}">
                        <button type="button" class="search-trigger" aria-label="Aktifkan pencarian" data-search-toggle><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg></button>
                        <input type="search" name="q" value="{{ $search }}" placeholder="Cari pesanan..." aria-label="Cari pesanan">
                        @if ($selectedStatus)<input type="hidden" name="status" value="{{ $selectedStatus }}">@endif
                        <kbd>Ctrl K</kbd>
                    </form>
                    <button class="icon-button notification-button" type="button" aria-label="Notifikasi belum tersedia" title="Notifikasi belum tersedia" disabled>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                    </button>
                    <span class="topbar-divider"></span>
                    <div class="profile-button">
                        <span class="avatar avatar-admin">AD</span>
                        <span class="profile-copy"><strong>{{ $admin->name }}</strong><small>Administrator</small></span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="logout-button" type="submit">Keluar</button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="page-content" id="dashboardWorkspace" aria-live="polite" aria-busy="false">
                @if (session('success'))
                    <div class="flash-message" role="status">{{ session('success') }}</div>
                @endif
                <section class="welcome-row">
                    <div>
                        <div class="eyebrow"><span class="live-dot"></span> DASHBOARD OPERASIONAL</div>
                        <h1>Selamat datang, {{ $admin->name }}</h1>
                        <p>Pantau operasional dan performa Daylivery hari ini.</p>
                    </div>
                    <div class="welcome-actions">
                        <a class="button button-secondary" href="{{ route('admin.orders.create') }}" aria-label="Tambah pesanan" title="Tambah pesanan">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            <span>Tambah pesanan</span>
                        </a>
                        <a class="button button-primary" href="{{ route('admin.orders.export', request()->only('q', 'status')) }}" aria-label="Unduh laporan pesanan" title="Unduh laporan pesanan">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg>
                            <span>Unduh laporan</span>
                        </a>
                    </div>
                </section>

                <section class="stats-grid" aria-label="Ringkasan hari ini">
                    @foreach ($stats as $stat)
                        <article class="stat-card">
                            <div class="stat-top">
                                <span class="stat-label">{{ $stat['label'] }}</span>
                                <span class="stat-icon stat-icon-{{ $stat['tone'] }}">
                                    @if ($stat['icon'] === 'orders')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z"/><path d="M8 9h8M8 13h8M8 17h4"/></svg>
                                    @elseif ($stat['icon'] === 'revenue')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    @elseif ($stat['icon'] === 'active')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="7" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M10 17h5l2-8h3l2 5h-4M7 14V7h7l3 7M3 10h6"/></svg>
                                    @elseif ($stat['icon'] === 'finished')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10h16l-1 11H5L4 10Z"/><path d="M3 10 5 3h14l2 7M9 10V3m6 7V3"/></svg>
                                    @endif
                                </span>
                            </div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                            <div class="stat-foot"><span class="stat-note">{{ $stat['detail'] }}</span></div>
                        </article>
                    @endforeach
                </section>

                <section class="overview-grid" id="analitik">
                    <article class="panel revenue-panel">
                        <div class="panel-heading">
                            <div>
                                <h2>Ringkasan pendapatan</h2>
                                <p>Nilai pesanan non-pembatalan, 7 hari terakhir</p>
                            </div>
                            <span class="select-button">7 hari terakhir</span>
                        </div>
                        <div class="revenue-total"><strong>Rp {{ number_format($weeklyRevenueTotal, 0, ',', '.') }}</strong><span class="stat-period">nilai pesanan · 7 hari</span></div>
                        <div class="chart-wrap">
                            <div class="chart-y-labels" aria-hidden="true"><span>Rp {{ number_format($chartMax, 0, ',', '.') }}</span><span>Rp {{ number_format($chartMax * .75, 0, ',', '.') }}</span><span>Rp {{ number_format($chartMax * .5, 0, ',', '.') }}</span><span>Rp {{ number_format($chartMax * .25, 0, ',', '.') }}</span><span>Rp 0</span></div>
                            <div class="chart-area">
                                <div class="chart-gridlines" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
                                <svg class="revenue-chart" viewBox="0 0 700 190" preserveAspectRatio="none" role="img" aria-label="Grafik pendapatan selama tujuh hari terakhir">
                                    <defs><linearGradient id="chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#18a873" stop-opacity=".18"/><stop offset="100%" stop-color="#18a873" stop-opacity="0"/></linearGradient></defs>
                                    <path d="{{ $chartFill }}" fill="url(#chart-fill)"/>
                                    <path d="{{ $chartPath }}" fill="none" stroke="#18a873" stroke-width="3" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="700" cy="{{ 170 - (($weeklyRevenue[6]['amount'] / $chartMax) * 150) }}" r="5" fill="#fff" stroke="#18a873" stroke-width="3" vector-effect="non-scaling-stroke"/>
                                </svg>
                                <div class="chart-x-labels" aria-hidden="true">@foreach ($weeklyRevenue as $day)<span>{{ $day['label'] }}</span>@endforeach</div>
                            </div>
                        </div>
                    </article>

                    <article class="panel order-status-panel">
                        <div class="panel-heading">
                            <div><h2>Status pesanan</h2><p>Persebaran pesanan hari ini</p></div>
                            <span class="sr-only">Rincian status pesanan hari ini</span>
                        </div>
                        <div class="donut-wrap">
                            @php
                                $statusColors = ['pending' => '#e9edf0', 'confirmed' => '#5d91ed', 'preparing' => '#f2aa51', 'ready' => '#43a98a', 'assigned' => '#6689c7', 'picked_up' => '#4378bd', 'delivering' => '#5d91ed', 'completed' => '#21a875', 'cancelled' => '#e47770'];
                                $chartStatuses = $statuses->values();
                                $statusAngle = 0;
                                $statusGradient = [];
                                foreach ($chartStatuses as $statusItem) {
                                    $percentage = $todayOrderCount > 0 ? ($statusItem['count'] / $todayOrderCount * 100) : 0;
                                    $nextAngle = $statusAngle + $percentage * 3.6;
                                    $statusGradient[] = $statusColors[$statusItem['key']].' '.$statusAngle.'deg '.$nextAngle.'deg';
                                    $statusAngle = $nextAngle;
                                }
                                if ($statusAngle < 360) {
                                    $statusGradient[] = '#e9edf0 '.$statusAngle.'deg 360deg';
                                }
                            @endphp
                            <div class="donut-chart" style="background: conic-gradient({{ implode(', ', $statusGradient) }})" role="img" aria-label="Status {{ $todayOrderCount }} pesanan hari ini">
                                <div class="donut-hole"><strong>{{ number_format($todayOrderCount, 0, ',', '.') }}</strong><span>Pesanan hari ini</span></div>
                            </div>
                        </div>
                        <div class="status-legend">
                            @foreach ($chartStatuses as $statusItem)
                                <div><span class="legend-dot" style="background: {{ $statusColors[$statusItem['key']] }}"></span><span>{{ $statusItem['label'] }}</span><strong>{{ number_format($statusItem['count'], 0, ',', '.') }}</strong><small>{{ $todayOrderCount > 0 ? number_format($statusItem['count'] / $todayOrderCount * 100, 0) : 0 }}%</small></div>
                            @endforeach
                        </div>
                    </article>
                </section>

                <section class="panel deliveries-panel" id="pesanan">
                    <div class="panel-heading deliveries-heading">
                        <div><h2>Pesanan terbaru</h2><p>Data pesanan tersimpan di database</p></div>
                        <div class="table-actions">
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="order-filters">
                                <input type="search" name="q" value="{{ $search }}" placeholder="Cari ID/nama/telepon/restoran" aria-label="Cari pesanan">
                                <select name="status" aria-label="Filter status pesanan">
                                    <option value="">Semua status</option>
                                    @foreach ($statusOptions as $key => $label)
                                        <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="button button-secondary" type="submit">Terapkan</button>
                            </form>
                        </div>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead><tr><th>ID PESANAN</th><th>PELANGGAN</th><th>RESTORAN</th><th>TOTAL</th><th>STATUS</th><th>WAKTU</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                            <tbody>
                                @foreach ($deliveries as $delivery)
                                    <tr>
                                        <td><span class="order-code">{{ $delivery->order_number }}</span></td>
                                        <td><div class="customer-cell"><span class="avatar avatar-customer">{{ strtoupper(substr($delivery->customer_name, 0, 2)) }}</span><span>{{ $delivery->customer_name }}@if ($delivery->customer_phone)<small class="customer-phone">{{ $delivery->customer_phone }}</small>@endif</span></div></td>
                                        <td class="restaurant-cell">{{ $delivery->restaurant_name }}</td>
                                        <td class="amount-cell">Rp {{ number_format($delivery->total_amount, 0, ',', '.') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.orders.status', $delivery) }}" class="status-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="q" value="{{ $search }}">
                                                <input type="hidden" name="filter_status" value="{{ $selectedStatus }}">
                                                <label class="sr-only" for="status-{{ $delivery->id }}">Status pesanan {{ $delivery->order_number }}</label>
                                                <select id="status-{{ $delivery->id }}" name="status" class="status-select status-{{ $delivery->status }}" onchange="this.form.requestSubmit()">
                                                    @foreach ($statusOptions as $key => $label)
                                                        <option value="{{ $key }}" @selected($delivery->status === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                        <td class="time-cell">{{ $delivery->created_at->format('H:i, d/m/Y') }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                                @if ($deliveries->isEmpty())
                                    <tr class="empty-row"><td colspan="7">Belum ada pesanan yang cocok. Tambahkan pesanan pertama untuk memulai.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer">
                        <span>Menampilkan <strong>{{ $deliveries->firstItem() ?? 0 }}–{{ $deliveries->lastItem() ?? 0 }}</strong> dari <strong>{{ $deliveries->total() }}</strong> pesanan</span>
                        {{ $deliveries->links() }}
                    </div>
                </section>

                <footer class="page-footer"><span>© 2026 Daylivery. Dibuat untuk operasional yang lebih mudah.</span><span>Butuh bantuan? <a href="mailto:support@daylivery.id">Hubungi tim kami</a></span></footer>
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    const sidebar=document.getElementById('sidebar');
    const backdrop=document.getElementById('sidebarBackdrop');
    const menuToggle=document.querySelector('[data-menu-toggle]');
    const searchInput=document.querySelector('.search-box input');

    function setSidebarState(open){
        if(!sidebar) return;
        sidebar.classList.toggle('open', open);
        if(backdrop){
            backdrop.classList.toggle('visible', open);
            backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        if(menuToggle){
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    }

    if(menuToggle&&sidebar){
        menuToggle.addEventListener('click',function(){
            const open=!sidebar.classList.contains('open');
            setSidebarState(open);
        });
    }

    if(backdrop){
        backdrop.addEventListener('click',function(){
            setSidebarState(false);
        });
    }

    document.querySelectorAll('.nav-link:not(.nav-link-disabled)').forEach(function(link){
        link.addEventListener('click',function(){
            if(window.innerWidth<=1000){
                setSidebarState(false);
            }
        });
    });

    document.addEventListener('keydown',function(event){
        if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){
            event.preventDefault();
            if(searchInput){searchInput.focus();searchInput.select();}
        }
        if(event.key==='Escape'){
            setSidebarState(false);
            if(searchInput) searchInput.blur();
        }
    });

    if(window.innerWidth <= 1000 && backdrop){
        backdrop.setAttribute('aria-hidden', 'true');
    }

    const workspace=document.getElementById('dashboardWorkspace');
    const shellLinks=document.querySelectorAll('.sidebar .nav-link');
    const dashboardUrl=new URL(@json(route('admin.dashboard')),window.location.origin);
    let navigationRequest=0;

    function isInternalAdminUrl(url){
        return url.origin===window.location.origin
            && url.pathname.startsWith('/admin')
            && !url.pathname.includes('/logout')
            && !url.pathname.includes('/orders/export');
    }

    function setActiveNavigation(url){
        let activePath=url.pathname;
        if(activePath.startsWith('/admin/users')) activePath='/admin/users';
        if(activePath.startsWith('/admin/menus')) activePath='/admin/menus';
        if(activePath.startsWith('/admin/settings')) activePath='/admin/settings';
        if(activePath.startsWith('/admin/orders')) activePath='/admin/orders';
        shellLinks.forEach(function(link){
            const linkUrl=new URL(link.href,window.location.origin);
            const isActive=linkUrl.pathname===activePath
                && (!linkUrl.search || linkUrl.search===url.search);
            link.classList.toggle('active',isActive);
            if(isActive) link.setAttribute('aria-current','page');
            else link.removeAttribute('aria-current');
        });
        const breadcrumb=document.querySelector('.breadcrumb strong');
        const activeLink=Array.from(shellLinks).find(function(link){return link.classList.contains('active');});
        if(breadcrumb) breadcrumb.textContent=activeLink?.querySelector('span')?.textContent.trim()||'Dashboard';
    }

    function extractWorkspace(documentToRender){
        const page=documentToRender.querySelector('#dashboardWorkspace');
        if(page) return page.innerHTML;
        const standalonePage=documentToRender.querySelector('.wrap, .shell, main.main');
        return standalonePage ? standalonePage.outerHTML : documentToRender.body.innerHTML;
    }

    function dashboardViewUrl(url){
        const viewUrl=new URL(dashboardUrl.href);
        const role=new URLSearchParams(url.search).get('role');

        if(url.hash==='#analitik'||url.hash==='#pesanan'){
            viewUrl.hash=url.hash;
        }else if(url.pathname.startsWith('/admin/users')){
            viewUrl.hash=role==='driver'?'drivers':role==='seller'?'restaurants':role==='customer'?'customers':'users';
        }else if(url.pathname.startsWith('/admin/menus')){
            viewUrl.hash='menus';
        }else if(url.pathname.startsWith('/admin/settings')){
            viewUrl.hash='settings';
        }else if(url.pathname.startsWith('/admin/orders')){
            viewUrl.hash='orders';
        }else{
            viewUrl.hash='dashboard';
        }

        return viewUrl;
    }

    function renderAdminPage(html,url,addHistory){
        const parsed=new DOMParser().parseFromString(html,'text/html');

        document.head.querySelectorAll('[data-dashboard-page-style]').forEach(function(style){style.remove();});
        if(!parsed.querySelector('#dashboardWorkspace')){
            parsed.head.querySelectorAll('style').forEach(function(style){
                const pageStyle=document.createElement('style');
                pageStyle.dataset.dashboardPageStyle='true';
                pageStyle.textContent=style.textContent;
                document.head.appendChild(pageStyle);
            });
        }

        workspace.innerHTML=extractWorkspace(parsed);
        workspace.setAttribute('aria-busy','false');
        workspace.querySelectorAll('script:not([src])').forEach(function(oldScript){
            const script=document.createElement('script');
            script.textContent='(function(){\n'+oldScript.textContent+'\n})();';
            oldScript.replaceWith(script);
        });
        if(addHistory) history.pushState({dashboardFetchUrl:url.href},'',dashboardViewUrl(url).href);
        setActiveNavigation(url);
        const anchor=url.hash ? document.querySelector(url.hash) : null;
        if(anchor) anchor.scrollIntoView({behavior:'smooth'});
        else window.scrollTo({top:0,behavior:'smooth'});
    }

    async function loadAdminPage(url,options,addHistory){
        const requestId=++navigationRequest;
        workspace.setAttribute('aria-busy','true');
        try{
            const response=await fetch(url.href,{
                ...options,
                headers:{
                    'X-Requested-With':'XMLHttpRequest',
                    'Accept':'text/html',
                    ...(options?.headers||{})
                },
                credentials:'same-origin'
            });
            const html=await response.text();
            if(requestId!==navigationRequest) return;
            const finalUrl=new URL(response.url,window.location.origin);
            if(url.hash&&finalUrl.pathname===url.pathname) finalUrl.hash=url.hash;
            if(finalUrl.pathname==='/admin/login'||finalUrl.pathname==='/login'){
                window.location.assign(finalUrl.href);
                return;
            }
            if(response.ok){
                renderAdminPage(html,finalUrl,addHistory);
                if(!addHistory&&finalUrl.href!==window.location.href){
                    history.replaceState({},'',finalUrl.href);
                }
            }else{
                workspace.setAttribute('aria-busy','false');
                workspace.insertAdjacentHTML('afterbegin','<div class="flash-message" role="alert">Halaman tidak dapat dimuat. Silakan coba lagi.</div>');
            }
        }catch(error){
            if(requestId!==navigationRequest) return;
            workspace.setAttribute('aria-busy','false');
            workspace.insertAdjacentHTML('afterbegin','<div class="flash-message" role="alert">Koneksi gagal. Periksa jaringan lalu coba lagi.</div>');
        }
    }

    document.addEventListener('click',function(event){
        const clickedElement=event.target instanceof Element ? event.target : null;
        const link=clickedElement?.closest('a[href]');
        if(!link||event.defaultPrevented||event.button!==0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey||link.target||link.hasAttribute('download')) return;
        const url=new URL(link.href,window.location.origin);
        if(!isInternalAdminUrl(url)) return;
        event.preventDefault();
        if(window.innerWidth<=1000) setSidebarState(false);
        loadAdminPage(url,{method:'GET'},true);
    });

    document.addEventListener('submit',function(event){
        const form=event.target;
        if(!(form instanceof HTMLFormElement)) return;
        const url=new URL(form.action,window.location.origin);
        if(!isInternalAdminUrl(url)) return;
        event.preventDefault();

        const method=(form.method||'GET').toUpperCase();
        const options={method};
        if(method==='GET'){
            const formData=new FormData(form);
            for(const [key,value] of formData.entries()){
                if(typeof value==='string') url.searchParams.set(key,value);
            }
        }else{
            options.body=new FormData(form);
        }
        loadAdminPage(url,options,true);
    });

    history.replaceState({dashboardFetchUrl:window.location.href},'',window.location.href);
    window.addEventListener('popstate',function(event){
        const fetchUrl=event.state?.dashboardFetchUrl||dashboardUrl.href;
        loadAdminPage(new URL(fetchUrl),{method:'GET'},false);
    });

    setActiveNavigation(new URL(window.location.href));
});
</script>

</body>
</html>
