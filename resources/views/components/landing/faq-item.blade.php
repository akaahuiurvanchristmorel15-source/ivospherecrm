@props([
    'id',
    'question',
    'answer',
])

<div class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden transition-all duration-200"
     :class="openFaq === '{{ $id }}' ? 'border-[#0066FF] shadow-xs' : 'hover:border-slate-300'">
    <button type="button" 
            @click="openFaq = (openFaq === '{{ $id }}' ? null : '{{ $id }}')"
            :aria-expanded="(openFaq === '{{ $id }}').toString()"
            class="w-full text-left p-5 sm:p-6 flex items-center justify-between gap-4 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#0066FF] rounded-2xl">
        <span class="text-sm sm:text-base font-bold text-[#0B0F14]" 
              :class="openFaq === '{{ $id }}' ? 'text-[#0066FF]' : 'text-[#0B0F14]'">
            {{ $question }}
        </span>
        <div class="w-8 h-8 rounded-xl bg-[#F5F7FA] flex items-center justify-center shrink-0 text-[#0B0F14] transition-transform duration-200"
             :class="openFaq === '{{ $id }}' ? 'rotate-180 bg-blue-50 text-[#0066FF]' : ''">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
    </button>

    <div x-show="openFaq === '{{ $id }}'" 
         x-cloak
         x-collapse
         class="px-5 pb-5 sm:px-6 sm:pb-6 text-xs sm:text-sm text-[#64748B] leading-relaxed border-t border-slate-100 pt-3">
        <p>{{ $answer }}</p>
    </div>
</div>
