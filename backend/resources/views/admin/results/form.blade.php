@extends('admin.layouts.app')
@php $isEdit = $result->exists; @endphp
@section('title', $isEdit ? 'Editar resultado' : 'Nuevo resultado')
@section('page-title', $isEdit ? 'Editar resultado' : 'Nuevo resultado')
@section('page-subtitle', $race->name)

@section('content')
<div class="row"><div class="col-lg-6">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.races.results.update', [$race, $result]) : route('admin.races.results.store', $race) }}">
            @csrf @if($isEdit) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Piloto *</label>
                <select name="user_id" class="form-select" required>
                    <option value="">— Seleccionar —</option>
                    @foreach ($pilots as $p)
                        <option value="{{ $p->id }}" @selected(old('user_id', $result->user_id) == $p->id)>{{ $p->name }} ({{ $p->email }})</option>
                    @endforeach
                </select>
                <div class="form-text">Solo pilotos con inscripción confirmada (en creación).</div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Posición</label>
                    <input type="number" name="position" class="form-control" value="{{ old('position', $result->position) }}" min="1">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Puntos</label>
                    <input type="number" name="points" class="form-control" value="{{ old('points', $result->points ?? 0) }}" min="0">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mejor vuelta</label>
                    <input type="text" name="best_lap_time" class="form-control" placeholder="1:23.456" value="{{ old('best_lap_time', $result->best_lap_time) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tiempo total</label>
                    <input type="text" name="total_time" class="form-control" value="{{ old('total_time', $result->total_time) }}">
                </div>
            </div>

            <div class="mb-3 d-flex gap-4">
                <div class="form-check">
                    <input type="checkbox" name="dnf" value="1" class="form-check-input" id="dnf" @checked(old('dnf', $result->dnf))>
                    <label class="form-check-label" for="dnf">DNF</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="dsq" value="1" class="form-check-input" id="dsq" @checked(old('dsq', $result->dsq))>
                    <label class="form-check-label" for="dsq">DSQ</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $result->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.races.results.index', $race) }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection
