@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @foreach ([
        ['Usuarios', $stats['users'], 'people', 'primary'],
        ['Organizadores', $stats['organizers'], 'person-badge', 'info'],
        ['Pilotos', $stats['pilots'], 'person', 'success'],
        ['Campeonatos', $stats['championships'], 'trophy', 'danger'],
        ['Circuitos', $stats['circuits'], 'pin-map', 'secondary'],
        ['Carreras', $stats['races'], 'flag', 'dark'],
        ['Suscripciones activas', $stats['subscriptions'], 'credit-card', 'warning'],
        ['Ingresos mes', number_format($stats['payments_month'], 2).' €', 'cash', 'success'],
    ] as [$label, $value, $icon, $color])
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">{{ $label }}</div>
                        <div class="value text-{{ $color }}">{{ $value }}</div>
                    </div>
                    <i class="bi bi-{{ $icon }} fs-4 text-{{ $color }} opacity-50"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Campeonatos recientes</div>
            <div class="table-responsive">
                <table class="table table-admin table-hover mb-0">
                    <thead><tr><th>Nombre</th><th>Organizador</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($recentChampionships as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td class="small text-muted">{{ $c->user?->name }}</td>
                                <td><span class="badge-status badge-{{ $c->status }}">{{ $c->status }}</span></td>
                                <td><a href="{{ route('admin.championships.show', $c) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-3">Sin campeonatos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Pagos recientes</div>
            <div class="table-responsive">
                <table class="table table-admin table-hover mb-0">
                    <thead><tr><th>Usuario</th><th>Importe</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($recentPayments as $p)
                            <tr>
                                <td class="small">{{ $p->user?->name }}</td>
                                <td>{{ number_format($p->amount, 2) }} €</td>
                                <td><span class="badge-status badge-{{ $p->status }}">{{ $p->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">Sin pagos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
