@php
    $maxDays = (int) $settings->max_work_days_per_week;
    $coveragePct = $totalActive > 0 ? min(100, round($totalScheduledEmployees / $totalActive * 100)) : 0;
@endphp

<x-layouts.app>
    <x-slot:title>Plannings — IVOSPHERE RH</x-slot>

    <div class="space-y-5 sm:space-y-6">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[['label' => 'Ressources Humaines', 'url' => route('rh.index')], ['label' => 'Plannings']]" />
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Plannings</h1>
                    <p class="text-sm text-[#64748B]">Semaines de travail de l'équipe — {{ $maxDays }} jours maximum, repos obligatoire.</p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <form action="{{ route('rh.schedules.apply-standard') }}" method="POST" onsubmit="return confirm('Appliquer la semaine standard (lundi–samedi, dimanche repos) aux {{ $totalActive }} collaborateurs actifs pour cette semaine ?');">
                        @csrf
                        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                        <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[#E2E8F0] bg-white px-4 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Semaine standard
                        </button>
                    </form>
                    <a href="{{ route('rh.schedules.create', ['week' => $weekStart->toDateString()]) }}" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Planifier
                    </a>
                </div>
            </div>
        </header>

        {{-- ───────── Semaine & synthèse ───────── --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="flex flex-col justify-between gap-4 rounded-2xl bg-[#0B0F14] p-5 text-white lg:col-span-5">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('rh.schedules.index', ['week' => $prevWeek, 'department' => request('department')]) }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 transition hover:bg-white/20" aria-label="Semaine précédente">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div class="text-center">
                        <p class="text-xs text-slate-400">{{ $weekStart->isCurrentWeek() ? 'Cette semaine' : 'Semaine' }}</p>
                        <p class="text-base font-semibold">{{ $weekStart->locale('fr')->isoFormat('D MMM') }} → {{ $weekEnd->locale('fr')->isoFormat('D MMM YYYY') }}</p>
                    </div>
                    <a href="{{ route('rh.schedules.index', ['week' => $nextWeek, 'department' => request('department')]) }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 transition hover:bg-white/20" aria-label="Semaine suivante">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Équipe planifiée</span>
                        <span class="font-semibold">{{ $totalScheduledEmployees }} / {{ $totalActive }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-[#3B8BFF]" style="width: {{ $coveragePct }}%"></div>
                    </div>
                </div>
                @unless($weekStart->isCurrentWeek())
                    <a href="{{ route('rh.schedules.index', ['department' => request('department')]) }}" class="self-center text-xs font-medium text-[#7FB2FF] hover:underline">Revenir à cette semaine</a>
                @endunless
            </div>

            <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:col-span-7">
                @foreach([
                    [$shiftsCount, 'Jours travaillés', 'bg-emerald-50 text-emerald-600', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    [$restDaysCount, 'Jours de repos', 'bg-slate-100 text-slate-600', 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'],
                    [$maxDays.' j', 'Maximum / semaine', 'bg-amber-50 text-amber-600', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ] as [$value, $label, $tint, $icon])
                    <article class="flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $tint }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <div class="mt-3">
                            <p class="text-2xl font-semibold text-[#0B0F14]">{{ $value }}</p>
                            <p class="text-[11px] leading-tight text-[#64748B] sm:text-xs">{{ $label }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ───────── Planning ───────── --}}
        <section class="space-y-3" aria-labelledby="planning-title">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3 px-1">
                    <h2 id="planning-title" class="text-sm font-semibold text-[#0B0F14]">Planning de la semaine</h2>
                    <span class="flex items-center gap-3 text-[11px] text-[#64748B]">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Travail</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-slate-300"></span>Repos</span>
                    </span>
                </div>
                <form method="GET" action="{{ route('rh.schedules.index') }}">
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    <label for="schedDept" class="sr-only">Département</label>
                    <select id="schedDept" name="department" onchange="this.form.submit()" class="h-9 w-full rounded-full border border-[#E2E8F0] bg-white px-3.5 text-xs font-medium text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-52">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected(request('department') == $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if($employees->isEmpty())
                <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-14 text-center text-sm text-[#64748B]">Aucun collaborateur pour ce filtre.</div>
            @else
                {{-- Grille desktop --}}
                <div class="hidden overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white md:block">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-[#E2E8F0]">
                                <tr>
                                    <th class="min-w-52 px-5 py-3 text-[11px] font-medium text-[#64748B]">Collaborateur</th>
                                    @foreach($weekDays as $day)
                                        <th class="min-w-20 px-1.5 py-3 text-center">
                                            <span class="inline-flex flex-col items-center rounded-xl px-2.5 py-1 {{ $day['is_today'] ? 'bg-[#0066FF] text-white' : 'text-[#64748B]' }}">
                                                <span class="text-[11px] font-semibold uppercase">{{ $day['short_name'] }}</span>
                                                <span class="text-[10px] {{ $day['is_today'] ? 'text-white/80' : 'text-slate-400' }}">{{ $day['formatted'] }}</span>
                                            </span>
                                        </th>
                                    @endforeach
                                    <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @foreach($employees as $emp)
                                    @php
                                        $schedule = $emp->workSchedules->first();
                                        $workingDaysCount = $schedule ? $schedule->days->where('is_working_day', true)->count() : 0;
                                    @endphp
                                    <tr class="group transition hover:bg-[#F5F7FA]/60">
                                        <td class="px-5 py-3">
                                            <p class="text-sm font-medium text-[#0B0F14]">{{ $emp->full_name }}</p>
                                            <p class="text-[11px] {{ $schedule ? ($workingDaysCount > $maxDays ? 'text-rose-600' : 'text-[#64748B]') : 'text-amber-600' }}">
                                                {{ $schedule ? $workingDaysCount.' / '.$maxDays.' jours' : 'Non planifié' }} · {{ $emp->department ?? 'Général' }}
                                            </p>
                                        </td>
                                        @foreach($weekDays as $day)
                                            @php
                                                $schedDay = $schedule ? $schedule->days->firstWhere('day_of_week', $day['day_of_week']) : null;
                                                $isWork = $schedDay ? (bool) $schedDay->is_working_day : false;
                                            @endphp
                                            <td class="px-1.5 py-2.5 text-center">
                                                @if(! $schedule)
                                                    <div class="mx-auto h-10 rounded-lg border border-dashed border-[#E2E8F0]"></div>
                                                @elseif($isWork)
                                                    <div class="mx-auto flex h-10 flex-col items-center justify-center rounded-lg bg-emerald-50 text-emerald-800 {{ $day['is_today'] ? 'ring-2 ring-[#0066FF]/40' : '' }}">
                                                        <span class="text-[11px] font-semibold">{{ substr($schedDay->start_time, 0, 5) }}</span>
                                                        <span class="text-[10px] text-emerald-700/70">{{ substr($schedDay->end_time, 0, 5) }}</span>
                                                    </div>
                                                @else
                                                    <div class="mx-auto flex h-10 items-center justify-center rounded-lg bg-slate-100 text-[10px] font-medium text-slate-500">Repos</div>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-5 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('rh.schedules.create', ['employee_id' => $emp->id, 'week' => $weekStart->toDateString()]) }}"
                                                   class="inline-flex items-center rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-xs font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                                                    {{ $schedule ? 'Modifier' : 'Planifier' }}
                                                </a>
                                                @if($schedule)
                                                    <form action="{{ route('rh.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Supprimer ce planning ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Supprimer le planning">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </form>
                                                @endif
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
                    @foreach($employees as $emp)
                        @php
                            $schedule = $emp->workSchedules->first();
                            $workingDaysCount = $schedule ? $schedule->days->where('is_working_day', true)->count() : 0;
                        @endphp
                        <li class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $emp->full_name }}</p>
                                    <p class="truncate text-[11px] text-[#64748B]">{{ $emp->department ?? 'Général' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-medium {{ $schedule ? 'bg-blue-50 text-[#0066FF]' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $schedule ? $workingDaysCount.' / '.$maxDays.' j' : 'Non planifié' }}
                                </span>
                            </div>
                            <div class="mt-3 grid grid-cols-7 gap-1">
                                @foreach($weekDays as $day)
                                    @php
                                        $schedDay = $schedule ? $schedule->days->firstWhere('day_of_week', $day['day_of_week']) : null;
                                        $isWork = $schedDay ? (bool) $schedDay->is_working_day : false;
                                    @endphp
                                    <div class="flex flex-col items-center gap-1 rounded-lg py-1.5 {{ ! $schedule ? 'border border-dashed border-[#E2E8F0]' : ($isWork ? 'bg-emerald-50' : 'bg-slate-100') }} {{ $day['is_today'] ? 'ring-2 ring-[#0066FF]/40' : '' }}">
                                        <span class="text-[10px] font-semibold uppercase {{ $isWork ? 'text-emerald-800' : 'text-slate-500' }}">{{ mb_substr($day['short_name'], 0, 1) }}</span>
                                        <span class="h-1.5 w-1.5 rounded-full {{ $isWork ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('rh.schedules.create', ['employee_id' => $emp->id, 'week' => $weekStart->toDateString()]) }}" class="mt-3 flex h-9 items-center justify-center rounded-xl bg-[#F5F7FA] text-xs font-medium text-[#0B0F14] active:bg-slate-200">
                                {{ $schedule ? 'Modifier le planning' : 'Créer le planning' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.app>
