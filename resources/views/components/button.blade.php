@props([
    'variant' => 'primary', // primary, secondary, danger, ghost
    'size' => 'md', // sm, md, lg
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-1 rounded-xl cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $sizeClasses = [
        'sm' => 'h-8 px-3 text-xs gap-1.5',
        'md' => 'h-10 px-4 text-xs sm:text-sm gap-2',
        'lg' => 'h-11 px-5 text-sm sm:text-base gap-2.5',
    ][$size] ?? 'h-10 px-4 text-xs sm:text-sm gap-2';

    $variantClasses = [
        'primary' => 'bg-[#0066FF] hover:bg-[#0052CC] text-white active:scale-[0.99]',
        'secondary' => 'bg-white hover:bg-slate-50 text-[#0B0F14] border border-[#E2E8F0] active:scale-[0.99]',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white active:scale-[0.99]',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-[#64748B] hover:text-[#0B0F14]',
    ][$variant] ?? 'bg-[#0066FF] hover:bg-[#0052CC] text-white';

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
