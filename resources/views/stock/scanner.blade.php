<x-layouts.app title="Mode Scan & Terminal Magasinier">
    <div x-data="{
            code: '',
            actionType: 'entree',
            quantity: 1,
            products: @js($products->map(fn($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'name' => $p->name,
                'domain' => $p->domain?->name ?? 'Général',
                'stock' => $p->current_stock,
                'unit' => $p->unit ?? 'pièce',
                'price' => number_format((float) ($p->selling_price ?? 0), 0, ',', ' ')
            ])),
            get matchedProduct() {
                if (!this.code) return null;
                const q = this.code.trim().toLowerCase();
                return this.products.find(p =>
                    (p.sku && p.sku.toLowerCase() === q) ||
                    (p.barcode && p.barcode.toLowerCase() === q) ||
                    String(p.id) === q
                );
            }
        }"
        class="mx-auto max-w-5xl space-y-6 pb-12">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-[#0B0F14] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                        TERMINAL MAGASINIER
                    </span>
                    <span class="text-xs font-medium text-slate-500">Lecteur Code-barres / QR Code & Mobile</span>
                </div>
                <h1 class="mt-1.5 text-xl font-bold text-[#0B0F14]">
                    Scan Rapide & Opérations Entrepôt
                </h1>
                <p class="text-xs text-slate-500">
                    Scannez un code-barres EAN-13 ou sélectionnez un SKU pour déclencher une Entrée, Sortie, Transfert ou Inventaire en 1 clic.
                </p>
            </div>

            <a href="{{ route('stock.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Retour au WMS</span>
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Left 7 Cols: Scanner Terminal Form --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-7">
                <form action="{{ route('stock.scanner.action') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Barcode / SKU Input --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                            1. Scanner Code-Barres / QR Code ou Saisir SKU
                        </label>
                        <div class="mt-2 flex gap-2">
                            <input type="text" name="code" x-model="code" autofocus required
                                   placeholder="Scannez avec la douchette ou tapez le SKU (ex: PRT-842)..."
                                   class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 font-mono text-sm font-bold text-[#0B0F14] focus:border-[#0066FF] focus:bg-white focus:outline-hidden">
                        </div>

                        {{-- Quick Pick Pills for Demo / Mobile --}}
                        <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] font-semibold text-slate-400">Sélection rapide :</span>
                            @foreach($products->take(5) as $prod)
                                <button type="button" @click="code = '{{ $prod->sku }}'"
                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:border-[#0066FF] hover:text-[#0066FF]">
                                    {{ $prod->sku }} ({{ Str::limit($prod->name, 18) }})
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Instant Product Card Preview --}}
                    <template x-if="matchedProduct">
                        <div class="flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50/50 p-4">
                            <div>
                                <span class="rounded bg-[#0066FF] px-2 py-0.5 text-[10px] font-bold uppercase text-white" x-text="matchedProduct.domain"></span>
                                <h3 class="mt-1 text-sm font-bold text-[#0B0F14]" x-text="matchedProduct.name"></h3>
                                <p class="text-xs text-slate-500">
                                    SKU : <strong x-text="matchedProduct.sku"></strong> •
                                    EAN : <strong x-text="matchedProduct.barcode || 'N/A'"></strong> •
                                    Prix : <strong x-text="matchedProduct.price + ' FCFA'"></strong>
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-bold uppercase text-slate-400">Stock Actuel</div>
                                <div class="text-2xl font-bold text-[#0B0F14]" x-text="matchedProduct.stock"></div>
                                <div class="text-[11px] text-slate-500" x-text="matchedProduct.unit"></div>
                            </div>
                        </div>
                    </template>

                    {{-- 4 Fast Action Buttons --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">
                            2. Choisir l'Opération Magasinier
                        </label>
                        <input type="hidden" name="action_type" :value="actionType">
                        <div class="mt-2 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                            <button type="button" @click="actionType = 'entree'"
                                    :class="actionType === 'entree' ? 'border-[#0066FF] bg-[#0066FF] text-white' : 'border-slate-200 bg-white text-slate-700'"
                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-center text-xs font-bold transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Entrée Stock</span>
                            </button>
                            <button type="button" @click="actionType = 'sortie'"
                                    :class="actionType === 'sortie' ? 'border-[#0B0F14] bg-[#0B0F14] text-white' : 'border-slate-200 bg-white text-slate-700'"
                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-center text-xs font-bold transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                </svg>
                                <span>Sortie Stock</span>
                            </button>
                            <button type="button" @click="actionType = 'transfert'"
                                    :class="actionType === 'transfert' ? 'border-[#0066FF] bg-[#0066FF] text-white' : 'border-slate-200 bg-white text-slate-700'"
                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-center text-xs font-bold transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span>Transfert</span>
                            </button>
                            <button type="button" @click="actionType = 'inventaire'"
                                    :class="actionType === 'inventaire' ? 'border-[#0B0F14] bg-[#0B0F14] text-white' : 'border-slate-200 bg-white text-slate-700'"
                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-center text-xs font-bold transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span>Inventaire</span>
                            </button>
                        </div>
                    </div>

                    {{-- Warehouse & Quantity --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Entrepôt concerné</label>
                            <select name="warehouse_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700" x-text="actionType === 'inventaire' ? 'Nouveau Stock Réel Compté' : 'Quantité Scannée'"></label>
                            <input type="number" name="quantity" x-model="quantity" min="1" required
                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-bold">
                        </div>
                    </div>

                    <div x-show="actionType === 'transfert'" x-cloak>
                        <label class="block text-xs font-semibold text-slate-700">Entrepôt de Destination</label>
                        <select name="destination_warehouse_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs">
                            @foreach($warehouses->reverse() as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-[#0066FF] py-3.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition hover:bg-blue-700">
                        Exécuter l'Opération Scannée
                    </button>
                </form>
            </div>

            {{-- Right 5 Cols: Recent Scans Log --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-5">
                <h2 class="text-sm font-bold text-[#0B0F14]">Dernières Opérations Terminal</h2>
                <p class="text-xs text-slate-500">Historique instantané des mouvements validés</p>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($recentScans as $scan)
                        <div class="py-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#0B0F14]">{{ $scan->product?->name }}</span>
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-700">
                                    {{ $scan->type }} ({{ $scan->quantity }})
                                </span>
                            </div>
                            <div class="mt-1 text-[11px] text-slate-500">
                                {{ $scan->warehouse?->name }} • Stock après : <strong>{{ $scan->stock_after }}</strong> • {{ $scan->reference }}
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400">Aucun scan récent.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
