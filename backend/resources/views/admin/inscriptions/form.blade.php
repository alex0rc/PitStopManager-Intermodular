@extends('admin.layouts.app')
@php use App\Support\AdminLabels; @endphp
@section('title', 'Editar inscripción')
@section('page-title', 'Editar inscripción')
@section('page-subtitle', $inscription->user?->name.' — '.$championship->name)

@section('content')
@include('admin.partials.championship-nav', ['championship' => $championship, 'active' => 'inscriptions'])

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Datos de la inscripción</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.championships.inscriptions.update', [$championship, $inscription]) }}">
                    @csrf @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Piloto</label>
                            <p class="fw-semibold mb-0">{{ $inscription->user?->name }}</p>
                            <small class="text-muted">{{ $inscription->user?->email }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nº de dorsal</label>
                            <input type="number" name="car_number" class="form-control" min="1"
                                   value="{{ old('car_number', $inscription->car_number) }}" placeholder="Opcional">
                        </div>
                    </div>

                    @if($championship->usesOwnKarts())
                        <div class="mb-3">
                            <label class="form-label">Kart del piloto</label>
                            <input type="text" name="kart_info" class="form-control" maxlength="500"
                                   value="{{ old('kart_info', $inscription->kart_info) }}"
                                   placeholder="Chasis y motor">
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Estado de la inscripción</label>
                        <select name="status" class="form-select">
                            @foreach (['pending','confirmed','rejected','withdrawn'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $inscription->status) === $s)>
                                    {{ AdminLabels::inscriptionStatus($s) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Al confirmar o rechazar se envía un email al piloto (si el correo está configurado).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Carreras en las que participa</label>
                        <p class="text-muted small">Marca las carreras del calendario a las que está inscrito este piloto.</p>

                        @if($championship->races->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Este campeonato no tiene carreras.
                                <a href="{{ route('admin.championships.races.create', $championship) }}">Crear carreras</a>
                            </div>
                        @else
                            <div class="inscription-race-grid">
                                @foreach($championship->races as $race)
                                    @php
                                        $checked = in_array($race->id, old('race_ids', $inscription->races->pluck('id')->all()));
                                        $selectable = in_array($race->status, ['scheduled', 'in_progress']);
                                    @endphp
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="race_ids[]"
                                               value="{{ $race->id }}" id="race_{{ $race->id }}"
                                               @checked($checked) @disabled(!$selectable && !$checked)>
                                        <label class="form-check-label" for="race_{{ $race->id }}">
                                            <strong>{{ $race->name }}</strong>
                                            <span class="d-block small text-muted">
                                                {{ $race->scheduled_at?->format('d/m/Y H:i') }}
                                                · {{ AdminLabels::raceStatus($race->status) }}
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text mt-2">
                                Solo se pueden añadir carreras <strong>programadas</strong> o <strong>en curso</strong>.
                                Las ya seleccionadas en carreras finalizadas se mantienen marcadas.
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Guardar inscripción
                        </button>
                        <a href="{{ route('admin.championships.inscriptions.index', $championship) }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-admin">
            <div class="card-header bg-white fw-semibold">Información</div>
            <div class="card-body small">
                <p><strong>Solicitud:</strong> {{ $inscription->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Carreras actuales:</strong> {{ $inscription->races->count() }}</p>
                <hr>
                <p class="text-muted mb-0">
                    Los recordatorios por email y los resultados solo aplican a las carreras marcadas aquí.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
