@extends('emails.layout')

@section('title', 'Bienvenido a PitStop Manager')
@section('heading', '¡Bienvenido, ' . explode(' ', $user->name)[0] . '!')

@section('content')
    @if ($user->role === 'organizer')
        <p>Tu cuenta de <strong>Organizador</strong> está lista. Ya puedes crear campeonatos, gestionar inscripciones y publicar resultados.</p>
    @else
        <p>Tu cuenta de <strong>Piloto</strong> está lista. Ya puedes inscribirte en campeonatos, consultar clasificaciones y revisar tus resultados.</p>
    @endif

    <div class="info-card">
        <div class="info-row"><span class="label">Email</span><span class="value">{{ $user->email }}</span></div>
        <div class="info-row"><span class="label">Rol</span><span class="value"><span class="pill pill-success">{{ ucfirst($user->role) }}</span></span></div>
    </div>

    @if ($user->role === 'pilot')
        <p style="margin-top:14px">¿Quieres crear tus propios campeonatos? Conviértete en <strong>Organizador</strong> activando una suscripción.</p>
    @endif

    <p style="margin-top:18px">
        @php
            $base = rtrim(config('app.frontend_url'), '/');
            $ctaUrl = match ($user->role) {
                'organizer' => "{$base}/organizer/championships",
                default => "{$base}/championships",
            };
            $ctaLabel = $user->role === 'organizer' ? 'Ir al panel de organizador' : 'Explorar campeonatos';
        @endphp
        <a href="{{ $ctaUrl }}" class="cta">{{ $ctaLabel }}</a>
    </p>

    <p style="color:#6b7280;font-size:13px;margin-top:18px">
        Si no creaste esta cuenta, ignora este mensaje.
    </p>
@endsection
