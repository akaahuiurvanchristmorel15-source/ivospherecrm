@props([
    'name',
    'title' => null,
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl
])

@php
    $maxWidthClass = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth] ?? 'sm:max-w-md';
@endphp

<div 
    x-data="{ show: false }"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show" 
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="show" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="show = false" 
        class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-xs transition-opacity"
    ></div>

    <!-- Modal Box -->
    <div 
        x-show="show" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="bg-white rounded-[12px] border border-[#E2E8F0] overflow-hidden shadow-2xl transform transition-all sm:w-full {{ $maxWidthClass }} z-10"
    >
        @if($title)
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#0B0F14]">
                    {{ $title }}
                </h3>
                <button 
                    type="button" 
                    @click="show = false" 
                    class="text-slate-400 hover:text-[#0B0F14] transition-colors p-1 rounded-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="px-6 py-3 bg-[#F5F7FA] border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
