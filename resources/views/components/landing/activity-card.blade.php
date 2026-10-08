@props([
    'name',
    'tagline',
    'description',
    'features' => [],
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'group bg-white rounded-2xl border border-[#E2E8F0] p-6 sm:p-7 hover:border-[#0066FF] hover:shadow-xl transition-all duration-300 flex flex-col justify-between relative overflow-hidden']) }}>
    <!-- Accent line on top hover -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-[#0066FF] scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>

    <div>
        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="w-14 h-14 rounded-2xl bg-[#0B0F14] text-white group-hover:bg-[#0066FF] flex items-center justify-center transition-colors duration-300 shadow-xs">
                {{ $slot }}
            </div>
            @if($badge)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $badge }}
                </span>
            @endif
        </div>

        <div class="space-y-1 mb-3">
            <h3 class="text-lg sm:text-xl font-bold text-[#0B0F14] tracking-tight group-hover:text-[#0066FF] transition">
                {{ $name }}
            </h3>
            <p class="text-xs sm:text-sm font-semibold text-[#0066FF]">
                {{ $tagline }}
            </p>
        </div>

        <p class="text-xs sm:text-sm text-[#64748B] leading-relaxed mb-5">
            {{ $description }}
        </p>

        @if(!empty($features))
            <ul class="space-y-2 mb-6 pt-3 border-t border-slate-100">
                @foreach($features as $feature)
                    <li class="flex items-center gap-2 text-xs text-[#0B0F14] font-medium">
                        <svg class="w-3.5 h-3.5 text-[#0066FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="pt-2">
        <button type="button" 
                @click="$dispatch('open-demo-modal', { pole: '{{ $name }}' })"
                class="w-full h-11 rounded-xl border border-[#E2E8F0] group-hover:border-[#0066FF] group-hover:bg-[#0066FF] group-hover:text-white bg-slate-50 text-xs font-semibold text-[#0B0F14] transition flex items-center justify-center gap-2 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF]">
            <span>Découvrir le pôle</span>
            <svg class="w-3.5 h-3.5 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </button>
    </div>
</div>
