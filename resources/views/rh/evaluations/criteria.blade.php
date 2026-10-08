<x-layouts.app>
    <x-slot:title>Configuration des Critères d'Évaluation — IVOSPHERE RH</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Matrice des Critères d'Évaluation" 
            description="Définition des coefficients, modes de calcul (automatique ou managérial) et spécialisations par service"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations', 'url' => route('rh.evaluations.index')],
                    ['label' => 'Critères de Notation']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-create-criterion').classList.remove('hidden')" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Nouveau Critère</span>
                    </button>
                    <x-button :href="route('rh.evaluations.index')" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Retour Campagne</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Alerte Équilibre des Poids (Point 2 & 6) -->
        @php
            $isBalanced = round($totalWeight) == 100;
        @endphp
        <div class="p-4 rounded-2xl border flex items-center justify-between {{ $isBalanced ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
            <div class="flex items-center gap-3">
                <span class="text-xl">{{ $isBalanced ? '✅' : '⚠️' }}</span>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider">
                        Total des Pondérations Actives : <span class="font-mono text-sm underline">{{ number_format($totalWeight, 1) }} %</span>
                    </h4>
                    <p class="text-[11px] {{ $isBalanced ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $isBalanced 
                            ? 'Parfait : la répartition des coefficients atteint 100 %, la note normalisée sur 30 points est idéalement calibrée.' 
                            : 'Attention : le cumul des poids actifs devrait idéalement atteindre 100 % pour une note /30 équilibrée.' }}
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold {{ $isBalanced ? 'bg-emerald-200/60 text-emerald-800' : 'bg-amber-200 text-amber-900' }}">
                {{ number_format($totalWeight, 0) }} / 100 %
            </span>
        </div>

        <!-- Filtre par Service -->
        <x-card>
            <form method="GET" action="{{ route('rh.evaluations.criteria.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
                <label class="font-semibold text-[#0B0F14]">Filtrer par service / spécialisation :</label>
                <select name="department" onchange="this.form.submit()" class="px-3.5 py-1.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                    <option value="">Tous les critères (Communs & Spécifiques)</option>
                    <option value="GLOBAL" @selected(request('department') === 'GLOBAL')>Critères Transversaux (Généraux)</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                    @endforeach
                </select>
                @if(request()->filled('department'))
                    <a href="{{ route('rh.evaluations.criteria.index') }}" class="text-xs text-[#0066FF] hover:underline">Réinitialiser</a>
                @endif
            </form>
        </x-card>

        <!-- Tableau des Critères d'Évaluation -->
        <x-card :noPadding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Ordre</th>
                            <th class="py-3 px-4">Critère</th>
                            <th class="py-3 px-3 text-center">Spécialisation</th>
                            <th class="py-3 px-3 text-center">Mode de Calcul</th>
                            <th class="py-3 px-3 text-center">Pondération (%)</th>
                            <th class="py-3 px-3 text-center">Obligatoire</th>
                            <th class="py-3 px-3 text-center">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($criteria as $crit)
                            <tr class="hover:bg-[#F8FAFC] transition {{ !$crit->is_active ? 'opacity-60 bg-slate-50/50' : '' }}">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-400">
                                    #{{ $crit->order }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#0B0F14] text-sm">{{ $crit->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $crit->code }}</div>
                                    @if($crit->description)
                                        <div class="text-[11px] text-[#64748B] mt-0.5 max-w-md truncate">{{ $crit->description }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $crit->department ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $crit->department ?? 'Général (Tous)' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $crit->isAutomatic() ? 'bg-blue-50 text-[#0066FF] border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $crit->isAutomatic() ? '🤖 Automatique' : '✍️ Manuel / RH' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl font-mono font-extrabold text-xs bg-slate-100 text-[#0B0F14]">
                                        {{ number_format($crit->weight_percentage, 1) }} %
                                    </span>
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    @if($crit->is_mandatory)
                                        <span class="text-emerald-600 font-bold text-xs" title="Critère obligatoire">Oui</span>
                                    @else
                                        <span class="text-slate-400 text-xs">Optionnel</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-3 text-center">
                                    <form action="{{ route('rh.evaluations.criteria.toggle', $crit) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-0.5 rounded-full text-[10px] font-bold border transition {{ $crit->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                                            {{ $crit->is_active ? 'Actif' : 'Désactivé' }}
                                        </button>
                                    </form>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                onclick="openEditCriterionModal({{ json_encode($crit) }})"
                                                class="p-1 rounded-lg text-[#64748B] hover:text-[#0066FF] hover:bg-blue-50 transition" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form action="{{ route('rh.evaluations.criteria.destroy', $crit) }}" method="POST" onsubmit="return confirm('Supprimer ce critère ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded-lg text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-xs text-[#64748B]">
                                    Aucun critère défini. Cliquez sur « Nouveau Critère » pour en ajouter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- MODAL CRÉATION CRITÈRE -->
    <div id="modal-create-criterion" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-[#0B0F14]">Nouveau Critère d'Évaluation</h3>
                <button type="button" onclick="document.getElementById('modal-create-criterion').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('rh.evaluations.criteria.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Nom du critère *</label>
                        <input type="text" name="name" required placeholder="Ex : Ponctualité" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Code unique (slug) *</label>
                        <input type="text" name="code" required placeholder="Ex : punctuality" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] font-mono focus:outline-none focus:border-[#0066FF]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Poids / Coefficient (%) *</label>
                        <input type="number" name="weight_percentage" step="0.5" min="1" max="100" required placeholder="15" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] font-mono focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Mode de calcul *</label>
                        <select name="calculation_mode" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                            <option value="manual">Manuel (Saisie RH / Manager)</option>
                            <option value="automatic">Automatique (Badgeuse / Tâches)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Spécialisation métier / Service (Optionnel)</label>
                    <select name="department" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Général (Applicable à tous les collaborateurs)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Description & barème indicatif</label>
                    <textarea name="description" rows="2" placeholder="Préciser les attentes pour la notation..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_mandatory" value="1" checked class="rounded border-slate-300 text-[#0066FF]">
                        <span>Critère obligatoire</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-[#0066FF]">
                        <span>Actif immédiatement</span>
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-create-criterion').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold">Enregistrer le critère</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION CRITÈRE -->
    <div id="modal-edit-criterion" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-[#0B0F14]">Modifier le Critère</h3>
                <button type="button" onclick="document.getElementById('modal-edit-criterion').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form id="form-edit-criterion" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-semibold mb-1">Nom du critère *</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Poids / Coefficient (%) *</label>
                        <input type="number" id="edit-weight" name="weight_percentage" step="0.5" min="1" max="100" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] font-mono focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Mode de calcul *</label>
                        <select id="edit-mode" name="calculation_mode" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                            <option value="manual">Manuel (Saisie RH / Manager)</option>
                            <option value="automatic">Automatique (Badgeuse / Tâches)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Spécialisation métier / Service</label>
                    <select id="edit-dept" name="department" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                        <option value="">Général (Tous les services)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Description & directives de notation</label>
                    <textarea id="edit-desc" name="description" rows="2" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit-mandatory" name="is_mandatory" value="1" class="rounded border-slate-300 text-[#0066FF]">
                        <span>Critère obligatoire</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit-active" name="is_active" value="1" class="rounded border-slate-300 text-[#0066FF]">
                        <span>Critère actif</span>
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit-criterion').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditCriterionModal(crit) {
            const form = document.getElementById('form-edit-criterion');
            form.action = `/rh/evaluations-criteria/${crit.id}`;
            document.getElementById('edit-name').value = crit.name;
            document.getElementById('edit-weight').value = crit.weight_percentage;
            document.getElementById('edit-mode').value = crit.calculation_mode;
            document.getElementById('edit-dept').value = crit.department || '';
            document.getElementById('edit-desc').value = crit.description || '';
            document.getElementById('edit-mandatory').checked = !!crit.is_mandatory;
            document.getElementById('edit-active').checked = !!crit.is_active;
            document.getElementById('modal-edit-criterion').classList.remove('hidden');
        }
    </script>
</x-layouts.app>
