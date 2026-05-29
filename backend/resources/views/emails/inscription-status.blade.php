@extends('emails.layout')

@section('title', $statusTitle)
@section('heading', $statusTitle)

@section('content')
    <p>Hola {{ explode(' ', $user->name)[0] }},</p>

    @if ($status === 'confirmed')
        <p>Tu inscripción en el campeonato <strong>{{ $championship->name }}</strong> ha sido <strong>confirmada</strong>. ¡Nos vemos en la pista!</p>
    @elseif ($status === 'rejected')
        <p>Lo sentimos, tu inscripción en el campeonato <strong>{{ $championship->name }}</strong> ha sido <strong>rechazada</strong>. Si tienes dudas, contacta con el organizador.</p>
    @else
        <p>El estado de tu inscripción en <strong>{{ $championship->name }}</strong> ha cambiado a <strong>{{ ucfirst($status) }}</strong>.</p>
    @endif

    <div class="info-card">
        <div class="info-row"><span class="label">Campeonato</span><span class="value">{{ $championship->name }}</span></div>
        <div class="info-row"><span class="label">Temporada</span><span class="value">{{ $championship->season_year }}</span></div>
        @if (!empty($inscription->car_number))
            <div class="info-row"><span class="label">Dorsal</span><span class="value">#{{ $inscription->car_number }}</span></div>
        @endif
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value">
                <span class="pill {{ $status === 'confirmed' ? 'pill-success' : ($status === 'rejected' ? 'pill-danger' : 'pill-warning') }}">
                    {{ ucfirst($status) }}
                </span>
            </span>
        </div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/pilot/inscriptions" class="cta">Ver mis inscripciones</a>
    </p>
@endsection
