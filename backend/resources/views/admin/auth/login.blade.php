<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — PitStop Manager</title>
    <meta name="theme-color" content="#e11d2e">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body>
    @include('admin.partials.loading-overlay')
    <div class="login-page">
        <div class="login-card">
            <div class="text-center mb-4">
                @include('partials.brand-logo', ['class' => 'brand-logo-lg mb-3', 'width' => 56, 'height' => 56])
                <h4 class="fw-bold mb-1">Pit<span class="text-danger">Stop</span> Admin</h4>
                <p class="text-muted small mb-0">Panel de administración</p>
            </div>

            @if (session('error'))
                <div class="alert alert-danger small">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success small">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>
            <p class="text-center text-muted small mt-3 mb-0">
                Demo: admin@pitstop.com / password
            </p>
        </div>
    </div>
    <script src="{{ asset('js/admin-loading.js') }}" defer></script>
</body>
</html>
