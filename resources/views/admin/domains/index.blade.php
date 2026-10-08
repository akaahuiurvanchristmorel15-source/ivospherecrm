<x-layouts.app>
    <x-slot:title>Gestion des Domaines Métiers</x-slot>

    <div x-data="domainBatchManager({
        initialShowBatch: {{ $errors->any() || request()->has('batch') ? 'true' : 'false' }}
    })" class="space-y-6">

        <x-page-header 
            title="Gestion des Domaines d'Activité IVOSPHERE"
            description="Supervision, création, modification et segmentation des pôles d'activité de l'entreprise"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Domaines Métiers']
                ]" />
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openSingleModal()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Nouveau Domaine</span>
                    </button>

                    <button type="button" @click="showBatchSection = !showBatchSection"
                            :class="showBatchSection ? 'bg-[#0B0F14] text-white hover:bg-slate-800' : 'bg-white text-slate-700 hover:bg-slate-50 border border-[#E2E8F0]'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition shadow-xs">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span x-text="showBatchSection ? 'Fermer la saisie groupée' : 'Ajout groupé (plusieurs)'"></span>
                    </button>

                    <x-button href="{{ route('admin.users.index') }}" variant="secondary" size="sm" class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Collaborateurs</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Stat Cards Consolidation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Domaines -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Total Domaines</span>
                    <p class="text-2xl font-extrabold text-[#0B0F14] mt-1">{{ $totalCount }} <span class="text-sm font-semibold text-[#0066FF]">pôles</span></p>
                </div>
                <p class="text-[11px] text-[#64748B] mt-2">Segmentation métier globale IVOSPHERE</p>
            </div>

            <!-- Domaines Actifs -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Domaines Actifs</span>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $activeCount }} <span class="text-sm font-normal text-[#64748B]">/ {{ $totalCount }}</span></p>
                </div>
                <p class="text-[11px] text-[#64748B] mt-2">Opérationnels dans les modules CRM</p>
            </div>

            <!-- Collaborateurs affectés -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Utilisateurs Rattachés</span>
                    <p class="text-2xl font-extrabold text-[#0B0F14] mt-1">{{ $totalUsers }} <span class="text-sm font-normal text-[#64748B]">collaborateurs</span></p>
                </div>
                <p class="text-[11px] text-[#64748B] mt-2">Répartis sur les différents pôles</p>
            </div>

            <!-- Boutons d'Action Rapide -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between gap-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Actions Rapides</span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openSingleModal()"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Créer Domaine</span>
                    </button>
                    <button type="button" @click="showBatchSection = !showBatchSection"
                            title="Ajout multiple de domaines"
                            class="inline-flex items-center justify-center p-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F5F7FA] text-slate-700 transition">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- =======================================================================
             PARTIE DÉDIÉE : AJOUT MULTIPLE DE DOMAINES (Formulaire Multi-Lignes)
             ======================================================================= --}}
        <div x-show="showBatchSection" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="rounded-2xl border-2 border-[#0066FF]/30 bg-white p-5 sm:p-6 shadow-sm relative overflow-hidden">
            
            <div class="absolute top-0 right-0 bg-[#0066FF] text-white text-[10px] font-bold uppercase tracking-wider px-3 py-0.5 rounded-bl-lg shadow-2xs">
                Ajout Groupé Multi-Domaines
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#E2E8F0] pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0066FF] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-[#0B0F14]">Ajouter Plusieurs Domaines Simultanément</h2>
                            <p class="text-xs text-[#64748B]">Saisissez plusieurs pôles ou branches d'activité en une seule opération ou utilisez les suggestions.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="prefillSuggestions()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50/70 hover:bg-amber-100/70 text-amber-800 text-xs font-semibold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>⚡ Suggestions de Pôles</span>
                    </button>

                    <button type="button" @click="addRows(3)"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                        <span>+ 3 lignes</span>
                    </button>

                    <button type="button" @click="resetRows()"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-semibold transition"
                            title="Réinitialiser le formulaire">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span class="hidden sm:inline">Vider</span>
                    </button>
                </div>
            </div>

            <!-- Formulaire multi-domaines -->
            <form action="{{ route('admin.domains.batch') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase font-semibold text-[11px]">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">#</th>
                                <th class="py-3 px-3 min-w-[220px]">Nom du Domaine <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[150px]">Code Unique <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[220px]">Description Métier</th>
                                <th class="py-3 px-3 min-w-[150px]">Thème Couleur</th>
                                <th class="py-3 px-3 min-w-[130px]">Icône</th>
                                <th class="py-3 px-3 w-24 text-center">Statut</th>
                                <th class="py-3 px-3 w-12 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="hover:bg-blue-50/20 transition-colors">
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-400" x-text="index + 1"></td>

                                    <td class="py-2.5 px-3">
                                        <input type="text" 
                                               :name="`domains[${index}][name]`" 
                                               x-model="row.name" 
                                               @blur="suggestCode(index)"
                                               required
                                               placeholder="ex: IVOSPHERE LOGISTIQUE" 
                                               class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>

                                    <td class="py-2.5 px-3">
                                        <input type="text" 
                                               :name="`domains[${index}][code]`" 
                                               x-model="row.code" 
                                               @input="row.code = row.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                                               maxlength="50"
                                               required
                                               placeholder="ex: LOGISTIQUE" 
                                               class="w-full px-3 py-1.5 font-mono uppercase font-bold rounded-lg border border-[#E2E8F0] text-xs text-[#0066FF] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>

                                    <td class="py-2.5 px-3">
                                        <input type="text" 
                                               :name="`domains[${index}][description]`" 
                                               x-model="row.description" 
                                               placeholder="ex: Transport, flotte et entreposage" 
                                               class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>

                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs" :style="`background-color: ${getColorHex(row.color)}`"></span>
                                            <select :name="`domains[${index}][color]`" 
                                                    x-model="row.color"
                                                    class="w-full px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] bg-white focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                                                <option value="indigo">Indigo (Bleu)</option>
                                                <option value="emerald">Émeraude (Vert)</option>
                                                <option value="sky">Ciel (Bleu clair)</option>
                                                <option value="purple">Violet</option>
                                                <option value="amber">Ambre (Orange)</option>
                                                <option value="rose">Rose (Rouge)</option>
                                                <option value="teal">Sarcelle (Turquoise)</option>
                                                <option value="slate">Ardoise (Gris)</option>
                                            </select>
                                        </div>
                                    </td>

                                    <td class="py-2.5 px-3">
                                        <select :name="`domains[${index}][icon]`" 
                                                x-model="row.icon"
                                                class="w-full px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] bg-white focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                                            <option value="briefcase">Mallette (Affaires)</option>
                                            <option value="truck">Camion (Logistique)</option>
                                            <option value="building-office">Bâtiment (Immobilier)</option>
                                            <option value="academic-cap">Chapeau (Formation)</option>
                                            <option value="shield-check">Bouclier (Sécurité)</option>
                                            <option value="shopping-bag">Panier (Commerce)</option>
                                            <option value="globe-alt">Globe (International)</option>
                                            <option value="chart-bar">Graphique (Conseil)</option>
                                            <option value="printer">Imprimante (Print)</option>
                                            <option value="cpu-chip">Puce IT (Tech)</option>
                                            <option value="trophy">Trophée (Sport)</option>
                                            <option value="camera">Caméra (Média)</option>
                                        </select>
                                    </td>

                                    <td class="py-2.5 px-3 text-center">
                                        <input type="hidden" :name="`domains[${index}][is_active]`" value="0">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" 
                                                   :name="`domains[${index}][is_active]`" 
                                                   value="1"
                                                   x-model="row.is_active" 
                                                   class="rounded border-slate-300 text-[#0066FF] shadow-xs focus:ring-[#0066FF]">
                                            <span class="text-[11px] font-medium" :class="row.is_active ? 'text-emerald-700' : 'text-slate-400'" x-text="row.is_active ? 'Actif' : 'Inactif'"></span>
                                        </label>
                                    </td>

                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button" 
                                                @click="removeRow(index)"
                                                :disabled="rows.length <= 1"
                                                :class="rows.length <= 1 ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-rose-500 hover:text-rose-700 hover:bg-rose-50'"
                                                title="Supprimer cette ligne"
                                                class="p-1 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2 border-t border-[#E2E8F0]">
                    <div class="flex items-center gap-4 text-xs text-[#64748B]">
                        <div>
                            <span>Nombre de domaines à enregistrer :</span>
                            <span class="font-bold text-[#0B0F14] ml-1 bg-slate-100 px-2 py-0.5 rounded" x-text="rows.length"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition shadow-xs">
                            <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Ajouter une ligne de domaine</span>
                        </button>

                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="`Enregistrer les ${rows.length} domaine(s)`"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- =======================================================================
             BARRE DE RECHERCHE, FILTRES & BASULE D'AFFICHAGE
             ======================================================================= --}}
        <div class="rounded-xl bg-white border border-[#E2E8F0] p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.domains.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    <div class="relative flex-1 max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un domaine par nom, code..." 
                               class="w-full pl-9 pr-3 py-1.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                    </div>

                    <select name="status" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs uniquement</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs uniquement</option>
                    </select>

                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.domains.index') }}" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:text-[#0B0F14] hover:bg-slate-100 transition">
                            Réinitialiser
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-1 bg-[#F5F7FA] p-1 rounded-xl border border-[#E2E8F0] shrink-0 self-end md:self-auto">
                    <button type="button" @click="viewMode = 'cards'"
                            :class="viewMode === 'cards' ? 'bg-white text-[#0066FF] shadow-xs font-bold' : 'text-slate-500 hover:text-[#0B0F14]'"
                            class="px-2.5 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        <span>Cartes</span>
                    </button>
                    <button type="button" @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-white text-[#0066FF] shadow-xs font-bold' : 'text-slate-500 hover:text-[#0B0F14]'"
                            class="px-2.5 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        <span>Tableau</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- =======================================================================
             VUE 1 : GRILLE DES CARTES DE DOMAINES
             ======================================================================= --}}
        <div x-show="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($domains as $domain)
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-xs hover:border-[#0066FF] hover:shadow-sm transition-all flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3.5 h-3.5 rounded-full shadow-2xs shrink-0" style="background-color: {{ $domain->color === 'indigo' ? '#0066FF' : ($domain->color === 'emerald' ? '#10b981' : ($domain->color === 'sky' ? '#0ea5e9' : ($domain->color === 'purple' ? '#a855f7' : ($domain->color === 'amber' ? '#f59e0b' : ($domain->color === 'rose' ? '#f43f5e' : ($domain->color === 'teal' ? '#14b8a6' : '#64748b')))))) }}"></span>
                                <span class="font-mono text-xs font-bold text-[#0066FF] uppercase tracking-wider">{{ $domain->code }}</span>
                            </div>

                            <form method="POST" action="{{ route('admin.domains.toggle', $domain) }}">
                                @csrf
                                @method('PATCH')
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition cursor-pointer {{ $domain->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-[#64748B] border border-[#E2E8F0] hover:bg-slate-200' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $domain->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span>{{ $domain->is_active ? 'Actif' : 'Inactif' }}</span>
                                </button>
                            </form>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-[#0B0F14]">{{ $domain->name }}</h2>
                            <p class="text-xs text-[#64748B] leading-relaxed mt-1">{{ $domain->description ?: 'Aucune description renseignée pour ce domaine.' }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#E2E8F0] mt-4 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#64748B]">
                                <strong class="text-[#0B0F14] font-semibold">{{ $domain->users_count }}</strong> collaborateur(s) affecté(s)
                            </span>
                            <a 
                                href="{{ route('dashboard', ['domain' => $domain->code]) }}" 
                                class="text-[#0066FF] hover:underline font-semibold flex items-center gap-1"
                            >
                                <span>Dashboard</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>

                        <!-- Barre des actions Modifier / Supprimer -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openEditModal({{ Js::from($domain) }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] hover:bg-[#F5F7FA] text-slate-700 hover:text-[#0066FF] transition font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Modifier</span>
                                </button>
                                
                                <a href="{{ route('admin.domains.edit', $domain) }}" class="text-[11px] text-slate-400 hover:text-slate-600 transition" title="Page complète de modification">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>

                            <button type="button" @click="openDeleteModal({{ Js::from($domain) }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 font-semibold transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Supprimer</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state 
                        title="Aucun domaine trouvé" 
                        description="Aucun pôle d'activité ne correspond à votre recherche. Utilisez le bouton ci-dessus pour ajouter des domaines."
                    />
                </div>
            @endforelse
        </div>

        {{-- =======================================================================
             VUE 2 : TABLE RÉCAPITULATIVE DÉTAILLÉE
             ======================================================================= --}}
        <div x-show="viewMode === 'table'" class="rounded-2xl border border-[#E2E8F0] bg-white overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase font-semibold text-[11px]">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Nom du Domaine</th>
                            <th class="py-3 px-4">Description Métier</th>
                            <th class="py-3 px-4">Couleur</th>
                            <th class="py-3 px-4 text-center">Collaborateurs</th>
                            <th class="py-3 px-4 text-center">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($domains as $domain)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-[#64748B] font-mono">{{ $domain->id }}</td>
                                <td class="py-3 px-4 font-bold text-[#0066FF] font-mono">{{ $domain->code }}</td>
                                <td class="py-3 px-4 font-semibold text-[#0B0F14]">{{ $domain->name }}</td>
                                <td class="py-3 px-4 text-[#64748B] max-w-xs truncate">{{ $domain->description ?: '—' }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 text-[11px]">
                                        <span class="w-2.5 h-2.5 rounded-full shadow-2xs" style="background-color: {{ $domain->color === 'indigo' ? '#0066FF' : ($domain->color === 'emerald' ? '#10b981' : ($domain->color === 'sky' ? '#0ea5e9' : ($domain->color === 'purple' ? '#a855f7' : ($domain->color === 'amber' ? '#f59e0b' : ($domain->color === 'rose' ? '#f43f5e' : ($domain->color === 'teal' ? '#14b8a6' : '#64748b')))))) }}"></span>
                                        <span class="text-[#0B0F14] font-medium capitalize">{{ $domain->color }}</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-[#0B0F14]">{{ $domain->users_count }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $domain->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-[#64748B] border border-[#E2E8F0]' }}">
                                        {{ $domain->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('dashboard', ['domain' => $domain->code]) }}" 
                                           class="p-1.5 rounded-lg border border-[#E2E8F0] text-slate-600 hover:text-[#0066FF] hover:bg-slate-50 transition" title="Dashboard du Domaine">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>

                                        <button type="button" @click="openEditModal({{ Js::from($domain) }})"
                                                class="p-1.5 rounded-lg border border-[#E2E8F0] text-slate-600 hover:text-[#0066FF] hover:bg-slate-50 transition" title="Modifier">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>

                                        <form method="POST" action="{{ route('admin.domains.toggle', $domain) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg border border-[#E2E8F0] hover:bg-slate-50 transition {{ $domain->is_active ? 'text-amber-600' : 'text-emerald-600' }}" title="{{ $domain->is_active ? 'Désactiver' : 'Activer' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                            </button>
                                        </form>

                                        <button type="button" @click="openDeleteModal({{ Js::from($domain) }})"
                                                class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-500">
                                    Aucun domaine enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- =======================================================================
             MODAL : MODIFIER UN DOMAINE
             ======================================================================= --}}
        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="editModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" :style="`background-color: ${getColorHex(editForm.color)}`"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#0B0F14]">
                            Modifier le Domaine Métier
                        </h3>
                    </div>
                    <button type="button" @click="editModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="editActionUrl" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nom du Domaine <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Code unique identifiant <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="editForm.code" required maxlength="50"
                               @input="editForm.code = editForm.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                               class="mt-1 w-full font-mono uppercase font-bold text-[#0066FF] rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Description Métier</label>
                        <textarea name="description" x-model="editForm.description" rows="2"
                                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Couleur Thématique</label>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs" :style="`background-color: ${getColorHex(editForm.color)}`"></span>
                                <select name="color" x-model="editForm.color"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                                    <option value="indigo">Indigo (Bleu)</option>
                                    <option value="emerald">Émeraude (Vert)</option>
                                    <option value="sky">Ciel (Bleu clair)</option>
                                    <option value="purple">Violet</option>
                                    <option value="amber">Ambre (Orange)</option>
                                    <option value="rose">Rose (Rouge)</option>
                                    <option value="teal">Sarcelle (Turquoise)</option>
                                    <option value="slate">Ardoise (Gris)</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Icône</label>
                            <select name="icon" x-model="editForm.icon"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                                <option value="briefcase">Mallette</option>
                                <option value="truck">Camion</option>
                                <option value="building-office">Bâtiment</option>
                                <option value="academic-cap">Formation</option>
                                <option value="shield-check">Bouclier</option>
                                <option value="shopping-bag">Commerce</option>
                                <option value="globe-alt">International</option>
                                <option value="chart-bar">Graphique</option>
                                <option value="printer">Imprimante</option>
                                <option value="cpu-chip">Tech</option>
                                <option value="trophy">Trophée</option>
                                <option value="camera">Média</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer mt-1">
                            <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active"
                                   class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs font-medium text-slate-700">Domaine actif (disponible dans l'ensemble de l'ERP)</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <a :href="editPageUrl" class="text-xs text-[#0066FF] hover:underline font-semibold flex items-center gap-1">
                            <span>Ouvrir page complète</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="editModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</button>
                            <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-bold text-white hover:bg-[#0052CC] shadow-xs">Mettre à jour</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- =======================================================================
             MODAL : AJOUT D'UN SEUL DOMAINE
             ======================================================================= --}}
        <div x-show="singleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="singleModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#0B0F14]">
                        Ajouter un Nouveau Domaine
                    </h3>
                    <button type="button" @click="singleModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.domains.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nom du Domaine <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="singleForm.name" @blur="suggestSingleCode()" required placeholder="ex: IVOSPHERE LOGISTIQUE"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Code identifiant unique <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="singleForm.code" required maxlength="50" placeholder="ex: LOGISTIQUE"
                               @input="singleForm.code = singleForm.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                               class="mt-1 w-full font-mono uppercase font-bold text-[#0066FF] rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Description Métier</label>
                        <textarea name="description" rows="2" placeholder="Activités et périmètre de ce pôle..."
                                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Couleur Thématique</label>
                            <select name="color"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                                <option value="indigo">Indigo (Bleu)</option>
                                <option value="emerald">Émeraude (Vert)</option>
                                <option value="sky">Ciel (Bleu clair)</option>
                                <option value="purple">Violet</option>
                                <option value="amber">Ambre (Orange)</option>
                                <option value="rose">Rose (Rouge)</option>
                                <option value="teal">Sarcelle (Turquoise)</option>
                                <option value="slate">Ardoise (Gris)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Icône</label>
                            <select name="icon"
                                    class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                                <option value="briefcase">Mallette</option>
                                <option value="truck">Camion</option>
                                <option value="building-office">Bâtiment</option>
                                <option value="academic-cap">Formation</option>
                                <option value="shield-check">Bouclier</option>
                                <option value="shopping-bag">Commerce</option>
                                <option value="globe-alt">International</option>
                                <option value="chart-bar">Graphique</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer mt-1">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs font-medium text-slate-700">Activer immédiatement ce domaine</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <button type="button" @click="singleModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-bold text-white hover:bg-[#0052CC] shadow-xs">Créer le domaine</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =======================================================================
             MODAL : CONFIRMATION DE SUPPRESSION D'UN DOMAINE
             ======================================================================= --}}
        <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="deleteModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center gap-3 text-rose-600 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#0B0F14]">Supprimer le Domaine</h3>
                        <p class="text-xs text-[#64748B]">Action irréversible</p>
                    </div>
                </div>

                <div class="space-y-3 py-2 text-xs text-slate-600 leading-relaxed">
                    <p>
                        Êtes-vous certain de vouloir supprimer le domaine <strong class="text-[#0B0F14]" x-text="deleteDomain.name"></strong> (<span class="font-mono font-bold text-[#0066FF]" x-text="deleteDomain.code"></span>) ?
                    </p>
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px]">
                        <strong>Attention :</strong> Les utilisateurs rattachés seront automatiquement détachés et les données associées basculeront en gestion centrale.
                    </div>
                </div>

                <form :action="deleteActionUrl" method="POST" class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</button>
                    <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2 text-xs font-bold text-white transition shadow-xs">Confirmer la suppression</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        function domainBatchManager(config) {
            return {
                showBatchSection: config.initialShowBatch,
                viewMode: 'cards',
                editModal: false,
                singleModal: false,
                deleteModal: false,
                editActionUrl: '',
                editPageUrl: '',
                deleteActionUrl: '',
                deleteDomain: { name: '', code: '' },
                editForm: {
                    name: '',
                    code: '',
                    description: '',
                    color: 'indigo',
                    icon: 'briefcase',
                    is_active: true
                },
                singleForm: {
                    name: '',
                    code: ''
                },
                rows: [
                    { name: '', code: '', description: '', color: 'indigo', icon: 'briefcase', is_active: true },
                    { name: '', code: '', description: '', color: 'emerald', icon: 'truck', is_active: true }
                ],

                getColorHex(color) {
                    const map = {
                        indigo: '#0066FF',
                        emerald: '#10b981',
                        sky: '#0ea5e9',
                        purple: '#a855f7',
                        amber: '#f59e0b',
                        rose: '#f43f5e',
                        teal: '#14b8a6',
                        slate: '#64748b'
                    };
                    return map[color] || '#0066FF';
                },

                addRow(data = null) {
                    if (data) {
                        this.rows.push({
                            name: data.name || '',
                            code: data.code || '',
                            description: data.description || '',
                            color: data.color || 'indigo',
                            icon: data.icon || 'briefcase',
                            is_active: data.is_active !== undefined ? data.is_active : true
                        });
                    } else {
                        const colors = ['indigo', 'emerald', 'sky', 'purple', 'amber', 'rose', 'teal'];
                        const randomColor = colors[this.rows.length % colors.length];
                        this.rows.push({
                            name: '',
                            code: '',
                            description: '',
                            color: randomColor,
                            icon: 'briefcase',
                            is_active: true
                        });
                    }
                },

                addRows(count) {
                    for (let i = 0; i < count; i++) {
                        this.addRow();
                    }
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                resetRows() {
                    this.rows = [
                        { name: '', code: '', description: '', color: 'indigo', icon: 'briefcase', is_active: true }
                    ];
                },

                prefillSuggestions() {
                    this.rows = [
                        {
                            name: 'IVOSPHERE LOGISTIQUE',
                            code: 'LOGISTIQUE',
                            description: 'Entreposage, chaîne d\'approvisionnement et transport de marchandises',
                            color: 'teal',
                            icon: 'truck',
                            is_active: true
                        },
                        {
                            name: 'IVOSPHERE IMMOBILIER & BTP',
                            code: 'IMMOBILIER',
                            description: 'Gestion de biens, transaction immobilière et travaux d\'aménagement',
                            color: 'amber',
                            icon: 'building-office',
                            is_active: true
                        },
                        {
                            name: 'IVOSPHERE FORMATION',
                            code: 'FORMATION',
                            description: 'Académie, séminaires professionnels et développement des compétences',
                            color: 'rose',
                            icon: 'academic-cap',
                            is_active: true
                        },
                        {
                            name: 'IVOSPHERE CONSEIL & STRATÉGIE',
                            code: 'CONSEIL',
                            description: 'Consulting en organisation d\'entreprise, audit et ingénierie d\'affaires',
                            color: 'slate',
                            icon: 'chart-bar',
                            is_active: true
                        }
                    ];
                },

                suggestCode(index) {
                    const row = this.rows[index];
                    if (row && (!row.code || row.code.trim() === '')) {
                        let base = row.name.trim().toUpperCase()
                            .replace(/^IVOSPHERE\s+/, '')
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^A-Z0-9]/g, '');
                        if (base.length > 20) base = base.substring(0, 20);
                        if (base) {
                            row.code = base;
                        }
                    }
                },

                suggestSingleCode() {
                    if (!this.singleForm.code || this.singleForm.code.trim() === '') {
                        let base = this.singleForm.name.trim().toUpperCase()
                            .replace(/^IVOSPHERE\s+/, '')
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^A-Z0-9]/g, '');
                        if (base.length > 20) base = base.substring(0, 20);
                        if (base) {
                            this.singleForm.code = base;
                        }
                    }
                },

                openSingleModal() {
                    this.singleForm = { name: '', code: '' };
                    this.singleModal = true;
                },

                openEditModal(domain) {
                    this.editForm = {
                        name: domain.name,
                        code: domain.code,
                        description: domain.description || '',
                        color: domain.color || 'indigo',
                        icon: domain.icon || 'briefcase',
                        is_active: Boolean(domain.is_active)
                    };
                    this.editActionUrl = '/admin/domains/' + domain.id;
                    this.editPageUrl = '/admin/domains/' + domain.id + '/edit';
                    this.editModal = true;
                },

                openDeleteModal(domain) {
                    this.deleteDomain = {
                        name: domain.name,
                        code: domain.code
                    };
                    this.deleteActionUrl = '/admin/domains/' + domain.id;
                    this.deleteModal = true;
                }
            };
        }
    </script>
</x-layouts.app>
