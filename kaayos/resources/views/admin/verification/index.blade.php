<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Verifications – KaAyos Admin</title>
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
        .header{margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .header-left h1{font-size:1.8rem;font-weight:700;color:var(--b9);margin:0 0 4px 0}
        .header-left p{font-size:.95rem;color:var(--g4);margin:0}
        .filters-bar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;background:#fff;padding:16px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:24px}
        .filter-group{display:flex;align-items:center;gap:8px}
        .filter-group label{font-size:.85rem;font-weight:600;color:var(--g7)}
        .filter-group select,.filter-group input{padding:8px 12px;border:1.5px solid var(--g1);border-radius:6px;font-size:.9rem;font-family:'Inter',sans-serif;color:var(--g9);background:#fff;outline:none;transition:border-color .18s}
        .filter-group select:focus,.filter-group input:focus{border-color:var(--b4);box-shadow:0 0 0 3px rgba(26,111,196,.1)}
        .table-container{background:#fff;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,.08);overflow:hidden;border:1px solid rgba(0,0,0,.04)}
        table{width:100%;border-collapse:collapse}
        thead{background:var(--off);border-bottom:2px solid var(--g1)}
        th{padding:16px;text-align:left;font-size:.85rem;font-weight:700;color:var(--g7);text-transform:uppercase;letter-spacing:.05em}
        td{padding:16px;border-bottom:1px solid var(--g1);font-size:.9rem;color:var(--g9)}
        tbody tr:last-child td{border-bottom:none}
        tbody tr:hover{background:rgba(26,111,196,.02)}
        .status-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
        .status-pending{background:rgba(245,158,11,.1);color:var(--y8)}
        .status-approved{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-rejected{background:rgba(239,68,68,.1);color:var(--d8)}
        .btn{padding:10px 16px;border-radius:8px;border:none;font-size:.9rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:6px;text-decoration:none;justify-content:center;white-space:nowrap}
        .btn-primary{background:var(--b6);color:#fff}
        .btn-primary:hover{background:var(--b7);transform:translateY(-1px)}
        .btn-primary:active{transform:translateY(0)}
        .btn-sm{padding:8px 12px;font-size:.85rem}
        .actions-cell{display:flex;gap:8px;align-items:center}
        .empty-state{padding:60px 32px;text-align:center}
        .empty-state-icon{font-size:3rem;color:var(--g4);margin-bottom:16px}
        .empty-state-title{font-size:1.2rem;font-weight:600;color:var(--g9);margin-bottom:8px}
        .empty-state-subtitle{font-size:.95rem;color:var(--g4);margin-bottom:24px}
        .pagination{display:flex;justify-content:center;align-items:center;gap:8px;padding:24px;flex-wrap:wrap}
        .pagination a,.pagination span{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:6px;font-size:.9rem;font-weight:600;text-decoration:none;color:var(--b6);border:1px solid var(--g1);transition:all .18s}
        .pagination a:hover{background:var(--b0);border-color:var(--b4)}
        .pagination .active{background:var(--b6);color:#fff;border-color:var(--b6)}
        .pagination .disabled{color:var(--g4);pointer-events:none;opacity:.5}
        @media(max-width:768px){
            .admin-container{flex-direction:column}
            .sidebar{width:100%;height:auto;position:relative;padding:20px;margin-bottom:20px}
            .main-content{margin-left:0;padding:20px}
            .header{flex-direction:column;align-items:flex-start}
            .header-left h1{font-size:1.4rem}
            .table-container{overflow-x:auto}
            .filters-bar{flex-direction:column;align-items:stretch}
            .filter-group{width:100%}
            .filter-group select,.filter-group input{width:100%}
            th,td{padding:12px}
            .btn-sm{padding:8px 10px;font-size:.8rem}
            .actions-cell{flex-wrap:wrap}
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
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard Overview</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.verification.index') }}" class="active">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Worker Verifications</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        
        <div class="header">
            <div class="header-left">
                <h1>Worker Verifications</h1>
                <p>Review and approve pending service provider applications</p>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-status">Status:</label>
                <select id="filter-status">
                    <option value="">All Applications</option>
                    <option value="pending">Pending Review</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-category">Category:</label>
                <select id="filter-category">
                    <option value="">All Categories</option>
                    <option value="plumbing">Plumbing</option>
                    <option value="electrical">Electrical</option>
                    <option value="carpentry">Carpentry</option>
                    <option value="cleaning">Cleaning</option>
                    <option value="landscaping">Landscaping</option>
                </select>
            </div>
            <div class="filter-group" style="margin-left: auto;">
                <input type="text" placeholder="Search provider name..." style="width: 200px;">
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Provider Name</th>
                        <th>Service Category</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, var(--b4), var(--b6)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">JM</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--g9);">Juan Marquez</div>
                                    <div style="font-size: .8rem; color: var(--g4);">juan.marquez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Plumbing Services</td>
                        <td>2026-06-15 14:32</td>
                        <td><span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i>Pending</span></td>
                        <td style="text-align: center;">
                            <div class="actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.verification.show', 1) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i>Review
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #F59E0B, #FBBF24); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">MC</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--g9);">Maria Cruz</div>
                                    <div style="font-size: .8rem; color: var(--g4);">maria.cruz@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Electrical Services</td>
                        <td>2026-06-14 09:15</td>
                        <td><span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i>Pending</span></td>
                        <td style="text-align: center;">
                            <div class="actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.verification.show', 2) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i>Review
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #10B981, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">RP</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--g9);">Rafael Perez</div>
                                    <div style="font-size: .8rem; color: var(--g4);">rafael.perez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Carpentry & Woodwork</td>
                        <td>2026-06-13 16:44</td>
                        <td><span class="status-badge status-approved"><i class="fa-solid fa-check-circle"></i>Approved</span></td>
                        <td style="text-align: center;">
                            <div class="actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.verification.show', 3) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i>Review
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #EF4444, #DC2626); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">AC</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--g9);">Angela Chavez</div>
                                    <div style="font-size: .8rem; color: var(--g4);">angela.chavez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td>General Cleaning</td>
                        <td>2026-06-12 11:28</td>
                        <td><span class="status-badge status-rejected"><i class="fa-solid fa-x-circle"></i>Rejected</span></td>
                        <td style="text-align: center;">
                            <div class="actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.verification.show', 4) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i>Review
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, var(--b2), var(--b4)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">LG</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--g9);">Luis Gutierrez</div>
                                    <div style="font-size: .8rem; color: var(--g4);">luis.gutierrez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Landscaping & Garden</td>
                        <td>2026-06-11 13:56</td>
                        <td><span class="status-badge status-pending"><i class="fa-solid fa-hourglass-half"></i>Pending</span></td>
                        <td style="text-align: center;">
                            <div class="actions-cell" style="justify-content: center;">
                                <a href="{{ route('admin.verification.show', 5) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i>Review
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <a href="#" class="disabled"><i class="fa-solid fa-chevron-left"></i></a>
            <span class="active">1</span>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">4</a>
            <span>...</span>
            <a href="#">8</a>
            <a href="#"><i class="fa-solid fa-chevron-right"></i></a>
        </div>

    </main>

</div>

</body>
</html>
