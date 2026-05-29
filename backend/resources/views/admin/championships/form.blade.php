@extends('admin.layouts.app')
@php $isEdit = $championship->exists; @endphp
@section('title', $isEdit ? 'Editar campeonato' : 'Nuevo campeonato')
@section('page-title', $isEdit ? 'Editar campeonato' : 'Nuevo campeonato')

@push('styles')
    @include('admin.partials.location-assets')
@endpush

@push('scripts')
    @include('admin.partials.location-scripts')
@endpush

@section('content')
<div class="row"><div class="col-lg-10">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.championships.update', $championship) : route('admin.championships.store') }}" enctype="multipart/form-data">
            @csrf @if($isEdit) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $championship->name) }}" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Organizador *</label>
                    <select name="user_id" class="form-select" required>
                        @foreach ($organizers as $o)
                            <option value="{{ $o->id }}" @selected(old('user_id', $championship->user_id) == $o->id)>{{ $o->name }} ({{ $o->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Categoría *</label>
                    <select name="category_id" class="form-select" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $championship->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Modalidad de kart *</label>
                    <select name="kart_modality" class="form-select" required>
                        <option value="rental" @selected(old('kart_modality', $championship->kart_modality ?? 'rental') === 'rental')>Karts de alquiler</option>
                        <option value="own" @selected(old('kart_modality', $championship->kart_modality) === 'own')>Kart propio</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Clase de motor</label>
                    <input type="text" name="engine_class" class="form-control" maxlength="120"
                           value="{{ old('engine_class', $championship->engine_class) }}"
                           placeholder="Ej. Rental 390cc, IAME X30">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $championship->description) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Temporada *</label>
                    <input type="number" name="season_year" class="form-control" value="{{ old('season_year', $championship->season_year) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Inicio</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $championship->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fin</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $championship->end_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    @foreach (['draft','published','in_progress','finished','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $championship->status) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            @include('admin.partials.location-fields', [
                'prefix' => 'venue_',
                'showAddress' => false,
                'sectionTitle' => 'Ubicación base (clima OpenWeather)',
                'sectionHint' => 'Si el campeonato aún no tiene circuito fijo, define la ciudad para el tiempo en la ficha pública.',
                'mapId' => 'championship-venue-map',
                'values' => [
                    'country' => old('venue_country', $championship->venue_country),
                    'province' => old('venue_province', $championship->venue_province),
                    'city' => old('venue_city', $championship->venue_city),
                    'latitude' => old('venue_latitude', $championship->venue_latitude),
                    'longitude' => old('venue_longitude', $championship->venue_longitude),
                ],
            ])

            <div class="mb-3">
                <label class="form-label">Imagen</label>
                @if($championship->image)
                    <div class="mb-2"><img src="{{ asset('storage/'.$championship->image) }}" alt="" class="img-thumbnail" style="max-height:120px"></div>
                @endif
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ $isEdit ? route('admin.championships.show', $championship) : route('admin.championships.index') }}" class="btn btn-outline-secondary" data-full-load>Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection
