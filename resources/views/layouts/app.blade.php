<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}{{ (auth()->check() && auth()->user()->hasRole('admin')) ? ' · Admin' : '' }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- Theme (Modernize) -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root { --brand: #5d87ff; --brand-dark: #4570ea; }

        /* Portfolio disclaimer banner (storefront) */
        .portfolio-note {
            background: #fdecef;
            color: #d6304c;
            border-bottom: 1px solid #f5c2cb;
            text-align: center;
            padding: 9px 16px;
            font-weight: 600;
            font-size: .88rem;
            line-height: 1.4;
        }
        .portfolio-note i { margin-right: 6px; vertical-align: -1px; }

        /* ---------- Admin shell polish ---------- */
        body { background-color: #f5f7fb; font-family: 'Nunito', 'Plus Jakarta Sans', sans-serif; }

        .left-sidebar { box-shadow: 0 0 24px rgba(0,0,0,.04); }
        .brand-logo { padding: 20px 24px 8px; }
        .brand-logo .logo-title { font-weight: 800; font-size: 1.35rem; letter-spacing: -.5px; color: var(--brand); display: flex; align-items: center; gap: 10px; }
        .brand-logo .logo-title i { font-size: 1.6rem; }

        .sidebar-nav .nav-small-cap { text-transform: uppercase; font-size: .7rem; font-weight: 700; letter-spacing: .06em; color: #99a0ae; padding: 18px 24px 6px; }
        .sidebar-nav .sidebar-link { border-radius: 10px; font-weight: 600; color: #5a6a85; gap: 14px; padding: 11px 14px; transition: all .18s ease; }
        .sidebar-nav .sidebar-link:hover { background: rgba(93,135,255,.08); color: var(--brand); }
        .sidebar-nav .sidebar-link.active { background: var(--brand); color: #fff !important; box-shadow: 0 8px 16px rgba(93,135,255,.35); }
        .sidebar-nav .sidebar-link.active i { color: #fff !important; }

        .app-header { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.04); }
        .app-header .nav-link { color: #2a3547; }

        /* ---------- Deterministic admin layout (overrides theme's fragile attribute rules) ---------- */
        #main-wrapper { position: relative; min-height: 100vh; }
        #main-wrapper .left-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0;
            width: 270px;
            height: 100vh !important;
            z-index: 1030;
            background: #fff;
            border-right: 1px solid #eaeef4;
            transition: left .25s ease;
        }
        #main-wrapper .left-sidebar .brand-logo { min-height: auto; padding: 20px 20px 6px; }
        #main-wrapper .left-sidebar .scroll-sidebar {
            height: calc(100vh - 82px) !important;
            padding: 0 14px;
            overflow-y: auto;
        }
        #main-wrapper .body-wrapper {
            margin-left: 270px !important;
            min-height: 100vh;
            background: #f5f7fb;
        }
        #main-wrapper .app-header {
            position: sticky !important;
            top: 0 !important;
            width: 100% !important;
            z-index: 1020;
            padding: 6px 12px;
            border-bottom: 1px solid #eaeef4;
        }
        #main-wrapper .body-wrapper > .container-fluid {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 26px !important;
        }
        @media (max-width: 1199.98px) {
            #main-wrapper .left-sidebar { left: -290px; }
            #main-wrapper.show-sidebar .left-sidebar { left: 0; box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.2); }
            #main-wrapper .body-wrapper { margin-left: 0 !important; }
        }

        .page-title { font-weight: 800; letter-spacing: -.4px; color: #2a3547; }
        .page-subtitle { color: #7c8fac; margin-bottom: 0; }

        /* ---------- Stat cards ---------- */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(45,55,71,.06); }
        .stat-card .stat-icon { width: 56px; height: 56px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 800; color: #2a3547; line-height: 1; }
        .stat-card .stat-label { color: #7c8fac; font-weight: 600; font-size: .85rem; }
        .soft-primary { background: rgba(93,135,255,.12); color: #5d87ff; }
        .soft-success { background: rgba(19,222,185,.15); color: #13deb9; }
        .soft-warning { background: rgba(255,174,31,.15); color: #ffae1f; }
        .soft-danger  { background: rgba(250,137,107,.15); color: #fa896b; }
        .soft-info    { background: rgba(83,158,243,.15); color: #539bf5; }

        /* ---------- Tables ---------- */
        .dash-table { margin: 0; }
        .dash-table thead th { text-transform: uppercase; font-size: .7rem; letter-spacing: .03em; color: #7c8fac; font-weight: 700; border-bottom: 1px solid #eaeef4; padding: 12px 10px; white-space: nowrap; }
        .dash-table tbody td { vertical-align: middle; padding: 13px 10px; border-bottom: 1px solid #f2f5f9; color: #2a3547; }
        .dash-table tbody tr:last-child td { border-bottom: none; }
        .dash-table tbody tr:hover { background: #fafbfe; }
        .table-thumb { width: 44px; height: 44px; border-radius: 10px; object-fit: cover; background: #eef1f6; }

        .badge-soft-success { background: rgba(19,222,185,.15); color: #04b98c; }
        .badge-soft-warning { background: rgba(255,174,31,.18); color: #b9821a; }
        .badge-soft-danger  { background: rgba(250,137,107,.18); color: #e0603b; }
        .badge-soft-muted   { background: #eef1f6; color: #7c8fac; }
        .badge-soft-info    { background: rgba(83,158,243,.15); color: #2f7fe0; }
        .badge-pill { border-radius: 30px; padding: .4em .8em; font-weight: 700; font-size: .72rem; }

        .btn-icon { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 9px; padding: 0; }

        /* Table action buttons — labeled, always-visible colored buttons */
        .btn-action {
            display: inline-flex; align-items: center; justify-content: center; gap: 5px;
            border: none; border-radius: 8px; padding: 5px 9px; cursor: pointer;
            font-size: .74rem; font-weight: 600; line-height: 1; text-decoration: none;
            white-space: nowrap;
            transition: transform .12s ease, filter .12s ease;
        }
        .btn-action i { color: inherit !important; font-size: 14px; }
        .btn-action:hover { transform: translateY(-1px); filter: brightness(.95); }
        .btn-action-view   { background: rgba(83,158,243,.14);  color: #2f7fe0 !important; }
        .btn-action-edit   { background: rgba(93,135,255,.16);  color: #4570ea !important; }
        .btn-action-delete { background: rgba(250,137,107,.18); color: #e0603b !important; }

        /* ---------- Forms ---------- */
        .form-card .form-label { font-weight: 600; color: #2a3547; }
        .form-control, .form-select { border-radius: 10px; padding: .6rem .9rem; border-color: #e5e9f2; }
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 .2rem rgba(93,135,255,.15); }
    </style>
</head>
<body>
@hasrole('admin')
    {{-- ============ ADMIN DASHBOARD SHELL ============ --}}
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
         data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

        @include('partials.sidebar')

        <div class="body-wrapper">
            @include('partials.topbar')

            <div class="portfolio-note">
                <i class="ti ti-alert-triangle"></i>
                Note: This is a portfolio e-commerce project — all products, categories, orders and transactions are fake, and payments are in sandbox mode.
            </div>

            <div class="container-fluid">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
@else
    {{-- ============ STOREFRONT / GUEST SHELL ============ --}}
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Sales</a></li>
                        @auth
                            @hasrole('customer')
                            <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Orders</a></li>
                            @endhasrole
                        @endauth
                        <li class="nav-item"><a class="nav-link" href="#">Contact us</a></li>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="portfolio-note">
            <i class="ti ti-alert-triangle"></i>
            Note: This is a portfolio e-commerce project — all products, categories, orders and transactions are fake, and payments are in sandbox mode.
        </div>

        <main class="py-4">
            <div class="container">
                @include('partials.flash')
            </div>
            @yield('content')
        </main>
    </div>
@endhasrole

    {{-- Bootstrap (dropdowns, alerts, collapse) is bundled via @vite(resources/js/app.js).
         The theme's app.min.js is intentionally NOT loaded: it rewrites data-sidebartype
         on load, which broke the sidebar/main layout. We control the shell with our own CSS/JS. --}}
    <script>
        // Mobile off-canvas sidebar toggle + backdrop.
        document.addEventListener('DOMContentLoaded', function () {
            var wrapper = document.getElementById('main-wrapper');
            if (!wrapper) return;
            function toggle(e) { if (e) e.preventDefault(); wrapper.classList.toggle('show-sidebar'); }
            document.querySelectorAll('.sidebartoggler, #headerCollapse, #sidebarCollapse').forEach(function (btn) {
                btn.addEventListener('click', toggle);
            });
            // Close the mobile sidebar when clicking the main content area.
            var body = wrapper.querySelector('.body-wrapper');
            if (body) body.addEventListener('click', function () {
                if (window.innerWidth < 1200 && wrapper.classList.contains('show-sidebar')) {
                    wrapper.classList.remove('show-sidebar');
                }
            });
        });
    </script>
</body>
</html>
