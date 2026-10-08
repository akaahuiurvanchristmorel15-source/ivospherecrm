@props([
    'title',
    'description',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#E2E8F0] p-6 hover:border-[#0066FF] hover:shadow-md transition-all duration-200 flex flex-col justify-between']) }}>
    <div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0066FF] flex items-center justify-center mb-4">
            {{ $slot }}
        </div>
        <h3 class="text-base sm:text-lg font-bold text-[#0B0F14] tracking-tight">
            {{ $title }}
        </h3>
        <p class="mt-2 text-xs sm:text-sm text-[#64748B] leading-relaxed">
            {{ $description }}
        </p>
    </div>
</div>
