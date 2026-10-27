@php
    $categories = \App\Models\EvaluationSheet::CATEGORIES;
    $shortLabels = ['direction' => 'Dir.', 'rh' => 'RH', 'fonction' => 'Resp.', 'pairs' => 'Pairs'];
    $isManager = auth()->user()->hasRole('administrateur', 'responsable_rh');
    $isLocked = $evaluation->is_locked;

    $steps = [
        'en_evaluation' => ['Fiches en cours', 'Les évaluateurs notent au fil du mois'],
        'validation_manager' => ['Consolidation', 'Du 1er au 3 du mois suivant'],
        'valide_rh' => ['Entretien', 'Du 3 au 5 : note expliquée et plan de progrès'],
        'verrouille' => ['Clôturée', 'Contestation possible sous 48 heures'],
    ];
    $stepKeys = array_keys($steps);
    $currentStep = array_search($evaluation->workflow_step ?? 'en_evaluation', $stepKeys);
    $currentStep = $currentStep === false ? -1 : $currentStep;

    $scoreCell = fn (?float $value): string => $value === null ? '—' : number_format($value, 2, ',', '');
    $scoreClass = fn (?float $value): string => match (true) {
        $value === null => 'text-[#CBD5E1]',
        $value >= 4 => 'text-emerald-700',
        $value >= 3 => 'text-[#0B0F14]',
        $value >= 2 => 'text-amber-700',
        default => 'text-rose-700',
    };

    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10 disabled:bg-[#F8FAFC] disabled:text-[#94A3B8]';
    $label = 'mb-1.5 block text-xs font-medium text-[#0B0F14]';
    $primaryButton = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/20';
    $ghostButton = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]';
@endphp

<x-layouts.app>
    <x-slot:title>Évaluation de {{ $employee->full_name }} — {{ $evaluation->month_name }} {{ $evaluation->year }}</x-slot>

    <div x-data="{ goalOpen: false, pipOpen: false }" class="space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        <x-evaluation.header
            :title="$employee->full_name"
            :subtitle="$evaluation->month_name . ' ' . $evaluation->year . ' · ' . ($employee->position ?? 'Membre') . ' · ' . ($employee->department ?? 'Général')"
            :back-url="route('rh.evaluations.index', ['year' => $evaluation->year, 'month' => $evaluation->month])"
            back-label="Évaluations"
        >
            <x-slot:actions>
                <a href="{{ route('rh.evaluations.history', $employee) }}" class="{{ $ghostButton }}">Historique</a>
                <a href="{{ route('rh.evaluations.print', $evaluation) }}" target="_blank" class="{{ $ghostButton }}">Imprimer</a>
                @if($isLocked)
                    @if($isManager)
                        <form action="{{ route('rh.evaluations.unlock', $evaluation) }}" method="POST" onsubmit="return confirm('Rouvrir cette note pour correction ?');">
                            @csrf
                            <button type="submit" class="{{ $ghostButton }}">Rouvrir</button>
                        </form>
                    @endif
                @else
                    <form action="{{ route('rh.evaluations.lock', $evaluation) }}" method="POST" onsubmit="return confirm('Clôturer cette note ? Le délai de contestation de 48 heures commencera.');">
                        @csrf
                        <button type="submit" class="{{ $primaryButton }}">Clôturer la note</button>
                    </form>
                @endif
            </x-slot:actions>
        </x-evaluation.header>

        {{-- Alertes du dossier --}}
        @if($isLocked || $evaluation->is_trial || $evaluation->requiresFormalInterview())
            <div class="space-y-2">
                @if($isLocked)
                    <p class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 text-xs text-[#334155]">
                        <strong class="text-[#0B0F14]">Note clôturée</strong> le {{ $evaluation->locked_at?->format('d/m/Y à H:i') }} par {{ $evaluation->lockedByUser?->name ?? 'la Direction' }} — fiche en lecture seule.
                    </p>
                @endif
                @if($evaluation->is_trial)
                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                        <strong>Mois d'essai</strong> — la note est donnée et expliquée, sans conséquence (ni classement, ni récompense).
                    </p>
                @endif
                @if($evaluation->requiresFormalInterview())
                    <p class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-900">
                        <strong>Deux mois de suite sous 10 / 20</strong> — entretien formel et mesures prévues par le règlement intérieur, selon la procédure.
                    </p>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

            {{-- ───────── Panneau latéral : note, blocs, avancement ───────── --}}
            <aside class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:sticky xl:top-6 xl:order-last xl:block xl:space-y-4 xl:self-start">
                <x-evaluation.hero :glow="$evaluation->isScored() && $evaluation->final_score_20 < 10 ? 'bg-rose-500/25' : 'bg-[#0066FF]/30'">
                    <div class="flex items-center gap-5">
                        <x-evaluation.score-ring :score="$evaluation->isScored() ? $evaluation->final_score_20 : null" on-dark />
                        <div class="min-w-0 space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Note du mois</p>
                            <p class="text-2xl font-semibold tracking-tight">{{ $evaluation->appreciation }}</p>
                            @if((float) $evaluation->score_progress !== 0.0)
                                <p class="text-xs {{ $evaluation->score_progress > 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                                    {{ $evaluation->score_progress > 0 ? '↗ +' : '↘ ' }}{{ number_format($evaluation->score_progress, 2, ',', '') }} vs mois précédent
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($evaluation->follow_up)
                        <p class="mt-4 text-xs leading-relaxed text-slate-300">{{ $evaluation->follow_up }}</p>
                    @endif

                    <div class="mt-5 border-t border-white/10 pt-4">
                        <x-evaluation.block-bars :evaluation="$evaluation" on-dark />
                        <p class="mt-3 text-[11px] text-slate-500">4 × (0,40 A + 0,25 B + 0,25 C + 0,10 D)</p>
                    </div>
                </x-evaluation.hero>

                <x-evaluation.section title="Avancement">
                    <ol class="space-y-3">
                        @foreach($steps as $key => [$stepLabel, $stepHint])
                            @php $done = $loop->index <= $currentStep; @endphp
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold {{ $done ? 'bg-[#0066FF] text-white' : 'border border-[#CBD5E1] text-[#94A3B8]' }}">
                                    @if($done)
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium {{ $done ? 'text-[#0B0F14]' : 'text-[#64748B]' }}">{{ $stepLabel }}</p>
                                    <p class="text-[11px] text-[#94A3B8]">{{ $stepHint }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </x-evaluation.section>

                <x-evaluation.section title="Fiches reçues">
                    @unless($isLocked)
                        <x-slot:actions>
                            <a href="{{ route('rh.evaluation-sheets.index', ['year' => $evaluation->year, 'month' => $evaluation->month]) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Remplir une fiche</a>
                        </x-slot:actions>
                    @endunless
                    @php $sheetsByGroup = $evaluation->sheets->groupBy('evaluator_group'); @endphp
                    @if($sheetsByGroup->isEmpty())
                        <p class="text-xs text-[#64748B]">Aucune fiche pour le moment.</p>
                    @else
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach($sheetsByGroup as $group => $groupSheets)
                                <li class="rounded-full bg-[#F5F7FA] px-2.5 py-1 text-[11px] font-medium text-[#334155]">
                                    {{ \App\Models\EvaluationSheet::GROUPS[$group]['label'] ?? $group }}
                                    @if($groupSheets->count() > 1)<span class="text-[#64748B]">× {{ $groupSheets->count() }}</span>@endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-evaluation.section>
            </aside>

            {{-- ───────── Contenu principal ───────── --}}
            <div class="min-w-0 space-y-6">

                {{-- Tableau de consolidation --}}
                <x-evaluation.section title="Tableau de consolidation" subtitle="Chaque critère est noté de 1 à 5 par les évaluateurs qui l'observent ; l'auto-évaluation ne compte pas" :padding="false">
                    <div class="hidden grid-cols-[minmax(0,1fr)_repeat(5,3.75rem)_4.25rem] items-end gap-2 border-b border-[#E2E8F0] px-5 py-2.5 text-[10px] font-medium uppercase tracking-wide text-[#94A3B8] md:grid">
                        <span>Critère</span>
                        @foreach($categories as $categoryKey => $category)
                            <span class="text-center" title="{{ $category['label'] }}">{{ $shortLabels[$categoryKey] }}<br><span class="normal-case">{{ $category['weight'] }} %</span></span>
                        @endforeach
                        <span class="text-center">Auto</span>
                        <span class="text-center text-[#0B0F14]">Note</span>
                    </div>

                    @foreach($consolidation as $block => $rows)
                        @php $blockScore = $evaluation->{'block_'.strtolower($block).'_score'}; @endphp
                        <div class="flex items-center justify-between gap-3 bg-[#F8FAFC] px-4 py-2.5 sm:px-5">
                            <p class="text-xs font-semibold text-[#0B0F14]">
                                Bloc {{ $block }} <span class="font-normal text-[#64748B]">· {{ \App\Models\EvaluationCriterion::BLOCKS[$block]['label'] }} · {{ \App\Models\EvaluationCriterion::BLOCKS[$block]['weight'] * 100 }} %</span>
                            </p>
                            <span class="text-xs font-semibold {{ $scoreClass($blockScore === null ? null : (float) $blockScore) }}">{{ $blockScore === null ? 'n.c.' : number_format($blockScore, 2, ',', '').' / 5' }}</span>
                        </div>

                        <ul class="divide-y divide-[#E2E8F0]">
                            @foreach($rows as $row)
                                <li x-data="{ open: false }" class="px-4 py-3 sm:px-5">
                                    <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 md:grid-cols-[minmax(0,1fr)_repeat(5,3.75rem)_4.25rem]">
                                        <div class="min-w-0">
                                            <p class="text-sm text-[#0B0F14]">
                                                <span class="mr-1 font-mono text-xs text-[#94A3B8]">{{ $row['criterion']->code }}</span>{{ $row['criterion']->name }}
                                            </p>
                                            @if($row['justification'])
                                                <p class="mt-0.5 text-[11px] text-[#0066FF]">{{ $row['justification'] }}</p>
                                            @endif
                                            @if($row['observations'])
                                                <button type="button" @click="open = !open" class="mt-1 text-[11px] font-medium text-[#64748B] hover:text-[#0066FF]" :aria-expanded="open">
                                                    <span x-text="open ? 'Masquer' : 'Voir'"></span> {{ count($row['observations']) }} observation(s)
                                                </button>
                                            @endif
                                        </div>

                                        @foreach($row['categories'] as $categoryScore)
                                            <span class="hidden text-center text-sm md:block {{ $scoreClass($categoryScore) }}">{{ $scoreCell($categoryScore) }}</span>
                                        @endforeach
                                        <span class="hidden text-center text-sm text-[#94A3B8] md:block">{{ $row['self'] ?? '—' }}</span>
                                        <span class="justify-self-end rounded-lg bg-[#F1F5F9] px-2.5 py-1 text-center text-sm font-semibold md:justify-self-center {{ $scoreClass($row['score']) }}">{{ $row['score'] === null ? 'n.c.' : number_format($row['score'], 2, ',', '') }}</span>
                                    </div>

                                    {{-- Détail par évaluateur sur mobile --}}
                                    <dl class="mt-2 grid grid-cols-5 gap-1 md:hidden">
                                        @foreach($row['categories'] as $categoryKey => $categoryScore)
                                            <div class="rounded-lg bg-[#F8FAFC] px-1 py-1.5 text-center">
                                                <dt class="text-[9px] text-[#94A3B8]">{{ $shortLabels[$categoryKey] }}</dt>
                                                <dd class="text-xs font-medium {{ $scoreClass($categoryScore) }}">{{ $scoreCell($categoryScore) }}</dd>
                                            </div>
                                        @endforeach
                                        <div class="rounded-lg bg-[#F8FAFC] px-1 py-1.5 text-center">
                                            <dt class="text-[9px] text-[#94A3B8]">Auto</dt>
                                            <dd class="text-xs font-medium text-[#94A3B8]">{{ $row['self'] ?? '—' }}</dd>
                                        </div>
                                    </dl>

                                    @if($row['observations'])
                                        <ul x-show="open" x-collapse x-cloak class="mt-2 space-y-1.5">
                                            @foreach($row['observations'] as $observation)
                                                <li class="rounded-lg border-l-2 border-[#0066FF]/40 bg-[#F8FAFC] px-3 py-2 text-xs text-[#334155]">
                                                    <span class="font-medium text-[#0B0F14]">{{ $observation['group'] }}</span>
                                                    @if($observation['score'])<span class="text-[#64748B]"> · {{ $observation['score'] }}/5</span>@endif
                                                    — {{ $observation['text'] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </x-evaluation.section>

                {{-- Entretien individuel --}}
                <form action="{{ route('rh.evaluations.update', $evaluation) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-evaluation.section title="Entretien individuel" subtitle="15 minutes du 3 au 5 du mois suivant : note expliquée, points forts, plan de progrès">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach([
                                'strengths' => ['Point(s) fort(s)', 'Ex. a formé un collègue à la monétique le 23 octobre'],
                                'weaknesses' => ['Point(s) à progresser', 'Ex. doit écrire ses heures dans chaque bilan'],
                                'recommendations' => ['Plan de progrès', 'Actions, référent, formation…'],
                                'future_goals' => ['Objectifs du mois suivant', 'Cap à atteindre le mois prochain'],
                            ] as $field => [$fieldLabel, $placeholder])
                                <div>
                                    <label for="{{ $field }}" class="{{ $label }}">{{ $fieldLabel }}</label>
                                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" @disabled($isLocked) placeholder="{{ $placeholder }}" class="{{ $input }}">{{ old($field, $evaluation->{$field}) }}</textarea>
                                </div>
                            @endforeach
                            <div class="md:col-span-2">
                                <label for="manager_comment" class="{{ $label }}">Synthèse de l'entretien</label>
                                <textarea id="manager_comment" name="manager_comment" rows="2" @disabled($isLocked) class="{{ $input }}">{{ old('manager_comment', $evaluation->manager_comment) }}</textarea>
                            </div>
                        </div>

                        @unless($isLocked)
                            <div class="mt-5 flex flex-col gap-3 border-t border-[#E2E8F0] pt-4 sm:flex-row sm:items-end">
                                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="workflow_step" class="{{ $label }}">Étape</label>
                                        <select id="workflow_step" name="workflow_step" class="{{ $input }}">
                                            @foreach($steps as $key => [$stepLabel])
                                                @continue($key === 'verrouille')
                                                <option value="{{ $key }}" @selected($evaluation->workflow_step === $key)>{{ $loop->iteration }}. {{ $stepLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="status" class="{{ $label }}">Statut</label>
                                        <select id="status" name="status" class="{{ $input }}">
                                            <option value="en_cours" @selected($evaluation->status === 'en_cours')>En cours</option>
                                            <option value="valide" @selected($evaluation->status === 'valide')>Validée</option>
                                            <option value="cloture" @selected($evaluation->status === 'cloture')>Clôturée</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="{{ $primaryButton }}">Enregistrer l'entretien</button>
                            </div>
                        @endunless
                    </x-evaluation.section>
                </form>

                {{-- Plan de progrès --}}
                @php $needsPlan = $evaluation->isScored() && $evaluation->final_score_20 < 10; @endphp
                @if($needsPlan || $employee->improvementPlans->isNotEmpty())
                    <x-evaluation.section title="Plan de progrès" :subtitle="$needsPlan ? 'Moins de 10 / 20 : plan de 30 jours, avec un référent et une formation' : 'Plans en cours pour ce membre'">
                        <x-slot:actions>
                            <button type="button" @click="pipOpen = true" class="text-xs font-semibold text-[#0066FF] hover:underline">Ouvrir un plan</button>
                        </x-slot:actions>
                        <div class="space-y-3">
                            @forelse($employee->improvementPlans as $pip)
                                <article class="rounded-xl border border-[#E2E8F0] p-4 text-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-medium text-[#0B0F14]">{{ $pip->title }}</p>
                                        <span class="rounded-full bg-[#F5F7FA] px-2.5 py-0.5 text-[11px] font-medium text-[#334155]">{{ ucfirst(str_replace('_', ' ', $pip->status)) }} · {{ $pip->progress_pct }} %</span>
                                    </div>
                                    <div class="mt-2 h-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-[#0066FF]" style="width: {{ $pip->progress_pct }}%"></div></div>
                                    <dl class="mt-3 grid grid-cols-1 gap-2 text-[#334155] sm:grid-cols-2">
                                        <div><dt class="text-[#94A3B8]">Difficulté</dt><dd>{{ $pip->problem_identified }}</dd></div>
                                        <div><dt class="text-[#94A3B8]">Objectif</dt><dd>{{ $pip->target_objective }}</dd></div>
                                    </dl>
                                    <p class="mt-2 text-[11px] text-[#94A3B8]">{{ $pip->start_date?->format('d/m/Y') }} → {{ $pip->end_date?->format('d/m/Y') }} · {{ $pip->supervisor?->name ?? 'Référent RH' }}</p>
                                </article>
                            @empty
                                <p class="text-xs text-[#64748B]">Aucun plan ouvert.</p>
                            @endforelse
                        </div>
                    </x-evaluation.section>
                @endif

                {{-- Objectifs --}}
                <x-evaluation.section title="Objectifs" subtitle="1 à 3 objectifs ciblés, fixés à l'entretien">
                    <x-slot:actions>
                        <button type="button" @click="goalOpen = true" class="text-xs font-semibold text-[#0066FF] hover:underline">Ajouter</button>
                    </x-slot:actions>
                    <div class="divide-y divide-[#E2E8F0]">
                        @forelse($employee->goals as $goal)
                            <div class="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm text-[#0B0F14]">{{ $goal->title }}</p>
                                    <p class="text-[11px] text-[#94A3B8]">Échéance {{ $goal->due_date?->format('d/m/Y') }} · {{ ucfirst(str_replace('_', ' ', $goal->status)) }}</p>
                                </div>
                                <div class="flex items-center gap-2 sm:w-40">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-[#0066FF]" style="width: {{ $goal->progress_pct }}%"></div></div>
                                    <span class="w-9 text-right text-xs font-medium text-[#0B0F14]">{{ $goal->progress_pct }} %</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[#64748B]">Aucun objectif fixé.</p>
                        @endforelse
                    </div>
                </x-evaluation.section>

                {{-- Signatures --}}
                <x-evaluation.section title="Signatures" subtitle="La note est signée par le membre et la Gérante à l'issue de l'entretien">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach([
                            'manager' => ['La Gérante', $evaluation->manager_signature, $evaluation->manager_signed_at, auth()->user()?->name],
                            'employee' => ['Le membre', $evaluation->employee_signature, $evaluation->employee_signed_at, $employee->full_name],
                        ] as $signerType => [$signerLabel, $signature, $signedAt, $defaultName])
                            <div class="rounded-xl border border-[#E2E8F0] p-4">
                                <p class="text-xs font-medium text-[#64748B]">{{ $signerLabel }}</p>
                                @if($signature)
                                    <p class="mt-1.5 flex items-center gap-1.5 text-sm font-medium text-emerald-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ $signature }}
                                    </p>
                                    <p class="text-[11px] text-[#94A3B8]">le {{ $signedAt?->format('d/m/Y à H:i') }}</p>
                                @else
                                    <form action="{{ route('rh.evaluations.sign', $evaluation) }}" method="POST" class="mt-2 flex gap-2">
                                        @csrf
                                        <input type="hidden" name="signer_type" value="{{ $signerType }}">
                                        <input type="text" name="signature_name" value="{{ $defaultName }}" required aria-label="Nom du signataire" class="{{ $input }} min-w-0">
                                        <button type="submit" class="{{ $ghostButton }} shrink-0">Signer</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-evaluation.section>

                {{-- Contestation --}}
                <x-evaluation.section title="Contestation" subtitle="Écrite, dans les 48 heures après la clôture ; la Gérante répond par écrit">
                    @if($evaluation->contested_at)
                        <div class="space-y-3 text-sm">
                            <div class="rounded-xl bg-amber-50 px-4 py-3 text-amber-900">
                                <p class="text-[11px] font-medium text-amber-700">Contestation du {{ $evaluation->contested_at->format('d/m/Y à H:i') }}</p>
                                <p class="mt-1">{{ $evaluation->contestation }}</p>
                            </div>
                            @if($evaluation->contestation_answered_at)
                                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-emerald-900">
                                    <p class="text-[11px] font-medium text-emerald-700">Réponse de {{ $evaluation->contestationAnsweredBy?->name ?? 'la Gérante' }} le {{ $evaluation->contestation_answered_at->format('d/m/Y à H:i') }}</p>
                                    <p class="mt-1">{{ $evaluation->contestation_response }}</p>
                                </div>
                            @elseif(auth()->user()->hasRole('administrateur'))
                                <form action="{{ route('rh.evaluations.answer-contestation', $evaluation) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <label for="contestation_response" class="{{ $label }}">Réponse écrite</label>
                                    <textarea id="contestation_response" name="contestation_response" rows="3" required class="{{ $input }}">{{ old('contestation_response') }}</textarea>
                                    @error('contestation_response')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                                    <button type="submit" class="{{ $primaryButton }}">Envoyer la réponse</button>
                                </form>
                            @else
                                <p class="text-xs text-[#64748B]">En attente de la réponse écrite de la Gérante.</p>
                            @endif
                        </div>
                    @elseif($isLocked)
                        <p class="text-xs text-[#64748B]">Aucune contestation. Délai ouvert jusqu'au {{ $evaluation->contestationDeadline()?->format('d/m/Y à H:i') }}.</p>
                    @else
                        <p class="text-xs text-[#64748B]">Le délai de 48 heures s'ouvrira à la clôture de la note.</p>
                    @endif
                </x-evaluation.section>
            </div>
        </div>

        {{-- ───────── Fenêtres : objectif et plan de progrès (feuille en bas sur mobile) ───────── --}}
        @foreach([
            'goalOpen' => ['Nouvel objectif', route('rh.goals.store')],
            'pipOpen' => ['Ouvrir un plan de progrès', route('rh.pips.store')],
        ] as $flag => [$modalTitle, $action])
            <div x-show="{{ $flag }}" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" @keydown.escape.window="{{ $flag }} = false">
                <div x-show="{{ $flag }}" x-transition.opacity class="absolute inset-0 bg-[#0B0F14]/40 backdrop-blur-xs" @click="{{ $flag }} = false"></div>
                <div x-show="{{ $flag }}" x-transition class="relative max-h-[90vh] w-full overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-[#0B0F14]">{{ $modalTitle }}</h3>
                        <button type="button" @click="{{ $flag }} = false" class="rounded-lg p-1.5 text-[#94A3B8] hover:bg-[#F1F5F9] hover:text-[#0B0F14]" aria-label="Fermer">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form action="{{ $action }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $evaluation->employee_id }}">
                        <input type="hidden" name="monthly_evaluation_id" value="{{ $evaluation->id }}">
                        <div>
                            <label class="{{ $label }}">Intitulé</label>
                            <input type="text" name="title" required class="{{ $input }}">
                        </div>
                        @if($flag === 'goalOpen')
                            <input type="hidden" name="status" value="a_faire">
                            <div>
                                <label class="{{ $label }}">Description</label>
                                <textarea name="description" rows="2" class="{{ $input }}"></textarea>
                            </div>
                        @else
                            <div>
                                <label class="{{ $label }}">Difficulté constatée</label>
                                <textarea name="problem_identified" rows="2" required class="{{ $input }}"></textarea>
                            </div>
                            <div>
                                <label class="{{ $label }}">Objectif mesurable</label>
                                <textarea name="target_objective" rows="2" required class="{{ $input }}"></textarea>
                            </div>
                            <div>
                                <label class="{{ $label }}">Actions, référent et formation</label>
                                <textarea name="corrective_actions" rows="2" required class="{{ $input }}"></textarea>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $label }}">Début</label>
                                <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">{{ $flag === 'goalOpen' ? 'Échéance' : 'Fin' }}</label>
                                <input type="date" name="{{ $flag === 'goalOpen' ? 'due_date' : 'end_date' }}" value="{{ now()->addDays(30)->format('Y-m-d') }}" required class="{{ $input }}">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="{{ $flag }} = false" class="{{ $ghostButton }}">Annuler</button>
                            <button type="submit" class="{{ $primaryButton }}">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
