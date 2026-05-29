@extends('admin.layouts.app')
@section('title', 'Suscripciones')
@section('page-title', 'Suscripciones')
@section('page-actions')
    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva suscripción</a>
@endsection

@section('content')
<div class="card card-admin mb-3"><div class="card-body">
    <form class="row g-2" method="GET">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos</option>
                @foreach (['pending','active','expired','cancelled'] as $s)
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
            <thead><tr><th>Usuario</th><th>Plan</th><th>Estado</th><th>Inicio</th><th>Fin</th><th></th></tr></thead>
            <tbody>
                @foreach ($subscriptions as $sub)
                    <tr>
                        <td>{{ $sub->user?->name }}</td>
                        <td>{{ $sub->plan?->name }}</td>
                        <td><span class="badge-status badge-{{ $sub->status }}">{{ $sub->status }}</span></td>
                        <td>{{ $sub->starts_at?->format('d/m/Y') }}</td>
                        <td>{{ $sub->ends_at?->format('d/m/Y') }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.subscriptions.show', $sub) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="{{ route('admin.subscriptions.edit', $sub) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form method="POST" action="{{ route('admin.subscriptions.destroy', $sub) }}" class="d-inline" onsubmit="return confirm('¿Eliminar suscripción?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())
        <div class="card-footer">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection
