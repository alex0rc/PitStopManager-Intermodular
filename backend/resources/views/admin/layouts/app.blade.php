<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — PitStop Manager</title>
    <meta name="theme-color" content="#e11d2e">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-body">
    @include('admin.partials.sidebar')
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" hidden aria-hidden="true"></div>

    <header class="admin-mobile-bar d-lg-none">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="adminSidebarToggle" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <span class="admin-mobile-title">@yield('page-title', 'Panel Admin')</span>
    </header>

    <main class="admin-main admin-content-area" id="admin-main">
        @include('admin.partials.content-loading')

        <div id="admin-page">
            @include('admin.partials.flash')

            <div class="admin-topbar">
                <div>
                    <h1>@yield('page-title', 'Panel Admin')</h1>
                    @hasSection('page-subtitle')
                        <p class="text-muted mb-0 small">@yield('page-subtitle')</p>
                    @endif
                </div>
                <div>@yield('page-actions')</div>
            </div>

            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/admin-loading.js') }}" defer></script>
    <script src="{{ asset('js/admin-mobile.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
