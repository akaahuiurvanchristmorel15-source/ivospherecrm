@props(['warehouse', 'size' => 'sm', 'showName' => false])

@php
    $sizeClasses = match ($size) {
        'xs' => 'h-4 min-w-4 px-1 text-[9px]',
        'lg' => 'h-9 min-w-9 px-2 text-base',
        default => 'h-5 min-w-5 px-1.5 text-[11px]',
    };
@endphp

@if($warehouse)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 align-middle']) }} title="{{ $warehouse->name }}">
        <span class="inline-flex shrink-0 items-center justify-center rounded-md font-bold leading-none shadow-xs {{ $sizeClasses }}"
              style="background-color: {{ $warehouse->label_color ?: \App\Models\Warehouse::defaultLabelColor((int) $warehouse->label_number) }}; color: {{ $warehouse->label_text_color }};">
            {{ $warehouse->label_number ?? '?' }}
        </span>
        @if($showName)
            <span class="truncate">{{ $warehouse->name }}</span>
        @endif
    </span>
@endif
