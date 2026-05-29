@extends('admin.layouts.app')
@php $isEdit = $category->exists; @endphp
@section('title', $isEdit ? 'Editar categoría' : 'Nueva categoría')
@section('page-title', $isEdit ? 'Editar categoría' : 'Nueva categoría')

@section('content')
<div class="row"><div class="col-lg-6">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf @if($isEdit) @method('PUT') @endif
            <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required></div>
            <div class="mb-3"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Edad mín.</label><input type="number" name="min_age" class="form-control" value="{{ old('min_age', $category->min_age) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Edad máx.</label><input type="number" name="max_age" class="form-control" value="{{ old('max_age', $category->max_age) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Peso máx. (kg)</label><input type="number" step="0.1" name="max_weight_kg" class="form-control" value="{{ old('max_weight_kg', $category->max_weight_kg) }}"></div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection
