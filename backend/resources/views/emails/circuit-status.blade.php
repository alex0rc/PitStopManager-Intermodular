@extends('emails.layout')

@section('title', $title)
@section('heading', $title)

@section('content')
    <p>Hola {{ explode(' ', $user->name)[0] }},</p>

    @if ($status === 'approved')
        <p>Tu circuito <strong>{{ $circuit->name }}</strong> ha sido <strong>aprobado</strong> y ya está visible en el catálogo público.</p>
    @elseif ($status === 'rejected')
        <p>Tu circuito <strong>{{ $circuit->name }}</strong> no ha sido aprobado. Puedes editarlo y volver a enviarlo para revisión.</p>
    @else
        <p>El estado de tu circuito <strong>{{ $circuit->name }}</strong> es ahora <strong>{{ ucfirst($status) }}</strong>.</p>
    @endif

    <div class="info-card">
        <div class="info-row"><span class="label">Circuito</span><span class="value">{{ $circuit->name }}</span></div>
        <div class="info-row"><span class="label">Ubicación</span><span class="value">{{ $circuit->location }}</span></div>
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value">
                <span class="pill {{ $status === 'approved' ? 'pill-success' : ($status === 'rejected' ? 'pill-danger' : 'pill-warning') }}">
                    {{ ucfirst($status) }}
                </span>
            </span>
        </div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/circuits" class="cta">Ver mis circuitos</a>
    </p>
@endsection
