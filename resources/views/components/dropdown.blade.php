@props([
    'align' => 'right', // left, right
    'width' => '48',    // 48, 56, etc.
])

@php
    $alignmentClasses = [
        'left' => 'origin-top-left left-0',
        'right' => 'origin-top-right right-0',
    ][$align] ?? 'origin-top-right right-0';

    $widthClasses = [
        '40' => 'w-40',
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
    ][$width] ?? 'w-48';
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div 
        x-show="open" 
        @click.outside="open = false" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute {{ $alignmentClasses }} {{ $widthClasses }} mt-2 rounded-xl bg-white border border-[#E2E8F0] shadow-lg py-1 z-50 text-xs focus:outline-none"
        style="display: none;"
    >
        {{ $content }}
    </div>
</div>
