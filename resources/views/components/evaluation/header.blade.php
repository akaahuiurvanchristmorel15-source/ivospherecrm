{{--
    En-tête des pages du volet évaluation, dans le style du tableau de bord RH :
    ligne d'accroche (date ou lien de retour), titre, sous-titre, actions, puis navigation du volet.
--}}
@props([
    'title',
    'subtitle' => null,
    'kicker' => null,
    'backUrl' => null,
    'backLabel' => null,
    'active' => null,
    'year' => null,
    'month' => null,
])

<header class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0 space-y-1">
            @if($backUrl)
                <a href="{{ $backUrl }}" class="inline-flex items-center gap-1 text-xs font-medium text-[#64748B] transition hover:text-[#0066FF]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    {{ $backLabel }}
                </a>
            @else
                <p class="text-xs font-medium capitalize text-[#64748B]">{{ $kicker ?? now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
            @endif
            <h1 class="truncate text-2xl font-semibold tracking-tight text-[#0B0F14]">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-[#64748B]">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">{{ $actions }}</div>
        @endisset
    </div>

    @if($active)
        @include('rh.evaluations._nav', ['active' => $active, 'year' => $year, 'month' => $month])
    @endif
</header>
