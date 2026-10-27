{{--
    Navigation commune du volet évaluation, dans l'ordre du déroulé mensuel du référentiel :
    campagne du mois → fiches des évaluateurs → classement, puis le suivi (objectifs, plans de progrès) et le référentiel.
    Paramètres : $active (clé de l'onglet courant), $year et $month (facultatifs).
--}}
@php
    $period = array_filter(['year' => $year ?? null, 'month' => $month ?? null]);
    $canManage = auth()->user()?->hasRole('administrateur', 'responsable', 'responsable_rh');

    $steps = [
        'campaign' => ['1. Campagne du mois', 'rh.evaluations.index', $period, true],
        'sheets' => ['2. Fiches des évaluateurs', 'rh.evaluation-sheets.index', $period, false],
        'ranking' => ['3. Classement & récompenses', 'rh.evaluations.ranking', $period, true],
    ];

    $followUp = [
        'goals' => ['Objectifs', 'rh.goals.index', []],
        'pips' => ['Plans de progrès', 'rh.pips.index', []],
        'criteria' => ['Référentiel', 'rh.evaluations.criteria.index', []],
    ];
@endphp

<nav aria-label="Volet évaluation" x-data x-init="$el.querySelector('[aria-current]')?.scrollIntoView({ inline: 'center', block: 'nearest' })" class="no-scrollbar -mx-3.5 flex items-center gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:flex-wrap sm:px-0">
    @foreach($steps as $key => [$label, $routeName, $params, $managersOnly])
        @continue($managersOnly && ! $canManage)
        <a href="{{ route($routeName, $params) }}"
           @if($active === $key) aria-current="page" @endif
           class="inline-flex shrink-0 items-center rounded-full px-3.5 py-2 text-xs font-medium transition {{ $active === $key ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:border-[#0066FF] hover:text-[#0066FF]' }}">
            {{ $label }}
        </a>
    @endforeach

    @if($canManage)
        <span class="mx-1 hidden h-5 w-px bg-[#E2E8F0] sm:inline-block" aria-hidden="true"></span>
        @foreach($followUp as $key => [$label, $routeName, $params])
            <a href="{{ route($routeName, $params) }}"
               @if($active === $key) aria-current="page" @endif
               class="inline-flex shrink-0 items-center rounded-full px-3 py-1.5 text-[11px] font-medium transition {{ $active === $key ? 'bg-slate-200 text-[#0B0F14]' : 'text-[#64748B] hover:text-[#0066FF]' }}">
                {{ $label }}
            </a>
        @endforeach
    @endif
</nav>
