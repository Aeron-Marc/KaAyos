@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span class="disabled" aria-disabled="true">&laquo;</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="disabled" aria-disabled="true">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
    @else
        <span class="disabled" aria-disabled="true">&raquo;</span>
    @endif
@endif
