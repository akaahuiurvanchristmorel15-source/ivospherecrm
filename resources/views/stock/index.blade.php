@php
    // Catalogue : modification, ajustement du stock et suppression réservés (Administration › Rôles & permissions)
    $canEditProducts = auth()->user()->hasPermission('products.edit');
    $canAdjustProducts = auth()->user()->hasPermission('products.adjust');
    $canDeleteProducts = auth()->user()->hasPermission('products.delete');
    $canSelectProducts = $canEditProducts || $canDeleteProducts;
    $canCreateProducts = auth()->user()->hasPermission('products.create');
    $canPrintCatalog = auth()->user()->hasPermission('stocks.catalog');
@endphp
<x-layouts.app title="Gestion des Stocks & Entrepôts (WMS)">
    <div x-data="{
        activeTab: '{{ request('tab', 'synthese') }}',
        showActions: false,
        search: '',
        filterStatus: 'all',
        filterWarehouse: {{ Js::from((string) request('warehouse_id', '')) }},
        warehouseProductCounts: {{ Js::from($warehouses->mapWithKeys(fn ($wh) => [$wh->id => $wh->warehouseStocks->where('physical_quantity', '>', 0)->count()])) }},
        selectedProductIds: [],
        allProductIds: {{ Js::from($products->pluck('id')->values()) }},
        get isAllSelected() {
            return this.allProductIds.length > 0 && this.selectedProductIds.length === this.allProductIds.length;
        },
        toggleSelectAll() {
            this.selectedProductIds = this.isAllSelected ? [] : [...this.allProductIds];
        },
        matches(text, avail, min, warehouseIds = []) {
            const okSearch = this.search === '' || text.includes(this.search.toLowerCase());
            const okWarehouse = this.filterWarehouse === '' || warehouseIds.includes(Number(this.filterWarehouse));
            const okStatus = this.filterStatus === 'all'
                || (this.filterStatus === 'in_stock' && avail > min)
                || (this.filterStatus === 'low_stock' && avail > 0 && avail <= min)
                || (this.filterStatus === 'out_of_stock' && avail <= 0);
            return okSearch && okStatus && okWarehouse;
        }
    }" class="mx-auto max-w-7xl space-y-5 pb-12">

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900">
                <span class="h-2 w-2 rounded-full bg-rose-600"></span>{{ session('error') }}
            </div>
        @endif

        {{-- ============ EN-TÊTE ============ --}}
        <header class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="hidden text-[11px] font-medium uppercase tracking-wider text-[#64748B] sm:block">
                    Ivosphere WMS · {{ $activeWarehousesCount }} entrepôt(s) actif(s)
                </p>
                <h1 class="mt-0.5 text-xl font-semibold tracking-tight text-[#0B0F14] sm:text-2xl">Gestion des Stocks</h1>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                {{-- Menu actions secondaires --}}
                <div class="relative" @click.outside="showActions = false">
                    <button type="button" @click="showActions = !showActions"
                            class="flex h-10 items-center gap-1.5 rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-medium text-[#0B0F14] hover:bg-slate-50">
                        <span>Outils</span>
                        <svg class="h-3.5 w-3.5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showActions" x-cloak x-transition
                         class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-xl border border-[#E2E8F0] bg-white py-1 shadow-lg">
                        <a href="{{ route('stock.scanner') }}" class="block px-4 py-2.5 text-xs text-[#0B0F14] hover:bg-[#F5F7FA]">Mode Scan / Magasinier</a>
                        <a href="{{ route('stock.warehouses.index') }}" class="block px-4 py-2.5 text-xs text-[#0B0F14] hover:bg-[#F5F7FA]">Entrepôts</a>
                        <a href="{{ route('stock.movements.create') }}" class="block px-4 py-2.5 text-xs text-[#0B0F14] hover:bg-[#F5F7FA]">Nouveau mouvement</a>
                        @if($canCreateProducts)
                        <a href="{{ route('stock.products.bulk-create') }}" class="block px-4 py-2.5 text-xs text-[#0B0F14] hover:bg-[#F5F7FA]">Ajout groupé</a>
                        @endif
                    </div>
                </div>

                @if($canEditProducts)
                <a href="{{ route('stock.products.bulk-edit') }}"
                   class="flex h-10 items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-100"
                   title="Modifier les photos de plusieurs articles en même temps">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="hidden sm:inline">Photos en masse</span>
                </a>
                @endif

                @if($canCreateProducts)
                <a href="{{ route('stock.products.create') }}"
                   class="flex h-10 items-center gap-1.5 rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline">Nouveau produit</span>
                    <span class="sm:hidden">Produit</span>
                </a>
                @endif
            </div>
        </header>

        {{-- Filtre domaine --}}
        <nav class="no-scrollbar -mx-1 flex items-center gap-1.5 overflow-x-auto px-1">
            <a href="{{ route('stock.index') }}"
               class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ !request('domain_id') ? 'bg-[#0B0F14] text-white' : 'bg-white border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14]' }}">
                Tous
            </a>
            @foreach($domains as $domain)
                <a href="{{ route('stock.index', ['domain_id' => $domain->id]) }}"
                   class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ (string) request('domain_id') === (string) $domain->id ? 'bg-[#0066FF] text-white' : 'bg-white border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14]' }}">
                    {{ strtoupper($domain->name) }}
                </a>
            @endforeach
        </nav>

        {{-- ============ CHIFFRES CLÉS ============ --}}
        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <p class="text-xs text-[#64748B]">Stock physique</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-[#0B0F14]">{{ number_format($totalStockItems, 0, ',', ' ') }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">unités · {{ $totalProductsCount }} articles</p>
            </div>

            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <p class="text-xs text-[#64748B]">Valeur du stock</p>
                <p class="mt-1 break-words font-semibold tabular-nums text-[#0B0F14] {{ $availableStockValue >= 10000000 ? 'text-lg sm:text-2xl' : 'text-2xl' }}">
                    {{ number_format($availableStockValue, 0, ',', ' ') }}
                </p>
                <p class="mt-0.5 text-[11px] text-slate-400">FCFA au prix d'achat</p>
            </div>

            <button type="button" @click="activeTab = 'disponibilite'; filterStatus = 'low_stock'"
                    class="rounded-2xl border border-[#E2E8F0] bg-white p-4 text-left transition hover:border-amber-300 hover:bg-amber-50/40">
                <p class="flex items-center gap-1.5 text-xs text-amber-700"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Stock faible</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-600">{{ $lowStockCount }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Voir les articles →</p>
            </button>

            <button type="button" @click="activeTab = 'disponibilite'; filterStatus = 'out_of_stock'"
                    class="rounded-2xl border border-[#E2E8F0] bg-white p-4 text-left transition hover:border-rose-300 hover:bg-rose-50/40">
                <p class="flex items-center gap-1.5 text-xs text-rose-700"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Ruptures</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-rose-600">{{ $outOfStockCount }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Voir les articles →</p>
            </button>
        </section>

        {{-- ============ ONGLETS ============ --}}
        <nav class="no-scrollbar -mx-1 flex gap-1 overflow-x-auto border-b border-[#E2E8F0] px-1">
            @foreach([
                'synthese' => 'Synthèse',
                'disponibilite' => 'Catalogue',
                'transferts' => 'Transferts',
                'achats' => 'Achats',
                'tracabilite' => 'Traçabilité',
            ] as $key => $label)
                <button type="button" @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'border-[#0066FF] text-[#0B0F14]' : 'border-transparent text-[#64748B] hover:text-[#0B0F14]'"
                        class="shrink-0 border-b-2 px-3 py-2.5 text-sm font-medium transition">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        {{-- ==================== TAB 1 : SYNTHÈSE ==================== --}}
        <div x-show="activeTab === 'synthese'" class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            {{-- À réapprovisionner --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white">
                <header class="flex items-center justify-between border-b border-[#E2E8F0] px-4 py-3">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">À réapprovisionner</h2>
                    @if($replenishmentSuggestions->count() > 5)
                        <button type="button" @click="activeTab = 'disponibilite'; filterStatus = 'low_stock'" class="text-xs font-medium text-[#0066FF] hover:underline">
                            Voir les {{ $replenishmentSuggestions->count() }} →
                        </button>
                    @endif
                </header>
                <ul class="divide-y divide-[#E2E8F0]">
                    @forelse($replenishmentSuggestions->take(5) as $item)
                        <li class="flex items-center gap-3 px-4 py-2.5">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $item->current_stock <= 0 ? 'bg-rose-500' : 'bg-amber-500' }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $item->name }}</p>
                                <p class="text-[11px] text-slate-500">
                                    {{ $item->current_stock <= 0 ? 'Rupture' : 'Reste ' . $item->current_stock }} · min {{ $item->min_stock }}
                                </p>
                            </div>
                            <form action="{{ route('stock.purchase-requests.store') }}" method="POST" class="shrink-0">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <input type="hidden" name="requested_quantity" value="{{ $item->suggested_order_qty }}">
                                <input type="hidden" name="priority" value="{{ $item->current_stock <= 0 ? 'urgente' : 'haute' }}">
                                <input type="hidden" name="reason" value="Suggestion IA : Stock actuel ({{ $item->current_stock }}) sous le seuil min ({{ $item->min_stock }})">
                                <button type="submit" class="rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-xs font-medium text-[#0066FF] hover:border-[#0066FF] hover:bg-blue-50"
                                        title="Créer une demande d'achat">
                                    Commander +{{ $item->suggested_order_qty }}
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-xs text-slate-400">Tous les articles sont au-dessus de leur seuil minimum.</li>
                    @endforelse
                </ul>
            </section>

            {{-- Derniers mouvements --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white">
                <header class="flex items-center justify-between border-b border-[#E2E8F0] px-4 py-3">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Derniers mouvements</h2>
                    <a href="{{ route('stock.movements.index') }}" class="text-xs font-medium text-[#0066FF] hover:underline">Voir tout →</a>
                </header>
                <ul class="divide-y divide-[#E2E8F0]">
                    @forelse($recentMovements->take(6) as $move)
                        @php
                            $isPositive = in_array($move->type, ['entree', 'retour']);
                            $isNegative = $move->type === 'sortie';
                            $typeLabel = match($move->type) {
                                'entree' => 'Entrée', 'retour' => 'Retour', 'sortie' => 'Sortie', 'transfert' => 'Transfert', 'ajustement' => 'Ajustement',
                                default => ucfirst($move->type),
                            };
                        @endphp
                        <li class="flex items-center gap-3 px-4 py-2.5">
                            <span class="w-12 shrink-0 text-right font-mono text-sm font-semibold {{ $isPositive ? 'text-emerald-600' : ($isNegative ? 'text-rose-600' : 'text-[#0066FF]') }}">
                                {{ $isPositive ? '+' : ($isNegative ? '−' : '') }}{{ $move->quantity }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $move->product?->name ?? 'Produit' }}</p>
                                <p class="truncate text-[11px] text-slate-500">
                                    {{ $typeLabel }} · {{ $move->warehouse?->name }}@if($move->destinationWarehouse) → {{ $move->destinationWarehouse->name }}@endif
                                </p>
                            </div>
                            <span class="shrink-0 text-[11px] text-slate-400">{{ $move->created_at?->locale('fr')->diffForHumans(short: true) }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-xs text-slate-400">Aucun mouvement enregistré.</li>
                    @endforelse
                </ul>
            </section>

            {{-- Produits vedettes --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white lg:col-span-2">
                <header class="flex items-center justify-between border-b border-[#E2E8F0] px-4 py-3">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Produits vedettes</h2>
                    <span class="text-[11px] text-slate-400">Les plus vendus</span>
                </header>
                <ul class="divide-y divide-[#E2E8F0]">
                    @forelse(($topSellersAnalyzed ?? collect())->take(5) as $item)
                        @php $p = $item['product']; $critical = in_array($item['risk_level'], ['rupture', 'critique']); @endphp
                        <li class="flex flex-col gap-2 px-4 py-2.5 sm:flex-row sm:items-center sm:gap-3">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="w-5 shrink-0 text-center text-xs font-semibold text-slate-400">{{ $item['rank'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $p->name }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $item['exit_volume'] }} vendus · {{ number_format($item['revenue_generated'], 0, ',', ' ') }} F
                                        · reste {{ $p->current_stock }}
                                        @if($critical)<span class="font-semibold text-rose-600"> · {{ $item['risk_label'] }}</span>@endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-2 pl-8 sm:pl-0">
                                <form action="{{ route('stock.revenue-booster.secure-stock') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                    <input type="hidden" name="requested_quantity" value="{{ $item['anti_stockout_lever']['recommended_reorder_qty'] }}">
                                    <input type="hidden" name="reason" value="Bouclier Anti-Rupture : Produit vedette {{ $p->name }} pour sécuriser {{ number_format($item['anti_stockout_lever']['secured_revenue_forecast'], 0, ',', ' ') }} FCFA de CA.">
                                    <button type="submit" class="rounded-lg border px-2.5 py-1.5 text-xs font-medium {{ $critical ? 'border-rose-200 text-rose-700 hover:bg-rose-50' : 'border-[#E2E8F0] text-[#0066FF] hover:border-[#0066FF] hover:bg-blue-50' }}"
                                            title="Demande d'achat prioritaire pour éviter la rupture">
                                        Réappro +{{ $item['anti_stockout_lever']['recommended_reorder_qty'] }}
                                    </button>
                                </form>
                                <form action="{{ route('stock.revenue-booster.optimize-price') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                    <input type="hidden" name="selling_price" value="{{ $item['pricing_lever']['opt_price_5'] }}">
                                    <input type="hidden" name="reason" value="Optimisation tarifaire recommandée IA (+5% sur produit vedette)">
                                    <button type="submit" class="rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:border-slate-400 hover:bg-slate-50"
                                            title="Passer le prix de {{ number_format($item['selling_price'], 0, ',', ' ') }} F à {{ number_format($item['pricing_lever']['opt_price_5'], 0, ',', ' ') }} F">
                                        Prix +5 %
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-xs text-slate-400">Pas encore assez de ventes pour identifier les produits vedettes.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- ==================== TAB 2 : CATALOGUE ==================== --}}
        <div x-show="activeTab === 'disponibilite'" x-cloak class="space-y-5">

            {{-- Catalogue --}}
            <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                <div class="space-y-3 border-b border-[#E2E8F0] p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Catalogue produits</h2>
                            <p class="text-[11px] text-[#64748B]">Disponible = Physique − Réservé</p>
                        </div>
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = !open" class="flex items-center gap-1.5 rounded-lg border border-[#E2E8F0] px-3 py-1.5 text-xs font-medium text-[#0B0F14] hover:bg-slate-50">
                                Imprimer / exporter
                                <svg class="h-3.5 w-3.5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-xl border border-[#E2E8F0] bg-white py-1 text-xs shadow-lg">
                                @if($canPrintCatalog)
                                <a href="{{ route('stock.products.print-catalog', request()->except('warehouse_id')) }}"
                                   :href="filterWarehouse === '' ? $el.dataset.base : $el.dataset.base + ($el.dataset.base.includes('?') ? '&' : '?') + 'warehouse_id=' + filterWarehouse"
                                   data-base="{{ route('stock.products.print-catalog', request()->except('warehouse_id')) }}" target="_blank" class="block px-4 py-2.5 text-[#0B0F14] hover:bg-[#F5F7FA]">Aperçu du catalogue (A4)</a>
                                <a href="{{ route('stock.products.download-catalog', request()->except('warehouse_id')) }}"
                                   :href="filterWarehouse === '' ? $el.dataset.base : $el.dataset.base + ($el.dataset.base.includes('?') ? '&' : '?') + 'warehouse_id=' + filterWarehouse"
                                   data-base="{{ route('stock.products.download-catalog', request()->except('warehouse_id')) }}" class="block px-4 py-2.5 font-semibold text-[#0B0F14] hover:bg-[#F5F7FA]">Télécharger le catalogue (PDF)</a>
                                <a href="{{ route('stock.products.download-catalog', array_merge(request()->except('warehouse_id'), ['per_warehouse' => 1])) }}" class="block px-4 py-2.5 text-[#0B0F14] hover:bg-[#F5F7FA]">Télécharger par entrepôt (PDF)</a>
                                @endif
                                <a href="{{ route('stock.export') }}" class="block px-4 py-2.5 text-[#0B0F14] hover:bg-[#F5F7FA]">Export CSV</a>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="search" placeholder="Rechercher nom, SKU, code-barres…"
                                   class="w-full rounded-xl border border-[#E2E8F0] bg-white py-2 pl-9 pr-8 text-xs focus:border-[#0066FF] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                            <button x-show="search.length > 0" @click="search = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">✕</button>
                        </div>
                        <div class="no-scrollbar flex items-center gap-1.5 overflow-x-auto text-[11px]">
                            <button type="button" @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-[#0B0F14] text-white' : 'bg-[#F5F7FA] text-slate-600'" class="whitespace-nowrap rounded-lg px-2.5 py-1.5 font-medium">Tous ({{ $products->count() }})</button>
                            <button type="button" @click="filterStatus = 'in_stock'" :class="filterStatus === 'in_stock' ? 'bg-[#0066FF] text-white' : 'bg-[#F5F7FA] text-slate-600'" class="whitespace-nowrap rounded-lg px-2.5 py-1.5 font-medium">En stock</button>
                            <button type="button" @click="filterStatus = 'low_stock'" :class="filterStatus === 'low_stock' ? 'bg-amber-600 text-white' : 'bg-[#F5F7FA] text-slate-600'" class="whitespace-nowrap rounded-lg px-2.5 py-1.5 font-medium">Faible ({{ $lowStockCount }})</button>
                            <button type="button" @click="filterStatus = 'out_of_stock'" :class="filterStatus === 'out_of_stock' ? 'bg-rose-600 text-white' : 'bg-[#F5F7FA] text-slate-600'" class="whitespace-nowrap rounded-lg px-2.5 py-1.5 font-medium">Rupture ({{ $outOfStockCount }})</button>
                        </div>
                    </div>

                    {{-- Filtre par entrepôt --}}
                    @if($warehouses->isNotEmpty())
                        <div class="flex items-start gap-2">
                            <a href="{{ route('stock.warehouses.index') }}" class="hidden shrink-0 py-1.5 text-[11px] font-medium text-[#64748B] hover:text-[#0066FF] sm:inline" title="Gérer les entrepôts">Entrepôt</a>
                            <div class="no-scrollbar -mx-4 flex items-center gap-1.5 overflow-x-auto px-4 text-[11px] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrer par entrepôt">
                                <button type="button" @click="filterWarehouse = ''" :aria-pressed="filterWarehouse === ''"
                                        :class="filterWarehouse === '' ? 'bg-[#0B0F14] text-white' : 'bg-[#F5F7FA] text-slate-600 hover:text-[#0B0F14]'"
                                        class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-1.5 font-medium">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg>
                                    Tous les entrepôts
                                </button>
                                @foreach($warehouses as $wh)
                                    <button type="button" @click="filterWarehouse = '{{ $wh->id }}'" :aria-pressed="filterWarehouse === '{{ $wh->id }}'"
                                            :class="filterWarehouse === '{{ $wh->id }}' ? 'bg-[#0066FF] text-white' : 'bg-[#F5F7FA] text-slate-600 hover:text-[#0B0F14]'"
                                            class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-1.5 font-medium" title="{{ $wh->name }}">
                                        <x-warehouse-label :warehouse="$wh" size="xs" />
                                        {{ $wh->name }}
                                        <span class="rounded-full bg-black/10 px-1.5 text-[10px] font-semibold">{{ $wh->warehouseStocks->where('physical_quantity', '>', 0)->count() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sélection (mobile) --}}
                @if($canSelectProducts)
                <div class="flex items-center justify-between border-b border-[#E2E8F0] px-4 py-2 md:hidden">
                    <label class="flex items-center gap-2 text-xs font-medium text-[#0B0F14]">
                        <input type="checkbox" @change="toggleSelectAll()" :checked="isAllSelected" class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                        Tout sélectionner
                    </label>
                    <span class="text-[11px] text-[#64748B]">puis « Prix &amp; photos »</span>
                </div>
                @endif

                {{-- Barre de sélection --}}
                <div x-show="selectedProductIds.length > 0" x-cloak
                     class="flex flex-col gap-2 bg-[#0B0F14] px-4 py-3 text-white sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs"><strong x-text="selectedProductIds.length"></strong> article(s) sélectionné(s)</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="selectedProductIds = []" class="rounded-lg px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800 hover:text-white">Désélectionner</button>
                        @if($canEditProducts)
                        {{-- GET : la page peut être rechargée (Android recharge l'onglet au retour de l'appareil photo) --}}
                        <form action="{{ route('stock.products.bulk-edit') }}" method="GET" class="inline">
                            {{-- Tout le catalogue sélectionné : aucune liste (une URL de plusieurs milliers d'identifiants peut être refusée) --}}
                            <input type="hidden" name="product_ids" :value="selectedProductIds.join(',')" :disabled="isAllSelected">
                            <button type="submit" class="rounded-lg bg-[#0066FF] px-3 py-1.5 text-xs font-semibold hover:bg-[#0052cc]">Prix &amp; photos</button>
                        </form>
                        @endif
                        @if($canDeleteProducts)
                        <button type="button" @click="$dispatch('open-bulk-delete-products', { product_ids: selectedProductIds })" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold hover:bg-rose-700">Supprimer</button>
                        @endif
                    </div>
                </div>

                {{-- En-tête de colonnes (desktop) --}}
                <div class="hidden items-center gap-3 border-b border-[#E2E8F0] bg-[#F5F7FA] px-4 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-[#64748B] md:grid md:grid-cols-12">
                    <div class="col-span-6 flex items-center gap-3">
                        @if($canSelectProducts)
                        <input type="checkbox" @change="toggleSelectAll()" :checked="isAllSelected" class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]" title="Tout sélectionner">
                        @endif
                        <span>Article · stock par entrepôt</span>
                    </div>
                    <div class="col-span-2 text-right">Disponible</div>
                    <div class="col-span-2 text-right">Prix de vente</div>
                    <div class="col-span-2 text-right">Actions</div>
                </div>

                {{-- Aucun produit dans l'entrepôt filtré --}}
                <div x-show="filterWarehouse !== '' && !warehouseProductCounts[filterWarehouse]" x-cloak class="px-4 py-10 text-center">
                    <p class="text-sm font-medium text-[#0B0F14]">Aucun produit en stock dans cet entrepôt</p>
                    <button type="button" @click="filterWarehouse = ''" class="mt-2 text-xs font-medium text-[#0066FF] hover:underline">Voir tous les entrepôts</button>
                </div>

                {{-- Liste produits (responsive : cartes mobile / lignes desktop) --}}
                <div class="divide-y divide-[#E2E8F0]">
                    @forelse($products as $product)
                        @php
                            $avail = (int) $product->available_stock;
                            $minVal = max(1, (int) $product->min_stock);
                            $searchString = strtolower($product->name . ' ' . $product->sku . ' ' . ($product->barcode ?? '') . ' ' . ($product->ean ?? '') . ' ' . ($product->brand?->name ?? ''));
                            $photoPayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'current_image' => $product->image ? asset('storage/' . $product->image) : '',
                                'action_url' => route('stock.products.quick-image', $product),
                            ];
                            $adjustPayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'unit' => $product->unit ?? 'pièce',
                                'current_stock' => $product->current_stock,
                                'stocks' => $product->warehouseStocks->map(fn($ws) => [
                                    'warehouse_id' => $ws->warehouse_id,
                                    'warehouse_name' => $ws->warehouse?->name ?? 'Entrepôt',
                                    'warehouse_code' => $ws->warehouse?->code ?? '',
                                    'physical_quantity' => $ws->physical_quantity,
                                ])->values(),
                            ];
                            $qrPayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'barcode' => $product->ean,
                                'price' => number_format((float) $product->selling_price, 0, ',', ' ') . ' FCFA',
                                'domain' => $product->domain?->name ?? 'Général',
                                'unit' => $product->unit ?? 'pièce',
                                'qrDownloadUrl' => route('stock.products.qr-download', $product),
                                'labelDownloadUrl' => route('stock.products.qr-download', ['product' => $product, 'label' => 1]),
                            ];
                            $deletePayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'current_stock' => $product->current_stock,
                            ];
                        @endphp

                        <div x-show="matches({{ Js::from($searchString) }}, {{ $avail }}, {{ $minVal }}, {{ Js::from($product->warehouseStocks->where('physical_quantity', '>', 0)->pluck('warehouse_id')->values()) }})"
                             :class="{ 'bg-blue-50/40': selectedProductIds.includes({{ $product->id }}) }"
                             class="px-4 py-3 transition-colors hover:bg-slate-50/60 md:grid md:grid-cols-12 md:items-center md:gap-3">

                            {{-- Produit --}}
                            <div class="flex items-center gap-3 md:col-span-6">
                                @if($canSelectProducts)
                                <input type="checkbox" :value="{{ $product->id }}" x-model.number="selectedProductIds"
                                       class="shrink-0 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">
                                @endif

                                <button type="button" @if($canEditProducts) @click="$dispatch('open-product-photo', {{ Js::from($photoPayload) }})" title="Changer la photo" @else disabled @endif
                                        class="relative h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-[#E2E8F0] {{ $canEditProducts ? 'hover:ring-2 hover:ring-[#0066FF]' : 'cursor-default' }}">
                                    @if($product->image)
                                        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-[#0B0F14]">{{ $product->name }}</p>
                                    <p class="truncate text-[11px] text-slate-500">
                                        <span class="font-mono font-semibold text-[#0066FF]">{{ $product->sku }}</span> · {{ $product->domain?->name ?? 'Général' }}
                                    </p>
                                    @if($product->warehouseStocks->where('physical_quantity', '>', 0)->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($product->warehouseStocks->where('physical_quantity', '>', 0) as $ws)
                                                <span class="inline-flex items-center gap-1 rounded-md py-px pl-px pr-1.5 text-[10px] font-semibold transition-colors" title="{{ $ws->warehouse?->name }}"
                                                      :class="filterWarehouse === '{{ $ws->warehouse_id }}' ? 'bg-[#0066FF] text-white' : 'bg-slate-100 text-slate-700'">
                                                    <x-warehouse-label :warehouse="$ws->warehouse" size="xs" />{{ $ws->physical_quantity }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Disponible --}}
                            <div class="mt-2 flex items-center justify-between gap-3 pl-[4.25rem] md:col-span-2 md:mt-0 md:block md:pl-0 md:text-right">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $avail <= 0 ? 'bg-rose-50 text-rose-700' : ($avail <= $minVal ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800') }}">
                                    {{ $avail <= 0 ? 'Rupture' : $avail . ' dispo' }}
                                </span>
                                @if($product->reserved_stock > 0)
                                    <span class="text-[10px] text-amber-700 md:mt-0.5 md:block">{{ $product->reserved_stock }} réservé(s)</span>
                                @endif
                                <span class="text-sm font-semibold text-[#0B0F14] md:hidden">{{ number_format((float) $product->selling_price, 0, ',', ' ') }} F</span>
                            </div>

                            {{-- Prix --}}
                            <div class="hidden md:col-span-2 md:block md:text-right">
                                <span class="text-sm font-semibold text-[#0B0F14]">{{ number_format((float) $product->selling_price, 0, ',', ' ') }} F</span>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-2 flex items-center gap-4 pl-[4.25rem] text-xs font-medium md:col-span-2 md:mt-0 md:justify-end md:gap-1.5 md:pl-0">
                                @if($canAdjustProducts)
                                <button type="button" @click="$dispatch('open-stock-adjust', {{ Js::from($adjustPayload) }})"
                                        class="text-emerald-600 hover:underline md:rounded-lg md:p-1.5 md:hover:bg-emerald-50 md:hover:no-underline" title="Ajuster le stock">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    <span class="md:hidden">Ajuster</span>
                                </button>
                                @endif
                                <button type="button" @click="$dispatch('open-product-qr', {{ Js::from($qrPayload) }})"
                                        class="text-[#0066FF] hover:underline md:rounded-lg md:p-1.5 md:hover:bg-blue-50 md:hover:no-underline" title="QR Code & EAN">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    <span class="md:hidden">QR</span>
                                </button>
                                @if($canEditProducts)
                                <a href="{{ route('stock.products.edit', $product) }}"
                                   class="text-slate-600 hover:underline md:rounded-lg md:p-1.5 md:hover:bg-slate-100 md:hover:no-underline" title="Modifier la fiche">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span class="md:hidden">Fiche</span>
                                </a>
                                @endif
                                @if($canDeleteProducts)
                                <button type="button" @click="$dispatch('open-delete-product', {{ Js::from($deletePayload) }})"
                                        class="text-rose-600 hover:underline md:rounded-lg md:p-1.5 md:hover:bg-rose-50 md:hover:no-underline" title="Supprimer">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="md:hidden">Suppr.</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-xs text-slate-400">Aucun produit répertorié.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ==================== TAB 3 : TRANSFERTS & INVENTAIRES ==================== --}}
        <div x-show="activeTab === 'transferts'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Transferts --}}
            <div class="space-y-6">
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Nouveau transfert</h2>
                    <p class="mt-0.5 text-xs text-[#64748B]">Déplacer du stock entre deux entrepôts.</p>

                    <form action="{{ route('stock.transfers.store') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Source</label>
                                <select name="source_warehouse_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Destination</label>
                                <select name="destination_warehouse_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                    @foreach($warehouses->reverse() as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-700">Produit</label>
                                <select name="product_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (Dispo: {{ $p->current_stock }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Quantité</label>
                                <input type="number" name="quantity" min="1" value="5" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700">Motif / Observation</label>
                            <input type="text" name="notes" placeholder="Ex: Réapprovisionnement Boutique PRINT" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>

                        <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="execute_immediately" value="1" checked class="rounded border-slate-300 text-[#0066FF]">
                                Exécuter & réceptionner immédiatement
                            </label>
                            <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0052cc]">Valider le transfert</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <h3 class="text-sm font-semibold text-[#0B0F14]">Transferts récents</h3>
                    <ul class="mt-3 divide-y divide-[#E2E8F0]">
                        @forelse($transfers as $trf)
                            <li class="flex items-center justify-between gap-3 py-3 text-xs">
                                <div class="min-w-0">
                                    <p class="truncate"><strong class="text-[#0B0F14]">{{ $trf->reference }}</strong> · {{ $trf->product?->name }} (x{{ $trf->quantity }})</p>
                                    <p class="truncate text-[11px] text-slate-400">{{ $trf->sourceWarehouse?->name }} → {{ $trf->destinationWarehouse?->name }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-700">{{ $trf->status }}</span>
                                    @if($trf->status !== 'receptionne')
                                        <form action="{{ route('stock.transfers.status', $trf) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="receptionne">
                                            <button type="submit" class="rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[11px] font-semibold text-white">Réceptionner</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="py-4 text-xs text-slate-400">Aucun transfert enregistré.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            {{-- Inventaire --}}
            <div class="space-y-6">
                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-[#0B0F14]">Inventaire physique</h2>
                    <p class="mt-0.5 text-xs text-[#64748B]">Comparez stock système et stock compté : l'écart est régularisé automatiquement.</p>

                    <form action="{{ route('stock.inventories.store') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Entrepôt contrôlé</label>
                                <select name="warehouse_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Quantité réelle comptée</label>
                                <input type="number" name="counted_quantity" min="0" value="45" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700">Produit contrôlé</label>
                            <select name="product_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (Système: {{ $p->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700">Justification de l'écart</label>
                            <input type="text" name="reason" placeholder="Ex: Comptage mensuel - écart constaté en rayon" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>

                        <input type="hidden" name="auto_validate" value="1">

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="rounded-xl bg-[#0B0F14] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0066FF]">Valider & ajuster le stock</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <h3 class="text-sm font-semibold text-[#0B0F14]">Procès-verbaux d'inventaire</h3>
                    <div class="mt-3 divide-y divide-[#E2E8F0]">
                        @forelse($inventories as $inv)
                            <div class="py-3 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate font-semibold text-[#0B0F14]">{{ $inv->reference }} — {{ $inv->warehouse?->name }}</span>
                                    <span class="shrink-0 rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-emerald-700">{{ $inv->status }}</span>
                                </div>
                                @foreach($inv->items as $item)
                                    <div class="mt-1.5 flex flex-col gap-0.5 rounded-lg bg-[#F5F7FA] px-3 py-1.5 text-[11px] sm:flex-row sm:items-center sm:justify-between">
                                        <span class="font-medium text-slate-700">{{ $item->product?->name }}</span>
                                        <span class="text-slate-600">
                                            Système <strong>{{ $item->system_quantity }}</strong> ·
                                            Compté <strong>{{ $item->real_quantity }}</strong> ·
                                            Écart
                                            <strong class="{{ $item->discrepancy < 0 ? 'text-rose-600' : ($item->discrepancy > 0 ? 'text-emerald-600' : 'text-slate-600') }}">
                                                {{ $item->discrepancy > 0 ? '+'.$item->discrepancy : $item->discrepancy }}
                                            </strong>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="py-4 text-xs text-slate-400">Aucun inventaire physique enregistré.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        {{-- ==================== TAB 4 : ACHATS & RÉCEPTIONS ==================== --}}
        <div x-show="activeTab === 'achats'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-[#0B0F14]">Demandes d'achat</h2>
                <p class="mt-0.5 text-xs text-[#64748B]">Employé → Responsable → Finance → Commande fournisseur</p>

                <ul class="mt-3 divide-y divide-[#E2E8F0]">
                    @forelse($purchaseRequests as $pr)
                        @php
                            $nextStatus = match($pr->status) {
                                'en_attente_responsable', 'soumis' => 'en_attente_finance',
                                'en_attente_finance', 'valide_responsable' => 'approuve_achat',
                                'approuve_achat', 'valide_finance' => 'commande_passee',
                                'commande_passee', 'commande_fournisseur' => 'receptionne',
                                default => null,
                            };
                        @endphp
                        <li class="py-3 text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <p class="min-w-0">
                                    <strong class="text-[#0B0F14]">{{ $pr->reference }}</strong>
                                    <span class="text-slate-700">· {{ $pr->product?->name }}</span>
                                    <strong class="text-[#0066FF]">(x{{ $pr->quantity }})</strong>
                                </p>
                                <span class="shrink-0 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-[#0066FF]">{{ str_replace('_', ' ', $pr->status) }}</span>
                            </div>
                            <div class="mt-1.5 flex flex-col gap-2 text-[11px] text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                                <span>{{ $pr->supplier?->name ?? 'Fournisseur à définir' }} · {{ number_format($pr->quantity * (float) $pr->estimated_unit_price, 0, ',', ' ') }} FCFA</span>
                                @if($nextStatus)
                                    <form action="{{ route('stock.purchase-requests.status', $pr) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                                        <button type="submit" class="rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-[#0066FF]">
                                            Valider → {{ str_replace('_', ' ', $nextStatus) }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400">Aucune demande d'achat en cours.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-[#0B0F14]">Réception fournisseur</h2>
                <p class="mt-0.5 text-xs text-[#64748B]">Contrôle qualité, entrée en stock et numéro de lot.</p>

                <form action="{{ route('stock.receptions.store') }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Entrepôt</label>
                            <select name="warehouse_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Fournisseur</label>
                            <select name="supplier_id" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Produit réceptionné</label>
                        <select name="product_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Commandée</label>
                            <input type="number" name="ordered_quantity" min="1" value="50" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Reçue</label>
                            <input type="number" name="received_quantity" min="0" value="50" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Endommagée</label>
                            <input type="number" name="damaged_quantity" min="0" value="0" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Contrôle qualité</label>
                            <select name="quality_status" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                <option value="conforme">Conforme (100%)</option>
                                <option value="partiel">Réception partielle</option>
                                <option value="non_conforme">Non conforme</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">N° de lot</label>
                            <input type="text" name="batch_number" placeholder="LOT-2026-X" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Expiration</label>
                            <input type="date" name="expiration_date" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0052cc]">Valider & entrer en stock</button>
                    </div>
                </form>
            </section>
        </div>

        {{-- ==================== TAB 5 : TRAÇABILITÉ & MAINTENANCE ==================== --}}
        <div x-show="activeTab === 'tracabilite'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-[#0B0F14]">Lots, séries / IMEI & expirations</h2>
                <ul class="mt-3 divide-y divide-[#E2E8F0]">
                    @forelse($batches as $batch)
                        <li class="flex items-start justify-between gap-3 py-3 text-xs">
                            <div class="min-w-0">
                                <p class="font-semibold text-[#0B0F14]">
                                    {{ $batch->product?->name }}
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-700">Lot {{ $batch->batch_number }}</span>
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-500">
                                    @if($batch->serial_number) Série <strong>{{ $batch->serial_number }}</strong> · @endif
                                    @if($batch->imei) IMEI <strong>{{ $batch->imei }}</strong> · @endif
                                    Garantie {{ $batch->warranty_months }} mois
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-700">{{ str_replace('_', ' ', $batch->status) }}</span>
                                @if($batch->expiration_date)
                                    @php $daysLeft = (int) now()->diffInDays($batch->expiration_date, false); @endphp
                                    <p class="mt-1 text-[11px] font-medium {{ $daysLeft <= 30 ? 'text-rose-600' : ($daysLeft <= 90 ? 'text-amber-600' : 'text-slate-500') }}">
                                        Exp. {{ $batch->expiration_date->format('d/m/Y') }} ({{ $daysLeft }}j)
                                    </p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400">Aucun lot ou numéro de série enregistré.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                <h2 class="text-sm font-semibold text-[#0B0F14]">Maintenance du parc</h2>
                <p class="mt-0.5 text-xs text-[#64748B]">Réparations, calibrations et remise en disponibilité.</p>

                <form action="{{ route('stock.maintenances.store') }}" method="POST" class="mt-4 space-y-3 border-b border-[#E2E8F0] pb-5">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Équipement</label>
                            <select name="product_id" required class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Intervention</label>
                            <select name="type" class="mt-1 w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                                <option value="preventive">Maintenance préventive</option>
                                <option value="reparation">Réparation panne</option>
                                <option value="calibration">Calibration optique / audio</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input type="text" name="serial_number" placeholder="N° de série (ex: SN-SONY-884920)" class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                        <input type="text" name="issue_description" required placeholder="Diagnostic / motif d'immobilisation" class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-[#0B0F14] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0066FF]">Placer en maintenance</button>
                    </div>
                </form>

                <ul class="mt-3 divide-y divide-[#E2E8F0]">
                    @forelse($maintenances as $m)
                        <li class="flex items-center justify-between gap-3 py-3 text-xs">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-[#0B0F14]">{{ $m->reference }} — {{ $m->product?->name }}</p>
                                <p class="truncate text-[11px] text-slate-500">{{ $m->issue_description }} · {{ $m->technician_name }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase {{ $m->status === 'termine' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ str_replace('_', ' ', $m->status) }}</span>
                                @if($m->status !== 'termine')
                                    <form action="{{ route('stock.maintenances.complete', $m) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg bg-[#0066FF] px-2.5 py-1 text-[10px] font-semibold text-white">Remettre dispo</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400">Aucune maintenance en cours.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>

    {{-- Modales --}}
    <x-stock-adjust-modal :warehouses="$warehouses" />
    <x-delete-warehouse-modal :warehouses="$warehouses" />
    <x-product-qr-modal />
    <x-product-quick-photo-modal />
    <x-delete-product-modal />
</x-layouts.app>