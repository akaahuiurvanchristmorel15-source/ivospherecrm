{{-- Bloc de contenu du volet évaluation : carte blanche, en-tête titre / sous-titre / actions. --}}
@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-[#E2E8F0] bg-white']) }}>
    @if($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-[#EEF2F6] px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-sm font-semibold text-[#0B0F14]">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="mt-0.5 text-xs text-[#64748B]">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div @class(['p-4 sm:p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
