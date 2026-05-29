@extends('admin.layouts.app')
@section('title', 'Suscripción')
@section('page-title', 'Suscripción #'.$subscription->id)

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card card-admin"><div class="card-body">
            <p><strong>Usuario:</strong> {{ $subscription->user?->name }} ({{ $subscription->user?->email }})</p>
            <p><strong>Plan:</strong> {{ $subscription->plan?->name }}</p>
            <p><strong>Estado:</strong> <span class="badge-status badge-{{ $subscription->status }}">{{ $subscription->status }}</span></p>
            <p><strong>Periodo:</strong> {{ $subscription->starts_at?->format('d/m/Y') }} — {{ $subscription->ends_at?->format('d/m/Y') }}</p>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Pagos</div>
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead><tr><th>ID</th><th>Importe</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @forelse ($subscription->payments as $p)
                            <tr>
                                <td><a href="{{ route('admin.payments.show', $p) }}">#{{ $p->id }}</a></td>
                                <td>{{ number_format($p->amount, 2) }} €</td>
                                <td><span class="badge-status badge-{{ $p->status }}">{{ $p->status }}</span></td>
                                <td>{{ $p->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">Sin pagos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-primary">Editar</a>
    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">Volver</a>
</div>
@endsection
