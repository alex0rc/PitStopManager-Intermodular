@extends('emails.layout')

@section('title', 'Nueva inscripción pendiente')
@section('heading', 'Nueva inscripción')

@section('content')
    <p>Hola {{ explode(' ', $organizer->name ?? 'Organizador')[0] }},</p>
    <p><strong>{{ $pilot->name }}</strong> ha solicitado inscribirse en tu campeonato <strong>{{ $championship->name }}</strong>.</p>

    <div class="info-card">
        <div class="info-row"><span class="label">Piloto</span><span class="value">{{ $pilot->name }}</span></div>
        <div class="info-row"><span class="label">Email</span><span class="value">{{ $pilot->email }}</span></div>
        <div class="info-row"><span class="label">Campeonato</span><span class="value">{{ $championship->name }}</span></div>
        @if (!empty($inscription->car_number))
            <div class="info-row"><span class="label">Dorsal</span><span class="value">#{{ $inscription->car_number }}</span></div>
        @endif
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value"><span class="pill pill-warning">Pendiente de revisión</span></span>
        </div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/championships/{{ $championship->id }}" class="cta">Revisar inscripciones</a>
    </p>
@endsection
