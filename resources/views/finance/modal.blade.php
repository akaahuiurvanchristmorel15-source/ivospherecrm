@props([
    'show',                      // expression Alpine, ex: "transferModal"
    'close',                     // instruction Alpine, ex: "transferModal = false"
    'title',
    'maxWidth' => 'sm:max-w-md',
])

<div x-show="{{ $show }}" x-cloak
     @keydown.escape.window="{{ $close }}"
     class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
     role="dialog" aria-modal="true" aria-label="{{ $title }}">

    {{-- Fond --}}
    <div class="absolute inset-0 bg-slate-900/40" @click="{{ $close }}" aria-hidden="true"></div>

    {{-- Panneau : bottom-sheet < sm, modale centrée >= sm --}}
    <div x-show="{{ $show }}"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
         class="relative flex max-h-[92dvh] w-full {{ $maxWidth }} flex-col rounded-t-2xl bg-white shadow-xl sm:rounded-2xl">

        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
            <button type="button" @click="{{ $close }}"
                    class="-mr-1.5 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-hidden"
                    aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto px-5 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
            {{ $slot }}
        </div>
    </div>
</div>