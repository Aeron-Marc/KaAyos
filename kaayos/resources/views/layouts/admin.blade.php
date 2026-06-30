<!DOCTYPE html>
<html lang="en">
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
        .admin-container{display:flex;min-height:100vh}
        .sidebar{width:280px;background:var(--b9);padding:32px 24px;position:fixed;height:100vh;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,.15);z-index:100}
        .main-content{margin-left:280px;flex:1;padding:32px;background:var(--off);min-height:100vh}
        .sidebar-brand{display:flex;align-items:center;gap:10px;margin-bottom:36px;padding-bottom:24px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-brand-icon{width:40px;height:40px;background:var(--b6);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px}
        .sidebar-brand-name{font-size:1.15rem;font-weight:700;color:#fff;letter-spacing:.02em}
        .sidebar-menu{list-style:none;display:flex;flex-direction:column;gap:8px}
        .sidebar-menu li a{display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:8px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.95rem;font-weight:500;transition:all .18s;border-left:3px solid transparent}
        .sidebar-menu li a:hover{background:rgba(255,255,255,.08);color:#fff;border-left-color:var(--b4)}
        .sidebar-menu li a.active{background:var(--b6);color:#fff;border-left-color:var(--b4)}
        .sidebar-menu li a i{font-size:1.1rem;width:20px;text-align:center}
        .sidebar-footer{margin-top:auto;padding-top:24px;border-top:1px solid rgba(255,255,255,.1)}
        .sidebar-footer a{display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:8px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.95rem;font-weight:500;transition:all .18s}
        .sidebar-footer a:hover{background:rgba(255,255,255,.08);color:#fff}
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
        @media(max-width:1024px){
            .layout-grid-2{grid-template-columns:1fr;gap:20px}
        }
        @media(max-width:768px){
            .admin-container{flex-direction:column}
            .sidebar{width:100%;height:auto;position:relative;padding:20px;margin-bottom:0}
            .main-content{margin-left:0;padding:20px}
            .header{flex-direction:column;align-items:flex-start}
            .header-left h1{font-size:1.4rem}
            .metrics-grid{grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
            .table-container{overflow-x:auto}
            .filters-bar{flex-direction:column;align-items:stretch}
            .filter-group{width:100%}
            .filter-group select,.filter-group input{width:100%}
            .form-row{grid-template-columns:1fr}
            th,td{padding:12px}
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos" style="height:36px;width:auto;">
            <span class="sidebar-brand-name">Admin Panel</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.verification.index') }}" class="{{ request()->routeIs('admin.verification.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Verifications</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.service-categories.index') }}" class="{{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Service Categories</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wrench"></i>
                    <span>Services</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.provider-services.index') }}" class="{{ request()->routeIs('admin.provider-services.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-handshake"></i>
                    <span>Provider Services</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.disputes.index') }}" class="{{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Disputes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-chart-column"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-spacer" style="flex:1"></div>
        <div class="sidebar-footer">
            <a href="{{ route('home') }}">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Site</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display:block">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:8px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.95rem;font-weight:500;border:none;background:none;font-family:'Inter',sans-serif;width:100%;cursor:pointer;transition:all .18s;"
                        onmouseover="this.style.background='rgba(255,255,255,.08)';this.style.color='#fff'"
                        onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,.7)'">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
