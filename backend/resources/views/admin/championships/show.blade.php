@extends('admin.layouts.app')
@section('title', $championship->name)
@section('page-title', $championship->name)
@section('page-subtitle', $championship->category?->name.' · '.$championship->season_year)
@section('page-actions')
    <a href="{{ route('admin.championships.edit', $championship) }}" class="btn btn-outline-secondary">Editar</a>
    <a href="{{ route('admin.championships.races.index', $championship) }}" class="btn btn-primary">Carreras</a>
    <a href="{{ route('admin.championships.inscriptions.index', $championship) }}" class="btn btn-outline-primary">Inscripciones ({{ $championship->inscriptions_count }})</a>
@endsection

@section('content')
@include('admin.partials.championship-nav', ['championship' => $championship, 'active' => 'show'])

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card card-admin">
            @if($championship->image)
                <img src="{{ asset('storage/'.$championship->image) }}" class="card-img-top" alt="">
            @endif
            <div class="card-body">
                <p><strong>Organizador:</strong> {{ $championship->user?->name }}</p>
                <p><strong>Estado:</strong> <span class="badge-status badge-{{ $championship->status }}">{{ \App\Support\AdminLabels::championshipStatus($championship->status) }}</span></p>
                <p><strong>Karts:</strong> {{ \App\Support\AdminLabels::kartModality($championship->kart_modality ?? 'rental') }}
                    @if($championship->engine_class) <span class="text-muted">· {{ $championship->engine_class }}</span> @endif
                </p>
                <p><strong>Fechas:</strong> {{ $championship->start_date?->format('d/m/Y') ?? '—' }} — {{ $championship->end_date?->format('d/m/Y') ?? '—' }}</p>
                @if($championship->description)
                    <p class="text-muted">{{ $championship->description }}</p>
                @endif

                <form method="POST" action="{{ route('admin.championships.status', $championship) }}" class="d-flex gap-2 mt-3">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm">
                        @foreach (['draft','published','in_progress','finished','cancelled'] as $s)
                            <option value="{{ $s }}" @selected($championship->status === $s)>{{ \App\Support\AdminLabels::championshipStatus($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary">Cambiar estado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-admin mb-4">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-flag me-1"></i> Carreras ({{ $championship->races->count() }})</span>
                <div>
                    <a href="{{ route('admin.championships.inscriptions.index', $championship) }}" class="btn btn-sm btn-outline-primary me-1">Inscripciones</a>
                    <a href="{{ route('admin.championships.races.index', $championship) }}" class="btn btn-sm btn-outline-secondary me-1">Gestionar</a>
                    <a href="{{ route('admin.championships.races.create', $championship) }}" class="btn btn-sm btn-primary">+ Carrera</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead><tr><th>Nombre</th><th>Circuito</th><th>Fecha</th><th>Inscritos</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($championship->races as $race)
                            <tr>
                                <td class="fw-semibold">{{ $race->name }}</td>
                                <td>{{ $race->circuit?->name }}</td>
                                <td>{{ $race->scheduled_at?->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $race->inscriptions_count }}</span></td>
                                <td><span class="badge-status badge-{{ $race->status }}">{{ \App\Support\AdminLabels::raceStatus($race->status) }}</span></td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.races.results.index', $race) }}" class="btn btn-sm btn-outline-primary">Resultados</a>
                                    <a href="{{ route('admin.championships.races.edit', [$championship, $race]) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted text-center py-3">Sin carreras — <a href="{{ route('admin.championships.races.create', $championship) }}">crear la primera</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Clasificación (puntos)</div>
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead><tr><th>#</th><th>Piloto</th><th>Puntos</th></tr></thead>
                    <tbody>
                        @forelse ($standings as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->name }}</td>
                                <td><strong>{{ $row->total_points }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center">Sin resultados aún</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.championships.destroy', $championship) }}" onsubmit="return confirm('¿Eliminar este campeonato?')">
    @csrf @method('DELETE')
    <button class="btn btn-outline-danger btn-sm">Eliminar campeonato</button>
</form>
<a href="{{ route('admin.championships.index') }}" class="btn btn-outline-secondary btn-sm ms-2">Volver al listado</a>
@endsection
