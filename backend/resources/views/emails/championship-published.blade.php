@extends('emails.layout')

@section('title', 'Campeonato publicado')
@section('heading', '¡Campeonato publicado!')

@section('content')
    <p>Hola {{ explode(' ', $organizer->name)[0] }},</p>
    <p>Tu campeonato <strong>{{ $championship->name }}</strong> ya está <strong>publicado</strong> y los pilotos pueden inscribirse.</p>

    <div class="info-card">
        <div class="info-row"><span class="label">Campeonato</span><span class="value">{{ $championship->name }}</span></div>
        <div class="info-row"><span class="label">Temporada</span><span class="value">{{ $championship->season_year }}</span></div>
        @if ($championship->start_date)
            <div class="info-row"><span class="label">Inicio</span><span class="value">{{ $championship->start_date->format('d/m/Y') }}</span></div>
        @endif
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value"><span class="pill pill-success">Publicado</span></span>
        </div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/championships/{{ $championship->id }}" class="cta">Gestionar campeonato</a>
    </p>
@endsection
