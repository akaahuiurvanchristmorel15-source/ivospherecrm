@props([
    'placeholder' => 'Rechercher...',
    'name' => 'search',
    'value' => null,
])

<div class="relative w-full sm:w-72">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    <input 
        type="search" 
        name="{{ $name }}" 
        value="{{ $value ?? request($name) }}"
        placeholder="{{ $placeholder }}" 
        {{ $attributes->merge(['class' => 'w-full pl-9 pr-4 py-2 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] shadow-2xs transition-colors']) }}
    />
</div>
