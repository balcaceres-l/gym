{{-- Paginación propia (en español y con los estilos del proyecto). Uso: $coleccion->links('partials.paginacion') --}}
@if ($paginator->hasPages())
    <nav class="paginacion" aria-label="Paginación">
        <p class="paginacion__resumen">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        <ul class="paginacion__lista">
            @if ($paginator->onFirstPage())
                <li><span class="paginacion__item deshabilitado">‹ Anterior</span></li>
            @else
                <li><a class="paginacion__item" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="paginacion__item deshabilitado">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="paginacion__item actual" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="paginacion__item" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="paginacion__item" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente ›</a></li>
            @else
                <li><span class="paginacion__item deshabilitado">Siguiente ›</span></li>
            @endif
        </ul>
    </nav>
@endif
