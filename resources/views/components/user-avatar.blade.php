@props(['user', 'size' => 'md'])

@php
    $sizeClasses = match ($size) {
        'xs' => 'h-6 w-6 text-[9px]',
        'sm' => 'h-8 w-8 text-[11px]',
        'lg' => 'h-12 w-12 text-sm',
        'xl' => 'h-20 w-20 text-xl',
        default => 'h-10 w-10 text-xs',
    };
    $photo = $user?->avatarUrl();
@endphp

@if($photo)
    <img src="{{ $photo }}" alt="{{ $user->name }}" {{ $attributes->merge(['class' => "shrink-0 rounded-full object-cover $sizeClasses"]) }}>
@else
    <span {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center rounded-full bg-[#0B0F14] font-semibold text-white $sizeClasses"]) }} aria-hidden="true">{{ $user?->initials() ?? 'IV' }}</span>
@endif
