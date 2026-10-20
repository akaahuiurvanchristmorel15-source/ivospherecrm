@props([])

@php
    $module = request()->routeIs('commercial.pos.*') ? null : app(\App\Services\NavigationService::class)->currentModule();
@endphp

@if($module && count($module['items']) > 1)
<nav
    aria-label="Navigation {{ $module['label'] }}"
    x-data
    x-init="$nextTick(() => $el.querySelector('[aria-current=page]')?.scrollIntoView({ block: 'nearest', inline: 'center' }))"
    class="relative shrink-0"
>
    <div class="flex items-center gap-3 mt-3 px-3 sm:px-4 lg:px-6">
        {{-- Nom du pôle (desktop) --}}
        <span class="hidden lg:block shrink-0 pl-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $module['label'] }}</span>
        <span class="hidden lg:block h-4 w-px bg-slate-200 shrink-0"></span>

        {{-- Onglets : défilement horizontal au doigt sur mobile --}}
        <ul class="flex min-w-0 items-center gap-1 overflow-x-auto overscroll-x-contain snap-x no-scrollbar rounded-full border border-slate-200/70 bg-white/70 p-1 shadow-sm backdrop-blur-xl">
            @foreach($module['items'] as $item)
                <li class="snap-start shrink-0">
                    <a
                        href="{{ $item['url'] }}"
                        @if($item['active']) aria-current="page" @endif
                        class="inline-flex items-center h-8 px-3 rounded-full text-xs whitespace-nowrap transition-colors {{ $item['active'] ? 'bg-[#0B0F14] text-white font-semibold' : 'text-slate-600 font-medium hover:bg-slate-100 hover:text-slate-900 active:bg-slate-100' }}"
                    >{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Fondu de bord indiquant le défilement (mobile) --}}
    <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-linear-to-l from-[#F5F7FA] to-transparent dark:from-black md:hidden"></div>
</nav>
@endif
