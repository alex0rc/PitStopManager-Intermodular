@extends('admin.layouts.app')
@php
    use App\Support\AdminLabels;
    $isEdit = $race->exists;
@endphp
@section('title', $isEdit ? 'Editar carrera' : 'Nueva carrera')
@section('page-title', $isEdit ? 'Editar carrera' : 'Nueva carrera')
@section('page-subtitle', $championship->name)

@section('content')
@include('admin.partials.championship-nav', ['championship' => $championship, 'active' => 'races'])

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Datos de la carrera</div>
            <div class="card-body">
                <form method="POST" action="{{ $isEdit ? route('admin.championships.races.update', [$championship, $race]) : route('admin.championships.races.store', $championship) }}">
                    @csrf @if($isEdit) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">Nombre de la carrera *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $race->name) }}" required placeholder="Ej. GP Ciudad — Ronda 1">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Circuito *</label>
                        <select name="circuit_id" class="form-select @error('circuit_id') is-invalid @enderror" required>
                            <option value="">— Seleccionar circuito —</option>
                            @foreach ($circuits as $c)
                                <option value="{{ $c->id }}" @selected(old('circuit_id', $race->circuit_id) == $c->id)>
                                    {{ $c->name }}@if($c->city) — {{ $c->city }}@endif
                                    @if($c->length_meters) ({{ round($c->length_meters / 1000, 2) }} km)@endif
                                </option>
                            @endforeach
                        </select>
                        @error('circuit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">El circuito determina la ubicación y el clima mostrado a los pilotos.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha y hora de la carrera *</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror"
                               value="{{ old('scheduled_at', $race->scheduled_at?->format('Y-m-d\TH:i')) }}" required>
                        @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vueltas totales</label>
                            <input type="number" name="total_laps" class="form-control" value="{{ old('total_laps', $race->total_laps) }}" min="1" placeholder="Opcional">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado *</label>
                            <select name="status" class="form-select" required>
                                @foreach (['scheduled','in_progress','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" @selected(old('status', $race->status) === $s)>
                                        {{ AdminLabels::raceStatus($s) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Solo las carreras <strong>Programada</strong> o <strong>En curso</strong> aparecen al inscribirse.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas internas</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Briefing, reglamento especial…">{{ old('notes', $race->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> {{ $isEdit ? 'Guardar cambios' : 'Crear carrera' }}
                        </button>
                        <a href="{{ route('admin.championships.races.index', $championship) }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if($isEdit)
            <div class="card card-admin mb-3">
                <div class="card-header bg-white fw-semibold">Resumen</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><strong>Pilotos inscritos en esta carrera:</strong> {{ $race->inscriptions_count ?? 0 }}</li>
                        <li class="mb-2"><strong>Resultados registrados:</strong> {{ $race->results_count ?? 0 }}</li>
                        <li>
                            <a href="{{ route('admin.races.results.index', $race) }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-trophy"></i> Gestionar resultados
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        @endif

        <div class="card card-admin border-0 bg-light">
            <div class="card-body small text-muted">
                <h6 class="fw-semibold text-dark"><i class="bi bi-lightbulb"></i> Consejos</h6>
                <ul class="mb-0 ps-3">
                    <li>Crea todas las carreras antes de abrir inscripciones.</li>
                    <li>Los pilotos amateur pueden inscribirse solo a algunas carreras.</li>
                    <li>Marca como <em>Completada</em> cuando publiques resultados.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
