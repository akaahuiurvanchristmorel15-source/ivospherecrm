@php
    $monthNames = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    $groups = \App\Models\EvaluationSheet::GROUPS;
    $period = \Carbon\Carbon::create($year, $month, 1);

    $selfRow = $evaluations->first(fn (array $row) => $row['groups'] === [\App\Models\EvaluationSheet::GROUP_SELF]);
    $others = $evaluations->reject(fn (array $row) => $row === $selfRow)->values();

    $possible = $others->sum(fn (array $row) => count($row['groups']));
    $done = $others->sum(fn (array $row) => $row['evaluation']->sheets->count());
@endphp

<x-layouts.app>
    <x-slot:title>Fiches d'évaluation — {{ $monthNames[$month] }} {{ $year }}</x-slot>

    <div x-data="{ search: '' }" class="mx-auto max-w-4xl space-y-6">
        <x-evaluation.header
            title="Fiches d'évaluation"
            subtitle="Chaque évaluateur note seulement ce qu'il observe dans son domaine."
            active="sheets" :year="$year" :month="$month"
        >
            <x-slot:actions>
                <div class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] bg-white p-1">
                    <a href="{{ route('rh.evaluation-sheets.index', ['year' => $period->copy()->subMonth()->year, 'month' => $period->copy()->subMonth()->month]) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-[#F1F5F9]" aria-label="Mois précédent">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="min-w-28 px-2 text-center text-sm font-semibold text-[#0B0F14]">{{ $monthNames[$month] }} {{ $year }}</span>
                    <a href="{{ route('rh.evaluation-sheets.index', ['year' => $period->copy()->addMonth()->year, 'month' => $period->copy()->addMonth()->month]) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-[#F1F5F9]" aria-label="Mois suivant">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </x-slot:actions>
        </x-evaluation.header>

        @if($possible > 0)
            @php $donePct = round($done / $possible * 100); @endphp
            <x-evaluation.hero>
                <div class="flex items-center gap-5">
                    <div class="relative h-24 w-24 shrink-0">
                        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
                            @if($donePct > 0)
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#3B8BFF" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $donePct }} 100"/>
                            @endif
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-semibold">{{ $donePct }}%</span>
                    </div>
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Vos fiches remises</p>
                        <p class="text-3xl font-semibold tracking-tight">{{ $done }} <span class="text-base font-normal text-slate-400">/ {{ $possible }}</span></p>
                        <p class="text-xs {{ $done >= $possible ? 'text-emerald-300' : 'text-slate-400' }}">{{ $done >= $possible ? 'Toutes vos fiches sont remises' : 'À remettre avant le dernier jour ouvrable' }}</p>
                    </div>
                </div>
            </x-evaluation.hero>
        @endif

        {{-- Auto-évaluation --}}
        @if($selfRow)
            @php $selfDone = $selfRow['evaluation']->sheets->isNotEmpty(); @endphp
            <a href="{{ $selfRow['evaluation']->is_locked ? '#' : route('rh.evaluation-sheets.edit', ['evaluation' => $selfRow['evaluation'], 'group' => 'auto']) }}"
               class="flex items-center gap-4 rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-[#0066FF] sm:p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $selfDone ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-[#0066FF]' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $selfDone ? 'M5 13l4 4L19 7' : 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' }}"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-[#0B0F14]">Mon auto-évaluation</p>
                    <p class="text-xs text-[#64748B]">{{ $selfDone ? 'Remplie — vous pouvez la modifier jusqu\'à la clôture.' : 'Elle prépare votre entretien et ne compte pas dans la note.' }}</p>
                </div>
                <svg class="h-4 w-4 shrink-0 text-[#CBD5E1]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endif

        @if($others->isNotEmpty())
            {{-- Avancement et recherche --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="px-1 text-sm font-semibold text-[#0B0F14]">Membres à évaluer</h2>
                <label class="relative block sm:w-72">
                    <span class="sr-only">Rechercher un membre</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                    <input type="search" x-model="search" placeholder="Rechercher un membre" class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                </label>
            </div>

            <ul class="divide-y divide-[#E2E8F0] overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                @foreach($others as $row)
                    @php
                        $evaluation = $row['evaluation'];
                        $employee = $evaluation->employee;
                        $initials = strtoupper(mb_substr($employee->first_name ?? '', 0, 1).mb_substr($employee->last_name ?? '', 0, 1));
                    @endphp
                    <li x-show="search === '' || @js(mb_strtolower($employee->full_name)).includes(search.toLowerCase())"
                        class="flex flex-col gap-3 p-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex min-w-0 shrink-0 items-center gap-3 lg:w-64">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F5F7FA] text-xs font-semibold text-[#334155]">{{ $initials }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $employee->full_name }}</p>
                                <p class="truncate text-xs text-[#64748B]">
                                    {{ $employee->position ?? 'Membre' }}
                                    @if($evaluation->is_locked)<span class="text-rose-600"> · note clôturée</span>@endif
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 lg:justify-end">
                            @foreach($row['groups'] as $group)
                                @php $filled = $evaluation->sheets->contains('evaluator_group', $group); @endphp
                                @if($evaluation->is_locked)
                                    <span class="inline-flex h-8 items-center gap-1 rounded-full bg-[#F8FAFC] px-3 text-xs text-[#94A3B8]">{{ $groups[$group]['label'] }}</span>
                                @else
                                    <a href="{{ route('rh.evaluation-sheets.edit', ['evaluation' => $evaluation, 'group' => $group]) }}"
                                       class="inline-flex h-8 items-center gap-1.5 rounded-full border px-3 text-xs font-medium transition {{ $filled ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-[#E2E8F0] bg-white text-[#0066FF] hover:border-[#0066FF]' }}">
                                        @if($filled)
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                        {{ $groups[$group]['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        @elseif(! $selfRow)
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-12 text-center">
                <p class="text-sm font-medium text-[#0B0F14]">Aucune fiche à remplir pour ce mois</p>
                <p class="mt-1 text-xs text-[#64748B]">La campagne d'évaluation doit d'abord être générée par les RH.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
