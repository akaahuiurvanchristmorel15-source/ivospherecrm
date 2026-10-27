<x-layouts.app>
    <x-slot:title>Configuration des Critères d'Évaluation — IVOSPHERE RH</x-slot>

    <div class="space-y-5 sm:space-y-6">
        <x-evaluation.header
            title="Référentiel d'évaluation"
            subtitle="17 critères notés de 1 à 5 en 4 blocs pondérés ; chaque évaluateur note seulement les critères de son domaine."
            active="criteria"
        >
            <x-slot:actions>
                <button type="button" onclick="document.getElementById('modal-create-criterion').classList.remove('hidden')" class="inline-flex h-10 items-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm font-medium text-[#0B0F14] transition hover:border-[#0066FF] hover:text-[#0066FF]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Autre critère
                </button>
            </x-slot:actions>
        </x-evaluation.header>

        @php
            $referential = $criteria->filter(fn ($criterion) => $criterion->block !== null)->groupBy('block');
            $others = $criteria->filter(fn ($criterion) => $criterion->block === null);
            $groupLabels = collect(\App\Models\EvaluationSheet::GROUPS)->map(fn ($group) => $group['label']);
        @endphp

        <!-- Rappel du barème : 4 blocs pondérés, échelle de 1 à 5 -->
        @php
            $blockTints = ['A' => 'bg-blue-50 text-[#0066FF]', 'B' => 'bg-purple-50 text-purple-600', 'C' => 'bg-emerald-50 text-emerald-600', 'D' => 'bg-amber-50 text-amber-600'];
        @endphp
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-4" aria-label="Blocs du référentiel">
            @foreach(\App\Models\EvaluationCriterion::BLOCKS as $block => $definition)
                <a href="#bloc-{{ $block }}" class="group rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-[#0066FF]/40 hover:shadow-[0_8px_24px_-12px_rgba(0,102,255,0.25)]">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-semibold {{ $blockTints[$block] }}">{{ $block }}</span>
                    <p class="mt-3 text-2xl font-semibold text-[#0B0F14]">{{ $definition['weight'] * 100 }} %</p>
                    <p class="text-sm font-medium text-[#0B0F14] group-hover:text-[#0066FF]">{{ $definition['label'] }}</p>
                    <p class="mt-0.5 text-[11px] text-[#64748B]">{{ $referential->get($block)?->count() ?? 0 }} critère(s)</p>
                </a>
            @endforeach
        </section>

        <!-- Les 17 critères du référentiel, par bloc -->
        @foreach($referential as $block => $blockCriteria)
            <x-evaluation.section :id="'bloc-' . $block" class="scroll-mt-6" :title="'Bloc ' . $block . ' — ' . \App\Models\EvaluationCriterion::BLOCKS[$block]['label']" :subtitle="(\App\Models\EvaluationCriterion::BLOCKS[$block]['weight'] * 100) . ' % de la note'" :padding="false">
                <ul class="divide-y divide-[#E2E8F0]">
                    @foreach($blockCriteria as $crit)
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between {{ ! $crit->is_active ? 'opacity-60' : '' }}">
                            <div class="min-w-0 space-y-1.5">
                                <p class="text-sm font-semibold text-[#0B0F14]">
                                    <span class="font-mono text-[#64748B]">{{ $crit->code }}</span> {{ $crit->name }}
                                    @if($crit->isAutomatic())
                                        <span class="ml-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-[#0066FF]">Calculé des bilans</span>
                                    @endif
                                </p>
                                <p class="text-xs text-[#64748B]">{{ $crit->description }}</p>
                                <div class="grid grid-cols-1 gap-1 text-[11px] text-[#64748B] sm:grid-cols-3">
                                    <span><strong class="text-rose-700">1</strong> · {{ $crit->level_1 }}</span>
                                    <span><strong class="text-[#0B0F14]">3</strong> · {{ $crit->level_3 }}</span>
                                    <span><strong class="text-emerald-700">5</strong> · {{ $crit->level_5 }}</span>
                                </div>
                                <p class="text-[11px] text-[#64748B]">
                                    Qui note : {{ collect($crit->evaluator_groups ?? [])->map(fn ($group) => $groupLabels[$group] ?? $group)->implode(', ') }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <form action="{{ route('rh.evaluations.criteria.toggle', $crit) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-full border px-2.5 py-1 text-[10px] font-semibold transition {{ $crit->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        {{ $crit->is_active ? 'Actif' : 'Désactivé' }}
                                    </button>
                                </form>
                                <button type="button" onclick="openEditCriterionModal({{ json_encode($crit) }})" class="rounded-lg p-1.5 text-[#64748B] transition hover:bg-blue-50 hover:text-[#0066FF]" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-evaluation.section>
        @endforeach

        <!-- Critères hors référentiel (anciens ou personnalisés) : ils n'entrent pas dans la note du mois -->
        @if($others->isNotEmpty())
            <details class="rounded-2xl border border-[#E2E8F0] bg-white">
                <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#0B0F14]">
                    Autres critères ({{ $others->count() }}) <span class="text-xs font-normal text-[#64748B]">— hors référentiel, ils ne comptent pas dans la note du mois</span>
                </summary>
                <ul class="divide-y divide-[#E2E8F0] border-t border-[#E2E8F0]">
                    @foreach($others as $crit)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-xs {{ ! $crit->is_active ? 'opacity-60' : '' }}">
                            <span class="min-w-0">
                                <span class="font-semibold text-[#0B0F14]">{{ $crit->name }}</span>
                                <span class="font-mono text-[#94A3B8]">{{ $crit->code }}</span>
                                @if($crit->department)<span class="text-[#64748B]">· {{ $crit->department }}</span>@endif
                            </span>
                            <span class="flex shrink-0 items-center gap-1.5">
                                <form action="{{ route('rh.evaluations.criteria.toggle', $crit) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $crit->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-100 text-slate-500' }}">{{ $crit->is_active ? 'Actif' : 'Désactivé' }}</button>
                                </form>
                                <button type="button" onclick="openEditCriterionModal({{ json_encode($crit) }})" class="rounded-lg p-1 text-[#64748B] hover:text-[#0066FF]" title="Modifier">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form action="{{ route('rh.evaluations.criteria.destroy', $crit) }}" method="POST" onsubmit="return confirm('Supprimer ce critère ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-1 text-[#64748B] hover:text-rose-600" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>

    <!-- MODAL CRÉATION CRITÈRE -->
    <div id="modal-create-criterion" class="hidden fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/40 backdrop-blur-xs sm:items-center sm:p-4">
        <div class="max-h-[90vh] w-full space-y-4 overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
            <div class="flex items-center justify-between ">
                <h3 class="text-base font-semibold text-[#0B0F14]">Autre critère (hors référentiel)</h3>
                <button type="button" onclick="document.getElementById('modal-create-criterion').classList.add('hidden')" class="rounded-lg p-1.5 text-[#94A3B8] hover:bg-[#F1F5F9] hover:text-[#0B0F14]" aria-label="Fermer"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="{{ route('rh.evaluations.criteria.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Nom du critère *</label>
                        <input type="text" name="name" required placeholder="Ex : Ponctualité" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Code unique (slug) *</label>
                        <input type="text" name="code" required placeholder="Ex : punctuality" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 font-mono text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Poids / Coefficient (%) *</label>
                        <input type="number" name="weight_percentage" step="0.5" min="1" max="100" required placeholder="15" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 font-mono text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Mode de calcul *</label>
                        <select name="calculation_mode" required class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                            <option value="manual">Manuel (Saisie RH / Manager)</option>
                            <option value="automatic">Automatique (Badgeuse / Tâches)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Spécialisation métier / Service (Optionnel)</label>
                    <select name="department" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                        <option value="">Général (Applicable à tous les collaborateurs)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Description & barème indicatif</label>
                    <textarea name="description" rows="2" placeholder="Préciser les attentes pour la notation..." class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10"></textarea>
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
                    <button type="button" onclick="document.getElementById('modal-create-criterion').classList.add('hidden')" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] px-4 text-sm font-medium text-[#0B0F14]">Annuler</button>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white hover:bg-[#0052cc]">Enregistrer le critère</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION CRITÈRE -->
    <div id="modal-edit-criterion" class="hidden fixed inset-0 z-50 flex items-end justify-center bg-[#0B0F14]/40 backdrop-blur-xs sm:items-center sm:p-4">
        <div class="max-h-[90vh] w-full space-y-4 overflow-y-auto rounded-t-3xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-3xl sm:p-6">
            <div class="flex items-center justify-between ">
                <h3 class="text-base font-semibold text-[#0B0F14]">Modifier le critère</h3>
                <button type="button" onclick="document.getElementById('modal-edit-criterion').classList.add('hidden')" class="rounded-lg p-1.5 text-[#94A3B8] hover:bg-[#F1F5F9] hover:text-[#0B0F14]" aria-label="Fermer"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form id="form-edit-criterion" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Nom du critère *</label>
                    <input type="text" id="edit-name" name="name" required class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Poids / Coefficient (%) *</label>
                        <input type="number" id="edit-weight" name="weight_percentage" step="0.5" min="1" max="100" required class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 font-mono text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Mode de calcul *</label>
                        <select id="edit-mode" name="calculation_mode" required class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                            <option value="manual">Manuel (Saisie RH / Manager)</option>
                            <option value="automatic">Automatique (Badgeuse / Tâches)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Spécialisation métier / Service</label>
                    <select id="edit-dept" name="department" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10">
                        <option value="">Général (Tous les services)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#0B0F14]">Description & directives de notation</label>
                    <textarea id="edit-desc" name="description" rows="2" class="w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-4 focus:ring-[#0066FF]/10"></textarea>
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
                    <button type="button" onclick="document.getElementById('modal-edit-criterion').classList.add('hidden')" class="inline-flex h-10 items-center rounded-xl border border-[#E2E8F0] px-4 text-sm font-medium text-[#0B0F14]">Annuler</button>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white hover:bg-[#0052cc]">Mettre à jour</button>
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
