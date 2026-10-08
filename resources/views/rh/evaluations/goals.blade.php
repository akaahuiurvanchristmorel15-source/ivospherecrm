<x-layouts.app>
    <x-slot:title>Objectifs Individuels des Collaborateurs — IVOSPHERE RH</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Suivi des Objectifs Individuels" 
            description="Assignation et pilotage des objectifs mensuels ciblés par collaborateur avec mesure de l'avancement"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                    ['label' => 'Objectifs']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-new-goal').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Nouvel Objectif</span>
                    </button>
                    <x-button :href="route('rh.evaluations.index')" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Retour Campagne</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- KPI Objectifs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-stat-card 
                title="Total des Objectifs" 
                :value="$totalGoals" 
                change="Assignés dans le système" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Objectifs Atteints" 
                :value="$achievedGoals" 
                change="Succès validés" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="En Cours d'Exécution" 
                :value="$inProgressGoals" 
                change="En phase active" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Taux d'Accomplissement" 
                :value="$achievementRate . ' %'" 
                change="Efficacité collective" 
                changeType="{{ $achievementRate >= 70 ? 'up' : 'down' }}"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Filtres -->
        <x-card>
            <form method="GET" action="{{ route('rh.goals.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Collaborateur</label>
                    <select name="employee_id" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Tous les collaborateurs</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>
                                {{ $emp->full_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut de l'objectif</label>
                    <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Tous les statuts</option>
                        <option value="a_faire" @selected(request('status') === 'a_faire')>À faire</option>
                        <option value="en_cours" @selected(request('status') === 'en_cours')>En cours</option>
                        <option value="atteint" @selected(request('status') === 'atteint')>Atteint ✅</option>
                        <option value="non_atteint" @selected(request('status') === 'non_atteint')>Non atteint ❌</option>
                    </select>
                </div>

                <div class="sm:col-span-3 flex items-end">
                    <button type="submit" class="w-full py-2 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition">
                        Filtrer les objectifs
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Tableau des Objectifs -->
        <x-card :noPadding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Collaborateur</th>
                            <th class="py-3 px-4">Objectif Assigné</th>
                            <th class="py-3 px-3 text-center">Échéance</th>
                            <th class="py-3 px-4">Avancement</th>
                            <th class="py-3 px-3 text-center">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($goals as $goal)
                            <tr class="hover:bg-[#F8FAFC] transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#0B0F14]">{{ $goal->employee->full_name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">
                                        {{ $goal->employee->employee_code }} • {{ $goal->employee->department ?? 'Général' }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#0B0F14] text-xs">{{ $goal->title }}</div>
                                    @if($goal->description)
                                        <div class="text-[11px] text-[#64748B] max-w-sm truncate">{{ $goal->description }}</div>
                                    @endif
                                    @if($goal->result_notes)
                                        <div class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded mt-1 inline-block">
                                            Bilan : {{ $goal->result_notes }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-3 text-center font-mono">
                                    <span class="text-xs font-bold text-slate-700">{{ $goal->due_date?->format('d/m/Y') }}</span>
                                    @if($goal->due_date && $goal->due_date->isPast() && $goal->status !== 'atteint')
                                        <span class="block text-[9px] text-rose-600 font-bold">Échu</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 min-w-[150px]">
                                    <div class="flex items-center justify-between text-[11px] font-mono mb-1">
                                        <span class="font-bold text-[#0B0F14]">{{ $goal->progress_pct }}%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#0066FF] transition-all" style="width: {{ $goal->progress_pct }}%;"></div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    @php
                                        $badge = match($goal->status) {
                                            'atteint' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            'en_cours' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                            'non_atteint' => 'bg-rose-50 text-rose-800 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                onclick="openEditGoalModal({{ json_encode($goal) }})"
                                                class="px-2.5 py-1 rounded-lg bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-slate-700 transition">
                                            Mettre à jour
                                        </button>

                                        <form action="{{ route('rh.goals.destroy', $goal) }}" method="POST" onsubmit="return confirm('Supprimer cet objectif ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-xs text-[#64748B]">
                                    Aucun objectif individuel répertorié.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($goals->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $goals->links() }}
                </div>
            @endif
        </x-card>
    </div>

    <!-- MODAL CRÉATION OBJECTIF -->
    <div id="modal-new-goal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-[#0B0F14]">Assigner un Objectif Individuel</h3>
                <button type="button" onclick="document.getElementById('modal-new-goal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('rh.goals.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold mb-1">Collaborateur concerné *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Intitulé de l'objectif *</label>
                    <input type="text" name="title" required placeholder="Ex : Traiter les dossiers d'impression sous 24h" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Modalités / Livrables attendus</label>
                    <textarea name="description" rows="2" placeholder="Critères de succès..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Date de début *</label>
                        <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Date d'échéance *</label>
                        <input type="date" name="due_date" value="{{ now()->addMonth()->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                </div>
                <input type="hidden" name="status" value="a_faire">
                <input type="hidden" name="progress_pct" value="0">
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-new-goal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold">Assigner l'objectif</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MISE À JOUR OBJECTIF -->
    <div id="modal-update-goal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-[#0B0F14]">Actualiser l'Objectif</h3>
                <button type="button" onclick="document.getElementById('modal-update-goal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form id="form-update-goal" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-semibold mb-1">Titre de l'objectif</label>
                    <input type="text" id="goal-edit-title" name="title" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Avancement (%) *</label>
                        <input type="number" id="goal-edit-progress" name="progress_pct" min="0" max="100" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] font-mono focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Statut *</label>
                        <select id="goal-edit-status" name="status" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                            <option value="a_faire">À faire</option>
                            <option value="en_cours">En cours</option>
                            <option value="atteint">Atteint (100%)</option>
                            <option value="non_atteint">Non atteint</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Nouvelle date d'échéance</label>
                    <input type="date" id="goal-edit-due-date" name="due_date" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Bilan / Commentaires sur le résultat</label>
                    <textarea id="goal-edit-notes" name="result_notes" rows="2" placeholder="Observations finales, constats de réussite..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-update-goal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditGoalModal(goal) {
            const form = document.getElementById('form-update-goal');
            form.action = `/rh/goals/${goal.id}`;
            document.getElementById('goal-edit-title').value = goal.title;
            document.getElementById('goal-edit-progress').value = goal.progress_pct;
            document.getElementById('goal-edit-status').value = goal.status;
            document.getElementById('goal-edit-due-date').value = goal.due_date ? goal.due_date.substring(0, 10) : '';
            document.getElementById('goal-edit-notes').value = goal.result_notes || '';
            document.getElementById('modal-update-goal').classList.remove('hidden');
        }
    </script>
</x-layouts.app>
