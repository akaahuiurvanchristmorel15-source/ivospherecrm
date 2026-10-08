<x-layouts.app>
    <x-slot:title>Mon Espace Collaborateur — IVOSPHERE ERP</x-slot>

    @php
        $initials = mb_substr($employee->first_name, 0, 1).mb_substr($employee->last_name, 0, 1);
        $employeeCode = $employee->employee_code ?? 'EMP-'.str_pad($employee->id, 4, '0', STR_PAD_LEFT);

        $tasks = ($todayTaskSheet && is_array($todayTaskSheet->tasks)) ? $todayTaskSheet->tasks : [];
        $totalCount = count($tasks);
        $validatedCount = collect($tasks)->filter(fn ($t) => is_array($t) && ($t['validation_status'] ?? '') === 'valide')->count();
        $pct = $totalCount > 0 ? round(($validatedCount / $totalCount) * 100) : 0;

        $finalScore = $currentEvaluation ? (float) $currentEvaluation->final_score_30 : 0;
        $workingDays = max(1, (int) ($currentEvaluation?->total_working_days ?? 22));
        $maxPunct = round($workingDays * (float) $settings->weight_punctuality, 2);
        $maxTasks = round($workingDays * (float) $settings->weight_tasks, 2);
        $maxTeam = round($workingDays * (float) $settings->weight_teamwork, 2);
        $gaugePunct = $maxPunct > 0 ? round(((float) ($currentEvaluation?->punctuality_points ?? 0) / $maxPunct) * 10, 1) : 0;
        $gaugeTasks = $maxTasks > 0 ? round(((float) ($currentEvaluation?->tasks_points ?? 0) / $maxTasks) * 10, 1) : 0;
        $gaugeTeam = $maxTeam > 0 ? round(((float) ($currentEvaluation?->teamwork_points ?? 0) / $maxTeam) * 10, 1) : 0;

        $scoreLabel = $finalScore >= 24 ? 'Performance remarquable' : ($finalScore >= 18 ? 'Objectifs atteints' : 'En progression');
        $scoreTone = $finalScore >= 24 ? 'text-emerald-600' : ($finalScore >= 18 ? 'text-[#0066FF]' : 'text-amber-600');
    @endphp

    <div x-data="{ leaveModalOpen: false }" class="max-w-5xl mx-auto space-y-5 sm:space-y-6">

        {{-- En-tête --}}
        <header class="space-y-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-full bg-[#0B0F14] text-white flex items-center justify-center text-sm font-semibold tracking-wide shrink-0">
                    {{ $initials }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] text-[#64748B] capitalize">{{ now()->translatedFormat('l d F') }}</p>
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-[#0B0F14] truncate">Bonjour, {{ $employee->first_name }}</h1>
                    <p class="text-xs text-[#64748B] truncate">
                        {{ $employee->position ?? 'Collaborateur' }}
                        <span class="mx-1 text-slate-300">·</span>
                        <span class="font-mono">{{ $employeeCode }}</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:justify-end gap-2">
                <a href="{{ route('rh.portal.badge', $employee) }}" target="_blank" class="h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-xs font-medium text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Mon badge
                </a>
                <button type="button" @click="leaveModalOpen = true" class="h-10 px-4 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-medium transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m7-7H5"/></svg>
                    Demander un congé
                </button>
            </div>

            @if(isset($allEmployees) && $allEmployees->count() > 1)
                <form method="GET" action="{{ route('rh.portal.index') }}">
                    <label class="sr-only" for="portal-preview">Aperçu collaborateur</label>
                    <select id="portal-preview" name="employee_id" onchange="this.form.submit()" class="w-full sm:w-auto h-9 rounded-lg border border-[#E2E8F0] bg-white text-xs text-[#64748B] px-3 focus:outline-none focus:border-[#0066FF]">
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}" @selected($emp->id === $employee->id)>Aperçu : {{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </header>

        {{-- Indicateurs clés --}}
        <section class="grid grid-cols-3 rounded-2xl border border-[#E2E8F0] bg-white divide-x divide-[#E2E8F0]">
            <div class="p-3.5 sm:p-5">
                <p class="text-[10px] sm:text-[11px] uppercase tracking-wider text-[#64748B]">Arrivée</p>
                <p class="mt-1 text-lg sm:text-2xl font-semibold tabular-nums text-[#0B0F14]">{{ $todayAttendance ? substr($todayAttendance->check_in_time, 0, 5) : '--:--' }}</p>
            </div>
            <div class="p-3.5 sm:p-5">
                <p class="text-[10px] sm:text-[11px] uppercase tracking-wider text-[#64748B]">Tâches</p>
                <p class="mt-1 text-lg sm:text-2xl font-semibold tabular-nums text-[#0B0F14]">{{ $validatedCount }}<span class="text-sm text-slate-400 font-normal">/{{ $totalCount }}</span></p>
            </div>
            <div class="p-3.5 sm:p-5">
                <p class="text-[10px] sm:text-[11px] uppercase tracking-wider text-[#64748B]">Note</p>
                <p class="mt-1 text-lg sm:text-2xl font-semibold tabular-nums text-[#0B0F14]">{{ number_format($finalScore, 1, ',', ' ') }}<span class="text-sm text-slate-400 font-normal">/30</span></p>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">

            <div class="lg:col-span-2 space-y-5 sm:space-y-6">

                {{-- Présence --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Présence du jour</h2>
                            <p class="text-[11px] text-[#64748B] mt-0.5">Zone GPS {{ $settings->geofence_radius_meters }} m · clôture auto 20:00</p>
                        </div>
                        @if(! $todayAttendance)
                            <span class="inline-flex items-center gap-1.5 text-[11px] text-amber-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Non pointé</span>
                        @elseif($todayAttendance->check_out_time)
                            <span class="inline-flex items-center gap-1.5 text-[11px] text-[#64748B] shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Journée close</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Présent</span>
                        @endif
                    </div>

                    @if(! $todayAttendance)
                        <p class="mt-5 text-sm text-[#64748B]">Scannez le QR code de la borne d'accueil en autorisant la localisation.</p>
                        <a href="{{ route('rh.attendance.scanner') }}" class="mt-4 h-12 w-full sm:w-auto sm:inline-flex px-6 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10"/></svg>
                            Pointer mon arrivée
                        </a>
                    @else
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-[#F5F7FA] p-3.5">
                                <p class="text-[11px] text-[#64748B]">Arrivée</p>
                                <p class="mt-0.5 text-2xl font-semibold tabular-nums text-[#0B0F14]">{{ substr($todayAttendance->check_in_time, 0, 5) }}</p>
                                <p class="mt-1 text-[11px] {{ $todayAttendance->is_late ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $todayAttendance->is_late ? 'Retard '.$todayAttendance->late_minutes.' min' : 'À l\'heure · +0,33 pt' }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-[#F5F7FA] p-3.5">
                                <p class="text-[11px] text-[#64748B]">Départ</p>
                                <p class="mt-0.5 text-2xl font-semibold tabular-nums {{ $todayAttendance->check_out_time ? 'text-[#0B0F14]' : 'text-slate-300' }}">
                                    {{ $todayAttendance->check_out_time ? substr($todayAttendance->check_out_time, 0, 5) : '--:--' }}
                                </p>
                                <p class="mt-1 text-[11px] text-[#64748B]">
                                    @if($todayAttendance->check_out_time)
                                        {{ $todayAttendance->check_out_type === 'automatic' ? 'Clôture automatique' : 'Sortie vérifiée' }}
                                    @else
                                        En cours
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if(! $todayAttendance->check_out_time)
                            <a href="{{ route('rh.attendance.scanner') }}" class="mt-4 h-11 w-full rounded-xl border border-[#E2E8F0] text-sm font-medium text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-2">
                                Pointer mon départ
                                <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endif
                    @endif
                </section>

                {{-- Tâches du jour --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Tâches du jour</h2>
                            <p class="text-[11px] text-[#64748B] mt-0.5">Déclarez vos réalisations pour validation</p>
                        </div>
                        @if($totalCount > 0)
                            <span class="text-xs tabular-nums text-[#64748B]">{{ $pct }} %</span>
                        @endif
                    </div>

                    @if($totalCount > 0)
                        <div class="mt-3 h-1 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>

                        <ul class="mt-4 divide-y divide-slate-100">
                            @foreach($tasks as $idx => $t)
                                @php
                                    $title = is_array($t) ? ($t['title'] ?? '') : (string) $t;
                                    $valStatus = is_array($t) ? ($t['validation_status'] ?? 'non_soumis') : 'non_soumis';
                                    $refusalReason = is_array($t) ? ($t['refusal_reason'] ?? null) : null;
                                    $isValidated = $valStatus === 'valide';
                                    $isPending = $valStatus === 'en_attente';
                                    $isRefused = $valStatus === 'refuse';
                                @endphp
                                <li class="py-3.5 flex items-center gap-3">
                                    <span class="w-5 h-5 rounded-full shrink-0 flex items-center justify-center {{ $isValidated ? 'bg-emerald-500 text-white' : ($isPending ? 'border-2 border-amber-400' : 'border-2 border-slate-200') }}">
                                        @if($isValidated)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm {{ $isValidated ? 'line-through text-slate-400' : 'text-[#0B0F14]' }}">{{ $title }}</p>
                                        @if($isValidated)
                                            <p class="text-[11px] text-emerald-600 mt-0.5">Validée · +{{ round(0.23 / $totalCount, 3) }} pt</p>
                                        @elseif($isPending)
                                            <p class="text-[11px] text-amber-600 mt-0.5">En attente du responsable</p>
                                        @elseif($isRefused && $refusalReason)
                                            <p class="text-[11px] text-rose-600 mt-0.5">Refusée : {{ $refusalReason }}</p>
                                        @endif
                                    </div>

                                    @if(! $isValidated && ! $isPending)
                                        <form action="{{ route('rh.portal.submit-task', $todayTaskSheet) }}" method="POST" class="shrink-0">
                                            @csrf
                                            <input type="hidden" name="task_index" value="{{ $idx }}" />
                                            <button type="submit" class="h-8 px-3 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#0066FF] hover:bg-slate-50 transition">
                                                Terminer
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400 font-mono">{{ $todayTaskSheet->reference }}</span>
                            <a href="{{ route('rh.daily-tasks.print', $todayTaskSheet) }}" target="_blank" class="text-[#64748B] hover:text-[#0066FF] transition">Fiche PDF</a>
                        </div>
                    @else
                        <p class="mt-6 mb-2 text-center text-sm text-[#64748B]">Aucune tâche assignée aujourd'hui.</p>
                    @endif
                </section>

                {{-- Planning --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Planning de la semaine</h2>
                    <p class="text-[11px] text-[#64748B] mt-0.5">6 jours travaillés maximum · 1 jour de repos</p>

                    @if($currentSchedule && $currentSchedule->days->isNotEmpty())
                        <div class="mt-4 -mx-4 px-4 sm:mx-0 sm:px-0 flex sm:grid sm:grid-cols-7 gap-2 overflow-x-auto no-scrollbar snap-x">
                            @foreach($currentSchedule->days as $d)
                                @php
                                    $isToday = (int) $d->day_of_week === (int) now()->isoWeekday();
                                @endphp
                                <div class="snap-start shrink-0 w-[4.5rem] sm:w-auto rounded-xl px-2 py-3 text-center {{ $isToday ? 'bg-[#0B0F14] text-white' : 'bg-[#F5F7FA]' }}">
                                    <p class="text-[10px] uppercase tracking-wider {{ $isToday ? 'text-white/70' : 'text-[#64748B]' }}">{{ mb_substr($d->day_name_fr, 0, 3) }}</p>
                                    @if($d->is_working)
                                        <p class="mt-1.5 text-[11px] font-medium tabular-nums {{ $isToday ? 'text-white' : 'text-[#0B0F14]' }}">{{ substr($d->start_time, 0, 5) }}</p>
                                        <p class="text-[11px] tabular-nums {{ $isToday ? 'text-white/70' : 'text-[#64748B]' }}">{{ substr($d->end_time, 0, 5) }}</p>
                                    @else
                                        <p class="mt-1.5 text-[11px] {{ $isToday ? 'text-white/70' : 'text-slate-400' }}">Repos</p>
                                        <p class="text-[11px]">&nbsp;</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-xs text-[#64748B]">Planning standard : lundi au samedi 08:00 – 20:00, dimanche repos.</p>
                    @endif
                </section>

                {{-- Congés --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Mes demandes de congé</h2>
                        <button type="button" @click="leaveModalOpen = true" class="text-xs font-medium text-[#0066FF] hover:underline">Nouvelle</button>
                    </div>

                    @if(isset($recentLeaves) && $recentLeaves->count() > 0)
                        <ul class="mt-3 divide-y divide-slate-100">
                            @foreach($recentLeaves as $l)
                                @php
                                    $leaveTone = $l->status === 'approuvé' ? 'bg-emerald-500' : ($l->status === 'en_attente' ? 'bg-amber-500' : 'bg-rose-500');
                                @endphp
                                <li class="py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm text-[#0B0F14] capitalize truncate">{{ str_replace('_', ' ', $l->type) }}</p>
                                        <p class="text-[11px] text-[#64748B]">{{ $l->start_date?->format('d/m') }} – {{ $l->end_date?->format('d/m/Y') }} · {{ $l->days }} j</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-[11px] text-[#64748B] shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $leaveTone }}"></span>
                                        {{ str_replace('_', ' ', $l->status) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-xs text-[#64748B]">Aucune demande récente.</p>
                    @endif
                </section>
            </div>

            <div class="space-y-5 sm:space-y-6">

                {{-- Note du mois --}}
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Note du mois</h2>
                    <p class="text-[11px] text-[#64748B] mt-0.5 capitalize">{{ now()->translatedFormat('F Y') }}</p>

                    <div class="mt-5 flex items-baseline gap-1.5">
                        <span class="text-5xl font-semibold tracking-tight tabular-nums text-[#0B0F14]">{{ number_format($finalScore, 2, ',', ' ') }}</span>
                        <span class="text-base text-slate-400">/ 30</span>
                    </div>
                    <p class="mt-1 text-xs {{ $scoreTone }}">{{ $scoreLabel }}</p>

                    <div class="mt-6 space-y-4">
                        @foreach([
                            ['label' => 'Ponctualité', 'weight' => $settings->weight_punctuality, 'value' => $gaugePunct, 'bar' => 'bg-[#0066FF]'],
                            ['label' => 'Tâches validées', 'weight' => $settings->weight_tasks, 'value' => $gaugeTasks, 'bar' => 'bg-emerald-500'],
                            ['label' => 'Travail en équipe', 'weight' => $settings->weight_teamwork, 'value' => $gaugeTeam, 'bar' => 'bg-purple-500'],
                        ] as $criterion)
                            <div>
                                <div class="flex items-baseline justify-between text-xs">
                                    <span class="text-[#0B0F14]">{{ $criterion['label'] }} <span class="text-[10px] text-slate-400">{{ $criterion['weight'] }} pt/j</span></span>
                                    <span class="tabular-nums text-[#64748B]">{{ $criterion['value'] }}/10</span>
                                </div>
                                <div class="mt-1.5 h-1 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $criterion['bar'] }} transition-all duration-500" style="width: {{ $criterion['value'] * 10 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-6 pt-4 border-t border-slate-100 text-[10px] leading-relaxed text-slate-400">
                        (Points obtenus / plafond {{ round($workingDays * 0.66, 2) }}) × 30 · arrêté le 5 de chaque mois
                    </p>
                </section>

                {{-- Historique --}}
                @if(isset($pastEvaluations) && $pastEvaluations->count() > 0)
                    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Historique</h2>
                        <ul class="mt-3 divide-y divide-slate-100">
                            @foreach($pastEvaluations as $pe)
                                <li class="py-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm text-[#0B0F14] capitalize">{{ $pe->month_name }} {{ $pe->year }}</p>
                                        <p class="text-[11px] text-[#64748B]">{{ $pe->total_working_days }} jours · {{ $pe->status }}</p>
                                    </div>
                                    <span class="text-sm font-semibold tabular-nums text-[#0B0F14]">{{ number_format((float) $pe->final_score_30, 2, ',', ' ') }}<span class="text-xs font-normal text-slate-400">/30</span></span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>

        {{-- Demande de congé : bottom sheet mobile / modale desktop --}}
        <div x-show="leaveModalOpen" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="leave-title" @keydown.escape.window="leaveModalOpen = false" style="display: none;">
            <div
                x-show="leaveModalOpen"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-[#0B0F14]/50 backdrop-blur-xs"
                @click="leaveModalOpen = false"
            ></div>

            <div class="absolute inset-x-0 bottom-0 sm:inset-0 sm:flex sm:items-center sm:justify-center sm:p-6 pointer-events-none">
                <div
                    x-show="leaveModalOpen"
                    x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-2"
                    class="pointer-events-auto w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl p-5 sm:p-6 safe-area-pb max-h-[90vh] overflow-y-auto"
                >
                    <div class="sm:hidden mx-auto mb-4 h-1 w-10 rounded-full bg-slate-200"></div>

                    <div class="flex items-center justify-between mb-5">
                        <h3 id="leave-title" class="text-base font-semibold text-[#0B0F14]">Demande de congé</h3>
                        <button type="button" @click="leaveModalOpen = false" class="p-1.5 -mr-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100" aria-label="Fermer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('rh.portal.request-leave') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="leave-type" class="block text-xs text-[#64748B] mb-1.5">Type</label>
                            <select id="leave-type" name="type" required class="w-full h-11 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                <option value="congé_annuel">Congé annuel payé</option>
                                <option value="congé_maladie">Congé maladie</option>
                                <option value="congé_maternité">Congé maternité / paternité</option>
                                <option value="sans_solde">Permission sans solde</option>
                                <option value="autre">Autre motif</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="leave-start" class="block text-xs text-[#64748B] mb-1.5">Début</label>
                                <input id="leave-start" type="date" name="start_date" required class="w-full h-11 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14] focus:outline-none focus:border-[#0066FF]" />
                            </div>
                            <div>
                                <label for="leave-end" class="block text-xs text-[#64748B] mb-1.5">Fin</label>
                                <input id="leave-end" type="date" name="end_date" required class="w-full h-11 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14] focus:outline-none focus:border-[#0066FF]" />
                            </div>
                        </div>

                        <div>
                            <label for="leave-days" class="block text-xs text-[#64748B] mb-1.5">Jours ouvrés</label>
                            <input id="leave-days" type="number" name="days" min="1" max="60" value="1" required class="w-full h-11 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm text-[#0B0F14] focus:outline-none focus:border-[#0066FF]" />
                        </div>

                        <div>
                            <label for="leave-reason" class="block text-xs text-[#64748B] mb-1.5">Motif <span class="text-slate-400">(facultatif)</span></label>
                            <textarea id="leave-reason" name="reason" rows="3" placeholder="Précisez le contexte…" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3 py-2.5 text-sm text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:border-[#0066FF]"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" @click="leaveModalOpen = false" class="h-11 rounded-xl border border-[#E2E8F0] text-sm font-medium text-[#0B0F14] hover:bg-slate-50 transition">Annuler</button>
                            <button type="submit" class="h-11 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-medium transition">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
