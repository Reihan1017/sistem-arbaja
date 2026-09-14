<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — TB. AR Baja Steelindo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand: #1c3faa;
            --brand-dark: #142d7a;
            --brand-light: #eef2fc;
            --sidebar-bg: #0f1b3d;
            --sidebar-bg-2: #142958;
            --text-muted-2: #8a93a6;
            --bg-page: #f4f6fb;
            --radius: 14px;
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        body { background-color: var(--bg-page); color: #2b2f3a; }

        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 250px;
            background: linear-gradient(180deg, var(--sidebar-bg), var(--sidebar-bg-2));
            color: #fff; z-index: 1030; display: flex; flex-direction: column;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 22px 20px; border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand .logo-box {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #3d63e0, #1c3faa);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 15px; flex-shrink: 0;
        }
        .sidebar-brand .brand-text { font-weight: 700; font-size: 14.5px; line-height: 1.25; }
        .sidebar-brand .brand-text small { display: block; font-size: 10.5px; color: var(--text-muted-2); font-weight: 400; }

        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .sidebar-nav .nav-section-title {
            font-size: 10.5px; text-transform: uppercase; letter-spacing: .08em;
            color: var(--text-muted-2); padding: 14px 12px 6px;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; margin-bottom: 3px; border-radius: 10px;
            color: #c7cde3; text-decoration: none; font-size: 13.5px; font-weight: 500;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar-nav a i { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-nav a:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar-nav a.active { background: var(--brand); color: #fff; box-shadow: 0 4px 12px rgba(28,63,170,.4); }

        .sidebar-user {
            padding: 14px 16px; border-top: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-user .avatar {
            width: 36px; height: 36px; border-radius: 50%; background: var(--brand);
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;
        }
        .sidebar-user .info { flex: 1; min-width: 0; }
        .sidebar-user .info .name { font-size: 12.5px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user .info .role { font-size: 10.5px; color: var(--text-muted-2); }
        .sidebar-user form button {
            background: none; border: none; color: var(--text-muted-2); font-size: 16px; padding: 4px;
        }
        .sidebar-user form button:hover { color: #fff; }

        /* ===== Topbar & content ===== */
        .main-wrapper { margin-left: 250px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: #fff; padding: 14px 28px; display: flex; align-items: center;
            justify-content: space-between; border-bottom: 1px solid #e9ecf3;
            position: sticky; top: 0; z-index: 1020;
        }
        .topbar .page-title { font-size: 18px; font-weight: 700; margin: 0; }
        .topbar .page-subtitle { font-size: 12px; color: #8a93a6; margin: 0; }
        .sidebar-toggle { display: none; background: none; border: none; font-size: 22px; color: #2b2f3a; }

        .content-area { padding: 26px 28px; flex: 1; }

        /* ===== Global component polish (berlaku di semua halaman) ===== */
        .card {
            border: none; border-radius: var(--radius);
            box-shadow: 0 2px 14px rgba(20,30,60,.06);
        }
        .btn { border-radius: 10px; font-weight: 600; font-size: 13.5px; }
        .btn-primary { background: var(--brand); border-color: var(--brand); }
        .btn-primary:hover { background: var(--brand-dark); border-color: var(--brand-dark); }
        .btn-sm { border-radius: 8px; }
        .badge { border-radius: 20px; padding: 5px 11px; font-weight: 600; font-size: 11px; }
        .form-control, .form-select {
            border-radius: 10px; border-color: #e0e4ee; font-size: 13.5px; padding: 9px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-light);
        }
        .form-label { font-size: 12.5px; font-weight: 600; color: #4a5169; margin-bottom: 5px; }
        .table { font-size: 13.5px; }
        .table thead th {
            font-size: 11px; text-transform: uppercase; letter-spacing: .04em;
            color: #8a93a6; font-weight: 700; border-bottom-width: 1px; background: transparent !important;
        }
        .table td, .table th { padding: 12px 14px; vertical-align: middle; }
        .table tbody tr:hover { background: #f8f9fd; }
        .nav-tabs { border-bottom: 1px solid #e9ecf3; }
        .nav-tabs .nav-link {
            border: none; color: #8a93a6; font-weight: 600; font-size: 13.5px;
            padding: 10px 16px; border-radius: 0;
        }
        .nav-tabs .nav-link.active { color: var(--brand); border-bottom: 2px solid var(--brand); background: transparent; }
        .alert { border-radius: 12px; border: none; }
        .page-link { border-radius: 8px; margin: 0 2px; border: none; color: #4a5169; font-weight: 600; font-size: 13px; }
        .page-item.active .page-link { background: var(--brand); }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .sidebar-toggle { display: inline-block; }
        }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-box">AR</div>
        <div class="brand-text">
            TB. AR Baja Steelindo
            <small>Sistem Dokumen & Invoice</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu Utama</div>
        <a href="{{ route('documents.index', ['type' => 'surat_keluar']) }}"
           class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">
            <i class="bi bi-envelope-paper"></i> Surat Menyurat
        </a>
        <a href="{{ route('invoices.index') }}"
           class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="bi bi-receipt-cutoff"></i> Invoice & Pengiriman
        </a>

        @auth
            @if(auth()->user()->isAdmin())
                <div class="nav-section-title">Administrasi</div>
                <a href="{{ route('users.index') }}"
                   class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Kelola User
                </a>
            @endif
        @endauth
    </nav>

    @auth
        <div class="sidebar-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="info">
                <div class="name">{{ auth()->user()->name }}</div>
                <div class="role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    @endauth
</aside>

<div class="main-wrapper">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" id="sidebar-toggle"><i class="bi bi-list"></i></button>
            <div>
                <p class="page-title">@yield('title', 'Dashboard')</p>
                <p class="page-subtitle mb-0">@yield('subtitle', 'TB. AR Baja Steelindo')</p>
            </div>
        </div>
    </div>

    <div class="content-area">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.CSRF_TOKEN = "{{ csrf_token() }}";

    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 2200, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), timer: 2500, showConfirmButton: false });
    @endif
</script>
@stack('scripts')
</body>
</html>
