@extends('admin.layouts.app')
@php $isEdit = $user->exists; @endphp
@section('title', $isEdit ? 'Editar usuario' : 'Nuevo usuario')
@section('page-title', $isEdit ? 'Editar usuario' : 'Nuevo usuario')

@section('content')
<div class="row"><div class="col-lg-6">
    <div class="card card-admin"><div class="card-body">
        <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña {{ $isEdit ? '(dejar vacío para no cambiar)' : '*' }}</label>
                <input type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
            </div>

            <div class="mb-3">
                <label class="form-label">Rol *</label>
                <select name="role" class="form-select" required>
                    @foreach (['admin','organizer','pilot'] as $r)
                        <option value="{{ $r }}" @selected(old('role', $user->role) === $r)>{{ $r }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                    @checked(old('is_active', $user->is_active ?? true))>
                <label class="form-check-label" for="is_active">Cuenta activa</label>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection
