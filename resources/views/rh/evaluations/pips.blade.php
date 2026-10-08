<x-layouts.app>
    <x-slot:title>Plans d'Amélioration de la Performance (PIP) — IVOSPHERE RH</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Plans d'Amélioration de la Performance (PIP)" 
            description="Accompagnement ciblé des collaborateurs en sous-performance, suivi des objectifs de redressement et actions correctives"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                    ['label' => 'Plans d\'Amélioration']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-new-pip').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Ouvrir un Nouveau PIP</span>
                    </button>
                    <x-button :href="route('rh.evaluations.index')" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Retour Campagne</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- KPI PIP -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <x-stat-card 
                title="PIP en Cours" 
                :value="$activeCount" 
                change="Accompagnements actifs" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Clôturés avec Succès" 
                :value="$satisfactoryCount" 
                change="Redressements validés" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Total des Dossiers" 
                :value="$totalCount" 
                change="Historique global PIP" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Filtre PIP -->
        <x-card>
            <form method="GET" action="{{ route('rh.pips.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
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
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut du PIP</label>
                    <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Tous les statuts</option>
                        <option value="en_cours" @selected(request('status') === 'en_cours')>En cours</option>
                        <option value="satisfaisant" @selected(request('status') === 'satisfaisant')>Satisfaisant (Résolu ✅)</option>
                        <option value="non_satisfaisant" @selected(request('status') === 'non_satisfaisant')>Non satisfaisant (Échec ❌)</option>
                        <option value="annule" @selected(request('status') === 'annule')>Annulé</option>
                    </select>
                </div>

                <div class="sm:col-span-3 flex items-end">
                    <button type="submit" class="w-full py-2 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-xl text-xs font-bold transition">
                        Filtrer les plans
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Liste des Plans d'Amélioration (Cards) -->
        <div class="space-y-4">
            @forelse($pips as $pip)
                @php
                    $isResolved = ($pip->status === 'satisfaisant');
                    $isFailed = ($pip->status === 'non_satisfaisant');
                    $borderCard = match(true) {
                        $isResolved => 'border-emerald-200 bg-emerald-50/20',
                        $isFailed => 'border-rose-200 bg-rose-50/20',
                        default => 'border-amber-200 bg-white',
                    };
                @endphp
                <div class="rounded-2xl border p-5 transition shadow-xs {{ $borderCard }} space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#E2E8F0] gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($pip->employee->first_name ?? 'E', 0, 1) . substr($pip->employee->last_name ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-extrabold text-[#0B0F14] text-sm flex items-center gap-2">
                                    <span>{{ $pip->title }}</span>
                                    <span class="text-xs font-normal text-[#64748B]">pour</span>
                                    <span class="text-[#0066FF] font-bold">{{ $pip->employee->full_name }}</span>
                                </h3>
                                <p class="text-[11px] text-[#64748B]">
                                    Matricule : {{ $pip->employee->employee_code }} • 
                                    Service : {{ $pip->employee->department ?? 'Général' }} •
                                    Superviseur : <strong class="text-slate-800">{{ $pip->supervisor?->name ?? 'Direction RH' }}</strong>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @php
                                $badgeStatus = match($pip->status) {
                                    'satisfaisant' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'non_satisfaisant' => 'bg-rose-50 text-rose-800 border-rose-200',
                                    'annule' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    default => 'bg-amber-50 text-amber-800 border-amber-200',
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $badgeStatus }}">
                                {{ ucfirst(str_replace('_', ' ', $pip->status)) }}
                            </span>
                            <button type="button" 
                                    onclick="openEditPipModal({{ json_encode($pip) }})"
                                    class="px-3 py-1 rounded-lg bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-bold text-slate-700 transition">
                                Gérer / Clôturer
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                            <span class="text-[10px] uppercase font-bold text-rose-700 block mb-1">1. Problème Identifié</span>
                            <p class="text-[#0B0F14] leading-relaxed">{{ $pip->problem_identified }}</p>
                        </div>

                        <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                            <span class="text-[10px] uppercase font-bold text-[#0066FF] block mb-1">2. Objectif Visé</span>
                            <p class="text-[#0B0F14] leading-relaxed">{{ $pip->target_objective }}</p>
                        </div>

                        <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                            <span class="text-[10px] uppercase font-bold text-emerald-700 block mb-1">3. Actions Correctives</span>
                            <p class="text-[#0B0F14] leading-relaxed">{{ $pip->corrective_actions }}</p>
                        </div>
                    </div>

                    @if($pip->final_assessment)
                        <div class="p-3 rounded-xl bg-slate-900 text-white text-xs">
                            <span class="text-[10px] uppercase font-bold text-[#0066FF] block mb-1">Bilan Final & Décision RH</span>
                            <p class="text-slate-200 leading-relaxed">{{ $pip->final_assessment }}</p>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 text-[11px] text-[#64748B]">
                        <div class="flex items-center gap-4">
                            <span>Début : <strong>{{ $pip->start_date?->format('d/m/Y') }}</strong></span>
                            <span>Fin prévisionnelle : <strong>{{ $pip->end_date?->format('d/m/Y') }}</strong></span>
                        </div>

                        <div class="flex items-center gap-3 sm:min-w-[220px]">
                            <span class="font-bold text-[#0B0F14] font-mono">{{ $pip->progress_pct }}% réalisé</span>
                            <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full rounded-full bg-amber-500 transition-all" style="width: {{ $pip->progress_pct }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-xs text-[#64748B] bg-white rounded-2xl border border-[#E2E8F0]">
                    Aucun plan d'amélioration actif pour cette sélection.
                </div>
            @endforelse
        </div>

        @if($pips->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $pips->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CRÉATION NOUVEAU PIP -->
    <div id="modal-new-pip" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-rose-700">Ouvrir un Plan d'Amélioration (PIP)</h3>
                <button type="button" onclick="document.getElementById('modal-new-pip').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('rh.pips.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Collaborateur concerné *</label>
                        <select name="employee_id" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Superviseur du plan</label>
                        <select name="supervisor_id" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                            @foreach($supervisors as $sup)
                                <option value="{{ $sup->id }}" @selected($sup->id === auth()->id())>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Titre du plan d'action *</label>
                    <input type="text" name="title" required placeholder="Ex : Plan de redressement de la ponctualité" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Problème ou lacune constatée *</label>
                    <textarea name="problem_identified" rows="2" required placeholder="Constats factuels précis..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Objectif ciblé à atteindre *</label>
                    <textarea name="target_objective" rows="2" required placeholder="Critères de redressement mesurables..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Actions correctives obligatoires *</label>
                    <textarea name="corrective_actions" rows="2" required placeholder="Mesures d'accompagnement, entretiens, binôme..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Date de début *</label>
                        <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Date de fin prévisionnelle *</label>
                        <input type="date" name="end_date" value="{{ now()->addMonths(2)->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-new-pip').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold">Lancer le PIP</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MISE À JOUR PIP -->
    <div id="modal-update-pip" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-[#0B0F14]">Actualiser ou Clôturer le PIP</h3>
                <button type="button" onclick="document.getElementById('modal-update-pip').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form id="form-update-pip" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Progression (%) *</label>
                        <input type="number" id="pip-edit-progress" name="progress_pct" min="0" max="100" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] font-mono focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Statut du dossier *</label>
                        <select id="pip-edit-status" name="status" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                            <option value="en_cours">En cours</option>
                            <option value="satisfaisant">Satisfaisant (Succès ✅)</option>
                            <option value="non_satisfaisant">Non satisfaisant (Échec ❌)</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Date de fin effective</label>
                    <input type="date" id="pip-edit-end-date" name="end_date" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Actions correctives complémentaires</label>
                    <textarea id="pip-edit-actions" name="corrective_actions" rows="2" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Évaluation finale / Conclusion formelle</label>
                    <textarea id="pip-edit-assessment" name="final_assessment" rows="3" placeholder="Constat final de la direction RH, maintien ou décision RH..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-update-pip').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold">Enregistrer l'actualisation</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditPipModal(pip) {
            const form = document.getElementById('form-update-pip');
            form.action = `/rh/pips/${pip.id}`;
            document.getElementById('pip-edit-progress').value = pip.progress_pct;
            document.getElementById('pip-edit-status').value = pip.status;
            document.getElementById('pip-edit-end-date').value = pip.end_date ? pip.end_date.substring(0, 10) : '';
            document.getElementById('pip-edit-actions').value = pip.corrective_actions || '';
            document.getElementById('pip-edit-assessment').value = pip.final_assessment || '';
            document.getElementById('modal-update-pip').classList.remove('hidden');
        }
    </script>
</x-layouts.app>
