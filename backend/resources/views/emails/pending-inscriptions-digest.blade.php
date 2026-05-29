@extends('emails.layout')

@section('title', 'Inscripciones pendientes')
@section('heading', 'Inscripciones por revisar')

@section('content')
    <p>Hola {{ explode(' ', $organizer->name)[0] }},</p>
    <p>Tienes <strong>{{ $totalPending }}</strong> inscripción(es) pendientes en uno o más de tus campeonatos:</p>

    <div class="info-card">
        @foreach ($summary as $row)
            <div class="info-row">
                <span class="label">{{ $row['championship_name'] }}</span>
                <span class="value">{{ $row['pending_count'] }} pendiente(s)</span>
            </div>
        @endforeach
    </div>

    <p style="margin-top:14px;color:#6b7280;font-size:13px">
        Confirma o rechaza las solicitudes para que los pilotos puedan preparar la temporada.
    </p>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/championships" class="cta">Ir a mis campeonatos</a>
    </p>
@endsection
