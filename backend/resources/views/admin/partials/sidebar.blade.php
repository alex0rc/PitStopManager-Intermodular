<aside class="admin-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="brand">
        @include('partials.brand-logo', ['alt' => '', 'width' => 34, 'height' => 34])
        <span class="brand-text">Pit<span>Stop</span> Admin</span>
    </a>

    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <span class="nav-section">Usuarios y planes</span>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Usuarios
        </a>
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Categorías
        </a>
        <a href="{{ route('admin.plans.index') }}" class="{{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
            <i class="bi bi-card-list"></i> Planes
        </a>
        <a href="{{ route('admin.subscriptions.index') }}" class="{{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Suscripciones
        </a>
        <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> Pagos
        </a>

        <span class="nav-section">Competición</span>
        <a href="{{ route('admin.championships.index') }}" class="{{ request()->routeIs('admin.championships.*') ? 'active' : '' }}">
            <i class="bi bi-trophy"></i> Campeonatos
        </a>
        <a href="{{ route('admin.circuits.index') }}" class="{{ request()->routeIs('admin.circuits.*') ? 'active' : '' }}">
            <i class="bi bi-pin-map"></i> Circuitos
        </a>
    </nav>

    <div class="admin-sidebar-footer">
        <div class="small text-white-50 mb-2">{{ auth()->user()->name }}</div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm w-100">
                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
            </button>
        </form>
        <a href="{{ route('admin.go-to-app') }}" class="btn btn-link btn-sm text-white-50 mt-2 p-0">
            <i class="bi bi-box-arrow-up-right"></i> Ir al sitio público
        </a>
    </div>
</aside>
