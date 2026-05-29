@extends('emails.layout')

@section('title', 'Recordatorio de carrera')
@section('heading', '¡Carrera mañana!')

@section('content')
    <p>Hola {{ explode(' ', $user->name)[0] }},</p>

    <p>Te recordamos que <strong>mañana</strong> tienes carrera en el campeonato <strong>{{ $championship->name }}</strong>.</p>

    <div class="info-card">
        <div class="info-row"><span class="label">Carrera</span><span class="value">{{ $race->name }}</span></div>
        <div class="info-row"><span class="label">Fecha y hora</span><span class="value">{{ $race->scheduled_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span></div>
        <div class="info-row"><span class="label">Circuito</span><span class="value">{{ $circuit->name }}</span></div>
        @if ($circuit->city || $circuit->province)
            <div class="info-row">
                <span class="label">Ubicación</span>
                <span class="value">{{ trim(($circuit->city ?? '') . ($circuit->province ? ', ' . $circuit->province : '')) }}</span>
            </div>
        @endif
        @if ($race->total_laps)
            <div class="info-row"><span class="label">Vueltas</span><span class="value">{{ $race->total_laps }}</span></div>
        @endif
        @if ($inscription?->car_number)
            <div class="info-row"><span class="label">Tu dorsal</span><span class="value">#{{ $inscription->car_number }}</span></div>
        @endif
    </div>

    @if ($race->notes)
        <p><strong>Notas del organizador:</strong> {{ $race->notes }}</p>
    @endif

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/pilot/championships" class="cta">Ver mis campeonatos</a>
    </p>

    <p style="color:#6b7280;font-size:13px;margin-top:18px">
        Recibes este correo porque tienes una inscripción confirmada en este campeonato.
    </p>
@endsection
