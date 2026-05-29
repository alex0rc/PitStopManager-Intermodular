@extends('emails.layout')

@section('title', 'Solicitud de inscripción recibida')
@section('heading', 'Solicitud recibida')

@section('content')
    <p>Hola {{ explode(' ', $user->name)[0] }},</p>
    <p>Hemos recibido tu solicitud de inscripción en <strong>{{ $championship->name }}</strong>. El organizador la revisará y te avisaremos cuando cambie el estado.</p>

    <div class="info-card">
        <div class="info-row"><span class="label">Campeonato</span><span class="value">{{ $championship->name }}</span></div>
        <div class="info-row"><span class="label">Temporada</span><span class="value">{{ $championship->season_year }}</span></div>
        @if (!empty($inscription->car_number))
            <div class="info-row"><span class="label">Dorsal solicitado</span><span class="value">#{{ $inscription->car_number }}</span></div>
        @endif
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value"><span class="pill pill-warning">Pendiente</span></span>
        </div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/pilot/inscriptions" class="cta">Ver mis inscripciones</a>
    </p>
@endsection
