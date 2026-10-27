@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <p class="pagination-summary">
            {{ __('Showing') }}
            <strong>{{ $paginator->firstItem() }}</strong>
            {{ __('to') }}
            <strong>{{ $paginator->lastItem() }}</strong>
            {{ __('of') }}
            <strong>{{ $paginator->total() }}</strong>
            {{ __('results') }}
        </p>

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-link is-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <i data-lucide="chevron-left" aria-hidden="true"></i>
                </span>
            @else
                <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                    <i data-lucide="chevron-left" aria-hidden="true"></i>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-ellipsis" aria-disabled="true">{{ $element }}</span>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                    <i data-lucide="chevron-right" aria-hidden="true"></i>
                </a>
            @else
                <span class="pagination-link is-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <i data-lucide="chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
