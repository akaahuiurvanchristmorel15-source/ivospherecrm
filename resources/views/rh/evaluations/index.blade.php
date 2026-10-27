@php
    $companyAverage = (float) ($stats['companyAverage'] ?? 0);
    $evaluatedCount = (int) ($stats['evaluatedCount'] ?? 0);
    $totalActive = (int) ($stats['totalActiveEmployees'] ?? 0);

    $scoreTone = fn (float $score): array => match (true) {
        $score >= 16 => ['text' => 'text-emerald-600', 'bg' => 'bg-emerald-500', 'soft' => 'bg-emerald-50 text-emerald-700'],
        $score >= 14 => ['text' => 'text-[#0066FF]', 'bg' => 'bg-[#0066FF]', 'soft' => 'bg-blue-50 text-[#0066FF]'],
        $score >= 10 => ['text' => 'text-amber-600', 'bg' => 'bg-amber-500', 'soft' => 'bg-amber-50 text-amber-700'],
        default => ['text' => 'text-rose-600', 'bg' => 'bg-rose-500', 'soft' => 'bg-rose-50 text-rose-700'],
    };

    $currentPeriod = \Carbon\Carbon::create($year, $month, 1);
    $periodQuery = fn (\Carbon\Carbon $date): array => array_merge(request()->query(), ['year' => $date->year, 'month' => $date->month, 'page' => null]);

    $workflow = [
        'verrouille' => ['Clôturée', 'bg-slate-100 text-slate-700'],
        'valide_rh' => ['Validée RH', 'bg-emerald-50 text-emerald-700'],
        'validation_manager' => ['Validation', 'bg-purple-50 text-purple-700'],
        'en_evaluation' => ['En évaluation', 'bg-blue-50 text-[#0066FF]'],
    ];

    $tabs = [
        'all' => ['Toutes', $evaluations->total()],
        'progressing' => ['En progression', $stats['progressingCount'] ?? 0],
        'follow_up' => ['À suivre', $stats['followUpCount'] ?? 0],
        'locked' => ['Clôturées', $stats['lockedCount'] ?? 0],
    ];

    // Déroulé de fin de mois du référentiel (section 7)
    $flowSteps = [
        ['Campagne générée', "{$evaluatedCount} / {$totalActive}", 'Du 1er au dernier jour', $evaluatedCount > 0 && $evaluatedCount >= $totalActive],
        ['Fiches remises', (string) $flow['sheets'], 'Avant-dernier et dernier jour ouvrable', $flow['sheets'] > 0],
        ['Notes consolidées', "{$flow['scored']} / {$evaluatedCount}", 'Du 1er au 3 du mois suivant', $evaluatedCount > 0 && $flow['scored'] >= $evaluatedCount],
        ['Entretiens & clôture', "{$flow['locked']} / {$evaluatedCount}", 'Du 3 au 5', $evaluatedCount > 0 && $flow['locked'] >= $evaluatedCount],
        ['Contestations à traiter', (string) $flow['pendingContestations'], 'Sous 48 heures', $flow['pendingContestations'] === 0 && $flow['locked'] > 0],
    ];

    $departmentAverages = collect($stats['departmentAverages'] ?? [])->sortByDesc('avg_score');
    $trend = collect($stats['historicalTrend'] ?? [])->map(fn ($t) => ['label' => $t['label'], 'score' => (float) ($t['average'] ?? $t['avg'] ?? 0)]);
    $blocks = array_keys(\App\Models\EvaluationCriterion::BLOCKS);
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
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Évaluations des membres</h1>
                    <p class="text-sm text-[#64748B]">Note du mois sur 20 = 4 × (0,40 A + 0,25 B + 0,25 C + 0,10 D).</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] bg-white p-1">
                        <a href="{{ route('rh.evaluations.index', $periodQuery($currentPeriod->copy()->subMonth())) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14]" aria-label="Mois précédent">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <span class="min-w-32 px-2 text-center text-sm font-semibold text-[#0B0F14]">{{ $monthNames[$month] }} {{ $year }}</span>
                        <a href="{{ route('rh.evaluations.index', $periodQuery($currentPeriod->copy()->addMonth())) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14]" aria-label="Mois suivant">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <form action="{{ route('rh.evaluations.generate') }}" method="POST" class="flex-1 sm:flex-none" onsubmit="return confirm('Générer ou mettre à jour la campagne de {{ $monthNames[$month] }} {{ $year }} ?');">
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

            @include('rh.evaluations._nav', ['active' => 'campaign'])
        </header>

        @if(\App\Models\MonthlyEvaluation::isTrialMonth($year, $month))
            <p class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                <strong>Mois d'essai :</strong> les notes sont données et expliquées, sans conséquence (pas de membre du mois ni de récompense).
            </p>
        @endif

        {{-- ───────── Déroulé du mois ───────── --}}
        <section aria-labelledby="flow-title" class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
            <h2 id="flow-title" class="text-sm font-semibold text-[#0B0F14]">Déroulé du mois</h2>
            <ol class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach($flowSteps as [$label, $value, $when, $done])
                    <li class="rounded-xl border p-3 {{ $done ? 'border-emerald-200 bg-emerald-50/50' : 'border-[#E2E8F0] bg-[#F8FAFC]' }}">
                        <p class="text-[11px] font-medium text-[#64748B]">{{ $loop->iteration }}. {{ $label }}</p>
                        <p class="mt-1 text-lg font-semibold {{ $done ? 'text-emerald-700' : 'text-[#0B0F14]' }}">{{ $value }}</p>
                        <p class="text-[10px] text-[#94A3B8]">{{ $when }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ───────── Synthèse ───────── --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4" aria-label="Synthèse du mois">
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <p class="text-xs text-[#64748B]">Moyenne des membres notés</p>
                <p class="mt-1 text-2xl font-semibold {{ $scoreTone($companyAverage)['text'] }}">{{ number_format($companyAverage, 2, ',', ' ') }} <span class="text-sm font-normal text-[#94A3B8]">/ 20</span></p>
                <p class="text-[11px] text-[#64748B]">{{ $companyAverage > 0 ? \App\Models\MonthlyEvaluation::computeAppreciation($companyAverage) : 'Aucune note consolidée' }}</p>
            </div>
            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'progressing', 'page' => null])) }}" class="rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-emerald-300">
                <p class="text-xs text-[#64748B]">En progression vs mois précédent</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-600">{{ $stats['progressingCount'] ?? 0 }}</p>
                <p class="text-[11px] text-[#64748B]">Voir la liste →</p>
            </a>
            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => 'follow_up', 'page' => null])) }}" class="rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-rose-300">
                <p class="text-xs text-[#64748B]">À suivre (sous 10 / 20 ou en forte baisse)</p>
                <p class="mt-1 text-2xl font-semibold {{ ($stats['followUpCount'] ?? 0) > 0 ? 'text-rose-600' : 'text-[#0B0F14]' }}">{{ $stats['followUpCount'] ?? 0 }}</p>
                <p class="text-[11px] text-[#64748B]">Plan de progrès de 30 jours →</p>
            </a>
        </section>

        {{-- ───────── Membres ───────── --}}
        <section class="space-y-3" aria-labelledby="members-title">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h2 id="members-title" class="px-1 text-sm font-semibold text-[#0B0F14]">Membres évalués</h2>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0" role="tablist">
                        @foreach($tabs as $key => [$label, $count])
                            <a href="{{ route('rh.evaluations.index', array_merge(request()->query(), ['tab' => $key, 'page' => null])) }}"
                               role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                               class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-medium transition {{ $tab === $key ? 'bg-slate-900 text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">
                                {{ $label }}
                                <span class="rounded-full px-1.5 text-[10px] font-semibold {{ $tab === $key ? 'bg-white/20' : 'bg-[#F5F7FA] text-[#0B0F14]' }}">{{ $count }}</span>
                            </a>
                        @endforeach
                    </div>

                    <form method="GET" action="{{ route('rh.evaluations.index') }}" x-data>
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <label for="evalDepartment" class="sr-only">Service</label>
                        <select id="evalDepartment" name="department" @change="$el.form.submit()"
                                class="h-9 w-full rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-48">
                            <option value="">Tous les services</option>
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
                        title="Aucune évaluation pour ce mois"
                        description="Cliquez sur « Générer la campagne » pour ouvrir les fiches du mois."
                    />
                </div>
            @else
                {{-- Tableau desktop --}}
                <div class="hidden overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white md:block">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-[#E2E8F0] text-[11px] font-medium text-[#64748B]">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Membre</th>
                                    @foreach($blocks as $block)
                                        <th class="px-2 py-3 text-center font-medium">{{ $block }}</th>
                                    @endforeach
                                    <th class="px-3 py-3 font-medium">Note / 20</th>
                                    <th class="px-3 py-3 text-center font-medium">Évolution</th>
                                    <th class="px-3 py-3 text-center font-medium">Fiches</th>
                                    <th class="px-3 py-3 font-medium">Statut</th>
                                    <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @foreach($evaluations as $eval)
                                    @php
                                        $score20 = (float) $eval->final_score_20;
                                        $tone = $scoreTone($score20);
                                        $prog = (float) $eval->score_progress;
                                        [$wfLabel, $wfClass] = $workflow[$eval->workflow_step] ?? ['Brouillon', 'bg-amber-50 text-amber-700'];
                                    @endphp
                                    <tr class="group transition hover:bg-[#F5F7FA]/70">
                                        <td class="px-5 py-3.5">
                                            <a href="{{ route('rh.evaluations.show', $eval) }}" class="block min-w-0">
                                                <span class="flex items-center gap-1.5 text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">
                                                    {{ $eval->employee->full_name }}
                                                    @if($eval->contested_at && ! $eval->contestation_answered_at)
                                                        <span class="rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">Contestée</span>
                                                    @endif
                                                </span>
                                                <span class="block truncate text-[11px] text-[#64748B]">{{ $eval->employee->department ?? 'Général' }} · {{ $eval->employee->position ?? 'Membre' }}</span>
                                            </a>
                                        </td>
                                        @foreach($blocks as $block)
                                            @php $blockScore = $eval->{'block_'.strtolower($block).'_score'}; @endphp
                                            <td class="px-2 py-3.5 text-center text-[#64748B]">{{ $blockScore !== null ? number_format($blockScore, 1, ',', '') : '—' }}</td>
                                        @endforeach
                                        <td class="px-3 py-3.5">
                                            @if($eval->isScored())
                                                <div class="flex items-center gap-2">
                                                    <span class="w-11 text-sm font-semibold {{ $tone['text'] }}">{{ number_format($score20, 2, ',', '') }}</span>
                                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $eval->appreciation_badge_class }}">{{ $eval->appreciation }}</span>
                                                </div>
                                            @else
                                                <span class="text-[11px] text-[#94A3B8]">Pas encore noté</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            @if($prog > 0)
                                                <span class="text-[11px] font-semibold text-emerald-700">↗ +{{ number_format($prog, 2, ',', '') }}</span>
                                            @elseif($prog < 0)
                                                <span class="text-[11px] font-semibold text-rose-700">↘ {{ number_format($prog, 2, ',', '') }}</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5 text-center font-medium text-[#0B0F14]">{{ $eval->sheets_count }}</td>
                                        <td class="px-3 py-3.5">
                                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-medium {{ $wfClass }}">{{ $wfLabel }}</span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('rh.evaluations.history', $eval->employee) }}" class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-blue-50 hover:text-[#0066FF]" title="Historique">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                                </a>
                                                <a href="{{ route('rh.evaluations.print', $eval) }}" target="_blank" class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-blue-50 hover:text-[#0066FF]" title="Imprimer">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                </a>
                                                <a href="{{ route('rh.evaluations.show', $eval) }}" class="ml-1 inline-flex items-center gap-1 rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                                                    Fiche
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
                            $score20 = (float) $eval->final_score_20;
                            [$wfLabel, $wfClass] = $workflow[$eval->workflow_step] ?? ['Brouillon', 'bg-amber-50 text-amber-700'];
                        @endphp
                        <li>
                            <a href="{{ route('rh.evaluations.show', $eval) }}" class="flex items-center gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4 active:bg-[#F5F7FA]">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $eval->employee->full_name }}</p>
                                    <p class="truncate text-[11px] text-[#64748B]">{{ $eval->employee->department ?? 'Général' }} · {{ $eval->sheets_count }} fiche(s)</p>
                                    <span class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium {{ $wfClass }}">{{ $wfLabel }}</span>
                                </div>
                                <div class="text-right">
                                    @if($eval->isScored())
                                        <p class="text-lg font-semibold {{ $scoreTone($score20)['text'] }}">{{ number_format($score20, 1, ',', '') }}</p>
                                        <p class="text-[10px] text-[#64748B]">{{ $eval->appreciation }}</p>
                                    @else
                                        <p class="text-[11px] text-[#94A3B8]">Pas encore noté</p>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if($evaluations->hasPages())
                    <div class="pt-2">{{ $evaluations->links() }}</div>
                @endif
            @endif
        </section>

        {{-- ───────── Tendances ───────── --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2" aria-label="Tendances">
            <article class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <h2 class="text-sm font-semibold text-[#0B0F14]">Moyenne sur 6 mois</h2>
                @if($trend->isNotEmpty())
                    <div class="mt-5">
                        <div class="relative flex h-24 items-end gap-2.5 border-b border-[#E2E8F0]">
                            <div class="pointer-events-none absolute inset-x-0 border-t border-dashed border-emerald-500/70" style="bottom: 70%" title="14 / 20"></div>
                            @foreach($trend as $point)
                                @php $barPct = min(100, max(4, round($point['score'] / 20 * 100))); @endphp
                                <div class="group relative flex h-full flex-1 items-end">
                                    <span class="absolute inset-x-0 text-center text-[10px] font-semibold text-[#0B0F14] {{ $loop->last ? '' : 'opacity-0 group-hover:opacity-100' }}" style="bottom: calc({{ $barPct }}% + 4px)">{{ number_format($point['score'], 1, ',', '') }}</span>
                                    <div class="w-full rounded-t-lg {{ $loop->last ? 'bg-[#0066FF]' : 'bg-blue-100 group-hover:bg-blue-200' }}" style="height: {{ $barPct }}%"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-1.5 flex gap-2.5">
                            @foreach($trend as $point)
                                <span class="flex-1 text-center text-[10px] font-medium {{ $loop->last ? 'text-[#0066FF]' : 'text-[#64748B]' }}">{{ $point['label'] }}</span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-[11px] text-[#64748B]">Pointillé : 14 / 20 (« Très bien », seuil du membre du mois)</p>
                    </div>
                @else
                    <p class="py-8 text-center text-xs text-[#64748B]">Pas encore d'historique.</p>
                @endif
            </article>

            <article class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <h2 class="text-sm font-semibold text-[#0B0F14]">Moyenne par service</h2>
                @if($departmentAverages->isNotEmpty())
                    <ul class="mt-4 space-y-3">
                        @foreach($departmentAverages as $deptName => $deptData)
                            @php $deptScore = (float) $deptData['avg_score']; @endphp
                            <li class="space-y-1">
                                <div class="flex items-baseline justify-between gap-3 text-xs">
                                    <span class="truncate font-medium text-[#0B0F14]">{{ $deptName }} <span class="font-normal text-[#64748B]">· {{ $deptData['count'] }}</span></span>
                                    <span class="font-semibold {{ $scoreTone($deptScore)['text'] }}">{{ number_format($deptScore, 2, ',', ' ') }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $scoreTone($deptScore)['bg'] }}" style="width: {{ min(100, round($deptScore / 20 * 100)) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="py-8 text-center text-xs text-[#64748B]">Aucune note consolidée pour ce mois.</p>
                @endif
            </article>
        </section>
    </div>
</x-layouts.app>
