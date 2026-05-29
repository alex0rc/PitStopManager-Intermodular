@extends('admin.layouts.app')
@php $isEdit = $plan->exists; @endphp
@section('title', $isEdit ? 'Editar plan' : 'Nuevo plan')
@section('page-title', $isEdit ? 'Editar plan' : 'Nuevo plan')

@section('content')
<div class="row"><div class="col-lg-6">
<div class="card card-admin"><div class="card-body">
<form method="POST" action="{{ $isEdit ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
@csrf @if($isEdit) @method('PUT') @endif
<div class="mb-3"><label class="form-label">Nombre *</label><input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required></div>
<div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $plan->slug) }}" placeholder="auto desde nombre"></div>
<div class="mb-3"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description) }}</textarea></div>
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Precio (€) *</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required></div>
    <div class="col-md-4 mb-3"><label class="form-label">Duración (días) *</label><input type="number" name="duration_days" class="form-control" value="{{ old('duration_days', $plan->duration_days ?? 30) }}" required></div>
    <div class="col-md-4 mb-3"><label class="form-label">Máx. campeonatos *</label><input type="number" name="max_championships" class="form-control" value="{{ old('max_championships', $plan->max_championships ?? 1) }}" required></div>
</div>
<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $plan->is_active ?? true))>
    <label class="form-check-label" for="is_active">Plan activo (visible para compra)</label>
</div>
<button type="submit" class="btn btn-primary">Guardar</button>
<a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
</div></div>
</div></div>
@endsection
