@php
    $companyAverage = (float) ($stats['companyAverage'] ?? 0);
    $evaluatedCount = (int) ($stats['evaluatedCount'] ?? 0);
    $totalActive = (int) ($stats['totalActiveEmployees'] ?? 0);
    $coveragePct = $totalActive > 0 ? min(100, round($evaluatedCount / $totalActive * 100)) : 0;
    $gaugePct = min(100, max(0, round($companyAverage / 20 * 100)));

    $scoreTone = fn (float $score): array => match (true) {
        $score >= 16 => ['text' => 'text-emerald-600', 'bg' => 'bg-emerald-500', 'soft' => 'bg-emerald-50 text-emerald-700', 'stroke' => '#10b981'],
        $score >= 14 => ['text' => 'text-[#0066FF]', 'bg' => 'bg-[#0066FF]', 'soft' => 'bg-blue-50 text-[#0066FF]', 'stroke' => '#0066FF'],
        $score >= 12 => ['text' => 'text-amber-600', 'bg' => 'bg-amber-500', 'soft' => 'bg-amber-50 text-amber-700', 'stroke' => '#f59e0b'],
        default => ['text' => 'text-rose-600', 'bg' => 'bg-rose-500', 'soft' => 'bg-rose-50 text-rose-700', 'stroke' => '#f43f5e'],
    };
    $companyTone = $scoreTone($companyAverage);

    $currentPeriod = \Carbon\Carbon::create($year, $month, 1);
    $previousPeriod = $currentPeriod->copy()->subMonth();
    $nextPeriod = $currentPeriod->copy()->addMonth();
    $periodQuery = fn (\Carbon\Carbon $date): array => array_merge(request()->query(), ['year' => $date->year, 'month' => $date->month]);

    $workflow = [
        'verrouille' => ['Verrouillé', 'bg-slate-100 text-slate-700'],
        'valide_rh' => ['Validé RH', 'bg-emerald-50 text-emerald-700'],
        'validation_manager' => ['Valid. manager', 'bg-purple-50 text-purple-700'],
        'en_evaluation' => ['En évaluation', 'bg-blue-50 text-[#0066FF]'],
    ];

    $tabs = [
        'all' => ['Toutes', $evaluations->total(), 'bg-slate-900 text-white'],
        'progressing' => ['En progression', $stats['progressingCount'] ?? 0, 'bg-emerald-600 text-white'],
        'follow_up' => ['À suivre', $stats['followUpCount'] ?? 0, 'bg-rose-600 text-white'],
        'locked' => ['Verrouillées', $stats['lockedCount'] ?? 0, 'bg-slate-700 text-white'],
    ];

    $tools = [
        ['rh.evaluations.criteria.index', [], 'Critères', 'text-slate-500', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
        ['rh.goals.index', [], 'Objectifs', 'text-emerald-600', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['rh.pips.index', [], 'Plans PIP', 'text-amber-600', 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['rh.evaluations.ranking', ['year' => $year, 'month' => $month], 'Palmarès', 'text-purple-600', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
    ];

    $departmentAverages = collect($stats['departmentAverages'] ?? [])->sortByDesc('avg_score');
    $trend = collect($stats['historicalTrend'] ?? [])->map(fn ($t) => ['label' => $t['label'], 'score' => (float) ($t['average'] ?? $t['avg'] ?? 0)]);
    $trendMax = 20;
@endphp

<x-layouts.app>
    <x-slot:title>Évaluations RH — IVOSPHERE</x-slot>

    <div class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Évaluations'],
            ]" />

            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Évaluations des collaborateurs</h1>
                    <p class="text-sm text-[#64748B]">Performances mensuelles normalisées, validation et suivi des progressions.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- Navigation par mois --}}
                    <div class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] bg-white p-1">
                        <a href="{{ route('rh.evaluations.index', $periodQuery($previousPeriod)) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14]" aria-label="Mois précédent">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="min-w-32 px-2 text-center text-sm font-semibold text-[#0B0F14]">{{ $monthNames[$month] }} {{ $year }}</span>
                        <a href="{{ route('rh.evaluations.index', $periodQuery($nextPeriod)) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14]" aria-label="Mois suivant">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <form action="{{ route('rh.evaluations.generate') }}" method="POST" class="flex-1 sm:flex-none" onsubmit="return confirm('Synchroniser et préremplir les évaluations pour {{ $monthNames[$month] }} {{ $year }} ?');">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white shadow-xs transition hover:bg-[#0052cc]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="whitespace-nowrap">Générer<span class="hidden sm:inline"> la campagne</span></span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Outils du module --}}
            <nav aria-label="Outils d'évaluation" class="no-scrollbar -mx-3.5 flex gap-2 overflow-x-auto px-3.5 sm:mx-0 sm:flex-wrap sm:px-0">
                @foreach($tools as [$routeName, $params, $label, $tint, $icon])
                    <a href="{{ route($routeName, $params) }}" class="inline-flex shrink-0 items-center gap-2 rounded-full border border-[#E2E8F0] bg-white px-3.5 py-2 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                        <svg class="h-4 w-4 {{ $tint }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </header>

        {{-- ───────── Synthèse ───────── --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12" aria-label="Synthèse de la campagne">
            {{-- Jauge moyenne entreprise --}}
            <article class="relative overflow-hidden rounded-3xl bg-[#0B0F14] p-5 text-white sm:p-6 lg:col-span-5">
                <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-[#0066FF]/30 blur-3xl"></div>
                <div class="relative flex items-center gap-5 sm:gap-6">
                    <div class="relative h-28 w-28 shrink-0 sm:h-32 sm:w-32">
                        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"/>
                            @if($gaugePct > 0)
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $companyTone['stroke'] }}" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $gaugePct }} 100"/>
                            @endif
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-semibold tracking-tight">{{ number_format($companyAverage, 1, ',', ' ') }}</span>
                            <span class="text-[11px] text-slate-400">/ 20</span>
                        </div>
                    </div>
                    <div class="min-w-0 space-y-2">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Moyenne entreprise</p>
                        <p class="text-lg font-semibold leading-snug">{{ $companyAverage >= 14 ? 'Niveau satisfaisant' : 'Vigilance requise' }}</p>
                        <p class="text-xs text-slate-400">Soit <strong class="font-semibold text-white">{{ number_format($companyAverage * 1.5, 1, ',', ' ') }} / 30</strong> · objectif 15 / 20</p>
                    </div>
                </div>

                <div class="relative mt-5 space-y-2 border-t border-white/10 pt-4">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Couverture de la campagne</span>
                        <span class="font-semibold">{{ $evaluatedCount }} / {{ $totalActive }} <span class="font-normal text-slate-400">({{ $coveragePct }} %)</span></span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-[#3B8BFF]" style="width: {{ $coveragePct }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400">{{ $stats['pendingCount'] ?? 0 }} fiche(s) en attente de validation</p>
                </div>
            </article>

            {{-- Compteurs --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:col-span-3 lg:grid-cols-1">
                <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'progressing'])) }}" class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-emerald-300">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                    <div class="mt-3">
                        <p class="text-2xl font-semibold text-[#0B0F14]">{{ $stats['progressingCount'] ?? 0 }}</p>
                        <p class="text-xs text-[#64748B]">En progression vs mois précédent</p>
                    </div>
                </a>
                <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'follow_up'])) }}" class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-rose-300">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div class="mt-3">
                        <p class="text-2xl font-semibold {{ ($stats['followUpCount'] ?? 0) > 0 ? 'text-rose-600' : 'text-[#0B0F14]' }}">{{ $stats['followUpCount'] ?? 0 }}</p>
                        <p class="text-xs text-[#64748B]">À suivre · candidats au PIP</p>
                    </div>
                </a>
            </div>

            {{-- Tendance 6 mois --}}
            <article class="flex flex-col rounded-2xl border border-[#E2E8F0] bg-white p-5 lg:col-span-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Tendance globale</h2>
                        <p class="text-xs text-[#64748B]">Moyenne entreprise sur 6 mois</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-[#0066FF]">/ 20</span>
                </div>

                @if($trend->isNotEmpty())
                    <div class="mt-5 flex-1">
                        <div class="relative flex h-28 items-end gap-2.5 border-b border-[#E2E8F0]">
                            {{-- Ligne objectif 15/20 --}}
                            <div class="pointer-events-none absolute inset-x-0 border-t border-dashed border-emerald-500/70" style="bottom: {{ 15 / $trendMax * 100 }}%"></div>
                            @foreach($trend as $point)
                                @php $barPct = min(100, max(4, round($point['score'] / $trendMax * 100))); @endphp
                                <div class="group relative flex h-full flex-1 items-end">
                                    <span class="absolute inset-x-0 text-center text-[10px] font-semibold text-[#0B0F14] transition {{ $loop->last ? '' : 'opacity-0 group-hover:opacity-100' }}" style="bottom: calc({{ $barPct }}% + 4px)">{{ number_format($point['score'], 1, ',', '') }}</span>
                                    <div class="w-full rounded-t-lg transition {{ $loop->last ? 'bg-[#0066FF]' : 'bg-blue-100 group-hover:bg-blue-200' }}" style="height: {{ $barPct }}%"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-1.5 flex gap-2.5">
                            @foreach($trend as $point)
                                <span class="flex-1 text-center text-[10px] font-medium {{ $loop->last ? 'text-[#0066FF]' : 'text-[#64748B]' }}">{{ $point['label'] }}</span>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-[11px] text-[#64748B]">
                        <span class="inline-block w-4 border-t border-dashed border-emerald-500"></span>
                        Objectif standard 15 / 20
                    </p>
                @else
                    <p class="flex flex-1 items-center justify-center py-8 text-xs text-[#64748B]">Pas encore d'historique.</p>
                @endif
            </article>
        </section>

        {{-- ───────── Performances par service ───────── --}}
        <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6" aria-labelledby="dept-title">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h2 id="dept-title" class="text-sm font-semibold text-[#0B0F14]">Performances par service</h2>
                    <p class="text-xs text-[#64748B]">Moyenne sur 20 · {{ $monthNames[$month] }} {{ $year }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-[#F5F7FA] px-2.5 py-1 text-[11px] font-medium text-[#64748B]">{{ $departmentAverages->count() }} service(s)</span>
            </div>

            @if($departmentAverages->isNotEmpty())
                <ul class="grid grid-cols-1 gap-x-10 gap-y-4 md:grid-cols-2">
                    @foreach($departmentAverages as $deptName => $deptData)
                        @php
                            $deptScore = (float) $deptData['avg_score'];
                            $deptTone = $scoreTone($deptScore);
                        @endphp
                        <li class="space-y-1.5">
                            <div class="flex items-baseline justify-between gap-3 text-xs">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="w-4 shrink-0 text-[11px] font-semibold text-[#64748B]">{{ $loop->iteration }}</span>
                                    <span class="truncate font-medium text-[#0B0F14]">{{ $deptName }}</span>
                                    <span class="shrink-0 text-[11px] text-[#64748B]">· {{ $deptData['count'] }} évalué(s)</span>
                                </span>
                                <span class="shrink-0 font-semibold {{ $deptTone['text'] }}">{{ number_format($deptScore, 2, ',', ' ') }}</span>
                            </div>
                            <div class="ml-6 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $deptTone['bg'] }}" style="width: {{ min(100, round($deptScore / 20 * 100)) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="py-6 text-center text-xs text-[#64748B]">Aucune note de service calculée pour cette période.</p>
            @endif
        </section>

        {{-- ───────── Fiches ───────── --}}
        <section class="space-y-3" aria-labelledby="sheets-title">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h2 id="sheets-title" class="px-1 text-sm font-semibold text-[#0B0F14]">Fiches d'évaluation</h2>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    {{-- Onglets --}}
                    <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0" role="tablist">
                        @foreach($tabs as $key => [$label, $count, $activeClass])
                            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => $key, 'page' => null])) }}"
                               role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                               class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-medium transition {{ $tab === $key ? $activeClass : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">
                                {{ $label }}
                                <span class="rounded-full px-1.5 text-[10px] font-semibold {{ $tab === $key ? 'bg-white/20' : 'bg-[#F5F7FA] text-[#0B0F14]' }}">{{ $count }}</span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Filtre département --}}
                    <form method="GET" action="{{ route('rh.evaluations.index') }}" x-data>
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <label for="evalDepartment" class="sr-only">Département</label>
                        <select id="evalDepartment" name="department" @change="$el.form.submit()"
                                class="h-9 w-full rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-52">
                            <option value="">Tous les départements</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" @selected(request('department') == $dept)>{{ $dept }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="sr-only">Filtrer</button></noscript>
                    </form>
                </div>
            </div>

            @if($evaluations->isEmpty())
                <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white py-12">
                    <x-empty-state
                        title="Aucune évaluation trouvée"
                        description="Cliquez sur « Générer la campagne » pour préremplir les fiches de ce mois."
                    />
                </div>
            @else
                {{-- Tableau desktop --}}
                <div class="hidden overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white md:block">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-[#E2E8F0] text-[11px] font-medium text-[#64748B]">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Collaborateur</th>
                                    <th class="px-3 py-3 text-center font-medium">Jours</th>
                                    <th class="px-3 py-3 text-center font-medium">Ponctualité</th>
                                    <th class="px-3 py-3 font-medium">Moyenne / 20</th>
                                    <th class="px-3 py-3 text-center font-medium">Progression</th>
                                    <th class="px-3 py-3 font-medium">Statut</th>
                                    <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @foreach($evaluations as $eval)
                                    @php
                                        $score20 = (float) ($eval->final_score_20 ?: ($eval->final_score_30 / 1.5));
                                        $tone = $scoreTone($score20);
                                        $prog = $eval->score_progress;
                                        [$wfLabel, $wfClass] = $workflow[$eval->workflow_step] ?? ['Brouillon', 'bg-amber-50 text-amber-700'];
                                    @endphp
                                    <tr class="group transition hover:bg-[#F5F7FA]/70">
                                        <td class="px-5 py-3.5">
                                            <a href="{{ route('rh.evaluations.show', $eval) }}" class="flex items-center gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold {{ $tone['soft'] }}">
                                                    {{ strtoupper(substr($eval->employee->first_name ?? 'E', 0, 1) . substr($eval->employee->last_name ?? '', 0, 1)) }}
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="flex items-center gap-1.5 text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">
                                                        {{ $eval->employee->full_name }}
                                                        @if($eval->is_locked)
                                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-label="Verrouillé"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                        @endif
                                                    </span>
                                                    <span class="block truncate text-[11px] text-[#64748B]">{{ $eval->employee->department ?? 'Général' }} · {{ $eval->employee->position ?? 'Collaborateur' }}</span>
                                                </span>
                                            </a>
                                        </td>
                                        <td class="px-3 py-3.5 text-center text-[#64748B]">{{ $eval->total_working_days }} j</td>
                                        <td class="px-3 py-3.5 text-center font-medium text-[#0B0F14]">{{ number_format($eval->punctuality_points, 1, ',', '') }}</td>
                                        <td class="px-3 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <span class="w-11 text-sm font-semibold {{ $tone['text'] }}">{{ number_format($score20, 2, ',', '') }}</span>
                                                <div class="hidden h-1.5 w-20 overflow-hidden rounded-full bg-slate-100 lg:block">
                                                    <div class="h-full rounded-full {{ $tone['bg'] }}" style="width: {{ min(100, round($score20 / 20 * 100)) }}%"></div>
                                                </div>
                                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $eval->appreciation_badge_class }}">{{ $eval->appreciation }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            @if(is_null($prog))
                                                <span class="text-slate-400">—</span>
                                            @elseif($prog > 0)
                                                <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">↗ +{{ number_format($prog, 2, ',', '') }}</span>
                                            @elseif($prog < 0)
                                                <span class="inline-flex items-center gap-0.5 rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">↘ {{ number_format($prog, 2, ',', '') }}</span>
                                            @else
                                                <span class="text-[11px] text-slate-400">0,00</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-medium {{ $wfClass }}">{{ $wfLabel }}</span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('rh.evaluations.history', $eval->employee) }}" class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-blue-50 hover:text-[#0066FF]" title="Historique">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                                </a>
                                                <a href="{{ route('rh.evaluations.print', $eval) }}" target="_blank" class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-blue-50 hover:text-[#0066FF]" title="Imprimer le rapport A4">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                </a>
                                                <a href="{{ route('rh.evaluations.show', $eval) }}" class="ml-1 inline-flex items-center gap-1 rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                                                    Fiche
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Cartes mobile --}}
                <ul class="space-y-2.5 md:hidden">
                    @foreach($evaluations as $eval)
                        @php
                            $score20 = (float) ($eval->final_score_20 ?: ($eval->final_score_30 / 1.5));
                            $tone = $scoreTone($score20);
                            $prog = $eval->score_progress;
                            [$wfLabel, $wfClass] = $workflow[$eval->workflow_step] ?? ['Brouillon', 'bg-amber-50 text-amber-700'];
                            $ringPct = min(100, max(0, round($score20 / 20 * 100)));
                        @endphp
                        <li class="rounded-2xl border border-[#E2E8F0] bg-white">
                            <a href="{{ route('rh.evaluations.show', $eval) }}" class="flex items-center gap-3.5 p-4 active:bg-[#F5F7FA]">
                                <div class="relative h-14 w-14 shrink-0">
                                    <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#E2E8F0" stroke-width="3.5"/>
                                        @if($ringPct > 0)
                                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $tone['stroke'] }}" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="{{ $ringPct }} 100"/>
                                        @endif
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-sm font-semibold {{ $tone['text'] }}">{{ number_format($score20, 1, ',', '') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="flex items-center gap-1.5 truncate text-sm font-medium text-[#0B0F14]">
                                        {{ $eval->employee->full_name }}
                                        @if($eval->is_locked)
                                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-label="Verrouillé"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        @endif
                                    </p>
                                    <p class="truncate text-[11px] text-[#64748B]">{{ $eval->employee->department ?? 'Général' }} · {{ $eval->total_working_days }} j · ponctualité {{ number_format($eval->punctuality_points, 1, ',', '') }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $wfClass }}">{{ $wfLabel }}</span>
                                        @if(!is_null($prog) && $prog != 0)
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $prog > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                {{ $prog > 0 ? '↗ +' : '↘ ' }}{{ number_format($prog, 2, ',', '') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <div class="flex border-t border-[#E2E8F0] text-xs font-medium text-[#64748B]">
                                <a href="{{ route('rh.evaluations.history', $eval->employee) }}" class="flex flex-1 items-center justify-center gap-1.5 py-2.5 active:bg-[#F5F7FA]">Historique</a>
                                <a href="{{ route('rh.evaluations.print', $eval) }}" target="_blank" class="flex flex-1 items-center justify-center gap-1.5 border-l border-[#E2E8F0] py-2.5 active:bg-[#F5F7FA]">Imprimer</a>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if($evaluations->hasPages())
                    <div class="pt-2">{{ $evaluations->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.app>
