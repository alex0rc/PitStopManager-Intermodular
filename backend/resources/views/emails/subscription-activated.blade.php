@extends('emails.layout')

@section('title', 'Tu suscripción está activa')
@section('heading', '¡Bienvenido a la parrilla, ' . explode(' ', $user->name)[0] . '!')

@section('content')
    <p>Tu pago ha sido procesado y tu suscripción <strong>{{ $plan->name }}</strong> está activa.</p>
    <p>Ya puedes acceder a tu panel como <strong>organizador</strong> y empezar a crear campeonatos.</p>

    <div class="info-card">
        <div class="info-row"><span class="label">Plan</span><span class="value">{{ $plan->name }}</span></div>
        <div class="info-row"><span class="label">Duración</span><span class="value">{{ $plan->duration_days }} días</span></div>
        <div class="info-row"><span class="label">Campeonatos máx.</span><span class="value">{{ $plan->max_championships }}</span></div>
        <div class="info-row"><span class="label">Activa hasta</span><span class="value">{{ optional($subscription->ends_at)->format('d/m/Y') }}</span></div>
        <div class="info-row"><span class="label">Estado</span><span class="value"><span class="pill pill-success">Activa</span></span></div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/championships" class="cta">Crear mi primer campeonato</a>
    </p>

    <p style="color:#6b7280;font-size:13px;margin-top:18px">
        ¿Quieres ver tu suscripción y descargar el comprobante? Accede a
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/subscription">tu panel de suscripción</a>.
    </p>
@endsection
