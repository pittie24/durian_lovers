@if ($paginator->hasPages())
    <nav class="admin-page-nav" role="navigation" aria-label="Pagination">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="admin-page-dots" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="admin-page-link is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="admin-page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
