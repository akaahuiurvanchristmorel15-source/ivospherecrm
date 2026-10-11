@php
    $sections = [
        ['horaires', 'Horaires', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['pointage', 'Pointage QR', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
        ['plannings', 'Plannings', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['evaluation', 'Évaluation', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];

    $input = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15';
    $label = 'mb-1.5 block text-xs font-medium text-[#0B0F14]';
    $hint = 'mt-1.5 text-[11px] text-[#64748B]';
    $card = 'scroll-mt-24 rounded-2xl border border-[#E2E8F0] bg-white';
@endphp

<x-layouts.app>
    <x-slot:title>Paramètres RH — IVOSPHERE ERP</x-slot>

    <div class="space-y-6"
         x-data="{
            active: 'horaires',
            punctuality: {{ (float) old('weight_punctuality', $settings->weight_punctuality) }},
            tasks: {{ (float) old('weight_tasks', $settings->weight_tasks) }},
            teamwork: {{ (float) old('weight_teamwork', $settings->weight_teamwork) }},
            autoCheckout: {{ old('auto_checkout_enabled', $settings->auto_checkout_enabled) ? 'true' : 'false' }},
            customSchedules: {{ old('allow_custom_schedules', $settings->allow_custom_schedules) ? 'true' : 'false' }},
            get dailyMax() { return (Number(this.punctuality) || 0) + (Number(this.tasks) || 0) + (Number(this.teamwork) || 0); },
            share(value) { return this.dailyMax > 0 ? Math.round((Number(value) || 0) / this.dailyMax * 100) : 0; },
         }">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Paramètres']]" />
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Paramètres RH</h1>
                    <p class="text-sm text-[#64748B]">Horaires, pointage par QR code, plannings et barème d'évaluation.</p>
                </div>
                <a href="{{ route('rh.attendance.poster') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-4 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Affiche QR du mois
                </a>
            </div>
        </header>

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
                <p class="font-semibold">Veuillez corriger les champs suivants :</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[13rem_1fr]">
            {{-- ───────── Navigation des sections ───────── --}}
            <nav aria-label="Sections des paramètres" class="no-scrollbar -mx-3.5 flex gap-1.5 overflow-x-auto px-3.5 sm:-mx-6 sm:px-6 lg:sticky lg:top-6 lg:mx-0 lg:flex-col lg:self-start lg:overflow-visible lg:px-0">
                @foreach($sections as [$id, $title, $icon])
                    <a href="#{{ $id }}" @click="active = '{{ $id }}'"
                       :class="active === '{{ $id }}' ? 'bg-[#0B0F14] text-white lg:bg-white lg:text-[#0066FF] lg:ring-1 lg:ring-[#E2E8F0]' : 'bg-white text-[#64748B] ring-1 ring-[#E2E8F0] hover:text-[#0B0F14] lg:bg-transparent lg:ring-0 lg:hover:bg-white'"
                       class="inline-flex shrink-0 items-center gap-2.5 rounded-full px-3.5 py-2 text-xs font-medium transition lg:rounded-xl lg:py-2.5">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $title }}
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('rh.settings.update') }}" class="min-w-0 space-y-5 md:pb-24">
                @csrf
                @method('PUT')

                {{-- ───────── 1. Horaires ───────── --}}
                <section id="horaires" class="{{ $card }}">
                    <div class="border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Horaires de travail</h2>
                        <p class="text-xs text-[#64748B]">Heures de référence de l'entreprise et clôture automatique des journées.</p>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="{{ $label }}" for="work_start_time">Début de journée</label>
                                <input type="time" id="work_start_time" name="work_start_time" value="{{ old('work_start_time', $settings->work_start_time) }}" class="{{ $input }}" required>
                                <p class="{{ $hint }}">Heure d'arrivée prévue</p>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="work_end_time">Fin de journée</label>
                                <input type="time" id="work_end_time" name="work_end_time" value="{{ old('work_end_time', $settings->work_end_time) }}" class="{{ $input }}" required>
                                <p class="{{ $hint }}">Heure de départ habituelle</p>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="auto_checkout_time">Départ automatique</label>
                                <input type="time" id="auto_checkout_time" name="auto_checkout_time" value="{{ old('auto_checkout_time', $settings->auto_checkout_time) }}" class="{{ $input }}" :class="autoCheckout ? '' : 'opacity-50'" required>
                                <p class="{{ $hint }}">Si aucun départ n'est scanné</p>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl bg-[#F5F7FA] p-4">
                            <span>
                                <span class="block text-sm font-medium text-[#0B0F14]">Clôture automatique des journées</span>
                                <span class="mt-0.5 block text-xs text-[#64748B]">Enregistre le départ à l'heure configurée pour les collaborateurs qui n'ont pas scanné leur sortie.</span>
                            </span>
                            <input type="checkbox" name="auto_checkout_enabled" value="1" x-model="autoCheckout" class="peer sr-only">
                            <span class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-[#0066FF] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]/40 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                        </label>
                    </div>
                </section>

                {{-- ───────── 2. Pointage QR ───────── --}}
                <section id="pointage" class="{{ $card }}">
                    <div class="border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Pointage par QR code</h2>
                        <p class="text-xs text-[#64748B]">Les collaborateurs pointent en scannant le QR code des locaux, sans géolocalisation.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-3 sm:p-6">
                        <a href="{{ route('rh.attendance.poster') }}" class="group rounded-xl border border-[#E2E8F0] p-4 transition hover:border-emerald-300">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </span>
                            <p class="mt-3 text-sm font-medium text-[#0B0F14] group-hover:text-emerald-700">Affiche mensuelle A4</p>
                            <p class="mt-0.5 text-[11px] text-[#64748B]">Renouvelée chaque 1er du mois, 2 jours de tolérance pour la remplacer.</p>
                        </a>
                        <a href="{{ route('rh.attendance.terminal') }}" target="_blank" class="group rounded-xl border border-[#E2E8F0] p-4 transition hover:border-[#0066FF]/40">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-[#0066FF]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <p class="mt-3 text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">Borne à écran</p>
                            <p class="mt-0.5 text-[11px] text-[#64748B]">QR dynamique renouvelé toutes les {{ $settings->dynamic_qr_refresh_seconds }} s.</p>
                        </a>
                        <div class="rounded-xl border border-[#E2E8F0] p-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </span>
                            <p class="mt-3 text-sm font-medium text-[#0B0F14]">Clé secrète</p>
                            <p class="mt-0.5 text-[11px] text-[#64748B]">Invalide les QR codes en circulation en cas de fuite.</p>
                            <button type="submit" form="regenerateQrForm" class="mt-2 text-xs font-medium text-amber-700 hover:underline">Régénérer la clé</button>
                        </div>
                    </div>
                </section>

                {{-- ───────── 3. Plannings ───────── --}}
                <section id="plannings" class="{{ $card }}">
                    <div class="border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Plannings & jours de travail</h2>
                        <p class="text-xs text-[#64748B]">Garde-fous légaux et organisation des semaines.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <label class="{{ $label }}" for="max_work_days_per_week">Jours travaillés max. par semaine</label>
                            <input type="number" id="max_work_days_per_week" name="max_work_days_per_week" value="{{ old('max_work_days_per_week', $settings->max_work_days_per_week) }}" min="1" max="6" class="{{ $input }}" required>
                            <p class="{{ $hint }}">De 1 à 6 jours — le 7e jour est toujours un jour de repos.</p>
                        </div>
                        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl bg-[#F5F7FA] p-4">
                            <span>
                                <span class="block text-sm font-medium text-[#0B0F14]">Horaires personnalisés</span>
                                <span class="mt-0.5 block text-xs text-[#64748B]">Le responsable peut ajuster début et fin pour certains postes (gardiennage, astreinte…).</span>
                            </span>
                            <input type="checkbox" name="allow_custom_schedules" value="1" x-model="customSchedules" class="peer sr-only">
                            <span class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-[#0066FF] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]/40 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                        </label>
                    </div>
                </section>

                {{-- ───────── 4. Évaluation ───────── --}}
                <section id="evaluation" class="{{ $card }}">
                    <div class="border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Moteur d'évaluation mensuelle</h2>
                        <p class="text-xs text-[#64748B]">Coefficients journaliers et normalisation de la note finale.</p>
                    </div>
                    <div class="space-y-6 p-5 sm:p-6">
                        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                            <div>
                                <label class="{{ $label }}" for="evaluation_day_of_month">Jour d'évaluation</label>
                                <input type="number" id="evaluation_day_of_month" name="evaluation_day_of_month" value="{{ old('evaluation_day_of_month', $settings->evaluation_day_of_month) }}" min="1" max="28" class="{{ $input }}" required>
                                <p class="{{ $hint }}">Du 1 au 28 du mois</p>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="max_evaluation_score">Note maximale</label>
                                <input type="number" step="0.5" id="max_evaluation_score" name="max_evaluation_score" value="{{ old('max_evaluation_score', $settings->max_evaluation_score) }}" class="{{ $input }} font-semibold" required>
                                <p class="{{ $hint }}">Référence (ex. 30)</p>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="punctuality_grace_minutes">Tolérance (min)</label>
                                <input type="number" id="punctuality_grace_minutes" name="punctuality_grace_minutes" value="{{ old('punctuality_grace_minutes', $settings->punctuality_grace_minutes) }}" min="0" max="60" class="{{ $input }}" required>
                                <p class="{{ $hint }}">Considéré à l'heure</p>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="late_threshold_minutes">Retard important (min)</label>
                                <input type="number" id="late_threshold_minutes" name="late_threshold_minutes" value="{{ old('late_threshold_minutes', $settings->late_threshold_minutes) }}" min="1" max="120" class="{{ $input }}" required>
                                <p class="{{ $hint }}">Ponctualité à 0 au-delà</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-3 text-xs font-medium uppercase tracking-wider text-[#64748B]">Points par jour travaillé</h3>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                @foreach([
                                    ['weight_punctuality', 'punctuality', 'Ponctualité', 'Arrivée à l\'heure ou dans la tolérance', 'bg-[#0066FF]'],
                                    ['weight_tasks', 'tasks', 'Tâches validées', 'Au prorata des tâches validées', 'bg-emerald-500'],
                                    ['weight_teamwork', 'teamwork', 'Esprit d\'équipe', 'Attribué par le manager', 'bg-amber-500'],
                                ] as [$field, $model, $title, $description, $color])
                                    <div class="rounded-xl border border-[#E2E8F0] p-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <label for="{{ $field }}" class="flex items-center gap-2 text-sm font-medium text-[#0B0F14]">
                                                <span class="h-2 w-2 rounded-full {{ $color }}"></span>{{ $title }}
                                            </label>
                                            <span class="text-[11px] font-medium text-[#64748B]" x-text="share({{ $model }}) + ' %'"></span>
                                        </div>
                                        <input type="number" step="0.01" min="0" max="1" id="{{ $field }}" name="{{ $field }}" x-model.number="{{ $model }}" class="{{ $input }} mt-3 font-semibold" required>
                                        <p class="{{ $hint }}">{{ $description }}</p>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Répartition visuelle + formule --}}
                            <div class="mt-4 rounded-2xl bg-[#0B0F14] p-5 text-white">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <span class="text-xs text-slate-400">Maximum par jour</span>
                                    <span class="text-2xl font-semibold" x-text="dailyMax.toFixed(2) + ' pt'"></span>
                                </div>
                                <div class="mt-3 flex h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full bg-[#0066FF] transition-all" :style="'width:' + share(punctuality) + '%'"></div>
                                    <div class="h-full bg-emerald-500 transition-all" :style="'width:' + share(tasks) + '%'"></div>
                                    <div class="h-full bg-amber-500 transition-all" :style="'width:' + share(teamwork) + '%'"></div>
                                </div>
                                <p class="mt-4 border-t border-white/10 pt-3 font-mono text-[11px] text-slate-300">
                                    Note finale = (score obtenu ÷ (jours travaillés × maximum par jour)) × note maximale
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ───────── Barre d'enregistrement ───────── --}}
                <div class="z-10 flex md:sticky md:bottom-4 items-center justify-between gap-3 rounded-2xl border border-[#E2E8F0] bg-white/95 p-3 pl-5 shadow-[0_12px_32px_-12px_rgba(15,23,42,0.25)] backdrop-blur">
                    <p class="hidden text-xs text-[#64748B] sm:block">Les modifications s'appliquent aux prochains pointages et évaluations.</p>
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-5 text-sm font-semibold text-white transition hover:bg-[#0052cc] sm:w-auto">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- Formulaire séparé (les formulaires ne peuvent pas être imbriqués) --}}
        <form id="regenerateQrForm" method="POST" action="{{ route('rh.settings.regenerate-qr') }}" class="hidden"
              onsubmit="return confirm('Régénérer la clé secrète ? Les QR codes déjà affichés ou imprimés ne fonctionneront plus.');">
            @csrf
        </form>
    </div>
</x-layouts.app>
