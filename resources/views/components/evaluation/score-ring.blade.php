{{-- Anneau de la note du mois sur 20, coloré selon l'appréciation du référentiel ; variante sur fond sombre (carte héro RH). --}}
@props([
    'score' => null,
    'size' => 'md',
    'onDark' => false,
])

@php
    $value = $score === null ? null : (float) $score;
    $percentage = $value === null ? 0 : min(100, max(0, round($value / 20 * 100)));
    $stroke = match (true) {
        $value === null => '#CBD5E1',
        $value >= 16 => '#10b981',
        $value >= 14 => '#3B8BFF',
        $value >= 12 => '#38BDF8',
        $value >= 10 => '#f59e0b',
        default => '#f43f5e',
    };
    [$box, $text] = match ($size) {
        'sm' => ['h-12 w-12', 'text-sm'],
        'lg' => ['h-28 w-28', 'text-2xl'],
        default => ['h-24 w-24', 'text-xl'],
    };
@endphp

<div {{ $attributes->merge(['class' => "relative shrink-0 {$box}"]) }} role="img" aria-label="{{ $value === null ? 'Pas encore noté' : number_format($value, 2, ',', ' ').' sur 20' }}">
    <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
        @if($onDark)
            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
        @else
            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor" stroke-width="3" class="text-[#EEF2F6]"/>
        @endif
        @if($percentage > 0)
            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $stroke }}" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $percentage }} 100" class="transition-all duration-700"/>
        @endif
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
        <span class="{{ $text }} font-semibold tracking-tight tabular-nums {{ $onDark ? 'text-white' : 'text-[#0B0F14]' }}">{{ $value === null ? '—' : number_format($value, $size === 'sm' ? 1 : 2, ',', '') }}</span>
        @if($size !== 'sm')
            <span class="mt-1 text-[10px] {{ $onDark ? 'text-slate-400' : 'text-[#94A3B8]' }}">/ 20</span>
        @endif
    </div>
</div>
