<x-layouts.app>
    <x-slot:title>Modification Groupée des Articles — Stocks & Logistique (WMS)</x-slot>

    @php
        $initialRowsData = $products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'domain' => $p->domain?->name ?? 'Général',
                'current_image' => $p->image ? asset('storage/' . $p->image) : '',
                'preview_image' => null,
                'delete_image' => false,
                'purchase_price' => (float) ($p->purchase_price ?? 0),
                'selling_price' => (float) ($p->selling_price ?? 0),
                'current_stock' => $p->current_stock,
            ];
        })->values();
    @endphp

    <div 
        x-data="bulkEditManager({
            initialProducts: {{ Js::from($initialRowsData) }}
        })" 
        class="max-w-7xl mx-auto space-y-6 pb-16 text-xs"
    >
        <x-page-header 
            title="Modification Groupée des Articles"
            description="Mise à jour simultanée des photos/images, prix d'achat et prix de revente sur les articles sélectionnés du catalogue WMS"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Stocks & Logistique', 'url' => route('stock.index')],
                    ['label' => 'Stock Disponible', 'url' => route('stock.index', ['tab' => 'disponibilite'])],
                    ['label' => 'Modification Groupée']
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

        <form action="{{ route('stock.products.bulk-update') }}" method="POST" enctype="multipart/form-data" id="bulkEditForm" class="space-y-6">
            @csrf

            {{-- 1. Outils d'Application Globale & Calculateur de Marge --}}
            <x-card title="1. Outils d'Application Rapide & Paramètres Globaux">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Outil A: Ajustement Rapide des Prix de Revente --}}
                    <div class="space-y-3 p-4 rounded-xl border border-blue-100 bg-blue-50/40">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#0B0F14] text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                                Calculateur & Ajustement Automatique des Prix
                            </span>
                            <span class="text-[10px] font-semibold text-[#0066FF] bg-blue-100 px-2 py-0.5 rounded-full">
                                Gain de temps
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Appliquez un coefficient de marge ou une augmentation tarifaire à l'ensemble des articles affichés ci-dessous.
                        </p>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <span class="text-[11px] font-semibold text-slate-700">Marge brute rapide :</span>
                            <button type="button" @click="applyMarginPercent(20)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#0066FF] text-[#0B0F14] hover:text-[#0066FF] font-semibold text-xs transition shadow-2xs">
                                +20%
                            </button>
                            <button type="button" @click="applyMarginPercent(30)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#0066FF] text-[#0B0F14] hover:text-[#0066FF] font-semibold text-xs transition shadow-2xs">
                                +30%
                            </button>
                            <button type="button" @click="applyMarginPercent(40)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#0066FF] text-[#0B0F14] hover:text-[#0066FF] font-semibold text-xs transition shadow-2xs">
                                +40%
                            </button>
                            <button type="button" @click="applyMarginPercent(50)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#0066FF] text-[#0B0F14] hover:text-[#0066FF] font-semibold text-xs transition shadow-2xs">
                                +50%
                            </button>
                            <button type="button" @click="applyMarginPercent(100)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#0066FF] text-[#0B0F14] hover:text-[#0066FF] font-semibold text-xs transition shadow-2xs">
                                x 2 (100%)
                            </button>
                        </div>

                        <div class="pt-2 flex items-center gap-2">
                            <div class="relative flex-1">
                                <input 
                                    type="number" 
                                    step="1" 
                                    x-model.number="customMarkupPercent" 
                                    placeholder="Taux de marge personnalisé (ex: 35 %)" 
                                    class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]"
                                >
                            </div>
                            <button 
                                type="button" 
                                @click="applyCustomMarkup()" 
                                class="px-3 py-1.5 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white font-semibold text-xs transition shadow-2xs shrink-0 cursor-pointer"
                            >
                                Appliquer la marge
                            </button>
                        </div>
                    </div>

                    {{-- Outil B: Visuel Commun pour Tous les Articles --}}
                    <div class="space-y-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#0B0F14] text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Image Commune au Lot (Optionnel)
                            </span>
                            <span class="text-[10px] text-slate-500 font-medium">Uniformisation</span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Téléversez un visuel unique qui sera appliqué à tous les articles du lot ne possédant pas de photo individuelle spécifique.
                        </p>

                        <div class="flex items-center gap-3 pt-1">
                            <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                <template x-if="commonImagePreview">
                                    <img :src="commonImagePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!commonImagePreview">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </template>
                            </div>

                            <div class="flex-1 space-y-2">
                                <input 
                                    type="file" 
                                    name="common_image" 
                                    accept="image/*" 
                                    @change="handleCommonImage($event)"
                                    class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:text-[#0066FF] hover:file:bg-blue-50 cursor-pointer"
                                >

                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input 
                                        type="checkbox" 
                                        name="apply_common_image_to_all" 
                                        value="1" 
                                        x-model="applyCommonImageToAll"
                                        class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]"
                                    >
                                    <span class="text-[11px] font-semibold text-slate-700">Appliquer cette image à tous les articles du lot</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- 2. Grille de Modification Groupée --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0B0F14]">
                            2. Tableau des Articles (<span x-text="products.length"></span>)
                        </h2>
                        <p class="text-xs text-slate-500">
                            Modifiez individuellement la photo, le prix d'achat et le prix de revente. La marge est recalculée en temps réel.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-500">
                            Articles chargés : <strong class="text-[#0B0F14] font-bold" x-text="products.length"></strong>
                        </span>
                    </div>
                </div>

                {{-- Table responsive --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-10 px-3 py-3 text-center">#</th>
                                <th class="w-32 px-3 py-3 text-center">Photo / Image</th>
                                <th class="min-w-[200px] px-3 py-3">Produit & Réf.</th>
                                <th class="min-w-[130px] px-3 py-3 text-right">Prix d'Achat (FCFA)</th>
                                <th class="min-w-[140px] px-3 py-3 text-right">Prix de Revente (FCFA) <span class="text-rose-500">*</span></th>
                                <th class="min-w-[140px] px-3 py-3 text-right">Marge Brute (FCFA)</th>
                                <th class="min-w-[110px] px-3 py-3 text-right">Taux Marge (%)</th>
                                <th class="w-12 px-3 py-3 text-center">Retirer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in products" :key="item.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Index --}}
                                    <td class="px-3 py-3 text-center font-mono text-slate-400 font-bold" x-text="index + 1"></td>

                                    {{-- Colonne Image --}}
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex flex-col items-center gap-1.5">
                                            {{-- Miniature interactive --}}
                                            <div class="relative w-14 h-14 rounded-xl border border-slate-200 bg-slate-100 overflow-hidden flex items-center justify-center shrink-0 shadow-2xs group">
                                                <template x-if="item.preview_image">
                                                    <img :src="item.preview_image" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.preview_image && item.current_image && !item.delete_image">
                                                    <img :src="item.current_image" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="(!item.preview_image && !item.current_image) || item.delete_image">
                                                    <div class="text-center p-1 text-slate-300">
                                                        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    </div>
                                                </template>

                                                {{-- Badge si modifiée --}}
                                                <template x-if="item.preview_image">
                                                    <span class="absolute top-0 right-0 bg-emerald-500 text-white rounded-bl px-1 text-[8px] font-bold">Nouveau</span>
                                                </template>
                                                <template x-if="item.delete_image">
                                                    <span class="absolute top-0 right-0 bg-rose-500 text-white rounded-bl px-1 text-[8px] font-bold">Supprimée</span>
                                                </template>
                                            </div>

                                            {{-- Bouton Sélecteur d'Image --}}
                                            <div class="flex items-center gap-1">
                                                <label class="px-2 py-0.5 rounded border border-slate-200 bg-white hover:bg-blue-50 hover:text-[#0066FF] hover:border-[#0066FF] text-[10px] font-semibold text-slate-700 cursor-pointer transition shadow-2xs">
                                                    <span>Changer</span>
                                                    <input 
                                                        type="file" 
                                                        :name="'products[' + index + '][image]'" 
                                                        accept="image/*" 
                                                        @change="handleRowImage($event, item)" 
                                                        class="sr-only"
                                                    >
                                                </label>

                                                <template x-if="(item.current_image || item.preview_image) && !item.delete_image">
                                                    <button 
                                                        type="button" 
                                                        @click="markImageDeleted(item)" 
                                                        class="p-0.5 rounded text-slate-400 hover:text-rose-600 transition" 
                                                        title="Supprimer la photo"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </template>

                                                <template x-if="item.delete_image">
                                                    <button 
                                                        type="button" 
                                                        @click="restoreImage(item)" 
                                                        class="px-1 text-[10px] text-[#0066FF] hover:underline"
                                                    >
                                                        Rétablir
                                                    </button>
                                                </template>
                                            </div>

                                            <input type="hidden" :name="'products[' + index + '][delete_image]'" :value="item.delete_image ? '1' : '0'">
                                        </div>
                                    </td>

                                    {{-- Produit & Réf --}}
                                    <td class="px-3 py-3">
                                        <input type="hidden" :name="'products[' + index + '][id]'" :value="item.id">
                                        <div class="font-bold text-[#0B0F14] text-xs line-clamp-1" x-text="item.name"></div>
                                        <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-slate-400">
                                            <span class="font-mono font-semibold text-[#0066FF]" x-text="item.sku"></span>
                                            <span>•</span>
                                            <span x-text="item.domain"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                            Stock : <strong class="text-[#0B0F14]" x-text="item.current_stock + ' unités'"></strong>
                                        </div>
                                    </td>

                                    {{-- Prix d'Achat --}}
                                    <td class="px-3 py-3 text-right">
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            :name="'products[' + index + '][purchase_price]'" 
                                            x-model.number="item.purchase_price" 
                                            placeholder="0" 
                                            class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                    </td>

                                    {{-- Prix de Revente --}}
                                    <td class="px-3 py-3 text-right">
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0" 
                                            required
                                            :name="'products[' + index + '][selling_price]'" 
                                            x-model.number="item.selling_price" 
                                            placeholder="0" 
                                            class="w-full px-2.5 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] text-xs text-right font-mono font-bold focus:outline-none focus:border-[#0066FF] transition"
                                        >
                                    </td>

                                    {{-- Marge Brute --}}
                                    <td class="px-3 py-3 text-right font-mono font-bold text-xs">
                                        <span 
                                            :class="calculateMargin(item) > 0 ? 'text-emerald-600' : (calculateMargin(item) === 0 ? 'text-slate-500' : 'text-rose-600')"
                                            x-text="formatNumber(calculateMargin(item)) + ' F'"
                                        ></span>
                                    </td>

                                    {{-- Taux de Marge % --}}
                                    <td class="px-3 py-3 text-right font-mono text-xs">
                                        <span 
                                            class="inline-block px-2 py-0.5 rounded font-bold"
                                            :class="calculateMarginPercent(item) >= 20 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (calculateMarginPercent(item) > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200')"
                                            x-text="calculateMarginPercent(item) + ' %'"
                                        ></span>
                                    </td>

                                    {{-- Action : Retirer du lot --}}
                                    <td class="px-3 py-3 text-center">
                                        <button 
                                            type="button" 
                                            @click="removeProduct(index)" 
                                            :disabled="products.length <= 1"
                                            class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition disabled:opacity-30 disabled:cursor-not-allowed" 
                                            title="Retirer cet article du lot"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Barre Récapitulative Totaux --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        Total des articles pris en compte : <strong class="text-[#0B0F14]" x-text="products.length"></strong>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <div class="text-slate-600">
                            Total Prix Achat : <span class="font-bold text-[#0B0F14] font-mono" x-text="formatNumber(totalPurchasePrice()) + ' FCFA'"></span>
                        </div>
                        <div class="text-slate-600">
                            Total Prix Revente : <span class="font-bold text-[#0066FF] font-mono" x-text="formatNumber(totalSellingPrice()) + ' FCFA'"></span>
                        </div>
                        <div class="text-slate-600">
                            Marge Globale : <span class="font-bold text-emerald-600 font-mono" x-text="formatNumber(totalMargin()) + ' FCFA'"></span>
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
                    <span>Enregistrer les Modifications (<span x-text="products.length"></span> articles)</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function bulkEditManager(config) {
            return {
                products: config.initialProducts || [],
                commonImagePreview: null,
                applyCommonImageToAll: false,
                customMarkupPercent: null,

                handleCommonImage(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.commonImagePreview = ev.target.result;
                            this.applyCommonImageToAll = true;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                handleRowImage(e, item) {
                    const file = e.target.files[0];
                    if (file) {
                        item.delete_image = false;
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            item.preview_image = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                markImageDeleted(item) {
                    item.delete_image = true;
                    item.preview_image = null;
                },

                restoreImage(item) {
                    item.delete_image = false;
                },

                removeProduct(index) {
                    if (this.products.length > 1) {
                        this.products.splice(index, 1);
                    }
                },

                calculateMargin(item) {
                    const buy = parseFloat(item.purchase_price) || 0;
                    const sell = parseFloat(item.selling_price) || 0;
                    return Math.round((sell - buy) * 100) / 100;
                },

                calculateMarginPercent(item) {
                    const buy = parseFloat(item.purchase_price) || 0;
                    const sell = parseFloat(item.selling_price) || 0;
                    if (sell <= 0) return 0;
                    const pct = ((sell - buy) / sell) * 100;
                    return Math.round(pct * 10) / 10;
                },

                applyMarginPercent(percent) {
                    this.products.forEach(p => {
                        const buy = parseFloat(p.purchase_price) || 0;
                        if (buy > 0) {
                            p.selling_price = Math.round(buy * (1 + (percent / 100)));
                        }
                    });
                },

                applyCustomMarkup() {
                    const pct = parseFloat(this.customMarkupPercent);
                    if (!isNaN(pct) && pct > 0) {
                        this.applyMarginPercent(pct);
                    }
                },

                totalPurchasePrice() {
                    return this.products.reduce((sum, p) => sum + (parseFloat(p.purchase_price) || 0), 0);
                },

                totalSellingPrice() {
                    return this.products.reduce((sum, p) => sum + (parseFloat(p.selling_price) || 0), 0);
                },

                totalMargin() {
                    return this.totalSellingPrice() - this.totalPurchasePrice();
                },

                formatNumber(val) {
                    return Math.round(val).toLocaleString('fr-FR');
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
