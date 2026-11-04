<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RMRS') — Reject Material Reporting System</title>
    <meta name="description" content="Sistem pelaporan material reject digital untuk departemen Lenscase dan Pouching.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #6C63FF;
            --primary-dark: #5A52E0;
            --primary-glow: rgba(108, 99, 255, 0.3);
            --secondary: #00D4AA;
            --secondary-glow: rgba(0, 212, 170, 0.2);
            --accent: #FF6B6B;
            --accent-glow: rgba(255, 107, 107, 0.2);
            --warning: #F59E0B;
            --warning-glow: rgba(245, 158, 11, 0.2);
            --success: #10B981;
            --danger: #EF4444;
            --bg: #0A0A1A;
            --bg-card: #111128;
            --bg-elevated: #181830;
            --bg-hover: #1E1E3A;
            --border: rgba(108, 99, 255, 0.15);
            --border-light: rgba(255, 255, 255, 0.06);
            --text-primary: #E8E8F5;
            --text-secondary: #8888AA;
            --text-muted: #5555777;
            --sidebar-w: 260px;
            --topbar-h: 64px;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow: 0 4px 24px rgba(0,0,0,0.4);
            --shadow-glow: 0 0 40px rgba(108,99,255,0.15);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 3px; }

        /* ===== Layout ===== */
        .app-layout { display: flex; min-height: 100vh; }

        /* ===== Sidebar ===== */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .brand-text { line-height: 1.2; }
        .brand-title { font-weight: 800; font-size: 15px; color: var(--text-primary); letter-spacing: 0.5px; }
        .brand-sub { font-size: 10px; color: var(--text-secondary); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
            border: 2px solid var(--primary-glow);
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--text-secondary); }

        .sidebar-nav { padding: 12px 0; flex: 1; }

        .nav-section { padding: 8px 20px 4px; font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
            margin: 1px 8px;
            border-radius: 8px;
        }

        .nav-item:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(108,99,255,0.2), rgba(108,99,255,0.05));
            color: var(--primary);
            font-weight: 600;
            border: 1px solid rgba(108,99,255,0.2);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--primary);
            border-radius: 0 2px 2px 0;
        }

        .nav-item i { width: 20px; text-align: center; font-size: 15px; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--border);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            background: rgba(239,68,68,0.1);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-logout:hover { background: rgba(239,68,68,0.2); }

        /* ===== Main Content ===== */
        .main-content {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== Topbar ===== */
        .topbar {
            height: var(--topbar-h);
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .topbar-breadcrumb { font-size: 12px; color: var(--text-secondary); }
        .topbar-breadcrumb span { color: var(--primary); }
        .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }

        .topbar-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--text-secondary);
            background: var(--bg-elevated);
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        /* ===== Page Content ===== */
        .page-content { padding: 24px; flex: 1; }

        /* ===== Cards ===== */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
        .card-title i { color: var(--primary); }
        .card-body { padding: 20px; }

        /* ===== Stat Cards ===== */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
        }

        .stat-card:hover { transform: translateY(-2px); border-color: var(--primary); }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-color, var(--primary));
        }

        .stat-card.green { --card-color: var(--success); }
        .stat-card.red { --card-color: var(--danger); }
        .stat-card.yellow { --card-color: var(--warning); }
        .stat-card.teal { --card-color: var(--secondary); }
        .stat-card.purple { --card-color: var(--primary); }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
            background: rgba(108,99,255,0.1);
            color: var(--card-color, var(--primary));
        }

        .stat-value { font-size: 28px; font-weight: 800; color: var(--text-primary); line-height: 1; font-family: 'JetBrains Mono', monospace; }
        .stat-label { font-size: 12px; color: var(--text-secondary); margin-top: 4px; font-weight: 500; }
        .stat-sub { font-size: 11px; color: var(--text-secondary); margin-top: 2px; }

        /* ===== Badges ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-pending { background: rgba(245,158,11,0.15); color: var(--warning); border: 1px solid rgba(245,158,11,0.3); }
        .badge-verified { background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .badge-rejected { background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
        .badge-critical { background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
        .badge-major { background: rgba(245,158,11,0.15); color: var(--warning); border: 1px solid rgba(245,158,11,0.3); }
        .badge-minor { background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .badge-lenscase { background: rgba(108,99,255,0.15); color: var(--primary); border: 1px solid rgba(108,99,255,0.3); }
        .badge-pouching { background: rgba(0,212,170,0.15); color: var(--secondary); border: 1px solid rgba(0,212,170,0.3); }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; box-shadow: 0 4px 12px var(--primary-glow); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px var(--primary-glow); }

        .btn-success { background: linear-gradient(135deg, var(--success), #0ea574); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
        .btn-success:hover { transform: translateY(-1px); }

        .btn-danger { background: linear-gradient(135deg, var(--danger), #dc2626); color: white; }
        .btn-danger:hover { transform: translateY(-1px); }

        .btn-warning { background: linear-gradient(135deg, var(--warning), #d97706); color: white; }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }

        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .btn-export {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-export:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }

        /* ===== Tables ===== */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--bg-elevated); }
        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid var(--border-light);
            transition: background 0.15s;
        }

        tbody tr:hover { background: var(--bg-hover); }
        tbody tr:last-child { border-bottom: none; }

        tbody td {
            padding: 12px 16px;
            font-size: 13px;
            color: var(--text-primary);
            vertical-align: middle;
        }

        .report-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            background: rgba(108,99,255,0.1);
            padding: 3px 8px;
            border-radius: 5px;
            display: inline-block;
        }

        /* ===== Forms ===== */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label.required::after {
            content: ' *';
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 13.5px;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            background: var(--bg-card);
        }

        .form-control::placeholder { color: var(--text-muted); }

        select.form-control { cursor: pointer; }

        textarea.form-control { resize: vertical; min-height: 80px; }

        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }

        .form-row-3 { grid-template-columns: repeat(3, 1fr); }
        .form-row-4 { grid-template-columns: repeat(4, 1fr); }

        .error-msg { font-size: 11px; color: var(--danger); margin-top: 4px; }

        /* ===== Filter Bar ===== */
        .filter-bar {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-group { display: flex; flex-direction: column; gap: 6px; min-width: 140px; flex: 1; }
        .filter-label { font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-control {
            padding: 8px 12px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 7px;
            color: var(--text-primary);
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }
        .filter-control:focus { border-color: var(--primary); }

        /* ===== Alert ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--success); }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: var(--danger); }
        .alert-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); color: var(--warning); }

        /* ===== Charts ===== */
        .chart-container { position: relative; height: 280px; }

        /* ===== Grid layouts ===== */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

        /* ===== Pagination ===== */
        .pagination { display: flex; gap: 4px; justify-content: center; padding: 16px; }
        .pagination a, .pagination span {
            padding: 6px 12px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            background: var(--bg-elevated);
            transition: all 0.2s;
        }
        .pagination a:hover { color: var(--primary); border-color: var(--primary); }
        .pagination .active { background: var(--primary); color: white; border-color: var(--primary); }

        /* ===== Modal ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .modal-overlay.open { opacity: 1; visibility: visible; }

        .modal {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.95) translateY(20px);
            transition: all 0.3s;
        }

        .modal-overlay.open .modal { transform: scale(1) translateY(0); }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title { font-size: 16px; font-weight: 700; }
        .modal-close { background: none; border: none; color: var(--text-secondary); font-size: 18px; cursor: pointer; transition: color 0.2s; }
        .modal-close:hover { color: var(--danger); }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i { font-size: 48px; color: var(--text-muted); margin-bottom: 16px; }
        .empty-state h3 { font-size: 16px; color: var(--text-primary); margin-bottom: 8px; }
        .empty-state p { font-size: 13px; }

        /* ===== Defect row (for create form) ===== */
        .defect-row {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            position: relative;
            transition: border-color 0.2s;
        }
        .defect-row:hover { border-color: var(--primary); }

        .defect-row-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .defect-row-num {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
        }

        .btn-remove-defect {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            color: var(--danger);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-remove-defect:hover { background: rgba(239,68,68,0.2); }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .form-row-3, .form-row-4 { grid-template-columns: 1fr; }
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in { animation: fadeInUp 0.4s ease both; }
        .fade-in-1 { animation-delay: 0.05s; }
        .fade-in-2 { animation-delay: 0.1s; }
        .fade-in-3 { animation-delay: 0.15s; }
        .fade-in-4 { animation-delay: 0.2s; }

        /* ===== Glow separator ===== */
        .glow-line {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            margin: 24px 0;
            opacity: 0.4;
        }

        /* ===== Chart card ===== */
        .chart-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-layout">

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
            <div class="brand-text">
                <div class="brand-title">RMRS</div>
                <div class="brand-sub">Reject Reporting System</div>
            </div>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->role_display }} · {{ auth()->user()->department_display }}</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            @if(auth()->user()->isOperator())
                <div class="nav-section">Utama</div>
                <a href="{{ route('operator.reports.create') }}" class="nav-item {{ request()->routeIs('operator.reports.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle"></i> Input Laporan Baru
                </a>
                <a href="{{ route('operator.reports.index') }}" class="nav-item {{ request()->routeIs('operator.reports.index') ? 'active' : '' }}">
                    <i class="fas fa-list-alt"></i> Riwayat Laporan
                </a>
            @elseif(auth()->user()->isLineLeader())
                <div class="nav-section">Dashboard</div>
                <a href="{{ route('leader.dashboard') }}" class="nav-item {{ request()->routeIs('leader.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                    @php $pending = \App\Models\RejectReport::where('status','pending')->count(); @endphp
                    @if($pending > 0)
                        <span class="nav-badge">{{ $pending }}</span>
                    @endif
                </a>
                <div class="nav-section">Laporan</div>
                <a href="{{ route('leader.reports') }}" class="nav-item {{ request()->routeIs('leader.reports') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i> Semua Laporan
                </a>
                <div class="nav-section">Export</div>
                <a href="{{ route('leader.export') }}" class="nav-item">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            @elseif(auth()->user()->isSupervisor())
                <div class="nav-section">Dashboard</div>
                <a href="{{ route('supervisor.dashboard') }}" class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <div class="nav-section">Laporan</div>
                <a href="{{ route('supervisor.reports') }}" class="nav-item {{ request()->routeIs('supervisor.reports') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i> Semua Laporan
                </a>
                <div class="nav-section">Export</div>
                <a href="{{ route('supervisor.export') }}" class="nav-item">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            @elseif(auth()->user()->isAdmin())
                <div class="nav-section">Manajemen</div>
                <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i> Manajemen User
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-content">
        <header class="topbar">
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-breadcrumb">RMRS / <span>@yield('page-title', 'Dashboard')</span></div>
            </div>
            <div class="topbar-right">
                <div class="topbar-time" id="topbarClock">{{ now()->format('H:i:s') }}</div>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success fade-in"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error fade-in"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error fade-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
// Live clock
(function() {
    function tick() {
        const el = document.getElementById('topbarClock');
        if (!el) return;
        const now = new Date();
        el.textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
    }
    tick();
    setInterval(tick, 1000);
})();

// Alert auto-hide
document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    }, 5000);
});
</script>
@stack('scripts')
</body>
</html>
