<x-layouts.app>
    <x-slot:title>Prix & photos — Stock</x-slot>

    @php
        $initialRowsData = $products->map(function ($p) use ($warehouses) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'domain' => $p->domain?->name ?? 'Général',
                'current_image' => $p->thumbnail_url ?? '',
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
        @visibilitychange.document="if (document.hidden) closeCamera()"
        class="mx-auto max-w-6xl space-y-4 pb-32 text-sm"
    >
        {{-- ============ EN-TÊTE ============ --}}
        <header class="flex flex-wrap items-center gap-3">
            <a href="{{ route('stock.index', ['tab' => 'disponibilite']) }}"
               class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#E2E8F0] bg-white text-slate-500 hover:text-[#0B0F14]" title="Retour au stock">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-semibold tracking-tight text-[#0B0F14]">Prix & photos</h1>
                <p class="text-xs text-[#64748B]"><span x-text="products.length"></span> article(s) · modifiez les photos, les prix et le stock</p>
            </div>
            <button type="button" @click="openCameraSequence()"
                    class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white shadow-sm hover:bg-[#0052cc] sm:w-auto">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                Photographier les articles
            </button>
        </header>

        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm font-medium text-rose-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                <p class="font-semibold">Veuillez corriger les erreurs :</p>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stock.products.bulk-update') }}" method="POST" id="bulkEditForm" x-ref="form" @submit.prevent="submitAll()" class="space-y-4">
            @csrf
            <input type="hidden" name="images_updated_count" :value="imagesUpdatedCount">
            {{-- Champs générés à l'envoi à partir des données (les lignes non affichées sont aussi envoyées) --}}
            <div x-ref="payload" class="hidden"></div>

            {{-- ============ BARRE D'OUTILS ============ --}}
            <section class="space-y-3 rounded-2xl border border-[#E2E8F0] bg-white p-3 sm:p-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" x-model.debounce.250ms="search" @input="visibleLimit = pageSize" placeholder="Rechercher un article (nom, SKU)…"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] py-2.5 pl-9 pr-3 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                </div>

                <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1">
                    <label class="flex shrink-0 cursor-pointer items-center gap-1.5 rounded-full border border-[#E2E8F0] px-3 py-1.5 text-xs font-medium text-[#0B0F14] hover:bg-slate-50">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Photos de la galerie
                        <input type="file" accept="image/*" multiple class="sr-only" @change="assignMultipleFiles($event.target.files); $event.target.value = ''">
                    </label>
                    <button type="button" @click="panel = panel === 'margin' ? '' : 'margin'" :class="panel === 'margin' ? 'border-[#0066FF] bg-blue-50 text-[#0066FF]' : 'border-[#E2E8F0] text-[#0B0F14] hover:bg-slate-50'"
                            class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-medium">Marge</button>
                    @if($warehouses->isNotEmpty())
                        <button type="button" @click="panel = panel === 'stock' ? '' : 'stock'" :class="panel === 'stock' ? 'border-[#0066FF] bg-blue-50 text-[#0066FF]' : 'border-[#E2E8F0] text-[#0B0F14] hover:bg-slate-50'"
                                class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-medium">Stock</button>
                    @endif
                    <button type="button" @click="panel = panel === 'common' ? '' : 'common'" :class="panel === 'common' ? 'border-[#0066FF] bg-blue-50 text-[#0066FF]' : 'border-[#E2E8F0] text-[#0B0F14] hover:bg-slate-50'"
                            class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-medium">Même photo pour tous</button>
                </div>

                {{-- Panneau : marge --}}
                <div x-show="panel === 'margin'" x-cloak class="space-y-2 rounded-xl bg-[#F8FAFC] p-3">
                    <p class="text-xs text-[#64748B]">Prix de vente = prix d'achat + marge, pour les articles affichés par la recherche.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach([20, 30, 40, 50, 100] as $percent)
                            <button type="button" @click="applyMarginPercent({{ $percent }})" class="rounded-lg border border-[#E2E8F0] bg-white px-3 py-1.5 text-xs font-semibold text-[#0B0F14] hover:border-[#0066FF] hover:text-[#0066FF]">+{{ $percent }}%</button>
                        @endforeach
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="1" min="1" x-model.number="customMarkupPercent" placeholder="Autre %" class="w-24 rounded-lg border border-[#E2E8F0] bg-white px-2.5 py-1.5 text-xs focus:border-[#0066FF] focus:outline-none">
                            <button type="button" @click="applyCustomMarkup()" class="rounded-lg bg-[#0066FF] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#0052cc]">Appliquer</button>
                        </div>
                    </div>
                </div>

                {{-- Panneau : stock par entrepôt --}}
                @if($warehouses->isNotEmpty())
                    <div x-show="panel === 'stock'" x-cloak class="space-y-2 rounded-xl bg-[#F8FAFC] p-3">
                        <p class="text-xs text-[#64748B]"><strong class="text-[#0B0F14]">Stock par entrepôt</strong> : choisissez l'entrepôt, puis saisissez la quantité exacte de chaque article. Chaque changement crée un ajustement tracé.</p>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <select x-model.number="stockWarehouseId" class="rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-xs font-semibold text-[#0B0F14] focus:border-[#0066FF] focus:outline-none sm:w-64">
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}{{ $warehouse->code ? ' (' . $warehouse->code . ')' : '' }}</option>
                                @endforeach
                            </select>
                            <div class="flex flex-1 items-center gap-2">
                                <input type="number" min="0" step="1" x-model.number="bulkStockValue" placeholder="Quantité pour tous"
                                       class="min-w-0 flex-1 rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-xs focus:border-[#0066FF] focus:outline-none">
                                <button type="button" @click="applyStockToAll()" class="shrink-0 rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600">Appliquer à tous</button>
                                <button type="button" @click="resetStocks()" x-show="stockChangesCount() > 0" x-cloak class="shrink-0 px-2 py-2 text-xs font-semibold text-slate-500 hover:text-[#0B0F14]">Annuler</button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Panneau : même photo pour tous --}}
                <div x-show="panel === 'common'" x-cloak class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] p-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-[#E2E8F0] bg-white">
                        <template x-if="commonImagePreview">
                            <img :src="commonImagePreview" alt="" class="h-full w-full object-cover">
                        </template>
                    </div>
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <label class="inline-flex cursor-pointer items-center rounded-lg border border-[#E2E8F0] bg-white px-3 py-1.5 text-xs font-semibold text-[#0066FF] hover:bg-blue-50">
                            Choisir la photo…
                            <input type="file" accept="image/*" class="sr-only" @change="handleCommonImage($event)">
                        </label>
                        <label class="flex cursor-pointer select-none items-center gap-2" x-show="commonImageFile" x-cloak>
                            <input type="checkbox" x-model="applyCommonImageToAll" class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                            <span class="text-xs text-slate-700">Appliquer aux articles sans nouvelle photo</span>
                        </label>
                    </div>
                </div>

                <p x-show="multiAssignMessage" x-cloak class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-800" x-text="multiAssignMessage"></p>
            </section>

            {{-- ============ ARTICLES ============ --}}
            <ul class="space-y-2">
                <template x-for="item in visibleProducts" :key="item.id">
                    <li class="rounded-2xl border bg-white p-3 transition sm:p-4"
                        :class="item.upload_status === 'error' ? 'border-rose-300 bg-rose-50/40' : (isRowDirty(item) || item.pending_file ? 'border-[#0066FF]/40' : 'border-[#E2E8F0]')">
                        <div class="flex items-start gap-3">
                            {{-- Photo : toucher pour photographier --}}
                            <button type="button" @click="openCamera(item)"
                                    @drop.stop.prevent="isDragging = false; $event.dataTransfer.files[0] && $event.dataTransfer.files[0].type.startsWith('image/') && setRowFile(item, $event.dataTransfer.files[0])"
                                    :class="isDragging ? 'ring-2 ring-emerald-400 ring-offset-1' : ''"
                                    class="group relative flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-[#E2E8F0] bg-slate-100 sm:h-16 sm:w-16"
                                    title="Prendre une photo">
                                <template x-if="displayImage(item)">
                                    <img :src="displayImage(item)" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!displayImage(item)">
                                    <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                </template>
                                <span x-show="item.upload_status === 'done'" x-cloak class="absolute bottom-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[11px] font-bold text-white shadow">✓</span>
                                <span x-show="item.pending_file && !['uploading', 'processing', 'done'].includes(item.upload_status)" x-cloak class="absolute bottom-1 right-1 rounded-full bg-[#0066FF] px-1.5 text-[9px] font-bold text-white shadow">Nouveau</span>
                                <span x-show="usesCommonImage(item)" x-cloak class="absolute bottom-1 right-1 rounded-full bg-sky-500 px-1.5 text-[9px] font-bold text-white shadow">Commune</span>
                                <span x-show="item.delete_image" x-cloak class="absolute inset-x-0 bottom-0 bg-rose-500 py-0.5 text-center text-[9px] font-bold text-white">Supprimée</span>
                                <div x-show="['uploading', 'processing'].includes(item.upload_status)" x-cloak class="absolute inset-0 flex items-center justify-center bg-white/70">
                                    <svg class="h-6 w-6 animate-spin text-[#0066FF]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                </div>
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-2 text-sm font-semibold text-[#0B0F14]" x-text="item.name"></p>
                                        <p class="truncate text-[11px] text-slate-500"><span class="font-mono font-semibold text-[#0066FF]" x-text="item.sku"></span> · <span x-text="item.domain"></span></p>
                                    </div>
                                    <button type="button" @click="removeProduct(item)" :disabled="products.length <= 1" title="Retirer cet article du lot"
                                            class="shrink-0 rounded-lg p-1.5 text-slate-300 hover:bg-slate-100 hover:text-slate-600 disabled:opacity-30">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                {{-- Actions photo --}}
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium">
                                    <button type="button" @click="openCamera(item)" class="text-[#0066FF] hover:underline">Photographier</button>
                                    <label class="cursor-pointer text-slate-600 hover:underline">
                                        Galerie
                                        <input type="file" accept="image/*" @change="handleRowImage($event, item)" class="sr-only">
                                    </label>
                                    <button type="button" x-show="(item.current_image || item.pending_file) && !item.delete_image" x-cloak @click="markImageDeleted(item)" class="text-slate-400 hover:text-rose-600">Supprimer la photo</button>
                                    <button type="button" x-show="item.delete_image" x-cloak @click="restoreImage(item)" class="text-[#0066FF] hover:underline">Rétablir</button>
                                </div>
                                <p x-show="item.upload_status === 'error'" x-cloak class="mt-1 text-[11px] font-semibold text-rose-600" x-text="item.upload_error"></p>

                                {{-- Prix & stock --}}
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    <label class="block">
                                        <span class="block truncate text-[10px] font-medium uppercase tracking-wide text-slate-400">Achat</span>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" x-model.number="item.purchase_price" placeholder="0"
                                               class="mt-0.5 w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 py-2 text-right font-mono text-sm focus:border-[#0066FF] focus:outline-none">
                                    </label>
                                    <label class="block">
                                        <span class="block truncate text-[10px] font-medium uppercase tracking-wide text-slate-400">Vente *</span>
                                        <input type="number" step="0.01" min="0" required inputmode="decimal" x-model.number="item.selling_price" placeholder="0"
                                               class="mt-0.5 w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 py-2 text-right font-mono text-sm font-semibold focus:border-[#0066FF] focus:outline-none">
                                    </label>
                                    <div>
                                        <span class="block truncate text-[10px] font-medium uppercase tracking-wide text-slate-400">Marge</span>
                                        <p class="mt-0.5 flex h-[38px] items-center justify-end rounded-lg bg-[#F8FAFC] px-2.5 font-mono text-sm font-semibold"
                                           :class="calculateMargin(item) > 0 ? 'text-emerald-600' : (calculateMargin(item) === 0 ? 'text-slate-400' : 'text-rose-600')">
                                            <span x-text="calculateMarginPercent(item) + ' %'"></span>
                                        </p>
                                    </div>
                                    @if($warehouses->isNotEmpty())
                                        <label class="block">
                                            <span class="block truncate text-[10px] font-medium uppercase tracking-wide text-slate-400">Stock · <span class="normal-case text-amber-700" x-text="currentWarehouseLabel()"></span></span>
                                            <input type="number" min="0" step="1" inputmode="numeric"
                                                   :value="item.stocks[stockWarehouseId]"
                                                   @input="item.stocks[stockWarehouseId] = $event.target.value === '' ? item.original_stocks[stockWarehouseId] : parseInt($event.target.value, 10)"
                                                   :class="isStockChanged(item, stockWarehouseId) ? 'border-amber-400 bg-amber-50' : 'border-[#E2E8F0] bg-white'"
                                                   class="mt-0.5 w-full rounded-lg border px-2.5 py-2 text-right font-mono text-sm focus:border-[#0066FF] focus:outline-none">
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>

            {{-- Affichage progressif : le navigateur ne construit que les articles visibles --}}
            <div class="flex flex-col items-center gap-2 text-xs text-slate-500">
                <p x-show="filteredProducts.length === 0" x-cloak class="font-medium">Aucun article ne correspond à « <span x-text="search"></span> ».</p>
                <p x-show="filteredProducts.length > 0"><span x-text="visibleProducts.length"></span> article(s) affiché(s) sur <span x-text="filteredProducts.length"></span></p>
                <button type="button" x-show="visibleProducts.length < filteredProducts.length" @click="visibleLimit += pageSize"
                        class="rounded-xl border border-[#E2E8F0] bg-white px-4 py-2 font-semibold text-[#0B0F14] hover:bg-slate-50">
                    Afficher <span x-text="Math.min(pageSize, filteredProducts.length - visibleProducts.length)"></span> de plus
                </button>
            </div>

            {{-- ============ BARRE D'ENREGISTREMENT (toujours visible) ============ --}}
            <div class="fixed inset-x-0 bottom-16 z-30 border-t border-[#E2E8F0] bg-white/95 px-4 py-3 backdrop-blur md:bottom-0">
                <div class="mx-auto flex max-w-6xl items-center gap-3">
                    <div class="min-w-0 flex-1 text-xs text-slate-500">
                        <template x-if="isSaving && uploadTotal > 0">
                            <div>
                                <p class="font-semibold text-[#0B0F14]">Envoi des photos… <span x-text="uploadDone + ' / ' + uploadTotal"></span></p>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-blue-100">
                                    <div class="h-full rounded-full bg-[#0066FF] transition-all" :style="'width:' + Math.round(uploadDone / uploadTotal * 100) + '%'"></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!(isSaving && uploadTotal > 0)">
                            <p>
                                <span x-show="saveError" x-cloak class="font-semibold text-rose-600" x-text="saveError"></span>
                                <span x-show="!saveError">
                                    <strong class="text-[#0B0F14]" x-text="pendingImagesCount() + imagesUpdatedCount"></strong> photo(s) ·
                                    <strong class="text-[#0B0F14]" x-text="products.filter(item => isRowDirty(item)).length"></strong> article(s) modifié(s)
                                </span>
                            </p>
                        </template>
                    </div>
                    <a href="{{ route('stock.index', ['tab' => 'disponibilite']) }}" class="hidden rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 sm:block">Annuler</a>
                    <button type="submit" :disabled="isSaving"
                            class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#0066FF] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0052cc] disabled:cursor-wait disabled:opacity-70">
                        <svg x-show="isSaving" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span x-text="isSaving ? 'Enregistrement…' : 'Enregistrer'"></span>
                    </button>
                </div>
            </div>
        </form>

        {{-- ============ APPAREIL PHOTO INTÉGRÉ ============ --}}
        <div x-show="cameraOpen" x-cloak class="fixed inset-0 z-[70] flex flex-col bg-black text-white" role="dialog" aria-modal="true" aria-label="Appareil photo">
            {{-- Article en cours --}}
            <div class="flex items-center gap-3 px-4 pb-3 pt-[max(0.75rem,env(safe-area-inset-top))]">
                <button type="button" @click="closeCamera()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Fermer">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold" x-text="cameraItem() ? cameraItem().name : ''"></p>
                    <p class="text-[11px] text-white/60">
                        <span class="font-mono" x-text="cameraItem() ? cameraItem().sku : ''"></span>
                        <span x-show="cameraContinuous"> · article <span x-text="cameraIndex + 1"></span> / <span x-text="products.length"></span></span>
                    </p>
                </div>
                <span x-show="cameraShots > 0" x-cloak class="shrink-0 rounded-full bg-emerald-500/20 px-2.5 py-1 text-[11px] font-semibold text-emerald-300"><span x-text="cameraShots"></span> photo(s)</span>
            </div>

            {{-- Visée --}}
            <div class="relative flex-1 overflow-hidden">
                <video x-ref="cameraVideo" playsinline muted autoplay class="absolute inset-0 h-full w-full object-cover"></video>
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="aspect-square w-[78%] max-w-md rounded-3xl border-2 border-white/70 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]"></div>
                </div>
                <div x-show="cameraFlash" class="absolute inset-0 bg-white/80"></div>
                <div x-show="cameraStarting" x-cloak class="absolute inset-0 flex items-center justify-center text-sm text-white/80">Ouverture de l'appareil photo…</div>
                <div x-show="cameraError" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-black p-6 text-center">
                    <p class="text-sm" x-text="cameraError"></p>
                    <label class="cursor-pointer rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-[#0B0F14]">
                        Choisir une photo à la place
                        <input type="file" accept="image/*" class="sr-only" @change="cameraItem() && handleRowImage($event, cameraItem()); closeCamera()">
                    </label>
                </div>
            </div>

            {{-- Déclencheur --}}
            <div class="flex items-center justify-between gap-4 px-6 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-4">
                <div class="h-14 w-14 overflow-hidden rounded-xl border border-white/30 bg-white/10">
                    <template x-if="cameraLastPreview">
                        <img :src="cameraLastPreview" alt="" class="h-full w-full object-cover">
                    </template>
                </div>
                <button type="button" @click="capturePhoto()" :disabled="!cameraReady"
                        class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-white/80 disabled:opacity-40" aria-label="Prendre la photo">
                    <span class="h-16 w-16 rounded-full bg-white transition active:scale-90"></span>
                </button>
                <button type="button" @click="cameraContinuous ? nextCameraItem() : closeCamera()" class="w-14 text-center text-sm font-semibold text-white/80 hover:text-white" x-text="cameraContinuous ? 'Passer' : 'Fermer'"></button>
            </div>
        </div>
    </div>

    @push('scripts')
    <x-image-compressor-script />
    <script>
        function bulkEditManager(config) {
            const UPLOAD_CONCURRENCY = 2;
            const compressImage = (file) => window.compressProductImage(file);
            const processImage = (file) => window.processProductImage(file);
            // Les photos sont réduites une par une (une photo de 12 Mpx décodée occupe ~50 Mo de mémoire).
            let processingQueue = Promise.resolve();
            // Flux vidéo de l'appareil photo intégré (hors de l'état Alpine : un MediaStream ne doit pas être rendu réactif).
            let cameraStream = null;
            // Envois lancés depuis l'appareil photo, attendus avant l'enregistrement final.
            const inFlightUploads = new Set();
            const canvasToBlob = (canvas, quality) => new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
            // Recadre au centre en carré, comme le cadre affiché dans la visée.
            const drawVideoFrame = (video, maxDimension) => {
                const side = Math.min(video.videoWidth, video.videoHeight);
                const size = Math.max(1, Math.round(Math.min(side, maxDimension)));
                const canvas = document.createElement('canvas');
                canvas.width = canvas.height = size;
                canvas.getContext('2d').drawImage(video, (video.videoWidth - side) / 2, (video.videoHeight - side) / 2, side, side, 0, 0, size, size);
                return canvas;
            };

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
                panel: '',
                cameraOpen: false,
                cameraIndex: 0,
                cameraContinuous: false,
                cameraStarting: false,
                cameraReady: false,
                cameraError: '',
                cameraFlash: false,
                cameraShots: 0,
                cameraLastPreview: null,
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
                 * Réduit la photo dès sa sélection : des miniatures en pleine résolution saturaient
                 * la mémoire du téléphone et faisaient planter l'onglet.
                 */
                setRowFile(item, file) {
                    const token = (item.processing_token || 0) + 1;
                    item.processing_token = token;
                    item.pending_file = file;
                    item.pending_file_ready = false;
                    item.delete_image = false;
                    item.upload_status = 'processing';
                    item.upload_error = '';

                    processingQueue = processingQueue.then(async () => {
                        const processed = await processImage(file);
                        if (item.processing_token !== token) {
                            return;
                        }
                        if (item.preview_image) {
                            URL.revokeObjectURL(item.preview_image);
                        }
                        item.pending_file = processed.file;
                        item.pending_file_ready = true;
                        item.preview_image = URL.createObjectURL(processed.preview);
                        item.upload_status = '';
                    }).catch(() => {
                        item.upload_status = '';
                    });

                    return processingQueue;
                },

                async handleCommonImage(event) {
                    const file = event.target.files[0];
                    if (!file) {
                        return;
                    }
                    if (this.commonImagePreview) {
                        URL.revokeObjectURL(this.commonImagePreview);
                    }
                    event.target.value = '';
                    const processed = await processImage(file);
                    this.commonImageFile = processed.file;
                    this.compressedCommonImage = processed.file;
                    this.commonImagePreview = URL.createObjectURL(processed.preview);
                    this.applyCommonImageToAll = true;
                },

                handleRowImage(event, item) {
                    const file = event.target.files[0];
                    if (file) {
                        this.setRowFile(item, file);
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

                cameraItem() {
                    return this.cameraOpen ? (this.products[this.cameraIndex] || null) : null;
                },

                /** Photographie un seul article. */
                openCamera(item) {
                    this.cameraIndex = Math.max(0, this.products.indexOf(item));
                    this.cameraContinuous = false;
                    this.startCamera();
                },

                /** Photographie les articles à la suite, en commençant par ceux qui n'ont pas de photo. */
                openCameraSequence() {
                    const pool = this.filteredProducts;
                    const firstWithoutPhoto = pool.find(item => !item.pending_file && !item.current_image && item.upload_status !== 'done');
                    this.cameraIndex = Math.max(0, this.products.indexOf(firstWithoutPhoto || pool[0]));
                    this.cameraContinuous = true;
                    this.startCamera();
                },

                async startCamera() {
                    this.cameraOpen = true;
                    this.cameraError = '';
                    this.cameraShots = 0;
                    this.cameraLastPreview = null;
                    if (cameraStream) {
                        return;
                    }
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.cameraError = "L'appareil photo n'est pas disponible dans ce navigateur (une connexion sécurisée https est nécessaire).";
                        return;
                    }

                    this.cameraStarting = true;
                    this.cameraReady = false;
                    try {
                        cameraStream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1440 } },
                            audio: false,
                        });
                        if (!this.cameraOpen) {
                            this.stopCameraStream();
                            return;
                        }
                        const video = this.$refs.cameraVideo;
                        video.srcObject = cameraStream;
                        await video.play();
                        this.cameraReady = true;
                    } catch (error) {
                        this.stopCameraStream();
                        this.cameraError = error && error.name === 'NotAllowedError'
                            ? "L'accès à l'appareil photo a été refusé. Autorisez-le dans les réglages du navigateur (icône à gauche de l'adresse), puis réessayez."
                            : "Impossible d'ouvrir l'appareil photo de cet appareil.";
                    } finally {
                        this.cameraStarting = false;
                    }
                },

                stopCameraStream() {
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(track => track.stop());
                        cameraStream = null;
                    }
                    if (this.$refs.cameraVideo) {
                        this.$refs.cameraVideo.srcObject = null;
                    }
                    this.cameraReady = false;
                },

                closeCamera() {
                    if (!this.cameraOpen) {
                        return;
                    }
                    this.stopCameraStream();
                    this.cameraOpen = false;
                    if (this.cameraShots > 0) {
                        this.multiAssignMessage = this.cameraShots + ' photo(s) prise(s) et enregistrée(s) (✓ sur la miniature).';
                    }
                },

                /**
                 * Capture l'image de la visée (≤ 1600 px, sans décoder de photo de 12 Mpx) et l'enregistre
                 * aussitôt : rien n'est perdu si la page est fermée ensuite.
                 */
                async capturePhoto() {
                    const item = this.cameraItem();
                    const video = this.$refs.cameraVideo;
                    if (!item || !this.cameraReady || !video.videoWidth) {
                        return;
                    }

                    this.cameraFlash = true;
                    setTimeout(() => { this.cameraFlash = false; }, 120);

                    const mainCanvas = drawVideoFrame(video, 1600);
                    const previewCanvas = drawVideoFrame(video, 200);
                    const [mainBlob, previewBlob] = await Promise.all([canvasToBlob(mainCanvas, 0.85), canvasToBlob(previewCanvas, 0.7)]);
                    mainCanvas.width = mainCanvas.height = 0;
                    previewCanvas.width = previewCanvas.height = 0;
                    if (!mainBlob) {
                        return;
                    }

                    item.processing_token = (item.processing_token || 0) + 1;
                    if (item.preview_image) {
                        URL.revokeObjectURL(item.preview_image);
                    }
                    item.pending_file = new File([mainBlob], (item.sku || 'article') + '.jpg', { type: 'image/jpeg' });
                    item.pending_file_ready = true;
                    item.preview_image = URL.createObjectURL(previewBlob || mainBlob);
                    item.delete_image = false;
                    item.upload_error = '';
                    this.cameraLastPreview = item.preview_image;
                    this.cameraShots++;
                    const upload = this.sendImageRequest(item);
                    inFlightUploads.add(upload);
                    upload.finally(() => inFlightUploads.delete(upload));

                    if (this.cameraContinuous) {
                        this.nextCameraItem();
                    } else {
                        this.closeCamera();
                    }
                },

                nextCameraItem() {
                    const pool = this.filteredProducts;
                    const position = pool.indexOf(this.products[this.cameraIndex]);
                    const next = pool[position + 1];
                    if (!next) {
                        this.closeCamera();
                        return;
                    }
                    this.cameraIndex = this.products.indexOf(next);
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
                            formData.append('image', item.pending_file_ready ? item.pending_file : await compressImage(item.pending_file));
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
                        item.current_image = data.thumbnail_url || data.image_url || '';
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

                    this.closeCamera();
                    this.isSaving = true;
                    await processingQueue;
                    await Promise.all([...inFlightUploads]);
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
