@props([
    'paginator',
])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between text-xs text-[#64748B]">
        <div>
            <p>
                Affichage de <span class="font-medium text-[#0B0F14]">{{ $paginator->firstItem() }}</span> à <span class="font-medium text-[#0B0F14]">{{ $paginator->lastItem() }}</span> sur <span class="font-medium text-[#0B0F14]">{{ $paginator->total() }}</span> résultats
            </p>
        </div>

        <div class="flex items-center gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-[#F5F7FA] text-slate-400 cursor-not-allowed">
                    Précédent
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-white text-[#0B0F14] hover:bg-[#F5F7FA] transition-colors">
                    Précédent
                </a>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-white text-[#0B0F14] hover:bg-[#F5F7FA] transition-colors">
                    Suivant
                </a>
            @else
                <span class="px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-[#F5F7FA] text-slate-400 cursor-not-allowed">
                    Suivant
                </span>
            @endif
        </div>
    </nav>
@endif
