<x-layouts.app>
    <x-slot:title>{{ $existingSchedule ? 'Modifier le Planning' : 'Nouveau Planning' }} — IVOSPHERE RH</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header 
            :title="$existingSchedule ? 'Modifier le Planning Hebdomadaire' : 'Créer un Planning Hebdomadaire'" 
            :description="'Semaine du ' . $weekStart->format('d/m/Y') . ' au ' . $weekStart->copy()->endOfWeek()->format('d/m/Y')"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Plannings', 'url' => route('rh.schedules.index', ['week' => $weekStart->toDateString()])],
                    ['label' => $existingSchedule ? 'Modification' : 'Nouveau']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button :href="route('rh.schedules.index', ['week' => $weekStart->toDateString()])" variant="secondary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la grille</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <form 
            method="POST" 
            action="{{ route('rh.schedules.store') }}" 
            class="space-y-6"
            x-data="{
                days: {
                    @foreach($scheduleDaysData as $i => $d)
                        {{ $i }}: {
                            working: {{ $d['is_working_day'] ? 'true' : 'false' }},
                            start: '{{ $d['start_time'] ?? '08:00' }}',
                            end: '{{ $d['end_time'] ?? '20:00' }}'
                        },
                    @endforeach
                },
                maxAllowed: {{ (int) $settings->max_work_days_per_week ?: 6 }},
                
                get totalWorkingDays() {
                    return Object.values(this.days).filter(d => d.working).length;
                },
                
                get isExceeded() {
                    return this.totalWorkingDays > this.maxAllowed;
                },

                applyStandardWeek() {
                    for (let i = 1; i <= 7; i++) {
                        this.days[i].working = (i <= 6); // Lun à Sam = travail (6j), Dim = repos
                        this.days[i].start = '08:00';
                        this.days[i].end = '20:00';
                    }
                }
            }"
        >
            @csrf
            <input type="hidden" name="week_start_date" value="{{ $weekStart->toDateString() }}">

            <!-- 1. Sélection Collaborateur & Raccourcis -->
            <x-card title="1. Collaborateur & Semaine" subtitle="Sélectionnez l'employé et appliquez le modèle souhaité">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-[#0B0F14] mb-1.5" for="employee_id">
                            Collaborateur concerné *
                        </label>
                        <select 
                            name="employee_id" 
                            id="employee_id" 
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] outline-none"
                            required
                        >
                            <option value="">Sélectionner un collaborateur...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" @selected(($employee && $employee->id == $emp->id) || old('employee_id') == $emp->id)>
                                    {{ $emp->full_name }} ({{ $emp->employee_code }} • {{ $emp->department ?? 'Général' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col justify-end">
                        <button 
                            type="button" 
                            @click="applyStandardWeek()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition"
                        >
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Appliquer la semaine standard (Lun–Sam 8h–20h, Dim Repos)</span>
                        </button>
                    </div>
                </div>
            </x-card>

            <!-- 2. Alerte en Direct : RÈGLE STRICTE DES 6 JOURS -->
            <div 
                x-show="isExceeded" 
                x-transition 
                class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 flex items-start gap-3 shadow-sm"
                style="display: none;"
            >
                <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold text-sm">
                    ⚠️
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-rose-950">Règle RH Stricte Non Respectée</h4>
                    <p class="text-xs text-rose-800 mt-0.5">
                        Vous avez sélectionné <strong x-text="totalWorkingDays"></strong> jours travaillés. 
                        Selon les paramètres RH d'IVOSPHERE, un collaborateur ne peut pas être programmé plus de <strong>{{ $settings->max_work_days_per_week }} jours</strong> sur une semaine. Le 7e jour doit obligatoirement être un jour de repos.
                    </p>
                </div>
            </div>

            <!-- 3. Grille des 7 Jours (Lundi à Dimanche) -->
            <x-card title="2. Programmation Quotidienne" subtitle="Activez les jours travaillés et ajustez les plages horaires">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <span class="text-xs font-bold text-[#0B0F14]">Compteur hebdomadaire :</span>
                    <span 
                        class="px-3 py-1 rounded-full text-xs font-extrabold border"
                        :class="isExceeded ? 'bg-rose-100 text-rose-800 border-rose-300' : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
                    >
                        <span x-text="totalWorkingDays"></span> / <span x-text="maxAllowed"></span> jours travaillés autorisés
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($scheduleDaysData as $i => $d)
                        <div 
                            class="p-3.5 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                            :class="days[{{ $i }}].working ? 'bg-white border-slate-200 shadow-2xs' : 'bg-slate-50/70 border-slate-200/60 opacity-80'"
                        >
                            <!-- Nom du Jour + Date -->
                            <div class="flex items-center gap-3 sm:w-1/3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="days[{{ $i }}][is_working_day]" 
                                        value="1" 
                                        x-model="days[{{ $i }}].working"
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                                <div>
                                    <div class="font-bold text-sm text-[#0B0F14] flex items-center gap-2">
                                        <span>{{ $d['name'] }}</span>
                                        <span class="text-xs font-mono font-normal text-slate-400">({{ $d['formatted'] }})</span>
                                    </div>
                                    <span 
                                        class="text-[11px] font-semibold"
                                        :class="days[{{ $i }}].working ? 'text-emerald-700' : 'text-slate-400'"
                                        x-text="days[{{ $i }}].working ? '🟢 Jour de Travail' : '⚪ Jour de Repos'"
                                    ></span>
                                </div>
                            </div>

                            <!-- Plages Horaires -->
                            <div class="flex items-center gap-2 sm:w-1/3" x-show="days[{{ $i }}].working">
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <span>De :</span>
                                    <input 
                                        type="time" 
                                        name="days[{{ $i }}][start_time]" 
                                        x-model="days[{{ $i }}].start"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-[#0B0F14] focus:border-[#0066FF] outline-none"
                                    >
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <span>À :</span>
                                    <input 
                                        type="time" 
                                        name="days[{{ $i }}][end_time]" 
                                        x-model="days[{{ $i }}].end"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-[#0B0F14] focus:border-[#0066FF] outline-none"
                                    >
                                </div>
                            </div>

                            <!-- Note éventuelle -->
                            <div class="sm:w-1/3" x-show="days[{{ $i }}].working">
                                <input 
                                    type="text" 
                                    name="days[{{ $i }}][notes]" 
                                    value="{{ $d['notes'] ?? '' }}"
                                    placeholder="Consigne particulière (ex: astreinte)..."
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-[#0B0F14] placeholder-slate-400 focus:border-[#0066FF] outline-none"
                                >
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Actions de soumission -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('rh.schedules.index', ['week' => $weekStart->toDateString()]) }}" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">
                    ← Annuler
                </a>

                <button 
                    type="submit" 
                    :disabled="isExceeded || totalWorkingDays === 0"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-extrabold shadow-md shadow-[#0066FF]/25 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Valider et Enregistrer le Planning</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
