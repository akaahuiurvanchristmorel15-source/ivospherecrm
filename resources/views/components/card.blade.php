@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'footer' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-[#E2E8F0] rounded-[28px] overflow-hidden shadow-[0_20px_50px_-30px_rgba(11,15,20,0.18)]']) }}>
    @if($title || $actions)
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                @if($title)
                    <h3 class="text-[15px] font-semibold tracking-tight text-[#0B0F14] leading-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if($subtitle)
                    <p class="text-[11px] text-[#64748B] mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding ? 'p-5 sm:p-6' : '' }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3.5 sm:px-6 bg-[#F5F7FA]/70 border-t border-slate-100 text-xs text-[#64748B]">
            {{ $footer }}
        </div>
    @endif
</div>
