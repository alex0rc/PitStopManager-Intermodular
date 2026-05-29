@php
    $racesCount = $championship->races_count ?? $championship->races?->count() ?? 0;
    $inscriptionsCount = $championship->inscriptions_count ?? $championship->inscriptions?->count() ?? 0;
@endphp
<ul class="nav nav-pills championship-admin-nav mb-4">
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'show' ? 'active' : '' }}"
           href="{{ route('admin.championships.show', $championship) }}">
            <i class="bi bi-info-circle me-1"></i> Resumen
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'races' ? 'active' : '' }}"
           href="{{ route('admin.championships.races.index', $championship) }}">
            <i class="bi bi-flag me-1"></i> Carreras
            <span class="badge bg-secondary ms-1">{{ $racesCount }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($active ?? '') === 'inscriptions' ? 'active' : '' }}"
           href="{{ route('admin.championships.inscriptions.index', $championship) }}">
            <i class="bi bi-people me-1"></i> Inscripciones
            <span class="badge bg-secondary ms-1">{{ $inscriptionsCount }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.championships.edit', $championship) }}">
            <i class="bi bi-pencil me-1"></i> Editar campeonato
        </a>
    </li>
</ul>
