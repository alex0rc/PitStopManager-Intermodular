@extends('admin.layouts.app')
@section('title', 'Categorías')
@section('page-title', 'Categorías')
@section('page-actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva categoría</a>
@endsection

@section('content')
<div class="card card-admin">
    <div class="table-responsive">
        <table class="table table-admin table-hover mb-0">
            <thead><tr><th>Nombre</th><th>Campeonatos</th><th>Edad</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                @foreach ($categories as $cat)
                    <tr>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td>{{ $cat->championships_count }}</td>
                        <td class="small text-muted">
                            @if($cat->min_age || $cat->max_age)
                                {{ $cat->min_age ?? '?' }}–{{ $cat->max_age ?? '?' }} años
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end action-btns">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')
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
