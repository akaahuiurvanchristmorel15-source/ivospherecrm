{{-- Notes des 4 blocs du référentiel (sur 5) d'une évaluation mensuelle, en barres horizontales ; variante sur fond sombre. --}}
@props([
    'evaluation' => null,
    'compact' => false,
    'onDark' => false,
])

<ul {{ $attributes->merge(['class' => $compact ? 'space-y-1.5' : 'space-y-3']) }}>
    @foreach(\App\Models\EvaluationCriterion::BLOCKS as $block => $definition)
        @php $blockScore = $evaluation?->{'block_'.strtolower($block).'_score'}; @endphp
        <li>
            <div class="flex items-baseline justify-between gap-3 text-xs">
                <span class="min-w-0 truncate {{ $onDark ? 'text-white' : 'text-[#0B0F14]' }}">
                    <span class="font-semibold">{{ $block }}</span>
                    @unless($compact)
                        <span class="{{ $onDark ? 'text-slate-400' : 'text-[#64748B]' }}">· {{ $definition['label'] }}</span>
                    @endunless
                    <span class="text-[10px] {{ $onDark ? 'text-slate-500' : 'text-[#94A3B8]' }}">{{ $definition['weight'] * 100 }} %</span>
                </span>
                <span class="shrink-0 font-medium tabular-nums {{ $blockScore === null ? ($onDark ? 'text-slate-500' : 'text-[#94A3B8]') : ($onDark ? 'text-white' : 'text-[#0B0F14]') }}">
                    {{ $blockScore === null ? 'n.c.' : number_format($blockScore, 2, ',', '').' / 5' }}
                </span>
            </div>
            <div class="mt-1 h-1.5 overflow-hidden rounded-full {{ $onDark ? 'bg-white/10' : 'bg-slate-100' }}">
                <div class="h-full rounded-full {{ $onDark ? 'bg-[#3B8BFF]' : 'bg-[#0066FF]' }} transition-all duration-700" style="width: {{ ($blockScore ?? 0) * 20 }}%"></div>
            </div>
        </li>
    @endforeach
</ul>
