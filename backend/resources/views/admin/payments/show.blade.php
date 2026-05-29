@extends('admin.layouts.app')
@section('title', 'Pago')
@section('page-title', 'Pago #'.$payment->id)

@section('content')
<div class="card card-admin col-lg-6">
    <div class="card-body">
        <p><strong>Usuario:</strong> {{ $payment->user?->name }}</p>
        <p><strong>Plan:</strong> {{ $payment->subscription?->plan?->name }}</p>
        <p><strong>Importe:</strong> {{ number_format($payment->amount, 2) }} {{ $payment->currency }}</p>
        <p><strong>Estado:</strong> <span class="badge-status badge-{{ $payment->status }}">{{ $payment->status }}</span></p>
        <p><strong>Pagado:</strong> {{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}</p>
        @if($payment->status === 'succeeded')
            <a href="{{ url('/api/my/payments/'.$payment->id.'/pdf') }}" class="btn btn-outline-primary btn-sm" target="_blank">Descargar PDF (API)</a>
        @endif
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-primary">Editar</a>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Volver</a>
</div>
@endsection
