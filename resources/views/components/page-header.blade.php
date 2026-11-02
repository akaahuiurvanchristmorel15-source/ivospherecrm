@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<header class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div class="min-w-0 space-y-1 sm:min-w-64 sm:flex-1">
        @if(!empty($breadcrumbs))
            <div class="mb-1.5">
                <x-breadcrumb :items="$breadcrumbs" />
            </div>
        @endif

        <h1 class="text-2xl sm:text-4xl font-bold tracking-tighter leading-tight text-[#0B0F14]">
            {{-- Échappé sans réencoder les entités déjà échappées par Blade (title="{{ … }}") --}}
            {!! htmlspecialchars((string) $title, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', false) !!}
        </h1>

        @if($description)
            <p class="text-xs sm:text-sm text-[#64748B] leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex min-w-0 flex-wrap items-center gap-2 sm:justify-end sm:gap-2.5">
            {{ $actions }}
        </div>
    @endif
</header>
