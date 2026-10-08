@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<header class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div class="space-y-1">
        @if(!empty($breadcrumbs))
            <div class="mb-1.5">
                <x-breadcrumb :items="$breadcrumbs" />
            </div>
        @endif

        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-[#0B0F14]">
            {!! $title !!}
        </h1>

        @if($description)
            <p class="text-xs sm:text-sm text-[#64748B] leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap shrink-0">
            {{ $actions }}
        </div>
    @endif
</header>
