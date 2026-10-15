<x-layouts.app>
    <x-slot:title>Modification Groupée des Articles — Stocks & Logistique (WMS)</x-slot>

    @php
        $initialRowsData = $products->map(function ($p) use ($warehouses) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'domain' => $p->domain?->name ?? 'Général',
                'current_image' => $p->image ? asset('storage/' . $p->image) : '',
                'preview_image' => null,
                'pending_file' => null,
                'delete_image' => false,
                'upload_status' => '',
                'upload_error' => '',
                'purchase_price' => (float) ($p->purchase_price ?? 0),
                'selling_price' => (float) ($p->selling_price ?? 0),
                'original_purchase_price' => (float) ($p->purchase_price ?? 0),
                'original_selling_price' => (float) ($p->selling_price ?? 0),
                'image_url' => route('stock.products.quick-image', $p),
                'current_stock' => $p->current_stock,
                'stocks' => $warehouses->mapWithKeys(fn ($w) => [$w->id => (int) ($p->warehouseStocks->firstWhere('warehouse_id', $w->id)?->physical_quantity ?? 0)]),
                'original_stocks' => $warehouses->mapWithKeys(fn ($w) => [$w->id => (int) ($p->warehouseStocks->firstWhere('warehouse_id', $w->id)?->physical_quantity ?? 0)]),
            ];
        })->values();

        $warehouseOptions = $warehouses->map(fn ($w) => ['id' => $w->id, 'name' => $w->name, 'code' => $w->code])->values();
    @endphp

    <div 
        x-data="bulkEditManager({
            initialProducts: {{ Js::from($initialRowsData) }},
            warehouses: {{ Js::from($warehouseOptions) }}
        })" 
        @dragover.window.prevent="isDragging = true"
        @dragleave.window="if (!$event.relatedTarget) isDragging = false"
        @drop.window.prevent="isDragging = false; assignMultipleFiles($event.dataTransfer.files)"
        class="max-w-7xl mx-auto space-y-6 pb-16 text-xs"
    >
        <x-page-header 
            title="Modification Groupée des Articles"
            description="Mise à jour simultanée des photos/images, prix d'achat, prix de revente et stock par entrepôt sur les articles sélectionnés du catalogue WMS"
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

        @if(session('error'))
            <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 font-semibold">{{ session('error') }}</div>
        @endif

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

        <form action="{{ route('stock.products.bulk-update') }}" method="POST" id="bulkEditForm" x-ref="form" @submit.prevent="submitAll()" class="space-y-6">
            @csrf
            <input type="hidden" name="images_updated_count" :value="imagesUpdatedCount">
            {{-- Champs générés à l'envoi à partir des données (les lignes non affichées sont aussi envoyées) --}}
            <div x-ref="payload" class="hidden"></div>

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

                    {{-- Outil C: Stock par entrepôt --}}
                    @if($warehouses->isNotEmpty())
                        <div class="space-y-3 rounded-xl border border-amber-200 bg-amber-50/40 p-4 lg:col-span-2 lg:order-last">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="flex items-center gap-2 text-xs font-bold text-[#0B0F14]">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    Stock par entrepôt
                                </span>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800" x-show="stockChangesCount() > 0" x-cloak>
                                    <span x-text="stockChangesCount()"></span> modification(s) de stock
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600">
                                Choisissez l'entrepôt, puis saisissez la quantité physique exacte de chaque article dans la colonne « Stock ». Chaque changement crée un mouvement d'ajustement tracé.
                            </p>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <select x-model.number="stockWarehouseId" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-64">
                                    <template x-for="wh in warehouses" :key="wh.id">
                                        <option :value="wh.id" x-text="wh.name + (wh.code ? ' (' + wh.code + ')' : '')" :selected="wh.id === stockWarehouseId"></option>
                                    </template>
                                </select>
                                <div class="flex flex-1 items-center gap-2">
                                    <input type="number" min="0" step="1" x-model.number="bulkStockValue" placeholder="Quantité pour tous (ex : 10)"
                                           class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-[#0B0F14] focus:border-[#0066FF] focus:outline-none">
                                    <button type="button" @click="applyStockToAll()" class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-600">
                                        Appliquer à tous
                                    </button>
                                    <button type="button" @click="resetStocks()" x-show="stockChangesCount() > 0" x-cloak class="shrink-0 rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-600 hover:text-[#0B0F14]">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Outil B: Photos de plusieurs articles --}}
                    <div class="space-y-3 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2 text-xs font-bold text-[#0B0F14]">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Photos de plusieurs articles
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800" x-show="pendingImagesCount() > 0" x-cloak>
                                <span x-text="pendingImagesCount()"></span> photo(s) à enregistrer
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-600">
                            Prenez les photos à la suite avec l'appareil photo du téléphone, ou sélectionnez plusieurs photos d'un coup (galerie ou dossier de l'ordinateur). Elles sont associées aux articles dont le SKU ou le nom figure dans le nom du fichier, sinon dans l'ordre du tableau. Vous pouvez ensuite changer chaque photo individuellement.
                        </p>

                        <label
                            :class="isDragging ? 'border-emerald-500 bg-emerald-100/60' : 'border-emerald-300 bg-white hover:bg-emerald-50'"
                            class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-4 py-5 text-center transition">
                            <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-bold text-emerald-800">Choisir plusieurs photos</span>
                            <span class="hidden text-[10px] text-slate-500 sm:block">ou glissez-déposez les images n'importe où sur la page (ou sur la miniature d'un article)</span>
                            <input type="file" accept="image/*" multiple class="sr-only" @change="assignMultipleFiles($event.target.files); $event.target.value = ''">
                        </label>

                        <p x-show="multiAssignMessage" x-cloak class="rounded-lg bg-white px-3 py-2 text-[11px] font-medium text-emerald-800 ring-1 ring-emerald-200" x-text="multiAssignMessage"></p>

                        {{-- Prise de vue à la suite (appareil photo du téléphone) --}}
                        <button type="button" x-show="shootingIndex === null" @click="startShooting()"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-4 py-3 text-xs font-bold text-white shadow-sm transition active:bg-[#0052cc] md:hidden">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                            Prendre les photos à la suite
                        </button>

                        <div x-show="shootingIndex !== null" x-cloak class="space-y-3 rounded-xl bg-white p-3 ring-1 ring-[#0066FF]/30">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                <span>Article <span x-text="(shootingIndex ?? 0) + 1"></span> / <span x-text="products.length"></span></span>
                                <button type="button" @click="stopShooting()" class="text-[#0066FF]">Terminer</button>
                            </div>
                            <template x-if="shootingItem()">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                        <template x-if="displayImage(shootingItem())">
                                            <img :src="displayImage(shootingItem())" alt="" class="h-full w-full object-cover">
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="line-clamp-2 text-sm font-bold text-[#0B0F14]" x-text="shootingItem().name"></p>
                                        <p class="font-mono text-[10px] font-semibold text-[#0066FF]" x-text="shootingItem().sku"></p>
                                    </div>
                                </div>
                            </template>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="col-span-2 flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-3 py-3 text-xs font-bold text-white active:bg-[#0052cc]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                    Prendre la photo
                                    <input type="file" accept="image/*" capture="environment" class="sr-only" @change="onShootingPhoto($event)">
                                </label>
                                <button type="button" @click="nextShootingItem()" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-600">Passer</button>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 border-t border-emerald-100 pt-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <template x-if="commonImagePreview">
                                    <img :src="commonImagePreview" alt="" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!commonImagePreview">
                                    <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <label class="inline-flex cursor-pointer items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-[#0066FF] hover:bg-blue-50">
                                    Même photo pour tous…
                                    <input type="file" accept="image/*" class="sr-only" @change="handleCommonImage($event)">
                                </label>
                                <label class="flex cursor-pointer select-none items-center gap-2" x-show="commonImageFile" x-cloak>
                                    <input type="checkbox" x-model="applyCommonImageToAll" class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                                    <span class="text-[11px] font-semibold text-slate-700">Appliquer aux articles sans nouvelle photo</span>
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

                    <div class="relative w-full sm:w-72">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="search" x-model.debounce.250ms="search" @input="visibleLimit = pageSize" placeholder="Rechercher un article (nom, SKU)…"
                               class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs text-[#0B0F14] focus:border-[#0066FF] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                    </div>
                </div>

                {{-- Tableau (cartes sur mobile) --}}
                <div class="md:overflow-x-auto">
                    <table class="block w-full text-left text-xs md:table">
                        <thead class="hidden border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500 md:table-header-group">
                            <tr>
                                <th class="w-10 px-3 py-3 text-center">#</th>
                                <th class="w-32 px-3 py-3 text-center">Photo / Image</th>
                                <th class="min-w-[200px] px-3 py-3">Produit & Réf.</th>
                                <th class="min-w-[130px] px-3 py-3 text-right">Prix d'Achat (FCFA)</th>
                                <th class="min-w-[140px] px-3 py-3 text-right">Prix de Revente (FCFA) <span class="text-rose-500">*</span></th>
                                <th class="min-w-[140px] px-3 py-3 text-right">Marge Brute (FCFA)</th>
                                <th class="min-w-[110px] px-3 py-3 text-right">Taux Marge (%)</th>
                                @if($warehouses->isNotEmpty())
                                    <th class="min-w-[130px] px-3 py-3 text-right">
                                        Stock
                                        <span class="block text-[9px] font-medium normal-case tracking-normal text-amber-700" x-text="currentWarehouseLabel()"></span>
                                    </th>
                                @endif
                                <th class="w-12 px-3 py-3 text-center">Retirer</th>
                            </tr>
                        </thead>
                        <tbody class="block divide-y divide-slate-100 md:table-row-group">
                            <template x-for="(item, index) in visibleProducts" :key="item.id">
                                <tr class="grid grid-cols-2 gap-x-3 gap-y-2 p-4 transition-colors hover:bg-slate-50/70 md:table-row md:p-0"
                                    :class="item.upload_status === 'error' ? 'bg-rose-50/60' : ''">
                                    {{-- Index --}}
                                    <td class="hidden px-3 py-3 text-center font-mono font-bold text-slate-400 md:table-cell" x-text="index + 1"></td>

                                    {{-- Colonne Image --}}
                                    <td class="col-span-2 md:table-cell md:px-3 md:py-3 md:text-center">
                                        <div class="flex items-center gap-3 md:flex-col md:gap-1.5">
                                            {{-- Miniature --}}
                                            <div @drop.stop.prevent="isDragging = false; $event.dataTransfer.files[0] && $event.dataTransfer.files[0].type.startsWith('image/') && setRowFile(item, $event.dataTransfer.files[0])"
                                                 :class="isDragging ? 'ring-2 ring-emerald-400 ring-offset-1' : ''"
                                                 title="Glissez une photo ici pour cet article"
                                                 class="relative flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-2xs md:h-14 md:w-14">
                                                <template x-if="displayImage(item)">
                                                    <img :src="displayImage(item)" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                                </template>
                                                <template x-if="!displayImage(item)">
                                                    <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </template>

                                                <span x-show="item.pending_file && !['uploading', 'processing'].includes(item.upload_status)" x-cloak class="absolute right-0 top-0 rounded-bl bg-emerald-500 px-1 text-[8px] font-bold text-white">Nouveau</span>
                                                <span x-show="usesCommonImage(item)" x-cloak class="absolute right-0 top-0 rounded-bl bg-sky-500 px-1 text-[8px] font-bold text-white">Commune</span>
                                                <span x-show="item.delete_image" x-cloak class="absolute right-0 top-0 rounded-bl bg-rose-500 px-1 text-[8px] font-bold text-white">Supprimée</span>
                                                <span x-show="item.upload_status === 'done'" x-cloak class="absolute right-0 top-0 rounded-bl bg-emerald-600 px-1 text-[8px] font-bold text-white">✓</span>
                                                <div x-show="['uploading', 'processing'].includes(item.upload_status)" x-cloak class="absolute inset-0 flex items-center justify-center bg-white/70">
                                                    <svg class="h-5 w-5 animate-spin text-[#0066FF]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                </div>
                                            </div>

                                            {{-- Sur mobile : nom de l'article à côté de la photo --}}
                                            <div class="min-w-0 flex-1 md:hidden">
                                                <div class="line-clamp-2 text-sm font-bold text-[#0B0F14]" x-text="item.name"></div>
                                                <div class="mt-0.5 text-[10px] text-slate-400"><span class="font-mono font-semibold text-[#0066FF]" x-text="item.sku"></span> · <span x-text="item.domain"></span></div>
                                            </div>

                                            {{-- Boutons photo --}}
                                            <div class="flex shrink-0 flex-col items-stretch gap-1 md:flex-row md:items-center">
                                                <label class="flex cursor-pointer items-center justify-center gap-1 rounded-lg bg-[#0066FF] px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-2xs transition active:bg-[#0052cc] md:hidden">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                                    Photo
                                                    <input type="file" accept="image/*" capture="environment" @change="handleRowImage($event, item, true)" class="sr-only">
                                                </label>
                                                <label class="cursor-pointer rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-center text-[11px] font-semibold text-slate-700 shadow-2xs transition hover:border-[#0066FF] hover:bg-blue-50 hover:text-[#0066FF] md:rounded md:px-2 md:py-0.5 md:text-[10px]">
                                                    <span class="md:hidden">Galerie</span>
                                                    <span class="hidden md:inline" x-text="displayImage(item) ? 'Changer' : 'Ajouter'"></span>
                                                    <input type="file" accept="image/*" @change="handleRowImage($event, item)" class="sr-only">
                                                </label>
                                                <button type="button" x-show="(item.current_image || item.pending_file) && !item.delete_image" x-cloak @click="markImageDeleted(item)"
                                                        class="rounded-lg px-2 py-1 text-[11px] font-semibold text-slate-400 transition hover:text-rose-600 md:p-0.5" title="Supprimer la photo">
                                                    <span class="md:hidden">Suppr. photo</span>
                                                    <svg class="hidden h-3.5 w-3.5 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <button type="button" x-show="item.delete_image" x-cloak @click="restoreImage(item)" class="px-1 text-[11px] text-[#0066FF] hover:underline md:text-[10px]">
                                                    Rétablir
                                                </button>
                                            </div>
                                        </div>
                                        <p x-show="item.upload_status === 'error'" x-cloak class="mt-1 text-[10px] font-semibold text-rose-600" x-text="item.upload_error"></p>
                                    </td>

                                    {{-- Produit & Réf (desktop) --}}
                                    <td class="hidden px-3 py-3 md:table-cell">
                                        <div class="line-clamp-1 text-xs font-bold text-[#0B0F14]" x-text="item.name"></div>
                                        <div class="mt-0.5 flex items-center gap-1.5 text-[10px] text-slate-400">
                                            <span class="font-mono font-semibold text-[#0066FF]" x-text="item.sku"></span>
                                            <span>•</span>
                                            <span x-text="item.domain"></span>
                                        </div>
                                        <div class="mt-0.5 text-[10px] text-slate-500">
                                            Stock : <strong class="text-[#0B0F14]" x-text="item.current_stock + ' unités'"></strong>
                                        </div>
                                    </td>

                                    {{-- Prix d'Achat --}}
                                    <td class="md:table-cell md:px-3 md:py-3 md:text-right">
                                        <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400 md:hidden">Prix d'achat</span>
                                        <input type="number" step="0.01" min="0" inputmode="decimal"
                                            x-model.number="item.purchase_price"
                                            placeholder="0"
                                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 py-2 text-right font-mono text-sm text-[#0B0F14] transition focus:border-[#0066FF] focus:outline-none md:py-1.5 md:text-xs">
                                    </td>

                                    {{-- Prix de Revente --}}
                                    <td class="md:table-cell md:px-3 md:py-3 md:text-right">
                                        <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400 md:hidden">Prix de revente *</span>
                                        <input type="number" step="0.01" min="0" required inputmode="decimal"
                                            x-model.number="item.selling_price"
                                            placeholder="0"
                                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 py-2 text-right font-mono text-sm font-bold text-[#0B0F14] transition focus:border-[#0066FF] focus:outline-none md:py-1.5 md:text-xs">
                                    </td>

                                    {{-- Marge Brute --}}
                                    <td class="font-mono text-xs font-bold md:table-cell md:px-3 md:py-3 md:text-right">
                                        <span class="mb-0.5 block font-sans text-[10px] font-semibold uppercase tracking-wide text-slate-400 md:hidden">Marge</span>
                                        <span :class="calculateMargin(item) > 0 ? 'text-emerald-600' : (calculateMargin(item) === 0 ? 'text-slate-500' : 'text-rose-600')"
                                              x-text="formatNumber(calculateMargin(item)) + ' F'"></span>
                                    </td>

                                    {{-- Taux de Marge % --}}
                                    <td class="font-mono text-xs md:table-cell md:px-3 md:py-3 md:text-right">
                                        <span class="mb-0.5 block font-sans text-[10px] font-semibold uppercase tracking-wide text-slate-400 md:hidden">Taux</span>
                                        <span class="inline-block rounded px-2 py-0.5 font-bold"
                                              :class="calculateMarginPercent(item) >= 20 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (calculateMarginPercent(item) > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200')"
                                              x-text="calculateMarginPercent(item) + ' %'"></span>
                                    </td>

                                    @if($warehouses->isNotEmpty())
                                        {{-- Stock de l'entrepôt sélectionné --}}
                                        <td class="col-span-2 md:table-cell md:px-3 md:py-3 md:text-right">
                                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400 md:hidden">Stock · <span class="normal-case text-amber-700" x-text="currentWarehouseLabel()"></span></span>
                                            <input type="number" min="0" step="1" inputmode="numeric"
                                                   :value="item.stocks[stockWarehouseId]"
                                                   @input="item.stocks[stockWarehouseId] = $event.target.value === '' ? item.original_stocks[stockWarehouseId] : parseInt($event.target.value, 10)"
                                                   :class="isStockChanged(item, stockWarehouseId) ? 'border-amber-400 bg-amber-50 ring-1 ring-amber-300' : 'border-[#E2E8F0] bg-white'"
                                                   class="w-full rounded-lg border px-2.5 py-2 text-right font-mono text-sm font-bold text-[#0B0F14] transition focus:border-[#0066FF] focus:outline-none md:py-1.5 md:text-xs">
                                            <div class="mt-1 text-[10px]" x-show="isStockChanged(item, stockWarehouseId)" x-cloak>
                                                <span class="text-slate-400 line-through" x-text="item.original_stocks[stockWarehouseId]"></span>
                                                <span class="font-semibold" :class="item.stocks[stockWarehouseId] > item.original_stocks[stockWarehouseId] ? 'text-emerald-600' : 'text-rose-600'"
                                                      x-text="(item.stocks[stockWarehouseId] > item.original_stocks[stockWarehouseId] ? '+' : '') + (item.stocks[stockWarehouseId] - item.original_stocks[stockWarehouseId])"></span>
                                            </div>
                                        </td>
                                    @endif

                                    {{-- Action : Retirer du lot --}}
                                    <td class="col-span-2 flex justify-end md:table-cell md:px-3 md:py-3 md:text-center">
                                        <button type="button" @click="removeProduct(item)" :disabled="products.length <= 1"
                                            class="flex items-center gap-1 rounded-md p-1.5 text-[11px] font-semibold text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-30"
                                            title="Retirer cet article du lot">
                                            <span class="md:hidden">Retirer du lot</span>
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Affichage progressif : le navigateur ne construit que les lignes visibles --}}
                <div class="flex flex-col items-center gap-2 border-t border-slate-100 px-4 py-4 text-xs text-slate-500">
                    <p x-show="filteredProducts.length === 0" x-cloak class="font-medium">Aucun article ne correspond à « <span x-text="search"></span> ».</p>
                    <p x-show="filteredProducts.length > 0">
                        <span x-text="visibleProducts.length"></span> article(s) affiché(s) sur <span x-text="filteredProducts.length"></span>
                        <span x-show="search" x-cloak>(sur <span x-text="products.length"></span> au total)</span>
                    </p>
                    <div x-show="visibleProducts.length < filteredProducts.length" class="flex flex-wrap justify-center gap-2">
                        <button type="button" @click="visibleLimit += pageSize" class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-[#0B0F14] hover:bg-slate-50">
                            Afficher <span x-text="Math.min(pageSize, filteredProducts.length - visibleProducts.length)"></span> de plus
                        </button>
                    </div>
                </div>

                {{-- Barre Récapitulative Totaux --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        Total des articles pris en compte : <strong class="text-[#0B0F14]" x-text="products.length"></strong>
                        <span x-show="stockChangesCount() > 0" x-cloak> · <strong class="text-amber-700" x-text="stockChangesCount()"></strong> ajustement(s) de stock</span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs font-semibold">
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

            {{-- Progression de l'envoi des photos --}}
            <div x-show="isSaving && uploadTotal > 0" x-cloak class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-center justify-between text-xs font-semibold text-[#0B0F14]">
                    <span>Envoi des photos…</span>
                    <span x-text="uploadDone + ' / ' + uploadTotal"></span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-blue-100">
                    <div class="h-full rounded-full bg-[#0066FF] transition-all" :style="'width:' + (uploadTotal ? Math.round(uploadDone / uploadTotal * 100) : 0) + '%'"></div>
                </div>
            </div>

            <div x-show="saveError" x-cloak class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800" x-text="saveError"></div>

            {{-- Actions Formulaire --}}
            <div class="sticky bottom-0 z-10 -mx-4 flex items-center justify-end gap-3 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:static sm:mx-0 sm:bg-transparent sm:px-0 sm:pt-4 sm:pb-0">
                <x-button variant="secondary" href="{{ route('stock.index', ['tab' => 'disponibilite']) }}">
                    Annuler
                </x-button>

                <button type="submit" :disabled="isSaving"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-6 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-[#0052CC] disabled:cursor-wait disabled:opacity-70 sm:flex-none">
                    <svg x-show="!isSaving" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="isSaving" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <span x-text="isSaving ? 'Enregistrement…' : 'Enregistrer les modifications'"></span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <x-image-compressor-script />
    <script>
        function bulkEditManager(config) {
            const UPLOAD_CONCURRENCY = 2;
            const compressImage = (file) => window.compressProductImage(file);
            // Les photos sont réduites une par une (une photo de 12 Mpx décodée occupe ~50 Mo de mémoire).
            let processingQueue = Promise.resolve();

            function normalizeForMatch(value) {
                return String(value || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]/g, '');
            }

            return {
                products: config.initialProducts || [],
                warehouses: config.warehouses || [],
                stockWarehouseId: (config.warehouses && config.warehouses.length) ? config.warehouses[0].id : null,
                bulkStockValue: null,
                commonImagePreview: null,
                commonImageFile: null,
                compressedCommonImage: null,
                applyCommonImageToAll: false,
                customMarkupPercent: null,
                isDragging: false,
                multiAssignMessage: '',
                shootingIndex: null,
                search: '',
                pageSize: 50,
                visibleLimit: 50,

                /** Articles correspondant à la recherche (les outils globaux s'y appliquent). */
                get filteredProducts() {
                    const term = normalizeForMatch(this.search);
                    if (!term) {
                        return this.products;
                    }
                    return this.products.filter(item => normalizeForMatch(item.name).includes(term) || normalizeForMatch(item.sku).includes(term));
                },

                get visibleProducts() {
                    return this.filteredProducts.slice(0, this.visibleLimit);
                },
                isSaving: false,
                saveError: '',
                uploadDone: 0,
                uploadTotal: 0,
                imagesUpdatedCount: 0,

                displayImage(item) {
                    if (item.delete_image) {
                        return null;
                    }
                    if (item.preview_image) {
                        return item.preview_image;
                    }
                    if (this.usesCommonImage(item)) {
                        return this.commonImagePreview;
                    }
                    return item.current_image || null;
                },

                usesCommonImage(item) {
                    return this.applyCommonImageToAll && !!this.commonImageFile && !item.pending_file && !item.delete_image && item.upload_status !== 'done';
                },

                /**
                 * Réduit la photo dès sa sélection (évite de saturer la mémoire du téléphone avec des
                 * miniatures en pleine résolution, ce qui faisait planter l'onglet), puis l'envoie
                 * immédiatement si demandé (photos prises avec l'appareil photo).
                 */
                setRowFile(item, file, autoUpload = false) {
                    const token = (item.processing_token || 0) + 1;
                    item.processing_token = token;
                    item.pending_file = file;
                    item.delete_image = false;
                    item.upload_status = 'processing';
                    item.upload_error = '';

                    processingQueue = processingQueue.then(async () => {
                        const compressed = await compressImage(file);
                        if (item.processing_token !== token) {
                            return;
                        }
                        if (item.preview_image) {
                            URL.revokeObjectURL(item.preview_image);
                        }
                        item.pending_file = compressed;
                        item.preview_image = URL.createObjectURL(compressed);
                        item.upload_status = '';
                        if (autoUpload) {
                            await this.sendImageRequest(item);
                        }
                    }).catch(() => {
                        item.upload_status = '';
                    });

                    return processingQueue;
                },

                handleCommonImage(event) {
                    const file = event.target.files[0];
                    if (!file) {
                        return;
                    }
                    if (this.commonImagePreview) {
                        URL.revokeObjectURL(this.commonImagePreview);
                    }
                    this.commonImageFile = file;
                    this.compressedCommonImage = null;
                    this.commonImagePreview = URL.createObjectURL(file);
                    this.applyCommonImageToAll = true;
                    event.target.value = '';
                },

                handleRowImage(event, item, autoUpload = false) {
                    const file = event.target.files[0];
                    if (file) {
                        this.setRowFile(item, file, autoUpload);
                    }
                    event.target.value = '';
                },

                /**
                 * Associe plusieurs photos aux articles : d'abord par SKU ou nom dans le nom du fichier,
                 * puis dans l'ordre du tableau (articles sans photo en priorité).
                 */
                assignMultipleFiles(fileList) {
                    const files = Array.from(fileList || []).filter(file => file.type.startsWith('image/'));
                    if (files.length === 0) {
                        return;
                    }

                    const assigned = new Set();
                    const unmatched = [];
                    let matchedByName = 0;

                    files.forEach(file => {
                        const fileKey = normalizeForMatch((file.name || '').replace(/\.[^.]+$/, ''));
                        const row = fileKey && this.products.find(item => {
                            if (assigned.has(item.id)) {
                                return false;
                            }
                            const skuKey = normalizeForMatch(item.sku);
                            const nameKey = normalizeForMatch(item.name);
                            return (skuKey.length >= 3 && fileKey.includes(skuKey)) || (nameKey.length >= 4 && (fileKey === nameKey || fileKey.includes(nameKey)));
                        });

                        if (row) {
                            this.setRowFile(row, file);
                            assigned.add(row.id);
                            matchedByName++;
                        } else {
                            unmatched.push(file);
                        }
                    });

                    const freeRows = this.products.filter(item => !assigned.has(item.id) && !item.pending_file);
                    const orderedRows = [
                        ...freeRows.filter(item => !item.current_image || item.delete_image),
                        ...freeRows.filter(item => item.current_image && !item.delete_image),
                    ];

                    let assignedInOrder = 0;
                    unmatched.forEach((file, position) => {
                        const row = orderedRows[position];
                        if (row) {
                            this.setRowFile(row, file);
                            assigned.add(row.id);
                            assignedInOrder++;
                        }
                    });

                    const ignored = files.length - matchedByName - assignedInOrder;
                    const parts = [assigned.size + ' photo(s) associée(s)'];
                    if (matchedByName > 0) {
                        parts.push(matchedByName + ' par SKU/nom');
                    }
                    if (assignedInOrder > 0) {
                        parts.push(assignedInOrder + ' dans l\'ordre du tableau');
                    }
                    let message = parts.join(' · ') + '.';
                    if (ignored > 0) {
                        message += ' ' + ignored + ' photo(s) ignorée(s) : plus de photos que d\'articles disponibles.';
                    }
                    this.multiAssignMessage = message + ' Vérifiez les miniatures avant d\'enregistrer.';
                },

                /** Prise de vue à la suite : une photo par article, en commençant par ceux sans photo. */
                startShooting() {
                    const firstWithoutPhoto = this.products.findIndex(item => !item.pending_file && (!item.current_image || item.delete_image));
                    this.shootingIndex = firstWithoutPhoto >= 0 ? firstWithoutPhoto : 0;
                    this.multiAssignMessage = '';
                },

                shootingItem() {
                    return this.shootingIndex === null ? null : this.products[this.shootingIndex] || null;
                },

                onShootingPhoto(event) {
                    const file = event.target.files[0];
                    event.target.value = '';
                    const item = this.shootingItem();
                    if (file && item) {
                        this.setRowFile(item, file, true);
                        this.nextShootingItem();
                    }
                },

                nextShootingItem() {
                    if (this.shootingIndex === null) {
                        return;
                    }
                    if (this.shootingIndex + 1 >= this.products.length) {
                        this.stopShooting();
                        return;
                    }
                    this.shootingIndex++;
                },

                stopShooting() {
                    const taken = this.products.filter(item => item.pending_file || item.upload_status === 'done').length;
                    this.shootingIndex = null;
                    if (taken > 0) {
                        this.multiAssignMessage = taken + ' photo(s) prise(s) : elles sont enregistrées automatiquement (✓ sur la miniature).';
                    }
                },

                markImageDeleted(item) {
                    item.processing_token = (item.processing_token || 0) + 1;
                    if (item.preview_image) {
                        URL.revokeObjectURL(item.preview_image);
                    }
                    item.pending_file = null;
                    item.preview_image = null;
                    item.delete_image = !!item.current_image;
                    item.upload_status = '';
                },

                restoreImage(item) {
                    item.delete_image = false;
                },

                pendingImagesCount() {
                    return this.products.filter(item => item.pending_file || item.delete_image || this.usesCommonImage(item)).length;
                },

                removeProduct(item) {
                    const index = this.products.indexOf(item);
                    if (this.products.length > 1 && index >= 0) {
                        this.products.splice(index, 1);
                        if (this.shootingIndex !== null && this.shootingIndex >= this.products.length) {
                            this.shootingIndex = this.products.length - 1;
                        }
                    }
                },

                isRowDirty(item) {
                    return Number(item.purchase_price || 0) !== Number(item.original_purchase_price || 0)
                        || Number(item.selling_price || 0) !== Number(item.original_selling_price || 0)
                        || this.warehouses.some(wh => this.isStockChanged(item, wh.id));
                },

                /**
                 * Les lignes modifiées (affichées ou non) sont envoyées dans un seul champ JSON :
                 * PHP ignore silencieusement les champs au-delà de max_input_vars (1000 par défaut).
                 */
                buildPayload() {
                    const changedRows = this.products.filter(item => this.isRowDirty(item)).map(item => {
                        const stocks = {};
                        this.warehouses.filter(wh => this.isStockChanged(item, wh.id)).forEach(wh => {
                            stocks[wh.id] = item.stocks[wh.id];
                        });
                        return {
                            id: item.id,
                            purchase_price: item.purchase_price,
                            selling_price: item.selling_price,
                            stocks,
                        };
                    });

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'products_json';
                    input.value = JSON.stringify(changedRows);
                    this.$refs.payload.replaceChildren(input);
                },

                csrfToken() {
                    return this.$refs.form.querySelector('input[name="_token"]').value;
                },

                async sendImageRequest(item) {
                    const formData = new FormData();
                    if (item.delete_image) {
                        formData.append('delete_image', '1');
                    } else {
                        if (item.pending_file) {
                            formData.append('image', await compressImage(item.pending_file));
                        } else {
                            if (!this.compressedCommonImage) {
                                this.compressedCommonImage = await compressImage(this.commonImageFile);
                            }
                            formData.append('image', this.compressedCommonImage);
                        }
                    }

                    item.upload_status = 'uploading';
                    item.upload_error = '';

                    try {
                        const response = await fetch(item.image_url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                            credentials: 'same-origin',
                        });
                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || data.success === false) {
                            const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                            throw new Error(firstError || data.message || (response.status === 413
                                ? 'Photo trop volumineuse pour le serveur.'
                                : 'Échec de l\'envoi (erreur ' + response.status + ').'));
                        }

                        if (item.preview_image) {
                            URL.revokeObjectURL(item.preview_image);
                        }
                        item.current_image = data.image_url || '';
                        item.preview_image = null;
                        item.pending_file = null;
                        item.delete_image = false;
                        item.upload_status = 'done';
                        this.imagesUpdatedCount++;
                    } catch (error) {
                        item.upload_status = 'error';
                        item.upload_error = error.message || 'Échec de l\'envoi de la photo.';
                    } finally {
                        this.uploadDone++;
                    }
                },

                async submitAll() {
                    if (this.isSaving) {
                        return;
                    }

                    this.isSaving = true;
                    await processingQueue;
                    const imageRows = this.products.filter(item => (item.pending_file && item.upload_status !== 'done') || item.delete_image || this.usesCommonImage(item));
                    this.saveError = '';
                    this.uploadDone = 0;
                    this.uploadTotal = imageRows.length;

                    const queue = [...imageRows];
                    const worker = async () => {
                        while (queue.length > 0) {
                            await this.sendImageRequest(queue.shift());
                        }
                    };
                    await Promise.all(Array.from({ length: Math.min(UPLOAD_CONCURRENCY, queue.length) }, worker));

                    const failedCount = this.products.filter(item => item.upload_status === 'error').length;
                    if (failedCount > 0) {
                        this.isSaving = false;
                        this.saveError = failedCount + ' photo(s) n\'ont pas pu être envoyées (voir les lignes en rouge). Les autres sont enregistrées : corrigez puis cliquez à nouveau sur « Enregistrer ».';
                        return;
                    }

                    if (this.applyCommonImageToAll) {
                        this.applyCommonImageToAll = false;
                    }

                    await this.$nextTick();
                    this.buildPayload();
                    this.$refs.form.submit();
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
                    this.filteredProducts.forEach(p => {
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

                currentWarehouseLabel() {
                    const wh = this.warehouses.find(w => w.id === this.stockWarehouseId);
                    return wh ? wh.name : '';
                },

                isStockChanged(item, warehouseId) {
                    return item.stocks[warehouseId] !== item.original_stocks[warehouseId];
                },

                stockChangesCount() {
                    return this.products.reduce((count, item) =>
                        count + this.warehouses.filter(wh => this.isStockChanged(item, wh.id)).length, 0);
                },

                applyStockToAll() {
                    const qty = parseInt(this.bulkStockValue, 10);
                    if (isNaN(qty) || qty < 0 || !this.stockWarehouseId) return;
                    this.filteredProducts.forEach(p => { p.stocks[this.stockWarehouseId] = qty; });
                },

                resetStocks() {
                    this.products.forEach(p => { p.stocks = { ...p.original_stocks }; });
                },

                formatNumber(val) {
                    return Math.round(val).toLocaleString('fr-FR');
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
