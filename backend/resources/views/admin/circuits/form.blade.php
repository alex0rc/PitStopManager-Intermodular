@extends('admin.layouts.app')
@php $isEdit = $circuit->exists; @endphp
@section('title', $isEdit ? 'Editar circuito' : 'Nuevo circuito')
@section('page-title', $isEdit ? 'Editar circuito' : 'Nuevo circuito')

@push('styles')
    @include('admin.partials.location-assets')
@endpush

@push('scripts')
    @include('admin.partials.location-scripts')
@endpush

@section('content')
<div class="row"><div class="col-lg-10">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.circuits.update', $circuit) : route('admin.circuits.store') }}" enctype="multipart/form-data">
            @csrf @if($isEdit) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Organizador *</label>
                <select name="user_id" class="form-select" required>
                    @foreach ($organizers as $o)
                        <option value="{{ $o->id }}" @selected(old('user_id', $circuit->user_id) == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $circuit->name) }}" required>
            </div>

            @include('admin.partials.location-fields', [
                'prefix' => '',
                'showAddress' => true,
                'addressRequired' => true,
                'sectionTitle' => 'Ubicación del circuito',
                'mapId' => 'circuit-map',
                'values' => [
                    'location' => old('location', $circuit->location),
                    'country' => old('country', $circuit->country),
                    'province' => old('province', $circuit->province),
                    'city' => old('city', $circuit->city),
                    'latitude' => old('latitude', $circuit->latitude),
                    'longitude' => old('longitude', $circuit->longitude),
                ],
            ])

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Longitud pista (m)</label>
                    <input type="number" name="length_meters" class="form-control" value="{{ old('length_meters', $circuit->length_meters) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        @foreach (['pending','approved','rejected'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $circuit->status ?? 'approved') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $circuit->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Imagen</label>
                @if($circuit->image)
                    <div class="mb-2"><img src="{{ asset('storage/'.$circuit->image) }}" alt="" class="img-thumbnail" style="max-height:120px"></div>
                @endif
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.circuits.index') }}" class="btn btn-outline-secondary" data-full-load>Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection
