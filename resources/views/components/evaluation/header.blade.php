{{-- En-tête des pages du volet évaluation : fil d'Ariane, titre, sous-titre, actions et navigation du volet. --}}
@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
    'active' => null,
    'year' => null,
    'month' => null,
])

<header class="space-y-4">
    @if($breadcrumbs)
        <x-breadcrumb :items="$breadcrumbs" />
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0 space-y-1">
            <h1 class="text-xl font-semibold tracking-tight text-[#0B0F14] sm:text-2xl">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-[#64748B]">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>

    @if($active)
        @include('rh.evaluations._nav', ['active' => $active, 'year' => $year, 'month' => $month])
    @endif
</header>
