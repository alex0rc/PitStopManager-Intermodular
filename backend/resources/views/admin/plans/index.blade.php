@extends('admin.layouts.app')
@section('title', 'Planes')
@section('page-title', 'Planes de suscripción')
@section('page-actions')
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo plan</a>
@endsection

@section('content')
<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead><tr><th>Nombre</th><th>Precio</th><th>Días</th><th>Máx. campeonatos</th><th>Activo</th><th></th></tr></thead>
            <tbody>
                @foreach ($plans as $plan)
                    <tr>
                        <td class="fw-semibold">{{ $plan->name }}<br><small class="text-muted">{{ $plan->slug }}</small></td>
                        <td>{{ number_format($plan->price, 2) }} €</td>
                        <td>{{ $plan->duration_days }}</td>
                        <td>{{ $plan->max_championships }}</td>
                        <td>
                            @if($plan->is_active)
                                <span class="text-success">Sí</span>
                            @else
                                <span class="text-muted">No</span>
                            @endif
                        </td>
                        <td class="text-end action-btns">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
