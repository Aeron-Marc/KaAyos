```<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Details – KaAyos Admin</title>
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
        .header{margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .header-left{flex:1;min-width:200px}
        .header-left h1{font-size:1.8rem;font-weight:700;color:var(--b9);margin:0 0 4px 0;display:flex;align-items:center;gap:12px}
        .header-left p{font-size:.95rem;color:var(--g4);margin:0}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--g4);margin-bottom:12px}
        .breadcrumb a{color:var(--b6);text-decoration:none}
        .breadcrumb a:hover{text-decoration:underline}
        .status-badge{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .status-pending{background:rgba(245,158,11,.1);color:#D97706}
        .status-approved{background:rgba(16,185,129,.1);color:var(--s8)}
        .status-rejected{background:rgba(239,68,68,.1);color:var(--d8)}
        .layout-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:28px}
        .card{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04)}
        .card-title{font-size:1.2rem;font-weight:700;color:var(--b9);margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .card-title i{color:var(--b6);font-size:1.3rem}
        .detail-section{margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--g1)}
        .detail-section:last-child{border-bottom:none}
        .detail-row{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
        .detail-label{font-size:.85rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.05em}
        .detail-value{font-size:.95rem;color:var(--g9);font-weight:500}
        .worker-avatar{width:80px;height:80px;border-radius:12px;background:linear-gradient(135deg, var(--b4), var(--b6));display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.2rem;font-weight:700;margin-bottom:16px;box-shadow:0 4px 12px rgba(26,111,196,.2)}
        .profile-header{margin-bottom:24px;display:flex;align-items:flex-start;gap:16px}
        .profile-header-content h2{font-size:1.4rem;font-weight:700;color:var(--b9);margin:0 0 4px 0}
        .profile-header-content p{font-size:.9rem;color:var(--g4);margin:0}
        .document-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
        .document-placeholder{width:100%;aspect-ratio:3/4;background:linear-gradient(135deg, var(--b0), rgba(26,111,196,.05));border:2px dashed var(--b4);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--b6);transition:all .25s;cursor:pointer}
        .document-placeholder:hover{background:linear-gradient(135deg, rgba(26,111,196,.08), rgba(26,111,196,.12));border-color:var(--b6)}
        .document-placeholder-icon{font-size:2.8rem;margin-bottom:8px}
        .document-placeholder-text{font-size:.85rem;font-weight:600;text-align:center;padding:0 12px}
        .document-placeholder-subtext{font-size:.75rem;color:var(--b2);margin-top:4px}
        .document-label{font-size:.8rem;font-weight:600;color:var(--g7);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;display:block}
        .action-bar{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 12px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04);display:flex;gap:16px;flex-wrap:wrap;align-items:center;justify-content:space-between}
        .action-bar-content{display:flex;gap:12px;flex-wrap:wrap;flex:1}
        .btn{padding:12px 24px;border-radius:8px;border:none;font-size:.95rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:8px;text-decoration:none;justify-content:center}
        .btn-success{background:var(--s10);color:#fff}
        .btn-success:hover{background:var(--s9);transform:translateY(-1px)}
        .btn-success:active{transform:translateY(0)}
        .btn-danger{background:var(--d10);color:#fff}
        .btn-danger:hover{background:var(--d9);transform:translateY(-1px)}
        .btn-danger:active{transform:translateY(0)}
        .btn-secondary{background:var(--off);color:var(--b6);border:1.5px solid var(--g1)}
        .btn-secondary:hover{background:#fff;border-color:var(--b4)}
        .info-box{background:rgba(26,111,196,.05);border-left:3px solid var(--b6);border-radius:8px;padding:14px;margin-bottom:16px;font-size:.9rem;color:var(--g9);display:flex;align-items:flex-start;gap:10px}
        .info-box i{color:var(--b6);font-size:1rem;margin-top:2px;flex-shrink:0}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--b6);text-decoration:none;font-size:.95rem;font-weight:600;margin-bottom:20px;transition:all .18s}
        .back-link:hover{gap:10px;color:var(--b8)}
        .back-link i{font-size:.85rem}
        @media(max-width:1024px){
            .layout-grid{grid-template-columns:1fr;gap:20px}
        }
        @media(max-width:768px){
            .admin-container{flex-direction:column}
            .sidebar{width:100%;height:auto;position:relative;padding:20px;margin-bottom:20px}
            .main-content{margin-left:0;padding:20px}
            .header{flex-direction:column;align-items:flex-start}
            .header-left h1{font-size:1.4rem}
            .layout-grid{grid-template-columns:1fr}
            .card{padding:20px}
            .card-title{font-size:1.1rem}
            .action-bar{flex-direction:column;align-items:stretch}
            .action-bar-content{flex-direction:column}
            .btn{width:100%}
            .document-grid{grid-template-columns:1fr}
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
        
        <a href="{{ route('admin.verification.index') }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Verifications</span>
        </a>

        <div class="header">
            <div class="header-left">
                <h1>
                    <i class="fa-solid fa-user-check"></i>
                    Verification Details
                </h1>
                <p>Review worker registration and supporting documents</p>
            </div>
            <div>
                <span class="status-badge status-pending">
                    <i class="fa-solid fa-hourglass-half"></i>Pending Review
                </span>
            </div>
        </div>

        <!-- Split Layout -->
        <div class="layout-grid">

            <!-- LEFT: Worker Details Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fa-solid fa-id-card"></i>
                    Worker Registration Details
                </div>

                <div class="profile-header">
                    <div class="worker-avatar">JM</div>
                    <div class="profile-header-content">
                        <h2>Juan Miguel Marquez</h2>
                        <p>Professional Plumber – 8 years experience</p>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value">Juan Miguel Marquez</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email Address</span>
                        <span class="detail-value">juan.marquez@email.com</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone Number</span>
                        <span class="detail-value">+63 917 234 5678</span>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Service Category</span>
                        <span class="detail-value">Plumbing Services</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Years of Experience</span>
                        <span class="detail-value">8 years</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">License/Certification</span>
                        <span class="detail-value">TESDA Certified Plumber</span>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Address</span>
                        <span class="detail-value">123 Manila Street, Quezon City 1110</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">ID Number</span>
                        <span class="detail-value">121-456-789-0</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date Submitted</span>
                        <span class="detail-value">June 15, 2026 at 2:32 PM</span>
                    </div>
                </div>

            </div>

            <!-- RIGHT: Documents Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fa-solid fa-file-invoice"></i>
                    Supporting Documents
                </div>

                <div class="info-box">
                    <i class="fa-solid fa-info-circle"></i>
                    <span>All documents have been uploaded and are ready for review. Click to view full documents.</span>
                </div>

                <div class="document-grid">
                    <!-- Valid ID Document -->
                    <div>
                        <label class="document-label">Valid ID Card (Front)</label>
                        <div class="document-placeholder">
                            <div class="document-placeholder-icon">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <div class="document-placeholder-text">Valid ID Card</div>
                            <div class="document-placeholder-subtext">PhilSys ID</div>
                        </div>
                    </div>

                    <!-- NBI Clearance -->
                    <div>
                        <label class="document-label">NBI Clearance Document</label>
                        <div class="document-placeholder">
                            <div class="document-placeholder-icon">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <div class="document-placeholder-text">NBI Clearance</div>
                            <div class="document-placeholder-subtext">Clearance Certificate</div>
                        </div>
                    </div>

                    <!-- License/Certification -->
                    <div>
                        <label class="document-label">Professional License</label>
                        <div class="document-placeholder">
                            <div class="document-placeholder-icon">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div class="document-placeholder-text">TESDA Certification</div>
                            <div class="document-placeholder-subtext">Professional License</div>
                        </div>
                    </div>

                    <!-- Business Permit -->
                    <div>
                        <label class="document-label">Business Permit</label>
                        <div class="document-placeholder">
                            <div class="document-placeholder-icon">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <div class="document-placeholder-text">Business Permit</div>
                            <div class="document-placeholder-subtext">Municipal Document</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: .85rem; font-weight: 600; color: var(--g7); text-transform: uppercase; letter-spacing: .04em;">Application Status</span>
                    <span style="font-size: 1rem; font-weight: 700; color: var(--b9);">Ready for Decision</span>
                </div>
            </div>
            <div class="action-bar-content">
                <form method="POST" action="{{ route('admin.verification.approve', 1) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check-circle"></i>
                        Approve Verification
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.verification.reject', 1) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-x-circle"></i>
                        Reject Application
                    </button>
                </form>
            </div>
        </div>

    </main>

</div>

</body>
</html>
