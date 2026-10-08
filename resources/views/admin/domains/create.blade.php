<x-layouts.app>
    <x-slot:title>Ajout de Domaines Métiers</x-slot>

    <x-slot name="header">
        <x-page-header 
            title="Ajout de Domaines d'Activité" 
            subtitle="Configurez un ou plusieurs nouveaux pôles d'activité en une seule opération">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Domaines Métiers', 'url' => route('admin.domains.index')],
                    ['label' => 'Nouveaux Domaines']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('admin.domains.index') }}" variant="secondary" size="sm" class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux domaines</span>
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div x-data="createDomainsPage()" class="space-y-6">

        <!-- Onglets Mode de Saisie (Groupé vs Unique) -->
        <div class="flex items-center gap-2 border-b border-[#E2E8F0] pb-3">
            <button type="button" @click="mode = 'batch'"
                    :class="mode === 'batch' ? 'bg-[#0066FF] text-white shadow-xs font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-[#E2E8F0]'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Ajout Multiple (Plusieurs domaines à la fois)</span>
            </button>

            <button type="button" @click="mode = 'single'"
                    :class="mode === 'single' ? 'bg-[#0066FF] text-white shadow-xs font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-[#E2E8F0]'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Ajout Simple (Un seul domaine)</span>
            </button>
        </div>

        {{-- =======================================================================
             MODE 1 : AJOUT MULTIPLE DE DOMAINES
             ======================================================================= --}}
        <div x-show="mode === 'batch'" class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#E2E8F0] pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#0B0F14]">Grille de Saisie Multi-Domaines</h2>
                    <p class="text-xs text-[#64748B]">Renseignez plusieurs pôles d'activité ou utilisez nos suggestions pour une configuration rapide.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="prefillSuggestions()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>⚡ Suggestions de Nouveaux Pôles</span>
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

            <form action="{{ route('admin.domains.batch') }}" method="POST" class="space-y-4">
                @csrf

                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#64748B] uppercase font-semibold text-[11px]">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">#</th>
                                <th class="py-3 px-3 min-w-[220px]">Nom du Domaine <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[150px]">Code Unique <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[220px]">Description Métier</th>
                                <th class="py-3 px-3 min-w-[150px]">Couleur</th>
                                <th class="py-3 px-3 min-w-[130px]">Icône</th>
                                <th class="py-3 px-3 w-24 text-center">Statut</th>
                                <th class="py-3 px-3 w-12 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" :name="`domains[${index}][name]`" x-model="row.name" @blur="suggestCode(index)" required
                                               placeholder="ex: IVOSPHERE LOGISTIQUE"
                                               class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" :name="`domains[${index}][code]`" x-model="row.code" required maxlength="50"
                                               @input="row.code = row.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                                               placeholder="ex: LOGISTIQUE"
                                               class="w-full px-3 py-1.5 font-mono uppercase font-bold text-[#0066FF] rounded-lg border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" :name="`domains[${index}][description]`" x-model="row.description"
                                               placeholder="ex: Transport et entreposage"
                                               class="w-full px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-xs focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs" :style="`background-color: ${getColorHex(row.color)}`"></span>
                                            <select :name="`domains[${index}][color]`" x-model="row.color"
                                                    class="w-full px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] text-xs bg-white focus:ring-1 focus:ring-[#0066FF]">
                                                <option value="indigo">Indigo</option>
                                                <option value="emerald">Émeraude</option>
                                                <option value="sky">Ciel</option>
                                                <option value="purple">Violet</option>
                                                <option value="amber">Ambre</option>
                                                <option value="rose">Rose</option>
                                                <option value="teal">Sarcelle</option>
                                                <option value="slate">Ardoise</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <select :name="`domains[${index}][icon]`" x-model="row.icon"
                                                class="w-full px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] text-xs bg-white focus:ring-1 focus:ring-[#0066FF]">
                                            <option value="briefcase">Mallette</option>
                                            <option value="truck">Camion</option>
                                            <option value="building-office">Bâtiment</option>
                                            <option value="academic-cap">Formation</option>
                                            <option value="shield-check">Bouclier</option>
                                            <option value="shopping-bag">Commerce</option>
                                            <option value="globe-alt">International</option>
                                            <option value="chart-bar">Graphique</option>
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="hidden" :name="`domains[${index}][is_active]`" value="0">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" :name="`domains[${index}][is_active]`" value="1" x-model="row.is_active"
                                                   class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                                            <span class="text-[11px] font-medium" :class="row.is_active ? 'text-emerald-700' : 'text-slate-400'" x-text="row.is_active ? 'Actif' : 'Inactif'"></span>
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
                    <div class="text-xs text-[#64748B]">
                        <span>Domaines à enregistrer :</span>
                        <span class="font-bold text-[#0B0F14] ml-1 bg-slate-100 px-2 py-0.5 rounded" x-text="rows.length"></span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                            <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            <span>+ Ajouter une ligne de domaine</span>
                        </button>

                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span x-text="`Valider et créer les ${rows.length} domaine(s)`"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- =======================================================================
             MODE 2 : AJOUT UNIQUE (1 DOMAINE)
             ======================================================================= --}}
        <div x-show="mode === 'single'" class="max-w-xl mx-auto rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-[#0B0F14] border-b border-[#E2E8F0] pb-3">Création d'un Seul Domaine Métier</h2>

            <form action="{{ route('admin.domains.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nom du Domaine <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="ex: IVOSPHERE LOGISTIQUE"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Code unique <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" required maxlength="50" placeholder="ex: LOGISTIQUE"
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
                    <a href="{{ route('admin.domains.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0052CC]">Créer le domaine</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function createDomainsPage() {
            return {
                mode: 'batch',
                rows: [
                    { name: '', code: '', description: '', color: 'indigo', icon: 'briefcase', is_active: true },
                    { name: '', code: '', description: '', color: 'emerald', icon: 'truck', is_active: true },
                    { name: '', code: '', description: '', color: 'amber', icon: 'building-office', is_active: true }
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
                }
            };
        }
    </script>
</x-layouts.app>
