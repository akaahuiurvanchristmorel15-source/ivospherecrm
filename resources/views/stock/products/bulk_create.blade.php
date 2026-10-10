<x-layouts.app>
    <x-slot:title>Ajout Groupé de Nouveaux Produits — Stocks & Logistique (WMS)</x-slot>

    <div 
        x-data="bulkProductManager({
            defaultTaxRate: {{ $defaultTaxRate }},
            warehouses: {{ Js::from($warehouses->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'code' => $w->code])) }},
            initialRows: 5
        })" 
        class="max-w-7xl mx-auto space-y-6 pb-16 text-xs"
    >
        <x-page-header 
            title="Ajout Groupé de Nouveaux Produits en Entrepôt"
            description="Création simultanée de plusieurs articles au catalogue et initialisation directe de leurs stocks dans l'entrepôt de votre choix"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Stocks & Logistique', 'url' => route('stock.index')],
                    ['label' => 'Stock Disponible', 'url' => route('stock.index', ['tab' => 'disponibilite'])],
                    ['label' => 'Ajout Groupé']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('stock.index', ['tab' => 'disponibilite']) }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour au Stock</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        @if($errors->any())
            <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 space-y-1">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Veuillez corriger les erreurs de saisie :</span>
                </div>
                <ul class="list-disc list-inside text-[11px] space-y-0.5 pl-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stock.products.bulk-store') }}" method="POST" id="bulkProductForm" class="space-y-6">
            @csrf

            {{-- 1. Paramètres Communs du Lot & Entrepôt Cible --}}
            <x-card title="1. Entrepôt Cible & Paramètres Généraux du Lot">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Sélection de l'entrepôt --}}
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-[#0B0F14] mb-1.5">
                            Entrepôt de Dépôt Récepteur <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="warehouse_id" 
                            required 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] font-medium text-xs focus:outline-none focus:border-[#0066FF] shadow-2xs transition"
                        >
                            <option value="">-- Sélectionnez l'entrepôt d'accueil --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ (string) old('warehouse_id', $warehouses->first()?->id) === (string) $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} (Code : {{ $wh->code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Tous les articles du tableau ci-dessous seront rattachés et stockés dans cet entrepôt.</p>
                    </div>

                    {{-- Domaine par défaut --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Domaine d'Activité par Défaut
                        </label>
                        <select 
                            name="domain_id" 
                            x-model="defaultDomainId"
                            @change="applyDefaultDomainToEmptyRows()"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] shadow-2xs transition"
                        >
                            <option value="">-- Aucun (Général) --</option>
                            @foreach($domains as $d)
                                <option value="{{ $d->id }}" {{ (string) old('domain_id') === (string) $d->id ? 'selected' : '' }}>
                                    {{ strtoupper($d->name) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Préremplit automatiquement le domaine pour toutes les lignes.</p>
                    </div>

                    {{-- Catégorie par défaut --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Catégorie par Défaut (Optionnel)
                        </label>
                        <select 
                            name="category_id" 
                            x-model="defaultCategoryId"
                            @change="applyDefaultCategoryToEmptyRows()"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] shadow-2xs transition"
                        >
                            <option value="">-- Aucune catégorie spécifique --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string) old('category_id') === (string) $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Catégorie assignée aux lignes sans catégorie spécifique.</p>
                    </div>
                </div>

                {{-- Note d'automatisation --}}
                <div class="mt-4 p-3 rounded-xl bg-blue-50/70 border border-blue-100 flex items-center justify-between flex-wrap gap-2 text-[11px] text-blue-900">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                        <span><strong>Automatisations appliquées :</strong> Codes EAN-13 GS1 générés automatiquement • Réf. SKU attribuée si vide • TVA configurée à <strong>{{ $defaultTaxRate }}%</strong> • Mouvement initial WMS généré pour chaque unité entrée.</span>
                    </div>
                    <div class="font-mono font-bold text-[#0066FF]">
                        TVA Catalogue : {{ $defaultTaxRate }}%
                    </div>
                </div>
            </x-card>

            {{-- 2. Tableau de Saisie Rapide Multi-Produits --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0B0F14]">
                            2. Grille de Saisie Rapide des Articles
                        </h2>
                        <p class="text-xs text-slate-500">
                            Renseignez les désignations, prix de vente et quantités initiales. Remplissez autant de lignes que nécessaire.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button 
                            type="button" 
                            @click="addRow()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ 1 ligne</span>
                        </button>
                        <button 
                            type="button" 
                            @click="addRows(5)" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ 5 lignes</span>
                        </button>
                    </div>
                </div>

                {{-- Table responsive --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-10 px-3 py-3 text-center">#</th>
                                <th class="min-w-[220px] px-3 py-3">Désignation du Produit <span class="text-rose-500">*</span></th>
                                <th class="min-w-[130px] px-3 py-3">SKU / Réf. (Optionnel)</th>
                                <th class="min-w-[130px] px-3 py-3">Domaine</th>
                                <th class="min-w-[110px] px-3 py-3 text-right">Prix Achat</th>
                                <th class="min-w-[120px] px-3 py-3 text-right">Prix Vente <span class="text-rose-500">*</span></th>
                                <th class="min-w-[110px] px-3 py-3 text-right">Quantité Initiale <span class="text-rose-500">*</span></th>
                                <th class="min-w-[90px] px-3 py-3 text-right">Seuil Min</th>
                                <th class="w-12 px-3 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in products" :key="item.uid">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Index --}}
                                    <td class="px-3 py-2 text-center font-mono text-slate-400 font-bold" x-text="index + 1"></td>

                                    {{-- Désignation --}}
                                    <td class="px-3 py-2">
                                        <input 
                                            type="text" 
                                            :name="'products[' + index + '][name]'" 
                                            x-model="item.name" 
                                            required
                                            placeholder="Ex: Rame Papier A4 80g" 
                                            class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] transition font-medium"
                                        >
                                    </td>

                                    {{-- SKU --}}
                                    <td class="px-3 py-2">
                                        <input 
                                            type="text" 
                                            :name="'products[' + index + '][sku]'" 
                                            x-model="item.sku" 
                                            placeholder="Auto-généré si vide" 
                                            class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs font-mono focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                    </td>

                                    {{-- Domaine --}}
                                    <td class="px-3 py-2">
                                        <select 
                                            :name="'products[' + index + '][domain_id]'" 
                                            x-model="item.domain_id"
                                            class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                            <option value="">Défaut général</option>
                                            @foreach($domains as $d)
                                                <option value="{{ $d->id }}">{{ strtoupper($d->name) }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- Prix Achat --}}
                                    <td class="px-3 py-2 text-right">
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            :name="'products[' + index + '][purchase_price]'" 
                                            x-model.number="item.purchase_price" 
                                            placeholder="0" 
                                            class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                    </td>

                                    {{-- Prix Vente --}}
                                    <td class="px-3 py-2 text-right">
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0" 
                                            required
                                            :name="'products[' + index + '][selling_price]'" 
                                            x-model.number="item.selling_price" 
                                            placeholder="0" 
                                            class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono font-bold focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                    </td>

                                    {{-- Quantité Initiale --}}
                                    <td class="px-3 py-2 text-right">
                                        <input 
                                            type="number" 
                                            min="0" 
                                            step="1"
                                            :name="'products[' + index + '][initial_quantity]'" 
                                            x-model.number="item.initial_quantity" 
                                            placeholder="0" 
                                            class="w-full px-2 py-1.5 rounded-lg bg-emerald-50/50 border border-emerald-200 text-emerald-950 text-xs text-right font-mono font-bold focus:outline-none focus:border-emerald-600 transition"
                                        >
                                    </td>

                                    {{-- Seuil Min --}}
                                    <td class="px-3 py-2 text-right">
                                        <input 
                                            type="number" 
                                            min="0" 
                                            step="1"
                                            :name="'products[' + index + '][min_stock]'" 
                                            x-model.number="item.min_stock" 
                                            placeholder="0" 
                                            class="w-full px-2 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                    </td>

                                    {{-- Bouton Supprimer Ligne --}}
                                    <td class="px-3 py-2 text-center">
                                        <button 
                                            type="button" 
                                            @click="removeRow(index)" 
                                            :disabled="products.length <= 1"
                                            class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition disabled:opacity-30 disabled:cursor-not-allowed" 
                                            title="Supprimer cette ligne"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Barre Récapitulative Totaux du Lot --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button 
                            type="button" 
                            @click="addRow()" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs"
                        >
                            <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Ajouter une autre ligne</span>
                        </button>

                        <button 
                            type="button" 
                            @click="addRows(5)" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs"
                        >
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Ajouter 5 lignes</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <div class="text-slate-600">
                            Articles à créer : <span class="font-bold text-[#0B0F14] font-mono" x-text="validRowsCount()"></span>
                        </div>
                        <div class="text-slate-600">
                            Total Unités entrantes : <span class="font-bold text-emerald-600 font-mono" x-text="totalQuantity().toLocaleString('fr-FR')"></span>
                        </div>
                        <div class="text-slate-600">
                            Valeur Vente du Lot : <span class="font-bold text-[#0066FF] font-mono" x-text="totalSellingValue().toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions Formulaire --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <x-button variant="secondary" href="{{ route('stock.index', ['tab' => 'disponibilite']) }}">
                    Annuler
                </x-button>

                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white px-6 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Valider et Enregistrer Tous les Produits dans l'Entrepôt</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function bulkProductManager(config) {
            return {
                defaultDomainId: '{{ old('domain_id', '') }}',
                defaultCategoryId: '{{ old('category_id', '') }}',
                defaultTaxRate: config.defaultTaxRate || 0,
                products: [],

                init() {
                    const count = config.initialRows || 5;
                    for (let i = 0; i < count; i++) {
                        this.addRow();
                    }
                },

                createEmptyRow() {
                    return {
                        uid: Date.now() + Math.random().toString(36).substr(2, 9),
                        name: '',
                        sku: '',
                        domain_id: this.defaultDomainId || '',
                        purchase_price: '',
                        selling_price: '',
                        initial_quantity: '',
                        min_stock: '5'
                    };
                },

                addRow() {
                    this.products.push(this.createEmptyRow());
                },

                addRows(count) {
                    for (let i = 0; i < count; i++) {
                        this.products.push(this.createEmptyRow());
                    }
                },

                removeRow(index) {
                    if (this.products.length > 1) {
                        this.products.splice(index, 1);
                    }
                },

                applyDefaultDomainToEmptyRows() {
                    this.products.forEach(p => {
                        if (!p.domain_id) {
                            p.domain_id = this.defaultDomainId;
                        }
                    });
                },

                applyDefaultCategoryToEmptyRows() {
                    // Si on ajoute la sélection catégorie par ligne
                },

                validRowsCount() {
                    return this.products.filter(p => p.name && p.name.trim().length > 0).length;
                },

                totalQuantity() {
                    return this.products.reduce((sum, p) => sum + (parseInt(p.initial_quantity, 10) || 0), 0);
                },

                totalSellingValue() {
                    return this.products.reduce((sum, p) => {
                        const qty = parseInt(p.initial_quantity, 10) || 0;
                        const price = parseFloat(p.selling_price) || 0;
                        return sum + (qty * price);
                    }, 0);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
