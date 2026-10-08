@props([
    'title',
    'description',
    'badge' => null,
    'number' => null,
])

<div {{ $attributes->merge(['class' => 'group relative bg-white rounded-2xl border border-[#E2E8F0] p-6 hover:border-[#0066FF]/60 hover:shadow-lg transition-all duration-200 flex flex-col justify-between']) }}>
    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="w-12 h-12 rounded-xl bg-[#F5F7FA] group-hover:bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center transition">
                {{ $slot }}
            </div>
            @if($badge)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-[#0066FF] border border-blue-100">
                    {{ $badge }}
                </span>
            @elseif($number)
                <span class="text-xs font-mono font-medium text-slate-400 group-hover:text-[#0066FF] transition">
                    #{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                </span>
            @endif
        </div>

        <h3 class="text-base font-bold text-[#0B0F14] group-hover:text-[#0066FF] transition tracking-tight">
            {{ $title }}
        </h3>

        <p class="mt-2 text-xs sm:text-sm text-[#64748B] leading-relaxed">
            {{ $description }}
        </p>
    </div>

    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-[#0B0F14] group-hover:text-[#0066FF] transition">
        <span>Explorer le module</span>
        <svg class="w-3.5 h-3.5 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
        </svg>
    </div>
</div>
