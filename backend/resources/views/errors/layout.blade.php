<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') — PitStop Manager</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f6fa; font-family: Inter, system-ui, sans-serif; }
        .error-box { max-width: 480px; text-align: center; padding: 2.5rem; background: #fff; border-radius: 1rem; border: 1px solid #e5e7eb; box-shadow: 0 8px 30px rgba(15,23,42,.06); }
        .error-code { font-size: 4rem; font-weight: 800; color: #e11d2e; line-height: 1; }
        .error-icon { font-size: 2.5rem; color: #6b7280; margin: .5rem 0 1rem; display: block; }
    </style>
</head>
<body>
    <div class="error-box">
        <img src="{{ asset('logo.png') }}" alt="PitStop Manager" width="56" height="56" style="border-radius:12px;margin-bottom:1rem">
        <div class="error-code">@yield('code')</div>
        <i class="bi @yield('icon') error-icon"></i>
        <h1 class="h4 fw-bold">@yield('heading')</h1>
        <p class="text-muted mb-4">@yield('message')</p>
        @yield('actions')
    </div>
</body>
</html>
