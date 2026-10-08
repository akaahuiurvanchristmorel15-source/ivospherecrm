@props([
    'show',
    'close' => null,
    'title' => null,
    'maxWidth' => 'sm:max-w-md',
])

<div 
    x-show="{{ $show }}" 
    x-cloak
    @keydown.escape.window="{{ $close ?? $show.' = false' }}"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
    style="display: none;"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div 
        x-show="{{ $show }}" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="{{ $close ?? $show.' = false' }}" 
        class="fixed inset-0 bg-[#0B0F14]/60 backdrop-blur-xs transition-opacity"
    ></div>

    <!-- Modal Box -->
    <div 
        x-show="{{ $show }}" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="bg-white rounded-2xl border border-[#E2E8F0] shadow-2xl overflow-hidden transform transition-all w-full {{ $maxWidth }} z-10 p-5 sm:p-6"
    >
        @if($title)
            <div class="flex items-center justify-between pb-4 border-b border-[#E2E8F0] mb-4">
                <h3 class="text-base font-bold text-[#0B0F14]">
                    {{ $title }}
                </h3>
                <button 
                    type="button" 
                    @click="{{ $close ?? $show.' = false' }}" 
                    class="p-1 rounded-lg text-slate-400 hover:text-[#0B0F14] hover:bg-slate-100 transition-colors cursor-pointer"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        <div>
            {{ $slot }}
        </div>
    </div>
</div>
