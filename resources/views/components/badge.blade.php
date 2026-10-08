@props([
    'variant' => 'neutral', // neutral, primary, success, warning, danger
    'size' => 'sm',         // sm, md
    'dot' => false,
])

@php
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-[10px] sm:text-[11px]',
        'md' => 'px-2.5 py-1 text-xs',
    ][$size] ?? 'px-2 py-0.5 text-[11px]';

    $variantClasses = [
        'primary' => 'bg-blue-50 text-[#0066FF] border border-blue-200/80',
        'neutral' => 'bg-[#F5F7FA] text-slate-600 border border-[#E2E8F0]',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
        'warning' => 'bg-amber-50 text-amber-800 border border-amber-200/80',
        'danger'  => 'bg-rose-50 text-rose-700 border border-rose-200/80',
    ][$variant] ?? 'bg-[#F5F7FA] text-slate-600 border border-[#E2E8F0]';

    $dotColors = [
        'primary' => 'bg-[#0066FF]',
        'neutral' => 'bg-slate-400',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger'  => 'bg-rose-500',
    ][$variant] ?? 'bg-slate-400';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-medium rounded-full {$sizeClasses} {$variantClasses}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}"></span>
    @endif
    {{ $slot }}
</span>
