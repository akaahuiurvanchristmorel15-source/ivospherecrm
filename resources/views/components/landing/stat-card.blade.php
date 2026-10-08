@props([
    'number',
    'title',
    'description' => null,
    'metric' => null,
])

<div {{ $attributes->merge(['class' => 'relative bg-white/[0.03] border border-white/10 rounded-2xl p-6 sm:p-8 hover:border-[#0066FF]/60 hover:bg-white/[0.05] transition-all duration-300 flex flex-col justify-between overflow-hidden group']) }}>
    <!-- Ambient glow hover -->
    <div class="absolute -top-12 -right-12 w-28 h-28 bg-[#0066FF]/10 rounded-full blur-2xl group-hover:bg-[#0066FF]/20 transition"></div>

    <div>
        <div class="flex items-baseline justify-between mb-4">
            <span class="text-4xl sm:text-5xl lg:text-6xl font-black text-[#0066FF] tracking-tight tabular-nums">
                {{ $number }}
            </span>
            @if($metric)
                <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-full bg-[#0066FF]/10 text-[#0066FF] border border-[#0066FF]/30">
                    {{ $metric }}
                </span>
            @endif
        </div>

        <h3 class="text-base sm:text-lg font-bold text-white tracking-tight group-hover:text-blue-100 transition">
            {{ $title }}
        </h3>

        @if($description)
            <p class="mt-2 text-xs sm:text-sm text-[#64748B] group-hover:text-slate-400 transition leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>
</div>
