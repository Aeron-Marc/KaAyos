<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — KaAyos Admin</title>
    <link rel="icon" href="{{ asset('images/KaAyos_logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --b9:#042C53;--b8:#0C447C;--b7:#185FA5;--b6:#1A6FC4;--b4:#378ADD;--b2:#85B7EB;--b1:#B5D4F4;--b0:#E6F1FB;
            --g9:#1B2430;--g7:#3D4A56;--g4:#8C97A4;--g1:#E8ECF0;--white:#fff;--off:#F7F8FA;
            --s10:#10B981;--s9:#059669;--s8:#047857;--d10:#EF4444;--d9:#DC2626;--d8:#B91C1C;
            --y10:#FBBF24;--y9:#F59E0B;--y8:#D97706;
        }
        html,body{height:100%;font-family:'Inter',sans-serif}
        body{background:var(--off);margin:0;padding:0}
        a,button,input,select,textarea{outline-offset:2px}
        :focus-visible{outline:3px solid var(--b4);outline-offset:2px}
        .skip-link{position:fixed;left:16px;top:-80px;z-index:300;background:#fff;color:var(--b8);padding:12px 18px;border-radius:8px;font-weight:700;text-decoration:none;box-shadow:0 8px 24px rgba(0,0,0,.2);transition:top .18s}
        .skip-link:focus{top:16px}
        .visually-hidden{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
        .admin-container{display:flex;min-height:100vh}
        .sidebar{width:280px;background:var(--b9);padding:32px 24px;position:fixed;height:100vh;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,.15);z-index:100;display:flex;flex-direction:column}
        .main-content{margin-left:280px;flex:1;padding:32px;background:var(--off);min-height:100vh}
        .sidebar-brand{display:flex;align-items:center;gap:10px;margin-bottom:36px;padding-bottom:24px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-brand-icon{width:40px;height:40px;background:var(--b6);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px}
        .sidebar-brand-name{font-size:1.15rem;font-weight:700;color:#fff;letter-spacing:.02em}
        .sidebar-menu{list-style:none;display:flex;flex-direction:column;gap:8px}
        .sidebar-menu li a{display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:8px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.95rem;font-weight:500;transition:all .18s;border-left:3px solid transparent}
        .sidebar-menu li a:hover{background:rgba(255,255,255,.08);color:#fff;border-left-color:var(--b4)}
        .sidebar-menu li a.active{background:var(--b6);color:#fff;border-left-color:var(--b4)}
        .sidebar-menu li a i{font-size:1.1rem;width:20px;text-align:center}
        .sidebar-badge{margin-left:auto;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:.68rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-variant-numeric:tabular-nums}
        .sidebar-menu li a.active .sidebar-badge{background:rgba(255,255,255,.25)}
        .sidebar-footer{margin-top:auto;padding-top:24px;border-top:1px solid rgba(255,255,255,.1)}
        .sidebar-footer a,.sidebar-logout{display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:8px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.95rem;font-weight:500;transition:all .18s}
        .sidebar-footer a:hover,.sidebar-logout:hover{background:rgba(255,255,255,.08);color:#fff}
        .sidebar-logout{width:100%;border:none;background:none;font-family:'Inter',sans-serif;cursor:pointer}
        .sidebar-spacer{flex:1}
        .header{margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .header-left h1{font-size:1.8rem;font-weight:700;color:var(--b9);margin:0 0 4px 0;display:flex;align-items:center;gap:12px}
        .header-left p{font-size:.95rem;color:var(--g4);margin:0}
        .header-right{display:flex;align-items:center;gap:12px}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--g4);margin-bottom:12px;flex-wrap:wrap}
        .breadcrumb a{color:var(--b6);text-decoration:none}
        .breadcrumb a:hover{text-decoration:underline}
        .breadcrumb span{color:var(--g7)}
        .status-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
        .status-pending{background:rgba(245,158,11,.1);color:var(--y8)}
        .status-approved,.status-verified{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-rejected{background:rgba(239,68,68,.1);color:var(--d8)}
        .status-under_review,.status-open{background:rgba(59,130,246,.1);color:#2563EB}
        .status-resolved{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-in_progress{background:rgba(59,130,246,.1);color:#2563EB}
        .status-confirmed{background:rgba(139,92,246,.1);color:#7C3AED}
        .status-completed{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-cancelled{background:rgba(107,114,128,.1);color:#6B7280}
        .status-suspended{background:rgba(239,68,68,.1);color:var(--d8)}
        .status-active{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-not_submitted{background:rgba(107,114,128,.1);color:#6B7280}
        .status-client{background:rgba(26,111,196,.1);color:var(--b7)}
        .status-worker{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-admin{background:rgba(239,68,68,.1);color:var(--d8)}
        .status-new{background:rgba(75,85,99,.1);color:#4B5563}
        .status-accepted{background:rgba(29,78,216,.1);color:#1D4ED8}
        .status-en_route{background:rgba(13,148,136,.1);color:#0D9488}
        .status-declined{background:rgba(124,58,237,.1);color:#7C3AED}
        .filters-bar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;background:#fff;padding:16px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:24px}
        .filter-group{display:flex;align-items:center;gap:8px}
        .filter-group label{font-size:.85rem;font-weight:600;color:var(--g7);white-space:nowrap}
        .filter-group select,.filter-group input{padding:8px 12px;border:1.5px solid var(--g1);border-radius:6px;font-size:.9rem;font-family:'Inter',sans-serif;color:var(--g9);background:#fff;outline:none;transition:border-color .18s}
        .filter-group select:focus,.filter-group input:focus{border-color:var(--b4);box-shadow:0 0 0 3px rgba(26,111,196,.1)}
        .table-container{background:#fff;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,.08);overflow:hidden;border:1px solid rgba(0,0,0,.04)}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--off);border-bottom:2px solid var(--g1)}
        th{padding:16px;text-align:left;font-size:.85rem;font-weight:700;color:var(--g7);text-transform:uppercase;letter-spacing:.05em}
        td{padding:16px;border-bottom:1px solid var(--g1);font-size:.9rem;color:var(--g9)}
        tbody tr:last-child td{border-bottom:none}
        tbody tr:hover{background:rgba(26,111,196,.02)}
        .btn{padding:12px 20px;border-radius:8px;border:none;font-size:.95rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:8px;text-decoration:none;justify-content:center}
        .btn:hover{transform:translateY(-1px)}
        .btn:active{transform:translateY(0)}
        .btn-primary{background:var(--b6);color:#fff}
        .btn-primary:hover{background:var(--b7)}
        .btn-secondary{background:var(--off);color:var(--b6);border:1.5px solid var(--g1)}
        .btn-secondary:hover{background:#fff;border-color:var(--b4)}
        .btn-success{background:var(--s10);color:#fff}
        .btn-success:hover{background:var(--s9)}
        .btn-danger{background:var(--d10);color:#fff}
        .btn-danger:hover{background:var(--d9)}
        .btn-warning{background:var(--y9);color:#fff}
        .btn-warning:hover{background:var(--y8)}
        .btn-sm{padding:8px 14px;font-size:.85rem}
        .btn-xs{padding:5px 10px;font-size:.78rem;border-radius:6px}
        .actions-cell{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .empty-state{padding:60px 32px;text-align:center}
        .empty-state-icon{font-size:3rem;color:var(--g4);margin-bottom:16px}
        .empty-state-title{font-size:1.2rem;font-weight:600;color:var(--g9);margin-bottom:8px}
        .empty-state-subtitle{font-size:.95rem;color:var(--g4);margin-bottom:24px}
        dialog.admin-dialog{border:0;border-radius:16px;padding:0;width:min(480px,calc(100vw - 32px));box-shadow:0 24px 60px rgba(0,0,0,.24);background:#fff;color:var(--g9)}
        dialog.admin-dialog::backdrop{background:rgba(9,30,66,.55)}
        dialog.admin-dialog form{padding:28px}
        .pagination{display:flex;justify-content:center;align-items:center;gap:8px;padding:24px;flex-wrap:wrap}
        .pagination a,.pagination span{display:flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 8px;border-radius:6px;font-size:.9rem;font-weight:600;text-decoration:none;color:var(--b6);border:1px solid var(--g1);transition:all .18s}
        .pagination a:hover{background:var(--b0);border-color:var(--b4)}
        .pagination .active{background:var(--b6);color:#fff;border-color:var(--b6)}
        .pagination .disabled{color:var(--g4);pointer-events:none;opacity:.5}
        .card{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04)}
        .card-title{font-size:1.2rem;font-weight:700;color:var(--b9);margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .card-title i{color:var(--b6);font-size:1.3rem}
        .metrics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;margin-bottom:32px}
        .metric-card{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);transition:all .25s;border:1px solid rgba(0,0,0,.04)}
        .metric-card:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(0,0,0,.12)}
        .metric-card.accent-blue{border-top:4px solid var(--b6)}
        .metric-card.accent-green{border-top:4px solid var(--s10)}
        .metric-card.accent-orange{border-top:4px solid #F59E0B}
        .metric-card.accent-red{border-top:4px solid var(--d10)}
        .metric-card.accent-purple{border-top:4px solid #8B5CF6}
        .metric-label{font-size:.85rem;font-weight:600;color:var(--g4);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px}
        .metric-value{font-size:2.2rem;font-weight:700;color:var(--b9);margin-bottom:8px}
        .metric-change{font-size:.82rem;color:var(--s10);display:flex;align-items:center;gap:4px}
        .metric-change.negative{color:#EF4444}
        .detail-section{margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--g1)}
        .detail-section:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
        .detail-row{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
        .detail-row:last-child{margin-bottom:0}
        .detail-label{font-size:.85rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.05em;flex-shrink:0;margin-right:16px}
        .detail-value{font-size:.95rem;color:var(--g9);font-weight:500;text-align:right}
        .info-box{background:rgba(26,111,196,.05);border-left:3px solid var(--b6);border-radius:8px;padding:14px;margin-bottom:16px;font-size:.9rem;color:var(--g9);display:flex;align-items:flex-start;gap:10px}
        .info-box i{color:var(--b6);font-size:1rem;margin-top:2px;flex-shrink:0}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--b6);text-decoration:none;font-size:.95rem;font-weight:600;margin-bottom:20px;transition:all .18s}
        .back-link:hover{gap:10px;color:var(--b8)}
        .back-link i{font-size:.85rem}
        .layout-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:28px}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;font-size:.85rem;font-weight:600;color:var(--g7);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;border:1.5px solid var(--g1);border-radius:8px;font-size:.95rem;font-family:'Inter',sans-serif;color:var(--g9);background:#fff;outline:none;transition:border-color .18s}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--b4);box-shadow:0 0 0 3px rgba(26,111,196,.1)}
        .form-group textarea{min-height:100px;resize:vertical}
        .form-group .error{color:var(--d10);font-size:.82rem;margin-top:4px}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .alert{padding:14px 20px;border-radius:10px;margin-bottom:20px;font-size:.9rem;font-weight:500;display:flex;align-items:center;gap:10px}
        .alert-success{background:rgba(16,185,129,.1);color:var(--s8);border:1px solid rgba(16,185,129,.2)}
        .alert-danger{background:rgba(239,68,68,.1);color:var(--d8);border:1px solid rgba(239,68,68,.2)}
        .alert-warning{background:rgba(245,158,11,.1);color:var(--y8);border:1px solid rgba(245,158,11,.2)}
        .alert-info{background:rgba(26,111,196,.1);color:var(--b7);border:1px solid rgba(26,111,196,.2)}
        .action-bar{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04);display:flex;gap:16px;flex-wrap:wrap;align-items:center;justify-content:space-between}
        .user-initials{width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;flex-shrink:0}
        .user-cell{display:flex;align-items:center;gap:12px}
        .user-cell-info{min-width:0}
        .user-cell-name{font-weight:600;color:var(--g9);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .user-cell-email{font-size:.8rem;color:var(--g4)}
        .toggle-label{display:flex;align-items:center;gap:8px;cursor:pointer}
        .toggle-label input[type="checkbox"]{width:18px;height:18px;accent-color:var(--b6)}
        .section-spacer{margin-top:32px}
        .quick-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px}
        .stat-item{padding:16px;background:rgba(26,111,196,.05);border-radius:12px;border-left:3px solid var(--b6)}
        .stat-item-label{font-size:.8rem;font-weight:600;color:var(--g4);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px}
        .stat-item-value{font-size:1.4rem;font-weight:700;color:var(--b9)}
        .document-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
        .document-placeholder{width:100%;aspect-ratio:3/4;background:linear-gradient(135deg, var(--b0), rgba(26,111,196,.05));border:2px dashed var(--b4);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--b6);transition:all .25s}
        .document-placeholder:hover{background:linear-gradient(135deg, rgba(26,111,196,.08), rgba(26,111,196,.12));border-color:var(--b6)}
        .document-placeholder-icon{font-size:2.8rem;margin-bottom:8px}
        .document-placeholder-text{font-size:.85rem;font-weight:600;text-align:center;padding:0 12px}
        .document-placeholder-subtext{font-size:.75rem;color:var(--b2);margin-top:4px}
        .document-label{font-size:.8rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;display:block}
        .preview-doc-img{width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:12px;border:2px solid var(--g1)}
        .page-actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
        .text-muted{color:var(--g4)}
        .text-sm{font-size:.85rem}
        .fw-600{font-weight:600}
        .mb-0{margin-bottom:0}
        .mt-1{margin-top:4px}
        .w-auto{width:auto}
        .table-col-price{text-align:right;font-variant-numeric:tabular-nums}
        .badge-dot{width:8px;height:8px;border-radius:50%;background:var(--d10);display:inline-block}
        .admin-topbar{display:none;position:fixed;top:0;left:0;right:0;height:56px;background:var(--b9);z-index:200;align-items:center;padding:0 16px;box-shadow:0 2px 8px rgba(0,0,0,.2)}

        /* ── Admin Skeleton Page ── */
        @keyframes ad-shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        .ad-sk {
            background: #e8ecf0;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }
        .ad-sk::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.5) 50%, transparent 100%);
            background-size: 800px 100%;
            animation: ad-shimmer 1.5s ease-in-out infinite;
        }
        .ad-sk-text { height: 14px; width: 100%; margin-bottom: 8px; }
        .ad-sk-text-sm { height: 14px; width: 60%; margin-bottom: 8px; }
        .ad-sk-title { height: 24px; width: 40%; margin-bottom: 12px; }
        .ad-sk-stat { height: 100px; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .ad-sk-stat-circle { width: 40px; height: 40px; border-radius: 10px; margin-bottom: 12px; }
        .ad-sk-row { height: 52px; width: 100%; margin-bottom: 4px; border-radius: 6px; }
        .ad-sk-panel { background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,.04); padding: 24px; margin-bottom: 20px; }

        .skeleton-page {
            position: absolute; inset: 0; padding: 32px; background: var(--off); z-index: 20;
            opacity: 1; transition: opacity 0.35s ease; pointer-events: auto; overflow-y: auto;
        }
        .skeleton-page--loaded { opacity: 0; pointer-events: none; transition-delay: 0.1s; }
        .skeleton-page .sp-section { margin-bottom: 24px; }
        .skeleton-page .sp-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 24px; margin-bottom: 28px; }
        .skeleton-page .sp-title-row { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .skeleton-page .sp-filter-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .skeleton-page .sp-filter-bar .ad-sk { height: 38px; width: 100px; }
        .skeleton-page .sp-filter-bar .ad-sk-search { width: 240px; }
        .main-content { position: relative; }
        .admin-hamburger{display:flex;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:8px;z-index:210}
        .admin-hamburger span{display:block;width:22px;height:2px;background:#fff;border-radius:2px;transition:all .3s}
        .admin-hamburger.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
        .admin-hamburger.active span:nth-child(2){opacity:0}
        .admin-hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px)}
        .admin-topbar-brand{display:flex;align-items:center;gap:8px;margin-left:12px}
        .admin-topbar-brand img{height:28px;width:auto}
        .admin-topbar-brand span{font-size:1rem;font-weight:700;color:#fff}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90;opacity:0;transition:opacity .3s}
        .sidebar-overlay.active{display:block;opacity:1}
        @media(max-width:1024px){
            .layout-grid-2{grid-template-columns:1fr;gap:20px}
        }
        @media(max-width:768px){
            .admin-topbar{display:flex}
            .sidebar{position:fixed;left:-280px;top:0;height:100vh;width:280px;z-index:100;transition:left .3s ease;padding-top:72px}
            .sidebar.open{left:0}
            .main-content{margin-left:0;padding:72px 20px 20px}
            .header{flex-direction:column;align-items:flex-start}
            .header-left h1{font-size:1.4rem}
            .metrics-grid{grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
            .table-container{overflow-x:auto}
            .card{overflow-x:auto}
            .card table{min-width:480px}
            .filters-bar{flex-direction:column;align-items:stretch}
            .filter-group{width:100%}
            .filter-group select,.filter-group input{width:100%}
            .form-row{grid-template-columns:1fr}
            th,td{padding:12px}
        }
        @media (prefers-reduced-motion: reduce){
            *,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;scroll-behavior:auto!important}
            .ad-sk::after{animation:none}
        }
        @media print{
            .sidebar,.admin-topbar,.sidebar-overlay,.skeleton-page,.filters-bar,.rpt-bar,.rpt-section,.header-right,.btn,.pagination,.actions-cell,.rpt-export-row{display:none!important}
            body{background:#fff}
            .main-content{margin-left:0;padding:0}
            .header{display:block;margin-bottom:16px}
            .header-left p{color:#555}
            .card,.metric-card,.table-container{box-shadow:none;border:1px solid #CBD5E1;border-radius:0;overflow:visible}
            .table-container,.card{overflow-x:visible}
            table{width:100%}
            thead{display:table-header-group}
            tbody tr{page-break-inside:avoid;break-inside:avoid}
            .status-badge,.metric-card,.metric-card.accent-blue,.metric-card.accent-green,.metric-card.accent-orange,.metric-card.accent-red,.metric-card.accent-purple{-webkit-print-color-adjust:exact;print-color-adjust:exact}
        }
    </style>
    @stack('styles')
</head>
<body class="@yield('body_class')">
<a class="skip-link" href="#main-content">{{ __('admin.shell.skip_to_content') }}</a>
<div class="admin-topbar">
    <button class="admin-hamburger" id="adminHamburger" type="button"
            aria-label="{{ __('admin.shell.toggle_menu') }}" aria-expanded="false" aria-controls="adminSidebar">
        <span></span><span></span><span></span>
    </button>
    <div class="admin-topbar-brand">
        <img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos">
        <span>{{ __('admin.shell.admin_panel') }}</span>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
<div class="admin-container">
    <aside class="sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos" style="height:36px;width:auto;">
            <span class="sidebar-brand-name">{{ __('admin.shell.admin_panel') }}</span>
        </div>
        <nav aria-label="{{ __('admin.shell.main_navigation') }}">
            <ul class="sidebar-menu">
                @php
                    $adminNav = $adminNav ?? ['disputes' => 0, 'verifications' => 0];
                @endphp
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.dashboard') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.users') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.workers.index') }}" class="{{ request()->routeIs('admin.workers.*') ? 'active' : '' }}" @if(request()->routeIs('admin.workers.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.workers') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.verification.index') }}" class="{{ request()->routeIs('admin.verification.*') ? 'active' : '' }}" @if(request()->routeIs('admin.verification.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.verifications') }}</span>
                        @if(($adminNav['verifications'] ?? 0) > 0)
                            <span class="sidebar-badge" aria-label="{{ __('admin.dash.nav.verifications_badge', ['count' => $adminNav['verifications']]) }}">{{ $adminNav['verifications'] }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.service-categories.index') }}" class="{{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" @if(request()->routeIs('admin.service-categories.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.service_categories') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}" @if(request()->routeIs('admin.services.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-wrench" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.services') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.provider-services.index') }}" class="{{ request()->routeIs('admin.provider-services.*') ? 'active' : '' }}" @if(request()->routeIs('admin.provider-services.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.provider_services') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" @if(request()->routeIs('admin.bookings.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.bookings') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.disputes.index') }}" class="{{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}" @if(request()->routeIs('admin.disputes.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-scale-balanced" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.disputes') }}</span>
                        @if(($adminNav['disputes'] ?? 0) > 0)
                            <span class="sidebar-badge" aria-label="{{ __('admin.dash.nav.disputes_badge', ['count' => $adminNav['disputes']]) }}">{{ $adminNav['disputes'] }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" @if(request()->routeIs('admin.testimonials.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-quote-left" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.testimonials') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                        <span>{{ __('admin.nav.reports') }}</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-spacer"></div>
        <div class="sidebar-footer">
            <div style="padding: 8px 16px; margin-bottom: 8px;">
                <x-language-switcher />
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display:block">
                @csrf
                <button type="submit" class="sidebar-logout">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    <span>{{ __('admin.shell.logout') }}</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="main-content" id="main-content" tabindex="-1">
        <div class="skeleton-page" id="skeletonPage" aria-hidden="true">
            @hasSection('skeleton')
                @yield('skeleton')
            @else
                <div class="sp-title-row">
                    <div class="ad-sk ad-sk-title" style="width:220px;"></div>
                    <div class="ad-sk" style="height:38px;width:120px;border-radius:8px;"></div>
                </div>
                <div class="sp-stats">
                    <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:60px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
                    <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:60px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
                    <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:60px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
                    <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:60px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
                </div>
                <div class="ad-sk-panel">
                    <div class="ad-sk ad-sk-title" style="width:160px;margin-bottom:20px;"></div>
                    <div class="ad-sk ad-sk-text"></div>
                    <div class="ad-sk ad-sk-text"></div>
                    <div class="ad-sk ad-sk-text-sm"></div>
                    <div style="height:16px;"></div>
                    <div class="ad-sk ad-sk-row"></div>
                    <div class="ad-sk ad-sk-row"></div>
                    <div class="ad-sk ad-sk-row"></div>
                    <div class="ad-sk ad-sk-row"></div>
                </div>
            @endif
        </div>
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success" role="status">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
<script>
(function(){
    var hamburger=document.getElementById('adminHamburger');
    var sidebar=document.getElementById('adminSidebar');
    var overlay=document.getElementById('sidebarOverlay');
    if(!hamburger||!sidebar||!overlay)return;

    function setMenu(open, restoreFocus){
        hamburger.classList.toggle('active', open);
        sidebar.classList.toggle('open', open);
        overlay.classList.toggle('active', open);
        hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
            var first = sidebar.querySelector('a,button');
            if (first) first.focus();
        } else if (restoreFocus) {
            hamburger.focus();
        }
    }

    hamburger.addEventListener('click', function(){
        setMenu(!sidebar.classList.contains('open'), false);
    });
    overlay.addEventListener('click', function(){
        setMenu(false, true);
    });
    document.addEventListener('keydown', function(e){
        if (!sidebar.classList.contains('open')) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            setMenu(false, true);
            return;
        }
        if (e.key !== 'Tab') return;
        var focusable = Array.prototype.slice.call(sidebar.querySelectorAll('a[href],button:not([disabled]),input,select,textarea'));
        if (!focusable.length) return;
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });
})();
</script>
@stack('scripts')
<script>
(function() {
    var el = document.getElementById('skeletonPage');
    if (!el) return;
    function hideSkeleton() {
        el.classList.add('skeleton-page--loaded');
        window.setTimeout(function() { el.style.display = 'none'; }, 350);
    }
    if (document.readyState === 'complete') {
        hideSkeleton();
    } else {
        window.addEventListener('load', hideSkeleton, { once: true });
    }
})();
</script>
</body>
</html>
