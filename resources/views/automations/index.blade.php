<x-layouts.app title="Moteur d'Automatisation & Workflows">
    <div class="space-y-6" x-data="{ tab: 'rules', newRuleModal: false, newWorkflowModal: false }">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Moteur Événementiel</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Automatisations & Workflows</h1>
                <p class="text-xs text-slate-500">Automatisez vos relances clients, approvisionnements et circuits d'approbation hiérarchiques.</p>
            </div>

            <!-- Tab switch & New Action -->
            <div class="flex items-center gap-3">
                <div class="inline-flex rounded-xl border border-[#E2E8F0] bg-white p-1 text-xs shadow-xs">
                    <button 
                        @click="tab = 'rules'" 
                        :class="tab === 'rules' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                        class="px-3.5 py-1.5 rounded-lg transition-all"
                    >
                        Règles
                    </button>
                    <button 
                        @click="tab = 'workflows'" 
                        :class="tab === 'workflows' ? 'bg-[#0066FF] text-white shadow-xs font-semibold' : 'text-slate-600 font-medium'"
                        class="px-3.5 py-1.5 rounded-lg transition-all"
                    >
                        Circuits de Validation
                    </button>
                </div>

                <button 
                    x-show="tab === 'rules'"
                    @click="newRuleModal = true"
                    class="px-4 py-2 rounded-xl bg-[#0B0F14] hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouvelle Règle</span>
                </button>

                <button 
                    x-show="tab === 'workflows'"
                    @click="newWorkflowModal = true"
                    class="px-4 py-2 rounded-xl bg-[#0B0F14] hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-2"
                    style="display: none;"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouveau Circuit</span>
                </button>
            </div>
        </div>

        <!-- TAB 1: RÈGLES D'AUTOMATISATION -->
        <div x-show="tab === 'rules'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($rules as $rule)
                    <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs flex flex-col justify-between hover:border-[#0066FF]/50 transition-colors">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $rule->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $rule->is_active ? 'Active' : 'Désactivée' }}
                                </span>
                                <span class="text-[11px] text-slate-400">Exécutée {{ $rule->execution_count }} fois</span>
                            </div>

                            <h3 class="text-sm font-bold text-[#0B0F14]">{{ $rule->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1">{{ $rule->description }}</p>

                            <!-- Visual IF THEN block -->
                            <div class="mt-4 p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs space-y-2">
                                <div class="flex items-start gap-2">
                                    <span class="font-extrabold text-[#0066FF] uppercase text-[10px] shrink-0 mt-0.5">SI :</span>
                                    <span class="text-slate-700 font-mono text-[11px]">{{ $rule->trigger_event }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="font-extrabold text-[#0B0F14] uppercase text-[10px] shrink-0 mt-0.5">ALORS :</span>
                                    <span class="text-slate-700 font-medium">Générer Alerte Intelligente & Notifier l'équipe</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E2E8F0] mt-4 flex items-center justify-between text-xs">
                            <span class="text-[11px] text-slate-400">
                                Dernier déclenchement : {{ $rule->last_triggered_at ? $rule->last_triggered_at->diffForHumans() : 'Jamais' }}
                            </span>
                            <form method="POST" action="{{ route('automations.rules.toggle', $rule) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold {{ $rule->is_active ? 'text-amber-600 hover:underline' : 'text-[#0066FF] hover:underline' }}">
                                    {{ $rule->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <!-- Default built-in standard rules showcased -->
                    <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-[#E2E8F0]">
                        <p class="text-xs font-medium text-[#0B0F14]">Les 4 règles maîtresses du système sont actives par défaut :</p>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-left">
                            <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs">
                                <span class="font-bold text-[#0B0F14] block">Facture échue > 7j</span>
                                <span class="text-slate-500 text-[11px]">Déclenche alerte recouvrement & relance</span>
                            </div>
                            <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs">
                                <span class="font-bold text-[#0B0F14] block">Stock critique</span>
                                <span class="text-slate-500 text-[11px]">Alerte approvisionnement fournisseur</span>
                            </div>
                            <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs">
                                <span class="font-bold text-[#0B0F14] block">Devis sans retour > 3j</span>
                                <span class="text-slate-500 text-[11px]">Rappel commercial d'opportunité</span>
                            </div>
                            <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs">
                                <span class="font-bold text-[#0B0F14] block">Fin contrat < 30j</span>
                                <span class="text-slate-500 text-[11px]">Alerte juridique & reconduction</span>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 2: CIRCUITS DE VALIDATION (WORKFLOWS) -->
        <div x-show="tab === 'workflows'" class="space-y-4" style="display: none;">
            <div class="space-y-4">
                @forelse($workflows as $wf)
                    <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                        <div class="flex items-center justify-between pb-4 border-b border-[#E2E8F0]">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-[#0B0F14]">{{ $wf->name }}</h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#0066FF]/10 text-[#0066FF] uppercase">
                                        Module : {{ $wf->module }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $wf->description }}</p>
                            </div>
                            <form method="POST" action="{{ route('automations.workflows.toggle', $wf) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs font-semibold hover:bg-slate-50 transition-colors">
                                    {{ $wf->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        </div>

                        <!-- Stepper visual representation -->
                        <div class="mt-5 flex items-center gap-3 overflow-x-auto pb-2">
                            @foreach($wf->steps as $index => $step)
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] min-w-[180px]">
                                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold uppercase mb-1">
                                            <span>Étape {{ $step->step_order }}</span>
                                            @if($step->time_limit_hours)
                                                <span>{{ $step->time_limit_hours }}h max</span>
                                            @endif
                                        </div>
                                        <p class="font-bold text-xs text-[#0B0F14]">{{ $step->name }}</p>
                                        <span class="text-[11px] text-[#0066FF] font-medium block mt-1">
                                            Rôle : {{ $step->role ? $step->role->name : 'Tout validateur' }}
                                        </span>
                                    </div>

                                    @if(!$loop->last)
                                        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center bg-white rounded-2xl border border-[#E2E8F0] text-slate-400 text-xs">
                        <p class="font-semibold text-[#0B0F14]">Aucun circuit personnalisé configuré.</p>
                        <p class="text-slate-400 mt-1">Cliquez sur « Nouveau Circuit » pour définir un schéma de validation multi-étapes (ex: Dépenses > 500 000 FCFA).</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Modal: Nouvelle Règle -->
        <div x-show="newRuleModal" class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/60 backdrop-blur-xs" style="display: none;">
            <div @click.outside="newRuleModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E8F0]">
                <h3 class="text-base font-bold text-[#0B0F14] mb-4">Créer une Règle d'Automatisation</h3>
                <form action="{{ route('automations.rules.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label for="rule-name-input" class="block font-semibold text-[#0B0F14] mb-1">Nom de la règle</label>
                        <input id="rule-name-input" type="text" name="name" placeholder="Ex: Relance automatique devis grand compte" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] focus:ring-1 focus:ring-[#0066FF]" required />
                    </div>
                    <div>
                        <label for="rule-trigger-select" class="block font-semibold text-[#0B0F14] mb-1">Événement déclencheur (SI)</label>
                        <select id="rule-trigger-select" name="trigger_event" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] focus:ring-1 focus:ring-[#0066FF]" required>
                            <option value="invoice_overdue_7d">Facture impayée depuis plus de 7 jours</option>
                            <option value="stock_below_minimum">Stock sous le seuil d'alerte minimal</option>
                            <option value="quote_unanswered_3d">Devis sans réponse client après 3 jours</option>
                            <option value="contract_expiring_30d">Contrat arrivant à expiration sous 30 jours</option>
                        </select>
                    </div>
                    <div>
                        <label for="rule-description-textarea" class="block font-semibold text-[#0B0F14] mb-1">Description / Notes</label>
                        <textarea id="rule-description-textarea" name="description" rows="2" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] focus:ring-1 focus:ring-[#0066FF]" placeholder="Explication de la règle..."></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="newRuleModal = false" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0066FF] text-white font-bold hover:bg-[#0052cc]">Enregistrer la règle</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Nouveau Circuit -->
        <div x-show="newWorkflowModal" class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/60 backdrop-blur-xs" style="display: none;">
            <div @click.outside="newWorkflowModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E8F0]">
                <h3 class="text-base font-bold text-[#0B0F14] mb-4">Nouveau Circuit d'Approbation</h3>
                <form action="{{ route('automations.workflows.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label for="wf-name-input" class="block font-semibold text-[#0B0F14] mb-1">Nom du workflow</label>
                        <input id="wf-name-input" type="text" name="name" placeholder="Ex: Validation Dépenses Élevées (> 1M FCFA)" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] focus:ring-1 focus:ring-[#0066FF]" required />
                    </div>
                    <div>
                        <label for="wf-module-select" class="block font-semibold text-[#0B0F14] mb-1">Module concerné</label>
                        <select id="wf-module-select" name="module" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] focus:ring-1 focus:ring-[#0066FF]" required>
                            <option value="expenses">Dépenses & Achats</option>
                            <option value="quotations">Devis & Remises exceptionnelles</option>
                            <option value="leaves">Congés & Absences</option>
                            <option value="contracts">Contrats Fournisseurs</option>
                        </select>
                    </div>
                    <!-- Step 1 -->
                    <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2">
                        <span class="font-bold text-[#0B0F14] block">Étape 1 : Première vérification</span>
                        <input type="text" name="steps[0][name]" value="Validation Responsable de Service" class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0]" required />
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <select name="steps[0][role_id]" class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0]">
                                <option value="">Tous rôles autorisés</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="steps[0][time_limit_hours]" placeholder="Délai max (heures)" value="24" class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0]" />
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2">
                        <span class="font-bold text-[#0B0F14] block">Étape 2 : Approbation finale</span>
                        <input type="text" name="steps[1][name]" value="Approbation Direction Financière" class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0]" required />
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <select name="steps[1][role_id]" class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0]">
                                <option value="">Direction Générale / Admin</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="steps[1][time_limit_hours]" placeholder="Délai max (heures)" value="48" class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0]" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="newWorkflowModal = false" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0066FF] text-white font-bold hover:bg-[#0052cc]">Créer le circuit</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
