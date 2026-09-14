@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center">
        <span class="inline-flex flex-wrap items-center justify-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                    class="px-3 py-1.5 rounded text-gray-400 text-sm cursor-not-allowed">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="px-3 py-1.5 rounded text-sm">‹</a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true"
                        class="px-3 py-1.5 rounded text-gray-400 text-sm">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                class="px-3 py-1.5 rounded text-gray-900 bg-gray-200 text-sm font-semibold">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="px-3 py-1.5 rounded text-sm">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="px-3 py-1.5 rounded text-sm">›</a>
            @else
                <span aria-disabled="true"
                    class="px-3 py-1.5 rounded text-gray-400 text-sm cursor-not-allowed">›</span>
            @endif
        </span>
    </nav>
@endif