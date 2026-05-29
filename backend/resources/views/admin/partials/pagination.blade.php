@if ($paginator->hasPages())
    <nav class="admin-pagination" aria-label="Paginación">
        @if ($paginator->total() > 0)
            <p class="admin-pagination-info mb-0">
                Mostrando <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
                de <strong>{{ $paginator->total() }}</strong>
            </p>
        @endif

        <ul class="pagination pagination-sm mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Anterior</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">
                        <i class="bi bi-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Anterior</span>
                    </a>
                </li>
            @endif

            <li class="page-item disabled d-md-none" aria-hidden="true">
                <span class="page-link admin-pagination-current">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled d-none d-md-block" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active d-none d-md-block" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item d-none d-md-block">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">
                        <span class="d-none d-sm-inline me-1">Siguiente</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true">
                        <span class="d-none d-sm-inline me-1">Siguiente</span>
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
