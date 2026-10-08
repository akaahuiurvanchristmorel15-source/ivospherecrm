<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Gestion des Caisses" 
            subtitle="Comptes de trésorerie, caisses physiques et gestion multi-caisses par pôle d'activité">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Caisses']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <x-button href="{{ route('finance.index') }}" variant="secondary" size="sm" class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Tableau de bord Finance</span>
                    </x-button>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div x-data="cashRegisterManager({
        domains: {{ Js::from($domains) }},
        initialShowBatch: {{ $errors->any() || request()->has('batch') ? 'true' : 'true' }}
    })" class="space-y-6">

        <!-- Stat Cards Consolidation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Solde Total -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Solde Consolidé Total</span>
                    <p class="text-2xl font-extrabold text-[#0B0F14] mt-1">{{ number_format($totalBalance, 0, ',', ' ') }} <span class="text-sm font-semibold text-[#0066FF]">FCFA</span></p>
                </div>
                <p class="text-[11px] text-[#64748B] mt-2">Cumul de l'ensemble des caisses</p>
            </div>

            <!-- Caisses Actives -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Caisses Actives</span>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $activeCount }} <span class="text-sm font-normal text-[#64748B]">/ {{ $totalCount }}</span></p>
                </div>
                <p class="text-[11px] text-[#64748B] mt-2">Disponibles pour encaissements & décaissements</p>
            </div>

            <!-- Pôles Couverts -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Pôles & Domaines</span>
                    <p class="text-2xl font-extrabold text-[#0B0F14] mt-1">{{ $domains->count() }} <span class="text-sm font-normal text-[#64748B]">pôles</span></p>
                </div>
                <p class="text-[11px] text-[#64748B] mt-2">PRINT, SPORT, TECH, MEDIA, ASSURANCE...</p>
            </div>

            <!-- Actions Rapides -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs flex flex-col justify-between gap-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Actions Rapides</span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showBatchSection = !showBatchSection"
                            :class="showBatchSection ? 'bg-[#0066FF] text-white hover:bg-[#0052CC]' : 'bg-[#0B0F14] text-white hover:bg-[#0066FF]'"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span x-text="showBatchSection ? 'Masquer l\'ajout multiple' : 'Ajouter plusieurs caisses'"></span>
                    </button>
                    <button type="button" @click="openSingleModal()"
                            title="Ajouter une seule caisse"
                            class="inline-flex items-center justify-center p-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F5F7FA] text-slate-700 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- =======================================================================
             PARTIE DÉDIÉE : AJOUT MULTIPLE DE CAISSES (Formulaire Multi-Lignes)
             ======================================================================= --}}
        <div x-show="showBatchSection" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="rounded-2xl border-2 border-[#0066FF]/30 bg-white p-5 sm:p-6 shadow-sm relative overflow-hidden">
            
            <!-- Ruban indicateur -->
            <div class="absolute top-0 right-0 bg-[#0066FF] text-white text-[10px] font-bold uppercase tracking-wider px-3 py-0.5 rounded-bl-lg shadow-2xs">
                Ajout Groupé Multi-Caisses
            </div>

            <!-- En-tête de la section d'ajout multiple -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#E2E8F0] pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0066FF] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-[#0B0F14]">Ajouter Plusieurs Caisses Simultanément</h2>
                            <p class="text-xs text-[#64748B]">Saisissez plusieurs caisses sur une seule page ou utilisez le pré-remplissage automatique par pôle.</p>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'aide et pré-remplissage -->
                <div class="flex flex-wrap items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="prefillByDomains()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50/70 hover:bg-amber-100/70 text-amber-800 text-xs font-semibold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>⚡ Pré-remplir par Pôle</span>
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

            <!-- Formulaire multi-caisses -->
            <form action="{{ route('finance.cash-registers.batch') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <!-- Tableau Desktop / Défilement Horizontal -->
                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase font-semibold text-[11px]">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">#</th>
                                <th class="py-3 px-3 min-w-[220px]">Nom de la caisse <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[150px]">Code identifiant <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[190px]">Domaine / Pôle d'activité</th>
                                <th class="py-3 px-3 min-w-[150px]">Solde initial (FCFA)</th>
                                <th class="py-3 px-3 w-28 text-center">Statut</th>
                                <th class="py-3 px-3 w-12 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="hover:bg-blue-50/20 transition-colors">
                                    <!-- Numéro de ligne -->
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-400" x-text="index + 1"></td>

                                    <!-- Nom de la caisse -->
                                    <td class="py-2.5 px-3">
                                        <input type="text" 
                                               :name="`registers[${index}][name]`" 
                                               x-model="row.name" 
                                               @blur="suggestCode(index)"
                                               required
                                               placeholder="ex: Caisse Comptoir 1" 
                                               class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>

                                    <!-- Code identifiant -->
                                    <td class="py-2.5 px-3">
                                        <div class="relative">
                                            <input type="text" 
                                                   :name="`registers[${index}][code]`" 
                                                   x-model="row.code" 
                                                   @input="row.code = row.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                                                   maxlength="30"
                                                   required
                                                   placeholder="ex: CS-CPT1" 
                                                   class="w-full px-3 py-1.5 font-mono uppercase rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                        </div>
                                    </td>

                                    <!-- Domaine / Pôle -->
                                    <td class="py-2.5 px-3">
                                        <select :name="`registers[${index}][domain_id]`" 
                                                x-model="row.domain_id"
                                                class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] bg-white focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                            <option value="">Trésorerie Centrale (Siège)</option>
                                            <template x-for="domain in domains" :key="domain.id">
                                                <option :value="domain.id" x-text="domain.name"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Solde initial -->
                                    <td class="py-2.5 px-3">
                                        <div class="relative">
                                            <input type="number" 
                                                   :name="`registers[${index}][balance]`" 
                                                   x-model.number="row.balance" 
                                                   min="0"
                                                   step="any"
                                                   placeholder="0" 
                                                   class="w-full pl-3 pr-12 py-1.5 rounded-lg border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                            <span class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[11px] font-semibold text-slate-400 pointer-events-none">FCFA</span>
                                        </div>
                                    </td>

                                    <!-- Statut actif -->
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="hidden" :name="`registers[${index}][is_active]`" value="0">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" 
                                                   :name="`registers[${index}][is_active]`" 
                                                   value="1"
                                                   x-model="row.is_active" 
                                                   class="rounded border-slate-300 text-[#0066FF] shadow-xs focus:ring-[#0066FF]">
                                            <span class="text-[11px] font-medium" :class="row.is_active ? 'text-emerald-700' : 'text-slate-400'" x-text="row.is_active ? 'Active' : 'Inactive'"></span>
                                        </label>
                                    </td>

                                    <!-- Supprimer ligne -->
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

                <!-- Barre d'actions & Résumé du lot -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2 border-t border-[#E2E8F0]">
                    <!-- Résumé dynamique -->
                    <div class="flex items-center gap-4 text-xs text-[#64748B]">
                        <div>
                            <span>Nombre de caisses à ajouter :</span>
                            <span class="font-bold text-[#0B0F14] ml-1 bg-slate-100 px-2 py-0.5 rounded" x-text="rows.length"></span>
                        </div>
                        <div>
                            <span>Total soldes d'ouverture :</span>
                            <span class="font-bold text-[#0066FF] ml-1" x-text="totalBalanceFormatted"></span>
                        </div>
                    </div>

                    <!-- Boutons de soumission -->
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition shadow-xs">
                            <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Ajouter une ligne de caisse</span>
                        </button>

                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="`Enregistrer les ${rows.length} caisse(s)`"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- =======================================================================
             BARRE DE RECHERCHE, FILTRES & BASULE D'AFFICHAGE
             ======================================================================= --}}
        <div class="rounded-xl bg-white border border-[#E2E8F0] p-4 shadow-xs">
            <form method="GET" action="{{ route('finance.cash-registers.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    <!-- Champ recherche -->
                    <div class="relative flex-1 max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou code..." 
                               class="w-full pl-9 pr-3 py-1.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                    </div>

                    <!-- Filtre domaine -->
                    <select name="domain_id" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Tous les domaines / pôles</option>
                        <option value="central" {{ request('domain_id') === 'central' ? 'selected' : '' }}>Trésorerie Centrale (Siège)</option>
                        @foreach($domains as $dom)
                            <option value="{{ $dom->id }}" {{ request('domain_id') == $dom->id ? 'selected' : '' }}>{{ $dom->name }}</option>
                        @endforeach
                    </select>

                    <!-- Filtre statut -->
                    <select name="status" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives uniquement</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives uniquement</option>
                    </select>

                    @if(request()->anyFilled(['search', 'domain_id', 'status']))
                        <a href="{{ route('finance.cash-registers.index') }}" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:text-[#0B0F14] hover:bg-slate-100 transition">
                            Réinitialiser
                        </a>
                    @endif
                </div>

                <!-- Switcher de vue (Cartes / Tableau) -->
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
             VUE 1 : GRILLE DES CARTES DE CAISSES
             ======================================================================= --}}
        <div x-show="viewMode === 'cards'" class="space-y-4">
            <!-- Indicateur de swipe mobile -->
            @if($cashRegisters->isNotEmpty())
                <div class="flex sm:hidden items-center justify-between text-xs text-[#64748B] px-1 -mb-1">
                    <span class="font-medium">{{ $cashRegisters->count() }} caisses répertoriées</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-[#0066FF]">
                        <span>Faire défiler</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            @endif

            <div class="flex sm:grid overflow-x-auto sm:overflow-visible gap-5 pb-3 pt-1 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid-cols-2 lg:grid-cols-3 snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @forelse($cashRegisters as $caisse)
                    <div class="w-[84vw] max-w-[320px] min-w-[270px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:min-w-0 sm:shrink rounded-2xl bg-white border border-[#E2E8F0] p-5 shadow-xs hover:border-[#0066FF] hover:shadow-sm transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <div class="min-w-0">
                                    <h3 class="text-base font-bold text-[#0B0F14] truncate" title="{{ $caisse->name }}">{{ $caisse->name }}</h3>
                                    <span class="inline-flex items-center rounded-md bg-[#F5F7FA] px-2 py-0.5 text-xs font-mono font-medium text-[#64748B] border border-[#E2E8F0] mt-1">
                                        {{ $caisse->code }}
                                    </span>
                                </div>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold border shrink-0 {{ $caisse->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $caisse->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <div class="mt-3 text-xs text-[#64748B] flex items-center gap-1.5">
                                <span class="text-slate-400">Pôle :</span>
                                @if($caisse->domain)
                                    <span class="font-semibold text-[#0B0F14] inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                                        {{ $caisse->domain->name }}
                                    </span>
                                @else
                                    <span class="font-semibold text-[#0B0F14] inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        Trésorerie Centrale (Siège)
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Solde Actuel -->
                            <div class="mt-4 p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]/70">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Solde Actuel Disponible</p>
                                <p class="text-xl font-black text-[#0066FF] mt-0.5">{{ number_format($caisse->balance, 0, ',', ' ') }} <span class="text-xs font-bold text-slate-500">FCFA</span></p>
                            </div>
                        </div>

                        <!-- Actions Carte -->
                        <div class="mt-5 pt-3.5 border-t border-[#E2E8F0] space-y-2">
                            <div class="flex items-center gap-2">
                                <x-button href="{{ route('finance.cash-registers.show', $caisse) }}" variant="secondary" size="sm" class="flex-1 justify-center inline-flex items-center gap-1.5">
                                    <span>Transactions</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </x-button>

                                <button type="button" @click="openEditModal({{ Js::from($caisse) }})" 
                                        class="p-2 rounded-lg border border-[#E2E8F0] hover:bg-[#F5F7FA] text-slate-600 hover:text-[#0066FF] transition"
                                        title="Modifier la caisse">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1">
                                <!-- Basculer statut -->
                                <form action="{{ route('finance.cash-registers.toggle', $caisse) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-[11px] font-semibold transition hover:underline {{ $caisse->is_active ? 'text-amber-600 hover:text-amber-700' : 'text-emerald-600 hover:text-emerald-700' }}">
                                        {{ $caisse->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>

                                <!-- Supprimer (uniquement si aucune transaction) -->
                                @if(!$caisse->transactions()->exists() && !$caisse->expenses()->exists() && !$caisse->revenues()->exists())
                                    <form action="{{ route('finance.cash-registers.destroy', $caisse) }}" method="POST" onsubmit="return confirm('Confirmez-vous la suppression de cette caisse ?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] font-medium text-rose-500 hover:text-rose-700 transition hover:underline">
                                            Supprimer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <x-empty-state 
                            title="Aucune caisse trouvée" 
                            description="Aucune caisse ne correspond à vos critères de recherche. Vous pouvez utiliser le formulaire ci-dessus pour ajouter plusieurs caisses."
                        />
                    </div>
                @endforelse
            </div>
        </div>

        {{-- =======================================================================
             VUE 2 : TABLEAU DÉTAILLÉ
             ======================================================================= --}}
        <div x-show="viewMode === 'table'" class="rounded-2xl border border-[#E2E8F0] bg-white overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase font-semibold text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Nom de la Caisse</th>
                            <th class="py-3 px-4">Domaine / Pôle</th>
                            <th class="py-3 px-4 text-right">Solde Actuel</th>
                            <th class="py-3 px-4 text-center">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($cashRegisters as $caisse)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-[#0B0F14]">
                                    <span class="rounded bg-[#F5F7FA] border border-[#E2E8F0] px-2 py-0.5">{{ $caisse->code }}</span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-[#0B0F14]">
                                    <a href="{{ route('finance.cash-registers.show', $caisse) }}" class="hover:text-[#0066FF] hover:underline">
                                        {{ $caisse->name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-[#64748B]">
                                    {{ $caisse->domain->name ?? 'Trésorerie Centrale (Siège)' }}
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-[#0066FF]">
                                    {{ number_format($caisse->balance, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold border {{ $caisse->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        {{ $caisse->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('finance.cash-registers.show', $caisse) }}" 
                                           class="p-1.5 rounded-lg border border-[#E2E8F0] text-slate-600 hover:text-[#0066FF] hover:bg-slate-50 transition" title="Transactions">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <button type="button" @click="openEditModal({{ Js::from($caisse) }})"
                                                class="p-1.5 rounded-lg border border-[#E2E8F0] text-slate-600 hover:text-[#0066FF] hover:bg-slate-50 transition" title="Modifier">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>

                                        <form action="{{ route('finance.cash-registers.toggle', $caisse) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg border border-[#E2E8F0] hover:bg-slate-50 transition {{ $caisse->is_active ? 'text-amber-600' : 'text-emerald-600' }}" title="{{ $caisse->is_active ? 'Désactiver' : 'Activer' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">
                                    Aucune caisse enregistrée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- =======================================================================
             MODAL : MODIFIER UNE CAISSE
             ======================================================================= --}}
        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="editModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#0B0F14]">
                        Modifier la Caisse
                    </h3>
                    <button type="button" @click="editModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="editActionUrl" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nom de la caisse <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Code identifiant unique <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="editForm.code" required maxlength="30"
                               @input="editForm.code = editForm.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                               class="mt-1 w-full font-mono uppercase rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Pôle d'activité / Domaine</label>
                        <select name="domain_id" x-model="editForm.domain_id"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                            <option value="">Trésorerie Centrale (Siège)</option>
                            <template x-for="domain in domains" :key="domain.id">
                                <option :value="domain.id" x-text="domain.name" :selected="domain.id == editForm.domain_id"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer mt-1">
                            <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active"
                                   class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs font-medium text-slate-700">Caisse active (autorise les opérations)</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <button type="button" @click="editModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0052CC]">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =======================================================================
             MODAL : AJOUT D'UNE SEULE CAISSE
             ======================================================================= --}}
        <div x-show="singleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="singleModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#0B0F14]">
                        Ajouter une Caisse
                    </h3>
                    <button type="button" @click="singleModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('finance.cash-registers.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nom de la caisse <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="singleForm.name" @blur="suggestSingleCode()" required placeholder="ex: Caisse Boutique Nord"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Code identifiant unique <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="singleForm.code" required maxlength="30" placeholder="ex: CS-NORD"
                               @input="singleForm.code = singleForm.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                               class="mt-1 w-full font-mono uppercase rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Pôle d'activité / Domaine</label>
                        <select name="domain_id" x-model="singleForm.domain_id"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                            <option value="">Trésorerie Centrale (Siège)</option>
                            <template x-for="domain in domains" :key="domain.id">
                                <option :value="domain.id" x-text="domain.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Solde d'ouverture initial (FCFA)</label>
                        <input type="number" name="balance" min="0" step="any" value="0"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer mt-1">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs font-medium text-slate-700">Activer immédiatement</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <button type="button" @click="singleModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0052CC]">Créer la caisse</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Script Alpine.js pour la gestion multi-caisses -->
    <script>
        function cashRegisterManager(config) {
            return {
                domains: config.domains || [],
                showBatchSection: config.initialShowBatch,
                viewMode: 'cards',
                editModal: false,
                singleModal: false,
                editActionUrl: '',
                editForm: {
                    name: '',
                    code: '',
                    domain_id: '',
                    is_active: true
                },
                singleForm: {
                    name: '',
                    code: '',
                    domain_id: ''
                },
                rows: [
                    { name: '', code: '', domain_id: '', balance: 0, is_active: true },
                    { name: '', code: '', domain_id: '', balance: 0, is_active: true }
                ],

                addRow(data = null) {
                    if (data) {
                        this.rows.push({
                            name: data.name || '',
                            code: data.code || '',
                            domain_id: data.domain_id || '',
                            balance: data.balance || 0,
                            is_active: data.is_active !== undefined ? data.is_active : true
                        });
                    } else {
                        this.rows.push({
                            name: '',
                            code: '',
                            domain_id: '',
                            balance: 0,
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
                        { name: '', code: '', domain_id: '', balance: 0, is_active: true }
                    ];
                },

                prefillByDomains() {
                    this.rows = [];
                    // Ajouter chaque pôle
                    this.domains.forEach(d => {
                        const cleanCode = (d.code || 'POL').toUpperCase();
                        const shortName = d.name.replace('IVOSPHERE ', '');
                        const randomSuffix = Math.floor(10 + Math.random() * 89);
                        this.rows.push({
                            name: 'Caisse ' + shortName,
                            code: 'CS-' + cleanCode + '-' + randomSuffix,
                            domain_id: d.id,
                            balance: 0,
                            is_active: true
                        });
                    });

                    // Ajouter aussi une Caisse Comptoir Siège
                    const randomCentral = Math.floor(10 + Math.random() * 89);
                    this.rows.unshift({
                        name: 'Caisse Comptoir Siège',
                        code: 'CS-SIEGE-' + randomCentral,
                        domain_id: '',
                        balance: 0,
                        is_active: true
                    });
                },

                suggestCode(index) {
                    const row = this.rows[index];
                    if (row && (!row.code || row.code.trim() === '')) {
                        let base = row.name.trim().toUpperCase()
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^A-Z0-9]/g, '');
                        if (base.length > 10) base = base.substring(0, 10);
                        if (base) {
                            row.code = 'CS-' + base;
                        }
                    }
                },

                suggestSingleCode() {
                    if (!this.singleForm.code || this.singleForm.code.trim() === '') {
                        let base = this.singleForm.name.trim().toUpperCase()
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^A-Z0-9]/g, '');
                        if (base.length > 10) base = base.substring(0, 10);
                        if (base) {
                            this.singleForm.code = 'CS-' + base;
                        }
                    }
                },

                get totalInitialBalance() {
                    return this.rows.reduce((sum, r) => sum + (parseFloat(r.balance) || 0), 0);
                },

                get totalBalanceFormatted() {
                    return new Intl.NumberFormat('fr-FR').format(this.totalInitialBalance) + ' FCFA';
                },

                openSingleModal() {
                    this.singleForm = { name: '', code: '', domain_id: '' };
                    this.singleModal = true;
                },

                openEditModal(caisse) {
                    this.editForm = {
                        name: caisse.name,
                        code: caisse.code,
                        domain_id: caisse.domain_id || '',
                        is_active: Boolean(caisse.is_active)
                    };
                    this.editActionUrl = '/finance/cash-registers/' + caisse.id;
                    this.editModal = true;
                }
            };
        }
    </script>
</x-layouts.app>
