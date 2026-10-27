{{-- Anneau de la note du mois sur 20, coloré selon l'appréciation du référentiel. --}}
@props([
    'score' => null,
    'size' => 'md',
])

@php
    $value = $score === null ? null : (float) $score;
    $percentage = $value === null ? 0 : min(100, max(0, round($value / 20 * 100)));
    $stroke = match (true) {
        $value === null => '#CBD5E1',
        $value >= 16 => '#059669',
        $value >= 14 => '#0066FF',
        $value >= 12 => '#0EA5E9',
        $value >= 10 => '#D97706',
        default => '#E11D48',
    };
    [$box, $text] = match ($size) {
        'sm' => ['h-12 w-12', 'text-sm'],
        'lg' => ['h-32 w-32', 'text-3xl'],
        default => ['h-20 w-20', 'text-xl'],
    };
@endphp

<div {{ $attributes->merge(['class' => "relative shrink-0 {$box}"]) }} role="img" aria-label="{{ $value === null ? 'Pas encore noté' : number_format($value, 2, ',', ' ').' sur 20' }}">
    <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor" stroke-width="3" class="text-[#EEF2F6]"/>
        @if($percentage > 0)
            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $stroke }}" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $percentage }} 100" class="transition-all duration-700"/>
        @endif
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
        <span class="{{ $text }} font-semibold tracking-tight text-[#0B0F14] tabular-nums">{{ $value === null ? '—' : number_format($value, $size === 'sm' ? 1 : 2, ',', '') }}</span>
        @if($size !== 'sm')
            <span class="mt-1 text-[10px] text-[#94A3B8]">/ 20</span>
        @endif
    </div>
</div>
