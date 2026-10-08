@props([
    'quote',
    'author',
    'role',
    'company',
    'initials' => 'EX',
    'metric' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#E2E8F0] p-6 sm:p-7 hover:border-[#0066FF]/60 hover:shadow-lg transition-all duration-200 flex flex-col justify-between']) }}>
    <div>
        <!-- Rating & quote icon -->
        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-1 text-[#0066FF]">
                @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endfor
            </div>

            @if($metric)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 tabular-nums">
                    {{ $metric }}
                </span>
            @endif
        </div>

        <blockquote class="text-xs sm:text-sm text-[#0B0F14] leading-relaxed italic mb-6">
            “{{ $quote }}”
        </blockquote>
    </div>

    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
        <div class="w-10 h-10 rounded-full bg-[#0B0F14] text-white flex items-center justify-center font-bold text-xs tracking-wider shrink-0">
            {{ $initials }}
        </div>
        <div class="min-w-0">
            <h4 class="text-xs sm:text-sm font-bold text-[#0B0F14] truncate">
                {{ $author }}
            </h4>
            <p class="text-[11px] sm:text-xs text-[#64748B] truncate">
                {{ $role }} • <span class="text-slate-500 font-medium">{{ $company }}</span>
            </p>
        </div>
    </div>
</div>
