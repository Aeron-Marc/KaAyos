<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – KaAyos</title>
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
        }
        html,body{height:100%;font-family:'Inter',sans-serif}
        body{background:var(--off);margin:0;padding:0}
        .admin-container{display:flex;min-height:100vh}
        .sidebar{width:280px;background:var(--b9);padding:32px 24px;position:fixed;height:100vh;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,.15)}
        .main-content{margin-left:280px;flex:1;padding:32px;background:var(--off)}
        .sidebar-brand{display:flex;align-items:center;gap:10px;margin-bottom:36px;padding-bottom:24px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-brand-icon{width:40px;height:40px;background:var(--b6);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px}
        .sidebar-brand-name{font-size:1.15rem;font-weight:700;color:#fff;letter-spacing:.02em}
        .sidebar-menu{list-style:none;display:flex;flex-direction:column;gap:8px}
        .sidebar-menu li a{display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:8px;color:rgba(255,255,255,.7);text-decoration:none;font-size:.95rem;font-weight:500;transition:all .18s;border-left:3px solid transparent}
        .sidebar-menu li a:hover{background:rgba(255,255,255,.08);color:#fff;border-left-color:var(--b4)}
        .sidebar-menu li a.active{background:var(--b6);color:#fff;border-left-color:var(--b4)}
        .sidebar-menu li a i{font-size:1.1rem}
        .header{margin-bottom:32px}
        .header-title{font-size:1.8rem;font-weight:700;color:var(--b9);margin-bottom:8px}
        .header-subtitle{font-size:.95rem;color:var(--g4)}
        .metrics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;margin-bottom:32px}
        .metric-card{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);transition:all .25s;border:1px solid rgba(0,0,0,.04)}
        .metric-card:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(0,0,0,.12)}
        .metric-card.accent-blue{border-top:4px solid var(--b6)}
        .metric-card.accent-green{border-top:4px solid var(--s10)}
        .metric-card.accent-orange{border-top:4px solid #F59E0B}
        .metric-card.accent-red{border-top:4px solid var(--d10)}
        .metric-label{font-size:.85rem;font-weight:600;color:var(--g4);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px}
        .metric-value{font-size:2.2rem;font-weight:700;color:var(--b9);margin-bottom:8px}
        .metric-change{font-size:.82rem;color:var(--s10);display:flex;align-items:center;gap:4px}
        .metric-change.negative{color:#EF4444}
        .card-section{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);margin-bottom:24px}
        .section-title{font-size:1.3rem;font-weight:700;color:var(--b9);margin-bottom:20px;display:flex;align-items:center;gap:12px}
        .section-title i{font-size:1.5rem;color:var(--b6)}
        .btn-group{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
        .btn{padding:12px 20px;border-radius:8px;border:none;font-size:.95rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:8px;text-decoration:none;justify-content:center}
        .btn-primary{background:var(--b6);color:#fff}
        .btn-primary:hover{background:var(--b7);transform:translateY(-1px)}
        .btn-primary:active{transform:translateY(0)}
        .btn-secondary{background:var(--off);color:var(--b6);border:1.5px solid var(--g1)}
        .btn-secondary:hover{background:#fff;border-color:var(--b4)}
        .quick-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px}
        .stat-item{padding:16px;background:rgba(26,111,196,.05);border-radius:12px;border-left:3px solid var(--b6)}
        .stat-item-label{font-size:.8rem;font-weight:600;color:var(--g4);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px}
        .stat-item-value{font-size:1.4rem;font-weight:700;color:var(--b9)}
        @media(max-width:768px){
            .admin-container{flex-direction:column}
            .sidebar{width:100%;height:auto;position:relative;padding:20px;margin-bottom:20px}
            .main-content{margin-left:0;padding:20px}
            .header-title{font-size:1.4rem}
            .metrics-grid{grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
            .metric-card{padding:20px}
            .metric-value{font-size:1.8rem}
        }
    </style>
</head>
<body>

<div class="admin-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="fa-solid fa-shield-admin"></i></div>
            <span class="sidebar-brand-name">Admin Panel</span>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="active">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard Overview</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.verification.index') }}">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Worker Verifications</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        
        <div class="header">
            <div class="header-title">Dashboard Overview</div>
            <div class="header-subtitle">Welcome back! Here's your platform overview.</div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-item">
                <div class="stat-item-label">Last Updated</div>
                <div class="stat-item-value">Today</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-label">Platform Status</div>
                <div class="stat-item-value" style="color: var(--s10);"><i class="fa-solid fa-check-circle" style="margin-right: 6px;"></i>Healthy</div>
            </div>
            <div class="stat-item">
                <div class="stat-item-label">Active Sessions</div>
                <div class="stat-item-value">127</div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <!-- Total Users Card -->
            <div class="metric-card accent-blue">
                <div class="metric-label"><i class="fa-solid fa-users" style="margin-right: 6px;"></i>Total Users</div>
                <div class="metric-value">3,824</div>
                <div class="metric-change">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <span>+12% from last month</span>
                </div>
            </div>

            <!-- Active Bookings Card -->
            <div class="metric-card accent-green">
                <div class="metric-label"><i class="fa-solid fa-calendar-check" style="margin-right: 6px;"></i>Active Bookings</div>
                <div class="metric-value">286</div>
                <div class="metric-change">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <span>+8% from yesterday</span>
                </div>
            </div>

            <!-- Pending Verifications Card -->
            <div class="metric-card accent-orange">
                <div class="metric-label"><i class="fa-solid fa-hourglass-half" style="margin-right: 6px;"></i>Pending Verifications</div>
                <div class="metric-value">42</div>
                <div class="metric-change negative">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <span>Awaiting review</span>
                </div>
            </div>

            <!-- Platform Revenue Card -->
            <div class="metric-card accent-red">
                <div class="metric-label"><i class="fa-solid fa-chart-line" style="margin-right: 6px;"></i>Platform Revenue</div>
                <div class="metric-value">₱124K</div>
                <div class="metric-change">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    <span>+24% from last month</span>
                </div>
            </div>
        </div>

        <!-- Actions Section -->
        <div class="card-section">
            <div class="section-title">
                <i class="fa-solid fa-list-check"></i>
                Quick Actions
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.verification.index') }}" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Review Pending Verifications
                </a>
                <a href="#" class="btn btn-secondary">
                    <i class="fa-solid fa-file-export"></i>
                    Export Report
                </a>
            </div>
        </div>

    </main>

</div>

</body>
</html>
