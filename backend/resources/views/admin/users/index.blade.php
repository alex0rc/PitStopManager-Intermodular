@extends('admin.layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Usuarios')
@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo usuario</a>
@endsection

@section('content')
<div class="card card-admin mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Buscar nombre o email" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Todos los roles</option>
                    @foreach (['admin','organizer','pilot'] as $r)
                        <option value="{{ $r }}" @selected(request('role') === $r)>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
        </form>
    </div>
</div>

<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-secondary">{{ $user->role }}</span></td>
                        <td>
                            @if ($user->is_active)
                                <span class="text-success"><i class="bi bi-check-circle"></i></span>
                            @else
                                <span class="text-danger"><i class="bi bi-x-circle"></i></span>
                            @endif
                        </td>
                        <td class="text-end action-btns">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="d-inline">@csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning" title="Activar/desactivar"><i class="bi bi-toggle-on"></i></button>
                            </form>
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection
