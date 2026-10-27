<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ $warehouse->name }}" 
            subtitle="Code : {{ $warehouse->code }} &bull; Domaine : {{ $warehouse->domain->name ?? 'Général' }}">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Entrepôts', 'url' => route('stock.warehouses.index')],
                    ['label' => $warehouse->name]
                ]" />
            </x-slot>
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <button 
                        type="button" 
                        x-data
                        @click="$dispatch('open-delete-warehouse', { 
                            id: {{ $warehouse->id }}, 
                            name: {{ Js::from($warehouse->name) }}, 
                            code: {{ Js::from($warehouse->code) }}, 
                            totalStock: {{ (int) $warehouse->warehouseStocks->sum('physical_quantity') }},
                            productCount: {{ (int) $warehouse->warehouseStocks->where('physical_quantity', '>', 0)->count() }}
                        })"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition border border-rose-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Supprimer</span>
                    </button>
                    @if(auth()->user()->hasPermission('stocks.catalog'))
                    <x-button href="{{ route('stock.products.print-catalog', ['warehouse_id' => $warehouse->id]) }}" target="_blank" variant="secondary">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimer le catalogue
                    </x-button>
                    <x-button href="{{ route('stock.products.download-catalog', ['warehouse_id' => $warehouse->id]) }}" variant="secondary">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Télécharger (PDF)
                    </x-button>
                    @endif
                    <x-button href="{{ route('stock.warehouses.qr-codes', $warehouse) }}" variant="secondary">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/></svg>
                        PDF des QR codes
                    </x-button>
                    <x-button href="{{ route('stock.warehouses.edit', $warehouse) }}" variant="secondary">
                        Modifier
                    </x-button>
                    <x-button href="{{ route('stock.movements.create', ['warehouse_id' => $warehouse->id]) }}" variant="primary">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau Mouvement
                    </x-button>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Informations Entrepôt -->
        <div class="lg:col-span-4 rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs h-fit">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-4">
                Informations du site
            </h3>
            
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Étiquette</dt>
                    <dd class="mt-1"><x-warehouse-label :warehouse="$warehouse" size="lg" /></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Code Entrepôt</dt>
                    <dd class="mt-1 text-sm font-semibold font-mono text-[#0B0F14]">{{ $warehouse->code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Domaine d'Activité</dt>
                    <dd class="mt-1 text-sm font-medium text-[#0B0F14]">{{ $warehouse->domain->name ?? 'Général' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Responsable Dédié</dt>
                    <dd class="mt-1 text-sm font-medium text-[#0B0F14]">{{ $warehouse->manager ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Statut Opérationnel</dt>
                    <dd class="mt-1">
                        @if($warehouse->is_active)
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                Actif
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                Inactif
                            </span>
                        @endif
                    </dd>
                </div>
                @if($warehouse->address)
                    <div>
                        <dt class="text-xs font-medium text-[#64748B]">Localisation / Adresse</dt>
                        <dd class="mt-1 text-sm text-[#0B0F14]">{{ $warehouse->address }}</dd>
                    </div>
                @endif
                @if($warehouse->description)
                    <div>
                        <dt class="text-xs font-medium text-[#64748B]">Notes & Spécificités</dt>
                        <dd class="mt-1 text-sm text-[#64748B] leading-relaxed">{{ $warehouse->description }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- État du Stock (et modification groupée des prix) -->
        @php
            $canEditPrices = auth()->user()->hasPermission('products.edit');
            $stockRows = $stockLevels->filter(fn ($level) => $level->product)->sortBy(fn ($level) => $level->product->name)->values()->map(fn ($level) => [
                'id' => $level->product->id,
                'name' => $level->product->name,
                'sku' => $level->product->sku,
                'quantity' => (int) $level->stock_after,
                'min_stock' => (int) ($level->product->min_stock ?? 10),
                'purchase' => (float) $level->product->purchase_price,
                'selling' => (float) $level->product->selling_price,
            ]);
        @endphp
        <div class="min-w-0 lg:col-span-8 rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden flex flex-col"
             x-data="warehousePrices(@js($stockRows))">
            <div class="p-5 border-b border-[#E2E8F0] flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-[#0B0F14]">État du Stock Disponible</h3>
                    <p class="text-xs text-[#64748B]" x-text="editing ? 'Cochez des produits puis modifiez leurs prix, un par un ou en lot.' : 'Inventaire actuel des produits référencés sur cet entrepôt'">Inventaire actuel des produits référencés sur cet entrepôt</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-[#F5F7FA] px-2.5 py-1 text-xs font-medium text-[#0B0F14] border border-[#E2E8F0]">
                        {{ count($stockRows) }} référence(s)
                    </span>
                    @if($canEditPrices && $stockRows->isNotEmpty())
                        <button type="button" x-show="! editing" @click="editing = true"
                                class="inline-flex h-9 items-center gap-1.5 rounded-full bg-[#0B0F14] px-4 text-xs font-semibold text-white hover:bg-black transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            Modifier les prix
                        </button>
                    @endif
                </div>
            </div>

            @if($canEditPrices)
                {{-- Outils de modification groupée --}}
                <div x-show="editing" x-cloak class="border-b border-[#E2E8F0] bg-[#F5F7FA]/60 p-4 space-y-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="font-semibold text-[#0B0F14]" x-text="selectedCount + ' produit(s) sélectionné(s)'"></span>
                        <button type="button" @click="toggleAll(true)" class="rounded-full border border-[#E2E8F0] bg-white px-3 py-1 text-[#0B0F14] hover:bg-slate-50">Tout sélectionner</button>
                        <button type="button" @click="toggleAll(false)" class="rounded-full border border-[#E2E8F0] bg-white px-3 py-1 text-[#0B0F14] hover:bg-slate-50">Aucun</button>
                    </div>
                    <div class="flex flex-wrap items-end gap-2">
                        <label class="w-full text-xs sm:w-auto">
                            <span class="mb-1 block font-medium text-[#64748B]">Appliquer à la sélection</span>
                            <select x-model="bulk.mode" class="h-9 w-full max-w-full rounded-full border border-[#E2E8F0] bg-white px-3 text-xs text-[#0B0F14] sm:w-auto">
                                <option value="selling_percent">Prix de vente : augmenter / baisser de %</option>
                                <option value="purchase_percent">Prix d'achat : augmenter / baisser de %</option>
                                <option value="margin">Prix de vente = prix d'achat + marge %</option>
                                <option value="selling_fixed">Prix de vente : même montant</option>
                                <option value="purchase_fixed">Prix d'achat : même montant</option>
                            </select>
                        </label>
                        <label class="text-xs">
                            <span class="mb-1 block font-medium text-[#64748B]" x-text="bulk.mode.endsWith('fixed') ? 'Montant (FCFA)' : 'Valeur (%)'"></span>
                            <input type="number" step="any" x-model.number="bulk.value" placeholder="ex : 10"
                                   class="h-9 w-28 rounded-full border border-[#E2E8F0] bg-white px-3 text-xs text-[#0B0F14]">
                        </label>
                        <button type="button" @click="applyBulk()" :disabled="! selectedCount || bulk.value === '' || bulk.value === null"
                                class="h-9 rounded-full border border-[#0B0F14] px-4 text-xs font-semibold text-[#0B0F14] hover:bg-[#0B0F14] hover:text-white transition disabled:opacity-40">
                            Appliquer
                        </button>
                        <label class="ml-auto inline-flex items-center gap-2 text-xs text-[#64748B]">
                            <input type="checkbox" x-model="roundPrices" class="rounded border-slate-300">
                            Arrondir à 5 F
                        </label>
                    </div>
                </div>
            @endif

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                        <tr>
                            <th x-show="editing" x-cloak class="w-10 py-3 pl-4">
                                <input type="checkbox" :checked="selectedCount === rows.length && rows.length > 0" @change="toggleAll($event.target.checked)" class="rounded border-slate-300" aria-label="Tout sélectionner">
                            </th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Quantité</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Prix d'achat</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Prix de vente</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Marge</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Niveau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="transition-colors" :class="isChanged(row) ? 'bg-amber-50/60' : 'hover:bg-[#F5F7FA]/60'">
                                <td x-show="editing" class="py-3 pl-4"><input type="checkbox" x-model="row.selected" class="rounded border-slate-300" :aria-label="'Sélectionner ' + row.name"></td>
                                <td class="py-3 px-4">
                                    <p class="text-sm font-semibold text-[#0B0F14]" x-text="row.name"></p>
                                    <p class="text-xs font-mono text-[#64748B]" x-text="row.sku || '-'"></p>
                                </td>
                                <td class="py-3 px-4 text-sm font-bold text-[#0B0F14] text-right" x-text="format(row.quantity)"></td>
                                <td class="py-3 px-4 text-right">
                                    <span x-show="! editing" class="text-sm text-[#0B0F14]" x-text="format(row.purchase) + ' F'"></span>
                                    <input x-show="editing" type="number" min="0" step="any" x-model.number="row.purchase" class="h-9 w-28 rounded-full border border-[#E2E8F0] bg-white px-3 text-right text-sm text-[#0B0F14]" :aria-label="'Prix d\'achat de ' + row.name">
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span x-show="! editing" class="text-sm font-semibold text-[#0B0F14]" x-text="format(row.selling) + ' F'"></span>
                                    <input x-show="editing" type="number" min="0" step="any" x-model.number="row.selling" class="h-9 w-28 rounded-full border border-[#E2E8F0] bg-white px-3 text-right text-sm font-semibold text-[#0B0F14]" :aria-label="'Prix de vente de ' + row.name">
                                </td>
                                <td class="py-3 px-4 text-right text-xs font-semibold" :class="margin(row) === null ? 'text-slate-400' : (margin(row) < 0 ? 'text-rose-600' : 'text-emerald-600')" x-text="margin(row) === null ? '—' : margin(row) + ' %'"></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold border"
                                          :class="row.quantity <= row.min_stock ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                          x-text="row.quantity <= row.min_stock ? 'Alerte (≤ ' + row.min_stock + ')' : 'Normal'"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="7" class="py-10 text-center text-sm text-[#64748B]">Aucun produit actuellement en stock dans cet entrepôt.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                <template x-for="row in rows" :key="'m' + row.id">
                    <div class="rounded-xl p-4 border flex flex-col gap-2.5" :class="isChanged(row) ? 'border-amber-300 bg-amber-50/60' : 'bg-white border-[#E2E8F0]'">
                        <div class="flex items-start justify-between gap-2">
                            <label class="flex min-w-0 items-start gap-2.5">
                                <input x-show="editing" type="checkbox" x-model="row.selected" class="mt-0.5 rounded border-slate-300">
                                <span class="min-w-0">
                                    <span class="block font-bold text-sm text-[#0B0F14]" x-text="row.name"></span>
                                    <span class="text-xs font-mono text-[#64748B] block mt-0.5" x-text="row.sku || '-'"></span>
                                </span>
                            </label>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold border shrink-0"
                                  :class="row.quantity <= row.min_stock ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                  x-text="row.quantity <= row.min_stock ? 'Alerte' : 'Normal'"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Quantité</span>
                                <span class="font-bold text-sm text-[#0B0F14]" x-text="format(row.quantity)"></span>
                            </div>
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Achat</span>
                                <span x-show="! editing" class="font-semibold text-sm text-[#0B0F14]" x-text="format(row.purchase)"></span>
                                <input x-show="editing" type="number" min="0" step="any" x-model.number="row.purchase" class="mt-0.5 h-8 w-full rounded-lg border border-[#E2E8F0] bg-white px-2 text-sm text-[#0B0F14]">
                            </div>
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Vente</span>
                                <span x-show="! editing" class="font-semibold text-sm text-[#0B0F14]" x-text="format(row.selling)"></span>
                                <input x-show="editing" type="number" min="0" step="any" x-model.number="row.selling" class="mt-0.5 h-8 w-full rounded-lg border border-[#E2E8F0] bg-white px-2 text-sm font-semibold text-[#0B0F14]">
                            </div>
                        </div>
                        <p class="text-[11px]" :class="margin(row) === null ? 'text-slate-400' : (margin(row) < 0 ? 'text-rose-600' : 'text-emerald-600')" x-text="margin(row) === null ? 'Marge : —' : 'Marge : ' + margin(row) + ' %'"></p>
                    </div>
                </template>
                <div x-show="rows.length === 0" class="py-8 text-center text-sm text-[#64748B]">Aucun produit actuellement en stock dans cet entrepôt.</div>
            </div>

            @if($canEditPrices)
                {{-- Barre d'enregistrement --}}
                <form x-show="editing" x-cloak method="POST" action="{{ route('stock.warehouses.prices', $warehouse) }}" @submit="prepare($event)"
                      class="sticky bottom-[5.5rem] z-10 flex flex-wrap items-center justify-between gap-3 border-t border-[#E2E8F0] bg-white/95 p-4 backdrop-blur md:bottom-0">
                    @csrf
                    <input type="hidden" name="prices_json" x-ref="pricesJson">
                    <p class="text-xs text-[#64748B]"><span class="font-semibold text-[#0B0F14]" x-text="changedRows.length"></span> produit(s) modifié(s)</p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="cancel()" class="h-10 rounded-full border border-[#E2E8F0] px-4 text-sm font-medium text-[#0B0F14] hover:bg-slate-50">Annuler</button>
                        <button type="submit" :disabled="changedRows.length === 0 || invalid" class="h-10 rounded-full bg-[#0B0F14] px-5 text-sm font-semibold text-white hover:bg-black transition disabled:opacity-40">
                            Enregistrer les prix
                        </button>
                    </div>
                </form>
            @endif
        </div>

        @push('scripts')
            <script>
                function warehousePrices(products) {
                    return {
                        editing: false,
                        roundPrices: true,
                        bulk: { mode: 'selling_percent', value: '' },
                        original: JSON.parse(JSON.stringify(products)),
                        rows: products.map((product) => ({ ...product, selected: false })),

                        get selectedCount() { return this.rows.filter((row) => row.selected).length; },
                        get changedRows() { return this.rows.filter((row) => this.isChanged(row)); },
                        get invalid() { return this.rows.some((row) => row.selling === '' || row.selling < 0 || row.purchase < 0); },

                        isChanged(row) {
                            const initial = this.original.find((item) => item.id === row.id);
                            return Number(initial.purchase) !== Number(row.purchase) || Number(initial.selling) !== Number(row.selling);
                        },
                        margin(row) {
                            const purchase = Number(row.purchase);
                            return purchase > 0 ? Math.round((Number(row.selling) - purchase) / purchase * 1000) / 10 : null;
                        },
                        format(value) { return Number(value || 0).toLocaleString('fr-FR', { maximumFractionDigits: 2 }); },
                        round(value) { return this.roundPrices ? Math.max(0, Math.round(value / 5) * 5) : Math.max(0, Math.round(value * 100) / 100); },
                        toggleAll(state) { this.rows.forEach((row) => { row.selected = state; }); },
                        applyBulk() {
                            const value = Number(this.bulk.value);
                            if (Number.isNaN(value)) { return; }

                            this.rows.filter((row) => row.selected).forEach((row) => {
                                switch (this.bulk.mode) {
                                    case 'selling_percent': row.selling = this.round(row.selling * (1 + value / 100)); break;
                                    case 'purchase_percent': row.purchase = this.round(row.purchase * (1 + value / 100)); break;
                                    case 'margin': if (Number(row.purchase) > 0) { row.selling = this.round(row.purchase * (1 + value / 100)); } break;
                                    case 'selling_fixed': row.selling = this.round(value); break;
                                    case 'purchase_fixed': row.purchase = this.round(value); break;
                                }
                            });
                        },
                        cancel() {
                            this.rows = this.original.map((product) => ({ ...product, selected: false }));
                            this.editing = false;
                        },
                        prepare(event) {
                            const changed = this.changedRows.map((row) => ({ id: row.id, purchase_price: row.purchase, selling_price: row.selling }));
                            if (! changed.length) { event.preventDefault(); return; }
                            this.$refs.pricesJson.value = JSON.stringify(changed);
                        },
                    };
                }
            </script>
        @endpush

        <!-- Mouvements Récents pour cet entrepôt -->
        <div class="col-span-1 lg:col-span-12 rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-[#0B0F14]">Mouvements Récents sur cet Entrepôt</h3>
                    <p class="text-xs text-[#64748B]">Journal des dernières entrées, sorties et réajustements</p>
                </div>
                <a href="{{ route('stock.movements.index', ['warehouse_id' => $warehouse->id]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0066FF] hover:underline">
                    <span>Consulter tout l'historique</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                        <tr>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Type</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Quantité</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Stock Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($recentMovements as $movement)
                            @php
                                $badgeClass = match($movement->type) {
                                    'entree' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'sortie' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'transfert' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                    'retour' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'ajustement' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                                };
                                $sign = match($movement->type) {
                                    'entree', 'retour' => '+',
                                    'sortie', 'transfert' => '-',
                                    default => ''
                                };
                            @endphp
                            <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                                <td class="py-3.5 px-4 text-xs font-medium text-[#0B0F14]">{{ $movement->date->format('d/m/Y') }}</td>
                                <td class="py-3.5 px-4 text-xs font-mono text-[#64748B]">{{ $movement->reference }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badgeClass }}">
                                        {{ ucfirst($movement->type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14]">
                                    {{ $movement->product->name ?? 'Produit #'.$movement->product_id }}
                                </td>
                                <td class="py-3.5 px-4 text-sm font-bold text-right {{ $sign === '+' ? 'text-emerald-600' : ($sign === '-' ? 'text-rose-600' : 'text-[#0B0F14]') }}">
                                    {{ $sign }}{{ $movement->quantity }}
                                </td>
                                <td class="py-3.5 px-4 text-sm font-mono text-right text-[#64748B]">{{ $movement->stock_after }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-[#64748B]">
                                    Aucun mouvement enregistré pour cet entrepôt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($recentMovements as $movement)
                    @php
                        $badgeClass = match($movement->type) {
                            'entree' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'sortie' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'transfert' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                            'retour' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'ajustement' => 'bg-purple-50 text-purple-700 border-purple-200',
                            default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                        };
                        $sign = match($movement->type) {
                            'entree', 'retour' => '+',
                            'sortie', 'transfert' => '-',
                            default => ''
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-[#0B0F14]">{{ $movement->product->name ?? 'Produit #'.$movement->product_id }}</span>
                                <div class="flex items-center gap-2 mt-0.5 text-xs text-[#64748B]">
                                    <span class="font-mono">{{ $movement->reference }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $movement->date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border shrink-0 {{ $badgeClass }}">
                                {{ ucfirst($movement->type) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Mouvement</span>
                                <span class="font-bold text-sm {{ $sign === '+' ? 'text-emerald-600' : ($sign === '-' ? 'text-rose-600' : 'text-[#0B0F14]') }}">
                                    {{ $sign }}{{ $movement->quantity }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Stock Final</span>
                                <span class="font-mono font-bold text-sm text-[#0B0F14]">{{ $movement->stock_after }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-[#64748B]">
                        Aucun mouvement enregistré pour cet entrepôt.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal de confirmation / transfert pour suppression d'entrepôt --}}
    <x-delete-warehouse-modal />
</x-layouts.app>
