@extends('admin.layouts.app')
@php use App\Support\AdminLabels; @endphp
@section('title', 'Carreras — '.$championship->name)
@section('page-title', 'Carreras del campeonato')
@section('page-subtitle', $championship->name)
@section('page-actions')
    <a href="{{ route('admin.championships.races.create', $championship) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva carrera
    </a>
@endsection

@section('content')
@include('admin.partials.championship-nav', ['championship' => $championship, 'active' => 'races'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value text-primary">{{ $stats['total'] }}</div>
            <div class="label">Carreras</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value">{{ $stats['scheduled'] }}</div>
            <div class="label">Programadas</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value">{{ $stats['in_progress'] }}</div>
            <div class="label">En curso</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value">{{ $stats['completed'] }}</div>
            <div class="label">Completadas</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="value text-success">{{ $stats['pilots'] }}</div>
            <div class="label">Pilotos confirmados</div>
        </div>
    </div>
</div>

<div class="card card-admin">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-flag me-1"></i> Calendario de carreras</span>
        <a href="{{ route('admin.championships.inscriptions.index', $championship) }}" class="btn btn-sm btn-outline-primary">
            Ver inscripciones
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Carrera</th>
                    <th>Circuito</th>
                    <th>Fecha</th>
                    <th>Vueltas</th>
                    <th>Inscritos</th>
                    <th>Resultados</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($races as $i => $race)
                    <tr>
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $race->name }}</td>
                        <td>
                            {{ $race->circuit?->name ?? '—' }}
                            @if ($race->circuit?->city)
                                <small class="d-block text-muted">{{ $race->circuit->city }}</small>
                            @endif
                        </td>
                        <td>{{ $race->scheduled_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $race->total_laps ?? '—' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people"></i> {{ $race->inscriptions_count }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-list-ol"></i> {{ $race->results_count }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status badge-{{ $race->status }}" title="{{ $race->status }}">
                                {{ AdminLabels::raceStatus($race->status) }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap action-btns">
                            <a href="{{ route('admin.races.results.index', $race) }}" class="btn btn-sm btn-primary" title="Resultados">
                                <i class="bi bi-trophy"></i>
                            </a>
                            <a href="{{ route('admin.championships.races.edit', [$championship, $race]) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.championships.races.destroy', [$championship, $race]) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la carrera «{{ $race->name }}»?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar" @disabled($race->results_count > 0)>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                            No hay carreras configuradas.
                            <div class="mt-2">
                                <a href="{{ route('admin.championships.races.create', $championship) }}" class="btn btn-sm btn-primary">Crear la primera carrera</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted small mb-0">
    <i class="bi bi-info-circle"></i>
    Los pilotos eligen en qué carreras participan al inscribirse. El contador «Inscritos» muestra pilotos con inscripción confirmada en esa carrera.
</p>
@endsection
