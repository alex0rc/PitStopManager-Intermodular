@extends('admin.layouts.app')
@section('title', 'Resultados — '.$race->name)
@section('page-title', 'Resultados')
@section('page-subtitle', $race->championship?->name.' · '.$race->name.' · '.$race->circuit?->name)
@section('page-actions')
    <a href="{{ route('admin.championships.races.index', $race->championship) }}" class="btn btn-outline-secondary">Carreras</a>
    <a href="{{ route('admin.races.results.create', $race) }}" class="btn btn-primary">+ Resultado</a>
@endsection

@section('content')
<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead>
                <tr><th>Pos.</th><th>Piloto</th><th>Mejor vuelta</th><th>Tiempo total</th><th>Puntos</th><th>DNF/DSQ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($race->results->sortBy('position') as $r)
                    <tr>
                        <td>{{ $r->position ?? '—' }}</td>
                        <td>{{ $r->user?->name }}</td>
                        <td>{{ $r->best_lap_time ?? '—' }}</td>
                        <td>{{ $r->total_time ?? '—' }}</td>
                        <td><strong>{{ $r->points }}</strong></td>
                        <td>
                            @if($r->dnf)
                                <span class="badge bg-warning text-dark">DNF</span>
                            @endif
                            @if($r->dsq)
                                <span class="badge bg-danger">DSQ</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.races.results.edit', [$race, $r]) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form method="POST" action="{{ route('admin.races.results.destroy', [$race, $r]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Sin resultados registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
