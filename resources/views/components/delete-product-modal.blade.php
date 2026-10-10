<div
    x-data="deleteProductModal()"
    @open-delete-product.window="openSingle($event.detail)"
    @open-bulk-delete-products.window="openBulk($event.detail)"
    @keydown.escape.window="close()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
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
                        <h3 class="text-sm font-bold text-[#0B0F14]">
                            <span x-show="isBulk">Suppression groupée d'articles</span>
                            <span x-show="!isBulk">Supprimer l'article</span>
                        </h3>
                        <p class="text-xs text-[#64748B] flex items-center gap-1.5 mt-0.5">
                            <span x-show="isBulk">
                                <strong class="text-rose-600 font-bold" x-text="productIds.length"></strong> produit(s) sélectionné(s)
                            </span>
                            <span x-show="!isBulk" class="truncate max-w-[220px]">
                                <span class="font-bold text-rose-600" x-text="productSku"></span> • <span class="font-semibold text-[#0B0F14]" x-text="productName"></span>
                            </span>
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
            <form :action="actionUrl" method="POST" class="p-5 space-y-4">
                @csrf
                <template x-if="!isBulk">
                    <input type="hidden" name="_method" value="DELETE">
                </template>

                {{-- Champs cachés pour le bulk --}}
                <template x-if="isBulk">
                    <div>
                        <template x-for="id in productIds" :key="id">
                            <input type="hidden" name="product_ids[]" :value="id">
                        </template>
                    </div>
                </template>

                {{-- Avertissement suppression groupée --}}
                <div x-show="isBulk" class="rounded-xl border border-rose-200 bg-rose-50/60 p-3.5 space-y-2">
                    <div class="flex items-center gap-2 text-rose-900 font-bold text-xs">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Confirmation de suppression multiple</span>
                    </div>
                    <p class="text-[11px] text-rose-800 leading-relaxed">
                        Vous êtes sur le point de supprimer définitivement <strong class="font-bold" x-text="productIds.length"></strong> article(s) du catalogue WMS.
                    </p>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Les stocks physiques enregistrés dans tous les entrepôts pour ces articles seront également purgés ainsi que leurs mouvements associés.
                    </p>
                </div>

                {{-- Avertissement suppression unitaire --}}
                <div x-show="!isBulk" class="space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-1.5">
                        <div class="text-xs font-bold text-[#0B0F14]">
                            Article : <span x-text="productName" class="text-[#0066FF]"></span>
                        </div>
                        <div class="text-[11px] text-[#64748B]">
                            Référence SKU : <strong class="font-mono text-slate-700" x-text="productSku"></strong>
                        </div>
                        <div class="text-[11px] text-[#64748B]">
                            Stock physique actuel : <strong class="font-bold" :class="currentStock > 0 ? 'text-amber-700' : 'text-slate-600'" x-text="currentStock + ' unités'"></strong>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Cette action est irréversible et supprimera la fiche de cet article ainsi que sa visibilité au catalogue et ses stocks associés.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="close()" 
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span x-show="isBulk">Confirmer la suppression (<span x-text="productIds.length"></span>)</span>
                        <span x-show="!isBulk">Supprimer cet article</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function deleteProductModal() {
        return {
            isOpen: false,
            isBulk: false,
            productId: null,
            productName: '',
            productSku: '',
            currentStock: 0,
            productIds: [],
            actionUrl: '',

            openSingle(detail) {
                this.isBulk = false;
                this.productId = detail.id;
                this.productName = detail.name || '';
                this.productSku = detail.sku || '';
                this.currentStock = detail.current_stock || 0;
                this.productIds = [detail.id];
                this.actionUrl = detail.action_url || ('{{ url('stock/products') }}/' + detail.id);
                this.isOpen = true;
            },

            openBulk(detail) {
                this.isBulk = true;
                this.productIds = detail.product_ids || [];
                this.actionUrl = '{{ route('stock.products.bulk-destroy') }}';
                this.isOpen = true;
            },

            close() {
                this.isOpen = false;
            }
        };
    }
</script>
