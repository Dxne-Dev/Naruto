<!doctype html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cockpit Arbitre Game Master') - Otaku Arena</title>

    <!-- Favicon Officiel Otaku Arena -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/otaku-arena-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/otaku-arena-favicon.png') }}">

    <!-- Library / Plugin Css Build -->
    <link rel="stylesheet" href="{{ asset('admin-assets/css/core/libs.min.css') }}">

    <!-- Hope Ui Design System Css -->
    <link rel="stylesheet" href="{{ asset('admin-assets/css/hope-ui.min.css?v=4.0.0') }}">

    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('admin-assets/css/custom.min.css?v=4.0.0') }}">

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --admin-font: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --admin-mono: 'JetBrains Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            --admin-dark: #0F172A;
            --admin-slate: #334155;
            --admin-muted: #64748B;
        }

        body {
            font-family: var(--admin-font) !important;
            color: var(--admin-dark) !important;
            background-color: #F8FAFC !important;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
        }

        /* En-tête / Header Banner */
        .iq-navbar-header {
            background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }
        .iq-navbar-header .text-white {
            color: #FFFFFF !important;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }
        .iq-navbar-header h1 {
            font-size: 1.85rem !important;
            font-weight: 800 !important;
            letter-spacing: -0.025em;
        }
        .iq-navbar-header p {
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 0.95rem;
            font-weight: 400;
        }

        /* Sidebar Ergonomics & Legibility */
        .sidebar {
            background-color: #FFFFFF !important;
            border-right: 1px solid #E2E8F0 !important;
            box-shadow: 2px 0 12px rgba(15, 23, 42, 0.03) !important;
        }
        .sidebar .navbar-brand .logo-title {
            color: #0F172A !important;
            font-weight: 800 !important;
            font-size: 1.25rem !important;
            letter-spacing: -0.02em;
        }
        .sidebar .static-item .default-icon {
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.1em !important;
            color: #94A3B8 !important;
        }
        .sidebar-list .nav-item .nav-link {
            color: #334155 !important;
            font-weight: 600 !important;
            font-size: 0.92rem !important;
            padding: 10px 16px !important;
            border-radius: 10px !important;
            margin: 2px 12px !important;
            transition: all 0.18s ease-in-out !important;
        }
        .sidebar-list .nav-item .nav-link:hover {
            color: #3B82F6 !important;
            background-color: #EFF6FF !important;
        }
        .sidebar-list .nav-item .nav-link.active {
            color: #FFFFFF !important;
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
        }
        .sidebar-list .nav-item .nav-link.active .icon svg {
            color: #FFFFFF !important;
            fill: #FFFFFF !important;
        }

        /* Cartes & Conteneurs */
        .card {
            border: 1px solid #E2E8F0 !important;
            border-radius: 14px !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04) !important;
            background-color: #FFFFFF !important;
        }
        .card-header {
            background-color: transparent !important;
            border-bottom: 1px solid #F1F5F9 !important;
            padding: 18px 24px !important;
        }
        .card-title {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #0F172A !important;
            letter-spacing: -0.015em;
        }

        /* KPI Cards Contrasting & Typography */
        .card-kpi-custom {
            border-radius: 14px !important;
            border: 1px solid #E2E8F0 !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-kpi-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06) !important;
        }
        .kpi-title {
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            color: #475569 !important;
        }
        .kpi-number {
            font-family: var(--admin-mono) !important;
            font-size: 2.2rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }
        .kpi-subtext {
            font-size: 0.85rem !important;
            color: #64748B !important;
            font-weight: 500 !important;
        }

        /* Tableaux Ergonomiques */
        .table {
            color: #1E293B !important;
            vertical-align: middle !important;
        }
        .table thead th {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            color: #475569 !important;
            background-color: #F8FAFC !important;
            border-bottom: 1px solid #E2E8F0 !important;
            padding: 14px 20px !important;
        }
        .table tbody td {
            font-size: 0.92rem !important;
            color: #1E293B !important;
            padding: 16px 20px !important;
            border-bottom: 1px solid #F1F5F9 !important;
        }
        .table tbody tr:hover td {
            background-color: #F8FAFC !important;
        }

        /* Textes & Labels Utilitaires */
        .text-muted, small.text-muted {
            color: #64748B !important;
            font-weight: 450 !important;
        }
        .fw-bold, .font-weight-bold {
            font-weight: 700 !important;
            color: #0F172A !important;
        }
        .font-mono {
            font-family: var(--admin-mono) !important;
        }

        /* Badges Ultra-Lisibles */
        .badge {
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 5px 10px !important;
            border-radius: 6px !important;
            letter-spacing: 0.02em;
        }

        /* Boutons & Contrôles */
        .btn {
            font-weight: 600 !important;
            font-size: 0.88rem !important;
            border-radius: 8px !important;
            letter-spacing: -0.01em;
            padding: 8px 16px;
        }
        .btn-sm {
            font-size: 0.8rem !important;
            padding: 6px 12px !important;
        }
        .form-control, .form-select {
            font-size: 0.92rem !important;
            color: #0F172A !important;
            border-color: #CBD5E1 !important;
            border-radius: 8px !important;
            padding: 9px 13px !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        .form-label {
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            color: #1E293B !important;
            margin-bottom: 6px !important;
        }
    </style>

    @stack('styles')
</head>
<body class=" ">
    <!-- Loader Start -->
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body"></div>
        </div>
    </div>
    <!-- Loader End -->

    <!-- Sidebar Start -->
    @include('layouts.admin.partials.sidebar')
    <!-- Sidebar End -->

    <main class="main-content">
        <div class="position-relative iq-banner">
            <!-- Navbar Start -->
            @include('layouts.admin.partials.navbar')
            <!-- Navbar End -->

            <!-- Header Banner -->
            <div class="iq-navbar-header" style="height: 180px;">
                <div class="container-fluid iq-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="flex-wrap d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 class="text-white">@yield('page_title', 'Tableau de Bord Game Master')</h1>
                                    <p class="text-white mb-0">@yield('page_subtitle', 'Supervision des tournois, arbitrage et gestion des combattants.')</p>
                                </div>
                                <div>
                                    @yield('page_actions')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="iq-header-img">
                    <img src="{{ asset('admin-assets/images/dashboard/top-header.png') }}" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="conatiner-fluid content-inner mt-n5 py-0">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-4 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Library Bundle Script -->
    <script src="{{ asset('admin-assets/js/core/libs.min.js') }}"></script>

    <!-- External Library Bundle Script -->
    <script src="{{ asset('admin-assets/js/core/external.min.js') }}"></script>

    <!-- App Script -->
    <script src="{{ asset('admin-assets/js/hope-ui.js') }}" defer></script>

    @stack('scripts')
</body>
</html>
