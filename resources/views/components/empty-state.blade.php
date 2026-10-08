@props([
    'title' => 'Aucun résultat trouvé',
    'description' => 'Il n\'y a aucun élément correspondant à vos critères actuels.',
    'actionText' => null,
    'actionUrl' => null,
])

<div class="text-center py-10 sm:py-12 px-4 bg-white border border-[#E2E8F0] rounded-2xl">
    <div class="w-10 h-10 rounded-full bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-center mx-auto text-slate-400 mb-2.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    </div>

    <h3 class="text-sm font-semibold text-[#0B0F14]">
        {{ $title }}
    </h3>

    <p class="mt-1 text-xs text-[#64748B] max-w-sm mx-auto leading-relaxed">
        {{ $description }}
    </p>

    @if($actionText && $actionUrl)
        <div class="mt-4">
            <x-button :href="$actionUrl" variant="primary" size="sm">
                {{ $actionText }}
            </x-button>
        </div>
    @elseif(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
