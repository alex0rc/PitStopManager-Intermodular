@extends('admin.layouts.app')
@section('title', 'Campeonatos')
@section('page-title', 'Campeonatos')
@section('page-actions')
    <a href="{{ route('admin.championships.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo campeonato</a>
@endsection

@section('content')
<div class="card card-admin mb-3"><div class="card-body">
    <form class="row g-2" method="GET">
        <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos los estados</option>
                @foreach (['draft','published','in_progress','finished','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary">Filtrar</button></div>
    </form>
</div></div>

<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead>
                <tr><th>Nombre</th><th>Categoría</th><th>Organizador</th><th>Temporada</th><th>Estado</th><th>Carreras</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($championships as $c)
                    <tr>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->category?->name }}</td>
                        <td>{{ $c->user?->name }}</td>
                        <td>{{ $c->season_year }}</td>
                        <td><span class="badge-status badge-{{ $c->status }}">{{ $c->status }}</span></td>
                        <td>{{ $c->races_count }} / {{ $c->inscriptions_count }} ins.</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.championships.show', $c) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="{{ route('admin.championships.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay campeonatos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($championships->hasPages())
        <div class="card-footer">{{ $championships->links() }}</div>
    @endif
</div>
@endsection
