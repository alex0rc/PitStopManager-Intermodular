@extends('admin.layouts.app')
@section('title', 'Pagos')
@section('page-title', 'Pagos')
@section('page-actions')
    <a href="{{ route('admin.payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo pago</a>
@endsection

@section('content')
<div class="card card-admin mb-3"><div class="card-body">
    <form class="row g-2" method="GET">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos</option>
                @foreach (['pending','succeeded','failed','refunded'] as $s)
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
            <thead><tr><th>ID</th><th>Usuario</th><th>Plan</th><th>Importe</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
                @foreach ($payments as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>{{ $p->user?->name }}</td>
                        <td>{{ $p->subscription?->plan?->name }}</td>
                        <td>{{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                        <td><span class="badge-status badge-{{ $p->status }}">{{ $p->status }}</span></td>
                        <td>{{ $p->paid_at?->format('d/m/Y H:i') ?? $p->created_at->format('d/m/Y') }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.payments.show', $p) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="{{ route('admin.payments.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form method="POST" action="{{ route('admin.payments.destroy', $p) }}" class="d-inline" onsubmit="return confirm('¿Eliminar pago?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="card-footer">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
