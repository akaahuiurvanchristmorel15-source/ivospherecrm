<x-layouts.app>
    <x-slot:title>Plannings Hebdomadaires — IVOSPHERE RH</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Planning des Équipes" 
            description="Organisation hebdomadaire du travail, respect du repos obligatoire et contrôle des 6 jours max"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Plannings']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <form action="{{ route('rh.schedules.apply-standard') }}" method="POST" onsubmit="return confirm('Appliquer la semaine standard (Lun–Sam 8h–20h, Dimanche Repos) à tous les {{ $totalActive }} collaborateurs actifs pour cette semaine ?');">
                        @csrf
                        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                        <x-button variant="secondary" type="submit" size="md">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Standard à tous (6j)</span>
                        </x-button>
                    </form>

                    <x-button :href="route('rh.schedules.create', ['week' => $weekStart->toDateString()])" variant="primary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Planifier un Collaborateur</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- KPI Plannings Semaine -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Collaborateurs Planifiés" 
                :value="$totalScheduledEmployees . ' / ' . $totalActive" 
                change="Couverture équipe" 
                changeType="{{ $totalScheduledEmployees == $totalActive ? 'up' : 'neutral' }}"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Jours Travaillés Prévus" 
                :value="$shiftsCount" 
                change="Total créneaux actifs" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Jours de Repos Planifiés" 
                :value="$restDaysCount" 
                change="Repos obligatoire respecté" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Garde-Fou Légal" 
                :value="'Max ' . $settings->max_work_days_per_week . 'j / sem'" 
                change="Blocage strict du 7e jour" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Navigation de la Semaine & Filtres -->
        <x-card>
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <!-- Sélecteur Précédent / Actuel / Suivant -->
                <div class="flex items-center gap-2 w-full lg:w-auto justify-between sm:justify-start">
                    <a href="{{ route('rh.schedules.index', ['week' => $prevWeek, 'department' => request('department')]) }}" 
                       class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span class="hidden sm:inline">Semaine préc.</span>
                    </a>

                    <div class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold flex items-center gap-2 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Semaine du {{ $weekStart->format('d/m') }} au {{ $weekEnd->format('d/m/Y') }}</span>
                        @if($weekStart->isCurrentWeek())
                            <span class="px-1.5 py-0.2 rounded bg-[#0066FF] text-white text-[9px] uppercase font-bold tracking-wider">Actuelle</span>
                        @endif
                    </div>

                    <a href="{{ route('rh.schedules.index', ['week' => $nextWeek, 'department' => request('department')]) }}" 
                       class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs transition flex items-center gap-1">
                        <span class="hidden sm:inline">Semaine suiv.</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- Formulaire Filtre Département -->
                <form method="GET" action="{{ route('rh.schedules.index') }}" class="flex items-center gap-2 w-full lg:w-auto">
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    <select name="department" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs text-[#0B0F14] focus:border-[#0066FF] outline-none">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected(request('department') == $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>

                    @if(request('department'))
                        <a href="{{ route('rh.schedules.index', ['week' => $weekStart->toDateString()]) }}" class="text-xs text-rose-600 hover:underline">
                            Réinitialiser
                        </a>
                    @endif
                </form>
            </div>
        </x-card>

        <!-- Matrice Hebdomadaire (Table Desktop) -->
        <x-card :noPadding="true">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4 min-w-[200px]">Collaborateur</th>
                            @foreach($weekDays as $day)
                                <th class="py-3 px-2 text-center min-w-[100px] {{ $day['is_today'] ? 'bg-blue-50/80 text-[#0066FF] font-bold' : '' }}">
                                    <div class="text-[11px]">{{ $day['short_name'] }}</div>
                                    <div class="text-[10px] font-mono font-normal {{ $day['is_today'] ? 'text-[#0066FF]' : 'text-slate-400' }}">{{ $day['formatted'] }}</div>
                                </th>
                            @endforeach
                            <th class="py-3 px-3 text-center">Total Jours</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($employees as $emp)
                            @php
                                $schedule = $emp->workSchedules->first();
                                $workingDaysCount = $schedule ? $schedule->days->where('is_working_day', true)->count() : 0;
                            @endphp
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <!-- Collaborateur -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#0B0F14]">{{ $emp->full_name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">
                                        {{ $emp->employee_code }} • {{ $emp->position ?? $emp->department ?? 'Général' }}
                                    </div>
                                </td>

                                <!-- 7 Colonnes Jours (LUN à DIM) -->
                                @foreach($weekDays as $day)
                                    @php
                                        $schedDay = $schedule ? $schedule->days->firstWhere('day_of_week', $day['day_of_week']) : null;
                                        $isWork = $schedDay ? (bool) $schedDay->is_working_day : false;
                                    @endphp
                                    <td class="py-3.5 px-2 text-center {{ $day['is_today'] ? 'bg-blue-50/30' : '' }}">
                                        @if(!$schedule)
                                            <span class="text-slate-300 font-mono text-[10px]">—</span>
                                        @elseif($isWork)
                                            <div class="inline-flex flex-col items-center">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Travail
                                                </span>
                                                <span class="text-[9px] font-mono text-slate-400 mt-0.5">
                                                    {{ substr($schedDay->start_time, 0, 5) }}–{{ substr($schedDay->end_time, 0, 5) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Repos
                                            </span>
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Total Jours Travaillés -->
                                <td class="py-3.5 px-3 text-center">
                                    @if($schedule)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $workingDaysCount <= 6 ? 'bg-blue-50 text-[#0066FF] border border-blue-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                            {{ $workingDaysCount }} / 6 j
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            Non planifié
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('rh.schedules.create', ['employee_id' => $emp->id, 'week' => $weekStart->toDateString()]) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-slate-700 transition">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>{{ $schedule ? 'Modifier' : 'Planifier' }}</span>
                                        </a>

                                        @if($schedule)
                                            <form action="{{ route('rh.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Supprimer ce planning hebdomadaire ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    Aucun collaborateur trouvé pour ce filtre.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 space-y-3">
                @foreach($employees as $emp)
                    @php
                        $schedule = $emp->workSchedules->first();
                        $workingDaysCount = $schedule ? $schedule->days->where('is_working_day', true)->count() : 0;
                    @endphp
                    <div class="bg-white rounded-2xl p-4 border border-[#E2E8F0] shadow-xs space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-bold text-sm text-[#0B0F14]">{{ $emp->full_name }}</h4>
                                <span class="text-[10px] font-mono text-slate-400">{{ $emp->employee_code }} • {{ $emp->department ?? 'Général' }}</span>
                            </div>
                            @if($schedule)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0066FF] border border-blue-200">
                                    {{ $workingDaysCount }} / 6 j
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    Non planifié
                                </span>
                            @endif
                        </div>

                        <!-- 7 Pastilles de la semaine -->
                        <div class="grid grid-cols-7 gap-1 text-center">
                            @foreach($weekDays as $day)
                                @php
                                    $schedDay = $schedule ? $schedule->days->firstWhere('day_of_week', $day['day_of_week']) : null;
                                    $isWork = $schedDay ? (bool) $schedDay->is_working_day : false;
                                @endphp
                                <div class="p-1 rounded-lg {{ $isWork ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    <span class="text-[9px] font-bold block">{{ $day['short_name'] }}</span>
                                    <span class="w-1.5 h-1.5 rounded-full mx-auto mt-0.5 block {{ $isWork ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400">Horaire : 08:00 → 20:00</span>
                            <a href="{{ route('rh.schedules.create', ['employee_id' => $emp->id, 'week' => $weekStart->toDateString()]) }}" 
                               class="text-xs font-bold text-[#0066FF] hover:underline">
                                {{ $schedule ? 'Modifier le planning' : 'Créer le planning' }} →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</x-layouts.app>
