@props(['warehouses' => null])

@php
    $allWarehousesList = $warehouses ?? \App\Models\Warehouse::orderBy('name')->get(['id', 'name', 'code']);
@endphp

<div
    x-data="deleteWarehouseModal()"
    @open-delete-warehouse.window="open($event.detail)"
    @keydown.escape.window="close()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-delete-warehouse-title"
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
            class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"
            @click.stop
        >
            {{-- En-tête du modal --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-rose-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-delete-warehouse-title" class="text-sm font-bold text-[#0B0F14]">
                            Supprimer l'entrepôt
                        </h3>
                        <p class="text-xs text-[#64748B] flex items-center gap-1.5 mt-0.5">
                            <span class="font-bold text-rose-600" x-text="warehouseCode"></span>
                            <span>•</span>
                            <span class="truncate max-w-[200px] font-semibold text-[#0B0F14]" x-text="warehouseName"></span>
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

            {{-- Formulaire de suppression --}}
            <form :action="deleteActionUrl" method="POST" class="p-5 space-y-4">
                @csrf
                @method('DELETE')

                {{-- Cas où l'entrepôt contient encore des stocks --}}
                <div x-show="totalStock > 0" x-cloak class="space-y-3">
                    <div class="p-3.5 bg-amber-50 border border-amber-200/80 rounded-xl text-amber-900 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-amber-800">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Stocks restants détectés</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Cet entrepôt contient actuellement <strong x-text="productCount"></strong> article(s) avec un total de <strong x-text="totalStock"></strong> unité(s) physique(s).
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#0B0F14]">Action sur le stock restant :</label>
                        
                        {{-- Option 1: Transférer vers un autre entrepôt --}}
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100/70 transition">
                            <input 
                                type="radio" 
                                name="stock_resolution" 
                                value="transfer" 
                                x-model="stockResolution"
                                class="mt-0.5 text-[#0066FF] focus:ring-[#0066FF]"
                            >
                            <div class="space-y-1.5 flex-1">
                                <span class="text-xs font-bold text-[#0B0F14] block">Transférer tout le stock vers un autre entrepôt (Recommandé)</span>
                                <span class="text-[11px] text-slate-500 block">Les stocks physiques seront déplacés automatiquement avant la suppression.</span>
                                
                                <div x-show="stockResolution === 'transfer'" class="pt-1.5">
                                    <select 
                                        name="transfer_to_warehouse_id" 
                                        x-model="transferToWarehouseId"
                                        class="w-full px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs font-medium text-[#0B0F14] focus:outline-none focus:border-[#0066FF]"
                                    >
                                        <template x-for="wh in otherWarehouses" :key="wh.id">
                                            <option :value="wh.id" x-text="wh.name + ' (' + (wh.code || 'ENT') + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </label>

                        {{-- Option 2: Suppression forcée --}}
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-rose-200 bg-rose-50/40 cursor-pointer hover:bg-rose-50 transition">
                            <input 
                                type="radio" 
                                name="stock_resolution" 
                                value="force" 
                                x-model="stockResolution"
                                class="mt-0.5 text-rose-600 focus:ring-rose-500"
                            >
                            <div class="space-y-0.5">
                                <span class="text-xs font-bold text-rose-700 block">Supprimer définitivement l'entrepôt et son stock</span>
                                <span class="text-[11px] text-rose-600/80 block">Attention : les enregistrements de stock physique associés seront effacés.</span>
                            </div>
                        </label>
                        <input type="hidden" name="force_delete" :value="stockResolution === 'force' ? '1' : '0'">
                    </div>
                </div>

                {{-- Cas où l'entrepôt est vide --}}
                <div x-show="totalStock === 0" x-cloak class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600">
                    <p class="leading-relaxed">
                        Êtes-vous sûr de vouloir supprimer définitivement cet entrepôt ? Cet entrepôt ne contient actuellement aucun stock physique. Cette opération est irréversible.
                    </p>
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
                        class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Confirmer la suppression</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteWarehouseModal() {
    return {
        isOpen: false,
        warehouseId: null,
        warehouseName: '',
        warehouseCode: '',
        totalStock: 0,
        productCount: 0,
        stockResolution: 'transfer',
        transferToWarehouseId: '',
        allWarehouses: @json($allWarehousesList),
        otherWarehouses: [],

        get deleteActionUrl() {
            if (!this.warehouseId) return '#';
            return '{{ url('stock/warehouses') }}/' + this.warehouseId;
        },

        open(detail) {
            this.warehouseId = detail.id;
            this.warehouseName = detail.name || '';
            this.warehouseCode = detail.code || '';
            this.totalStock = Number(detail.totalStock) || 0;
            this.productCount = Number(detail.productCount) || 0;
            this.stockResolution = 'transfer';

            this.otherWarehouses = this.allWarehouses.filter(w => String(w.id) !== String(this.warehouseId));
            if (this.otherWarehouses.length > 0) {
                this.transferToWarehouseId = this.otherWarehouses[0].id;
            } else {
                this.transferToWarehouseId = '';
                this.stockResolution = 'force';
            }

            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        }
    };
}
</script>
