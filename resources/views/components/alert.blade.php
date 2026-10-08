@props([
    'type' => 'info', // success, error, warning, info
    'dismissible' => true,
])

@php
    $typeConfig = [
        'success' => [
            'bg' => 'bg-emerald-50/80',
            'border' => 'border-emerald-200/80',
            'text' => 'text-emerald-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            'iconColor' => 'text-emerald-600',
        ],
        'error' => [
            'bg' => 'bg-rose-50/80',
            'border' => 'border-rose-200/80',
            'text' => 'text-rose-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            'iconColor' => 'text-rose-600',
        ],
        'warning' => [
            'bg' => 'bg-amber-50/80',
            'border' => 'border-amber-200/80',
            'text' => 'text-amber-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            'iconColor' => 'text-amber-600',
        ],
        'info' => [
            'bg' => 'bg-blue-50/80',
            'border' => 'border-blue-200/80',
            'text' => 'text-blue-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'iconColor' => 'text-[#0066FF]',
        ],
    ][$type] ?? [
        'bg' => 'bg-blue-50/80',
        'border' => 'border-blue-200/80',
        'text' => 'text-blue-800',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'iconColor' => 'text-[#0066FF]',
    ];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition
    {{ $attributes->merge(['class' => "p-3.5 sm:p-4 rounded-2xl border {$typeConfig['bg']} {$typeConfig['border']} {$typeConfig['text']} flex items-start justify-between gap-3 text-xs sm:text-sm"]) }}
>
    <div class="flex items-start gap-2.5">
        <svg class="w-4 h-4 shrink-0 mt-0.5 {{ $typeConfig['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $typeConfig['icon'] !!}
        </svg>
        <div class="font-medium leading-relaxed">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button 
            type="button" 
            @click="show = false" 
            class="text-slate-400 hover:text-slate-600 transition-colors p-0.5 rounded-lg hover:bg-black/5"
            aria-label="Fermer"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
