@extends('emails.layout')

@section('title', 'Recordatorio de suscripción')
@section('heading', 'Hola, ' . explode(' ', $user->name)[0])

@section('content')
    @if($daysRemaining <= 1)
        <p>Tu suscripción al plan <strong>{{ $plan->name }}</strong> <strong>caduca mañana</strong> ({{ optional($subscription->ends_at)->format('d/m/Y') }}).</p>
    @else
        <p>Tu suscripción al plan <strong>{{ $plan->name }}</strong> caduca en <strong>7 días</strong> ({{ optional($subscription->ends_at)->format('d/m/Y') }}).</p>
    @endif

    <p>Si no renuevas a tiempo, perderás el acceso como organizador y no podrás crear ni publicar nuevos campeonatos hasta que actives un plan.</p>

    <div class="info-card">
        <div class="info-row"><span class="label">Plan</span><span class="value">{{ $plan->name }}</span></div>
        <div class="info-row"><span class="label">Campeonatos incluidos</span><span class="value">{{ $plan->max_championships }}</span></div>
        <div class="info-row"><span class="label">Válida hasta</span><span class="value">{{ optional($subscription->ends_at)->format('d/m/Y') }}</span></div>
        <div class="info-row"><span class="label">Días restantes</span><span class="value">{{ $daysRemaining }}</span></div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/subscription" class="cta">Renovar mi suscripción</a>
    </p>
@endsection
