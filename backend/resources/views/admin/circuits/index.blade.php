@extends('admin.layouts.app')
@section('title', 'Circuitos')
@section('page-title', 'Circuitos')
@section('page-actions')
    <a href="{{ route('admin.circuits.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo circuito</a>
@endsection

@section('content')
<div class="card card-admin mb-3"><div class="card-body">
    <form class="row g-2" method="GET">
        <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}"></div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Todos los estados</option>
                @foreach (['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input type="text" name="province" class="form-control" placeholder="Provincia" value="{{ request('province') }}"></div>
        <div class="col-md-2"><button class="btn btn-primary">Filtrar</button></div>
    </form>
</div></div>

<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead><tr><th></th><th>Nombre</th><th>Provincia</th><th>Estado</th><th>Propietario</th><th></th></tr></thead>
            <tbody>
                @forelse ($circuits as $circuit)
                    <tr>
                        <td>
                            @if($circuit->image)
                                <img src="{{ asset('storage/'.$circuit->image) }}" alt="" class="rounded" style="width:48px;height:36px;object-fit:cover">
                            @endif
                        </td>
                        <td><strong>{{ $circuit->name }}</strong><br><small class="text-muted">{{ $circuit->location }}</small></td>
                        <td>{{ $circuit->province ?? '—' }}</td>
                        <td><span class="badge-status badge-{{ $circuit->status }}">{{ $circuit->status }}</span></td>
                        <td>{{ $circuit->user?->name }}</td>
                        <td class="text-nowrap">
                            @if($circuit->status === 'pending')
                                <form method="POST" action="{{ route('admin.circuits.status', $circuit) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success">Aprobar</button>
                                </form>
                                <form method="POST" action="{{ route('admin.circuits.status', $circuit) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-sm btn-outline-danger">Rechazar</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.circuits.edit', $circuit) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('admin.circuits.destroy', $circuit) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Sin circuitos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($circuits->hasPages())
        <div class="card-footer">{{ $circuits->links() }}</div>
    @endif
</div>
@endsection
