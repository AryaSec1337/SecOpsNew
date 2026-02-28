@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between w-full mt-4 p-2 bg-slate-900/50 backdrop-blur-sm rounded-xl border border-slate-700/50">
        
        <!-- Mobile View -->
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-600 bg-slate-800/50 border border-slate-700 rounded-lg cursor-default">
                    PREV
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-300 bg-slate-800 border border-slate-600 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-500 transition-all shadow-lg shadow-blue-900/20">
                    PREV
                </a>
            @endif

            <span class="text-xs font-mono text-slate-500 pt-2">
                Page {{ $paginator->currentPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-300 bg-slate-800 border border-slate-600 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-500 transition-all shadow-lg shadow-blue-900/20">
                    NEXT
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-600 bg-slate-800/50 border border-slate-700 rounded-lg cursor-default">
                    NEXT
                </span>
            @endif
        </div>

        <!-- Desktop View -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-500 font-mono">
                    Showing
                    <span class="font-bold text-white">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-bold text-white">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-bold text-white">{{ $paginator->total() }}</span>
                </p>
            </div>

            <div>
                <span class="isolate inline-flex rounded-md shadow-sm gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-slate-700 bg-slate-800/30 border border-slate-800 cursor-default rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-slate-400 bg-slate-800 border border-slate-700 rounded-lg hover:bg-slate-700 hover:text-white hover:border-slate-500 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 bg-transparent cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-blue-600/90 border border-blue-500 rounded-lg shadow-[0_0_10px_rgba(37,99,235,0.4)] z-10">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-slate-800/50 border border-slate-700 rounded-lg hover:bg-slate-700 hover:text-white hover:border-slate-500 hover:shadow-lg transition-all" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 text-slate-400 bg-slate-800 border border-slate-700 rounded-lg hover:bg-slate-700 hover:text-white hover:border-slate-500 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-slate-700 bg-slate-800/30 border border-slate-800 cursor-default rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
