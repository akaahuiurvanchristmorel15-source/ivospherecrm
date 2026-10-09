@props(['warehouses' => collect()])

<div
    x-data="stockAdjustModal()"
    @open-stock-adjust.window="open($event.detail)"
    @keydown.escape.window="close()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-stock-adjust-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    {{-- Arrière-plan flouté --}}
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#0B0F14]/70 backdrop-blur-xs transition-opacity"
        @click="close()"
    ></div>

    <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
        <div 
            x-show="isOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"
            @click.stop
        >
            {{-- En-tête du modal --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50/70">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold border border-emerald-200/60 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-stock-adjust-title" class="text-sm font-bold text-[#0B0F14]">
                            Ajustement Stock & Entrepôt
                        </h3>
                        <p class="text-[11px] text-[#64748B] flex items-center gap-1.5 mt-0.5">
                            <span class="font-bold text-[#0066FF]" x-text="productSku"></span>
                            <span>•</span>
                            <span class="truncate max-w-[200px]" x-text="productName"></span>
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="close()" 
                    class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Formulaire d'Ajustement --}}
            <form action="{{ route('stock.adjust') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="product_id" :value="productId">

                {{-- Résumé Rapide du Stock Actuel --}}
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Stock Total Actuel</span>
                        <span class="text-sm font-bold text-[#0B0F14]">
                            <span x-text="currentTotalStock"></span> <span class="text-[11px] font-normal text-slate-500" x-text="productUnit"></span>
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Entrepôt Sélectionné</span>
                        <span class="text-xs font-bold text-emerald-700">
                            <span x-text="currentWarehouseStock()"></span> <span class="text-[10px] font-normal" x-text="productUnit"></span>
                        </span>
                    </div>
                </div>

                {{-- Choix de l'Entrepôt Source / Concerné --}}
                <div>
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                        Entrepôt concerné <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="warehouse_id" 
                        x-model="selectedWarehouseId" 
                        @change="onWarehouseChange()"
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs font-medium focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                    >
                        <template x-for="wh in allWarehouses" :key="wh.id">
                            <option 
                                :value="wh.id" 
                                x-text="wh.name + ' (' + (wh.code || 'ENT') + ') — ' + getStockForWarehouse(wh.id) + ' ' + productUnit"
                            ></option>
                        </template>
                    </select>
                </div>

                {{-- Mode d'action (Tabs) --}}
                <div>
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Type d'opération</label>
                    <div class="grid grid-cols-4 gap-1.5 p-1 bg-slate-100 rounded-xl text-[11px]">
                        <button 
                            type="button" 
                            @click="setActionType('set')"
                            :class="actionType === 'set' ? 'bg-white font-bold text-[#0B0F14] shadow-xs' : 'text-slate-600 hover:text-[#0B0F14]'"
                            class="py-1.5 px-2 rounded-lg text-center transition"
                        >
                            Définir exact
                        </button>
                        <button 
                            type="button" 
                            @click="setActionType('add')"
                            :class="actionType === 'add' ? 'bg-white font-bold text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-emerald-700'"
                            class="py-1.5 px-2 rounded-lg text-center transition"
                        >
                            + Ajouter
                        </button>
                        <button 
                            type="button" 
                            @click="setActionType('remove')"
                            :class="actionType === 'remove' ? 'bg-white font-bold text-rose-700 shadow-xs' : 'text-slate-600 hover:text-rose-700'"
                            class="py-1.5 px-2 rounded-lg text-center transition"
                        >
                            - Déduire
                        </button>
                        <button 
                            type="button" 
                            @click="setActionType('transfer')"
                            :class="actionType === 'transfer' ? 'bg-white font-bold text-[#0066FF] shadow-xs' : 'text-slate-600 hover:text-[#0066FF]'"
                            class="py-1.5 px-2 rounded-lg text-center transition"
                        >
                            ⇄ Transférer
                        </button>
                    </div>
                    <input type="hidden" name="action_type" :value="actionType">
                </div>

                {{-- Mode Transfert: Sélection de l'entrepôt de destination --}}
                <div x-show="actionType === 'transfer'" x-cloak class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 space-y-2">
                    <label class="block text-xs font-semibold text-[#0066FF]">
                        Entrepôt de destination <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="target_warehouse_id" 
                        x-model="targetWarehouseId"
                        :required="actionType === 'transfer'"
                        class="w-full px-3 py-2 rounded-lg bg-white border border-blue-200 text-xs font-medium text-[#0B0F14] focus:outline-none focus:border-[#0066FF]"
                    >
                        <option value="">-- Choisir l'entrepôt de réception --</option>
                        <template x-for="wh in allWarehouses" :key="wh.id">
                            <option 
                                :value="wh.id" 
                                :disabled="String(wh.id) === String(selectedWarehouseId)"
                                x-text="wh.name + ' (' + (wh.code || 'ENT') + ') — Actuel : ' + getStockForWarehouse(wh.id) + ' ' + productUnit"
                            ></option>
                        </template>
                    </select>
                </div>

                {{-- Saisie de la Quantité --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-[#0B0F14]">
                            <span x-show="actionType === 'set'">Nouveau stock physique exact</span>
                            <span x-show="actionType === 'add'">Quantité à ajouter au stock</span>
                            <span x-show="actionType === 'remove'">Quantité à déduire du stock</span>
                            <span x-show="actionType === 'transfer'">Quantité à déplacer vers le nouvel entrepôt</span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500">
                            Dispo : <strong x-text="currentWarehouseStock()"></strong> <span x-text="productUnit"></span>
                        </span>
                    </div>

                    <div class="relative">
                        <input 
                            type="number" 
                            name="quantity" 
                            x-model.number="quantity"
                            :min="actionType === 'set' ? 0 : 1"
                            :max="actionType === 'remove' || actionType === 'transfer' ? currentWarehouseStock() : null"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E2E8F0] font-bold text-sm text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="0"
                        >
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400" x-text="productUnit"></span>
                    </div>

                    {{-- Indicateur dynamique d'impact --}}
                    <div class="mt-2 text-[11px] flex items-center justify-between">
                        <span class="text-slate-500">Impact prévisionnel :</span>
                        <template x-if="actionType === 'set'">
                            <span :class="stockDiff() > 0 ? 'text-emerald-600 font-bold' : (stockDiff() < 0 ? 'text-rose-600 font-bold' : 'text-slate-600 font-semibold')">
                                <span x-text="stockDiff() > 0 ? '+' + stockDiff() : stockDiff()"></span> unités d'écart
                            </span>
                        </template>
                        <template x-if="actionType === 'add'">
                            <span class="text-emerald-600 font-bold">
                                Nouveau stock : <span x-text="currentWarehouseStock() + (quantity || 0)"></span> <span x-text="productUnit"></span>
                            </span>
                        </template>
                        <template x-if="actionType === 'remove'">
                            <span class="text-rose-600 font-bold">
                                Nouveau stock : <span x-text="Math.max(0, currentWarehouseStock() - (quantity || 0))"></span> <span x-text="productUnit"></span>
                            </span>
                        </template>
                        <template x-if="actionType === 'transfer'">
                            <span class="text-[#0066FF] font-bold">
                                Source : <span x-text="Math.max(0, currentWarehouseStock() - (quantity || 0))"></span> | Destination : +<span x-text="quantity || 0"></span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Motif / Justification rapide --}}
                <div>
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Motif / Justification</label>
                    <div class="flex flex-wrap gap-1.5 mb-2 text-[10px]">
                        <button type="button" @click="reason = 'Comptage physique & inventaire'" class="px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">Inventaire physique</button>
                        <button type="button" @click="reason = 'Correction d\'écart de stock'" class="px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">Correction écart</button>
                        <button type="button" @click="reason = 'Changement d\'entrepôt / Réattribution'" class="px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">Réattribution</button>
                        <button type="button" @click="reason = 'Arrivage direct non-BL'" class="px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">Arrivage direct</button>
                        <button type="button" @click="reason = 'Perte / Casse / Avarie'" class="px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">Perte / Avarie</button>
                    </div>
                    <input 
                        type="text" 
                        name="reason" 
                        x-model="reason" 
                        class="w-full px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]"
                        placeholder="Ex: Comptage physique inventaire tournant"
                    >
                </div>

                {{-- Remarques additionnelles --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Notes internes (optionnel)</label>
                    <input 
                        type="text" 
                        name="notes" 
                        x-model="notes" 
                        class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]"
                        placeholder="Ex: Validé avec le responsable de dépôt..."
                    >
                </div>

                {{-- Boutons d'action --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="close()" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 rounded-xl bg-[#0066FF] text-white text-xs font-bold hover:bg-blue-700 transition shadow-xs flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Enregistrer l'Ajustement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function stockAdjustModal() {
    return {
        isOpen: false,
        productId: null,
        productName: '',
        productSku: '',
        productUnit: 'pièce',
        currentTotalStock: 0,
        stocks: [],
        allWarehouses: @json($warehouses->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'code' => $w->code])),
        selectedWarehouseId: '',
        actionType: 'set',
        quantity: 0,
        targetWarehouseId: '',
        reason: 'Comptage physique & inventaire',
        notes: '',

        open(detail) {
            this.productId = detail.id;
            this.productName = detail.name || '';
            this.productSku = detail.sku || '';
            this.productUnit = detail.unit || 'pièce';
            this.currentTotalStock = detail.current_stock || 0;
            this.stocks = detail.stocks || [];
            
            // Pré-sélection du premier entrepôt avec stock ou premier entrepôt global
            if (this.stocks.length > 0) {
                this.selectedWarehouseId = this.stocks[0].warehouse_id;
            } else if (this.allWarehouses.length > 0) {
                this.selectedWarehouseId = this.allWarehouses[0].id;
            } else {
                this.selectedWarehouseId = '';
            }

            this.actionType = 'set';
            this.quantity = this.currentWarehouseStock();
            this.targetWarehouseId = '';
            this.reason = 'Comptage physique & inventaire';
            this.notes = '';
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        },

        getStockForWarehouse(warehouseId) {
            const found = this.stocks.find(s => String(s.warehouse_id) === String(warehouseId));
            return found ? (found.physical_quantity || 0) : 0;
        },

        currentWarehouseStock() {
            return this.getStockForWarehouse(this.selectedWarehouseId);
        },

        onWarehouseChange() {
            if (this.actionType === 'set') {
                this.quantity = this.currentWarehouseStock();
            }
        },

        setActionType(type) {
            this.actionType = type;
            if (type === 'set') {
                this.quantity = this.currentWarehouseStock();
            } else if (this.quantity === this.currentWarehouseStock() || this.quantity === 0) {
                this.quantity = 1;
            }
        },

        stockDiff() {
            const qty = Number(this.quantity) || 0;
            return qty - this.currentWarehouseStock();
        }
    };
}
</script>
