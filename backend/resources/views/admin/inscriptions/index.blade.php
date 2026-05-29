@extends('admin.layouts.app')
@php use App\Support\AdminLabels; @endphp
@section('title', 'Inscripciones — '.$championship->name)
@section('page-title', 'Inscripciones')
@section('page-subtitle', $championship->name)
@section('page-actions')
    @if($stats['pending'] > 0)
        <form method="POST" action="{{ route('admin.championships.inscriptions.approve-pending', $championship) }}" class="d-inline"
              onsubmit="return confirm('¿Confirmar las {{ $stats['pending'] }} inscripción(es) pendientes?')">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-all"></i> Aprobar pendientes ({{ $stats['pending'] }})
            </button>
        </form>
    @endif
    <a href="{{ route('admin.championships.races.index', $championship) }}" class="btn btn-outline-secondary">
        <i class="bi bi-flag"></i> Carreras
    </a>
@endsection

@section('content')
@include('admin.partials.championship-nav', ['championship' => $championship, 'active' => 'inscriptions'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value">{{ $stats['total'] }}</div>
            <div class="label">Total</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value text-warning">{{ $stats['pending'] }}</div>
            <div class="label">Pendientes</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value text-success">{{ $stats['confirmed'] }}</div>
            <div class="label">Confirmadas</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value text-danger">{{ $stats['rejected'] }}</div>
            <div class="label">Rechazadas</div>
        </div>
    </div>
</div>

@if($races->isNotEmpty())
<div class="card card-admin mb-4">
    <div class="card-header bg-white fw-semibold small">Carreras del campeonato</div>
    <div class="card-body py-2">
        @foreach($races as $race)
            <span class="race-chip {{ $race->status === 'cancelled' ? 'race-chip-muted' : '' }}">
                {{ $race->name }}
                <span class="text-muted">· {{ AdminLabels::raceStatus($race->status) }}</span>
            </span>
        @endforeach
    </div>
</div>
@endif

<ul class="nav nav-pills filter-pills mb-3">
    @foreach (['all' => 'Todas', 'pending' => 'Pendientes', 'confirmed' => 'Confirmadas', 'rejected' => 'Rechazadas', 'withdrawn' => 'Retiradas'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ request('status', 'all') === $key ? 'active' : '' }}"
               href="{{ route('admin.championships.inscriptions.index', [$championship, 'status' => $key]) }}">
                {{ $label }}
                @if($key !== 'all' && isset($stats[$key]))
                    <span class="badge bg-secondary ms-1">{{ $stats[$key] }}</span>
                @endif
            </a>
        </li>
    @endforeach
</ul>

<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead>
                <tr>
                    <th>Piloto</th>
                    <th>Nº</th>
                    <th>Kart</th>
                    <th>Carreras elegidas</th>
                    <th>Estado</th>
                    <th>Solicitud</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inscriptions as $ins)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $ins->user?->name }}</div>
                            <small class="text-muted">{{ $ins->user?->email }}</small>
                        </td>
                        <td>
                            @if($ins->car_number)
                                <span class="badge bg-dark">#{{ $ins->car_number }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($ins->kart_info)
                                {{ $ins->kart_info }}
                            @elseif($championship->usesRentalKarts())
                                <span class="text-muted">Alquiler</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="max-width: 280px">
                            @if ($ins->races->isNotEmpty())
                                @foreach($ins->races as $r)
                                    <span class="race-chip">{{ $r->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">Sin carreras seleccionadas</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status badge-{{ $ins->status }}">
                                {{ AdminLabels::inscriptionStatus($ins->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $ins->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                @if($ins->status === 'pending')
                                    <form method="POST" action="{{ route('admin.championships.inscriptions.status', [$championship, $ins]) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-sm btn-success" title="Aprobar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.championships.inscriptions.status', [$championship, $ins]) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Rechazar"
                                                onclick="return confirm('¿Rechazar inscripción de {{ $ins->user?->name }}?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.championships.inscriptions.edit', [$championship, $ins]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar carreras y datos">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.championships.inscriptions.destroy', [$championship, $ins]) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar inscripción de {{ $ins->user?->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No hay inscripciones{{ request('status') && request('status') !== 'all' ? ' con este filtro' : '' }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
