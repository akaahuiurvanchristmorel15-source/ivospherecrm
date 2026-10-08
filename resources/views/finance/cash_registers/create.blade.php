<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Ajout de Caisses" 
            subtitle="Configurez une ou plusieurs caisses de trésorerie en une seule saisie">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Caisses', 'url' => route('finance.cash-registers.index')],
                    ['label' => 'Nouvelles Caisses']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('finance.cash-registers.index') }}" variant="secondary" size="sm" class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la liste des caisses</span>
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div x-data="createCashRegistersPage({{ Js::from($domains) }})" class="space-y-6">

        <!-- Onglets Mode de Saisie (Ajout Groupé vs Ajout Unique) -->
        <div class="flex items-center gap-2 border-b border-[#E2E8F0] pb-3">
            <button type="button" @click="mode = 'batch'"
                    :class="mode === 'batch' ? 'bg-[#0066FF] text-white shadow-xs font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-[#E2E8F0]'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Ajout Multiple (Plusieurs caisses à la fois)</span>
            </button>

            <button type="button" @click="mode = 'single'"
                    :class="mode === 'single' ? 'bg-[#0066FF] text-white shadow-xs font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-[#E2E8F0]'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Ajout Simple (Une seule caisse)</span>
            </button>
        </div>

        {{-- =======================================================================
             MODE 1 : AJOUT MULTIPLE DE CAISSES
             ======================================================================= --}}
        <div x-show="mode === 'batch'" class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#E2E8F0] pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#0B0F14]">Grille de Saisie Multi-Caisses</h2>
                    <p class="text-xs text-[#64748B]">Renseignez les caisses ligne par ligne ou générez automatiquement les caisses par pôle.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="prefillByDomains()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>⚡ Générer les caisses de chaque Pôle</span>
                    </button>

                    <button type="button" @click="addRows(3)"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                        + 3 lignes
                    </button>

                    <button type="button" @click="resetRows()"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-semibold transition">
                        Vider
                    </button>
                </div>
            </div>

            <form action="{{ route('finance.cash-registers.batch') }}" method="POST" class="space-y-4">
                @csrf

                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase font-semibold text-[11px]">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">#</th>
                                <th class="py-3 px-3 min-w-[220px]">Nom de la Caisse <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[150px]">Code Unique <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[190px]">Domaine / Pôle</th>
                                <th class="py-3 px-3 min-w-[150px]">Solde Initial (FCFA)</th>
                                <th class="py-3 px-3 w-28 text-center">Statut</th>
                                <th class="py-3 px-3 w-12 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" :name="`registers[${index}][name]`" x-model="row.name" @blur="suggestCode(index)" required
                                               placeholder="ex: Caisse Comptoir 1"
                                               class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" :name="`registers[${index}][code]`" x-model="row.code" required maxlength="30"
                                               @input="row.code = row.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                                               placeholder="ex: CS-CPT1"
                                               class="w-full px-3 py-1.5 font-mono uppercase rounded-lg border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <select :name="`registers[${index}][domain_id]`" x-model="row.domain_id"
                                                class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs bg-white focus:ring-1 focus:ring-[#0066FF]">
                                            <option value="">Trésorerie Centrale (Siège)</option>
                                            <template x-for="domain in domains" :key="domain.id">
                                                <option :value="domain.id" x-text="domain.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="relative">
                                            <input type="number" :name="`registers[${index}][balance]`" x-model.number="row.balance" min="0" step="any" placeholder="0"
                                                   class="w-full pl-3 pr-12 py-1.5 rounded-lg border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF]">
                                            <span class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[11px] font-semibold text-slate-400">FCFA</span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="hidden" :name="`registers[${index}][is_active]`" value="0">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" :name="`registers[${index}][is_active]`" value="1" x-model="row.is_active"
                                                   class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                                            <span class="text-[11px] font-medium" :class="row.is_active ? 'text-emerald-700' : 'text-slate-400'" x-text="row.is_active ? 'Active' : 'Inactive'"></span>
                                        </label>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button" @click="removeRow(index)" :disabled="rows.length <= 1"
                                                :class="rows.length <= 1 ? 'opacity-30 cursor-not-allowed text-slate-300' : 'text-rose-500 hover:text-rose-700 hover:bg-rose-50'"
                                                class="p-1 rounded-lg transition" title="Supprimer la ligne">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-3 border-t border-[#E2E8F0]">
                    <div class="flex items-center gap-4 text-xs text-[#64748B]">
                        <div>
                            <span>Caisses à enregistrer :</span>
                            <span class="font-bold text-[#0B0F14] ml-1 bg-slate-100 px-2 py-0.5 rounded" x-text="rows.length"></span>
                        </div>
                        <div>
                            <span>Total initial injecté :</span>
                            <span class="font-bold text-[#0066FF] ml-1" x-text="totalBalanceFormatted"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                            <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            <span>+ Ajouter une ligne</span>
                        </button>

                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span x-text="`Valider et créer les ${rows.length} caisses`"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- =======================================================================
             MODE 2 : AJOUT UNIQUE (1 CAISSE)
             ======================================================================= --}}
        <div x-show="mode === 'single'" class="max-w-xl mx-auto rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-[#0B0F14] border-b border-[#E2E8F0] pb-3">Création d'une Seule Caisse</h2>

            <form action="{{ route('finance.cash-registers.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nom de la caisse <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="ex: Caisse Boutique Sud"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Code identifiant unique <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" required maxlength="30" placeholder="ex: CS-SUD"
                           class="mt-1 w-full font-mono uppercase rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Pôle d'activité / Domaine</label>
                    <select name="domain_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none bg-white">
                        <option value="">Trésorerie Centrale (Siège)</option>
                        @foreach($domains as $dom)
                            <option value="{{ $dom->id }}">{{ $dom->name }}</option>
                        @endforeach
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
                        <span class="text-xs font-medium text-slate-700">Activer immédiatement cette caisse</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="{{ route('finance.cash-registers.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0052CC]">Créer la caisse</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function createCashRegistersPage(domains) {
            return {
                mode: 'batch',
                domains: domains || [],
                rows: [
                    { name: '', code: '', domain_id: '', balance: 0, is_active: true },
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
                    const randomCentral = Math.floor(10 + Math.random() * 89);
                    this.rows.unshift({
                        name: 'Caisse Secondaire Siège',
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
                get totalInitialBalance() {
                    return this.rows.reduce((sum, r) => sum + (parseFloat(r.balance) || 0), 0);
                },
                get totalBalanceFormatted() {
                    return new Intl.NumberFormat('fr-FR').format(this.totalInitialBalance) + ' FCFA';
                }
            };
        }
    </script>
</x-layouts.app>
