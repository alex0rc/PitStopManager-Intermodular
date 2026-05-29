@extends('emails.layout')

@section('title', 'Comprobante de pago')
@section('heading', 'Pago recibido')

@section('content')
    <p>Hola {{ explode(' ', $user->name)[0] }},</p>
    <p>Hemos registrado correctamente tu pago. Gracias por confiar en PitStop Manager.</p>

    <div class="info-card">
        @if ($plan)
            <div class="info-row"><span class="label">Plan</span><span class="value">{{ $plan->name }}</span></div>
        @endif
        <div class="info-row">
            <span class="label">Importe</span>
            <span class="value">{{ number_format((float) $payment->amount, 2, ',', '.') }} {{ strtoupper($payment->currency) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Fecha</span>
            <span class="value">{{ $payment->paid_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Referencia</span>
            <span class="value">#{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value"><span class="pill pill-success">Pagado</span></span>
        </div>
    </div>

    <p style="margin-top:18px">
        <a href="{{ rtrim(config('app.frontend_url'), '/') }}/organizer/subscription" class="cta">Ver mi suscripción</a>
    </p>
@endsection
