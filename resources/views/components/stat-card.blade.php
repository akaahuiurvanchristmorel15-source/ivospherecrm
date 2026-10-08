@props([
    'title',
    'value',
    'change' => null,      // e.g. "+12.5%" or "-3.2%"
    'changeType' => 'up',  // 'up', 'down', 'neutral'
    'timeframe' => 'ce mois',
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-5 flex flex-col justify-between hover:border-slate-300 transition-all']) }}>
    <div class="flex items-center justify-between gap-2">
        <span class="text-[10px] sm:text-[11px] uppercase tracking-wider text-[#64748B] font-medium">
            {{ $title }}
        </span>
        @if($icon)
            <div class="w-8 h-8 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]/80 flex items-center justify-center text-[#64748B] shrink-0">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-3 sm:mt-4">
        <div class="text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-[#0B0F14]">
            {{ $value }}
        </div>

        @if($change !== null)
            <div class="mt-2 flex items-center gap-1.5 text-[11px]">
                @if($changeType === 'up')
                    <span class="inline-flex items-center font-medium text-emerald-600">
                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        {{ $change }}
                    </span>
                @elseif($changeType === 'down')
                    <span class="inline-flex items-center font-medium text-rose-600">
                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        {{ $change }}
                    </span>
                @else
                    <span class="font-medium text-[#64748B]">{{ $change }}</span>
                @endif

                @if($timeframe)
                    <span class="text-slate-400 font-normal">· {{ $timeframe }}</span>
                @endif
            </div>
        @endif
    </div>
</div>
