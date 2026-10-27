{{-- Bloc de contenu du volet évaluation, dans le style des listes du tableau de bord RH. --}}
@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-[#E2E8F0] bg-white']) }}>
    @if($title || isset($actions))
        <header class="flex items-center justify-between gap-3 border-b border-[#E2E8F0] px-5 py-4">
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-sm font-semibold text-[#0B0F14]">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="text-xs text-[#64748B]">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
