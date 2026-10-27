{{-- Tuile d'indicateur du style RH : pastille d'icône teintée, valeur et libellé ; lien facultatif. --}}
@props([
    'label',
    'value',
    'icon',
    'tint' => 'bg-slate-100 text-slate-700',
    'hover' => 'hover:border-slate-300',
    'valueClass' => 'text-[#0B0F14]',
    'href' => null,
])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4 transition '.($href ? $hover : '')]) }}>
    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $tint }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
    </span>
    <div class="mt-3">
        <p class="text-2xl font-semibold {{ $valueClass }}">{{ $value }}</p>
        <p class="text-[11px] leading-tight text-[#64748B] sm:text-xs">{{ $label }}</p>
    </div>
</{{ $tag }}>
