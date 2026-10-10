<x-layouts.app title="Gestion des Stocks & Entrepôts (WMS)">
    <div x-data="{
        activeTab: '{{ request('tab', 'synthese') }}',
        showMoreKpis: false,
        showActions: false,
        search: '',
        filterStatus: 'all',
        selectedProductIds: [],
        allProductIds: {{ Js::from($products->pluck('id')->values()) }},
        get isAllSelected() {
            return this.allProductIds.length > 0 && this.selectedProductIds.length === this.allProductIds.length;
        },
        toggleSelectAll() {
            this.selectedProductIds = this.isAllSelected ? [] : [...this.allProductIds];
        },
        matches(text, avail, min) {
            const okSearch = this.search === '' || text.includes(this.search.toLowerCase());
            const okStatus = this.filterStatus === 'all'
                || (this.filterStatus === 'in_stock' && avail > min)
                || (this.filterStatus === 'low_stock' && avail > 0 && avail <= min)
                || (this.filterStatus === 'out_of_stock' && avail <= 0);
            return okSearch && okStatus;
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
                <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">
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
                        <a href="{{ route('stock.products.bulk-create') }}" class="block px-4 py-2.5 text-xs text-[#0B0F14] hover:bg-[#F5F7FA]">Ajout groupé</a>
                    </div>
                </div>

                <a href="{{ route('stock.products.create') }}"
                   class="flex h-10 items-center gap-1.5 rounded-xl bg-[#0066FF] px-4 text-xs font-semibold text-white hover:bg-[#0052cc]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline">Nouveau produit</span>
                    <span class="sm:hidden">Produit</span>
                </a>
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

        {{-- ============ KPI ============ --}}
        <section class="space-y-3">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Stock physique</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-[#0B0F14]">
                        {{ number_format($totalStockItems, 0, ',', ' ') }}
                        <span class="text-xs font-normal text-slate-400">unités</span>
                    </p>
                    <p class="mt-1 text-[11px] text-[#64748B]">{{ $totalProductsCount }} références</p>
                </div>

                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Valeur (achat)</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-[#0B0F14]">
                        @if($availableStockValue >= 1000000)
                            {{ number_format($availableStockValue / 1000000, 1, ',', ' ') }}M
                        @else
                            {{ number_format($availableStockValue, 0, ',', ' ') }}
                        @endif
                        <span class="text-xs font-normal text-slate-400">FCFA</span>
                    </p>
                    <p class="mt-1 text-[11px] text-[#64748B]">
                        Vente :
                        @if($potentialSellingValue >= 1000000)
                            {{ number_format($potentialSellingValue / 1000000, 1, ',', ' ') }}M FCFA
                        @else
                            {{ number_format($potentialSellingValue, 0, ',', ' ') }} FCFA
                        @endif
                    </p>
                </div>

                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Stock faible
                    </p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-amber-600">{{ $lowStockCount }}</p>
                    <p class="mt-1 text-[11px] text-[#64748B]">Sous le seuil d'alerte</p>
                </div>

                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-rose-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Ruptures
                    </p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-rose-600">{{ $outOfStockCount }}</p>
                    <p class="mt-1 text-[11px] text-[#64748B]">Articles épuisés</p>
                </div>
            </div>

            <button type="button" @click="showMoreKpis = !showMoreKpis"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">
                <span x-text="showMoreKpis ? 'Masquer les flux' : 'Voir les flux (entrées, sorties, surstock)'"></span>
                <svg class="h-3.5 w-3.5 transition-transform" :class="{ 'rotate-180': showMoreKpis }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="showMoreKpis" x-cloak class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Entrées</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums">{{ $entriesCount }}</p>
                </div>
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Sorties</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums">{{ $exitsCount }}</p>
                </div>
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Transferts</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums">{{ $transfersCount }}</p>
                </div>
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Immobilisation</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($immobilizedCostValue, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p>
                    <p class="text-[11px] text-[#64748B]">Surstock : {{ $overstockCount }}</p>
                </div>
            </div>
        </section>

        {{-- ============ ONGLETS ============ --}}
        <nav class="no-scrollbar -mx-1 flex gap-1 overflow-x-auto border-b border-[#E2E8F0] px-1">
            @foreach([
                'synthese' => ['Synthèse', 'Synthèse'],
                'disponibilite' => ['Catalogue', 'Catalogue & Disponibilité'],
                'transferts' => ['Transferts', 'Transferts & Inventaires'],
                'achats' => ['Achats', "Achats & Réceptions"],
                'tracabilite' => ['Traçabilité', 'Lots & Maintenance'],
            ] as $key => [$short, $long])
                <button type="button" @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'border-[#0066FF] text-[#0B0F14]' : 'border-transparent text-[#64748B] hover:text-[#0B0F14]'"
                        class="shrink-0 border-b-2 px-3 py-2.5 text-xs font-medium transition">
                    <span class="sm:hidden">{{ $short }}</span>
                    <span class="hidden sm:inline">{{ $long }}</span>
                </button>
            @endforeach
        </nav>

        {{-- ==================== TAB 1 : SYNTHÈSE ==================== --}}
        <div x-show="activeTab === 'synthese'" class="space-y-6">

            {{-- Booster de revenus --}}
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Booster de revenus · Produits vedettes</h2>
                        <p class="mt-0.5 text-xs text-[#64748B]">Prix, packs synergie et protection anti-rupture sur vos top ventes.</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                        <div class="rounded-xl bg-[#F5F7FA] px-3 py-1.5">
                            <dt class="text-[10px] font-medium uppercase tracking-wider text-[#64748B]">CA top ventes</dt>
                            <dd class="text-xs font-semibold text-[#0B0F14]">{{ number_format($revenueMetrics['total_top_revenue'] ?? 0, 0, ',', ' ') }} F</dd>
                        </div>
                        <div class="rounded-xl bg-emerald-50 px-3 py-1.5">
                            <dt class="text-[10px] font-medium uppercase tracking-wider text-emerald-700">Gain prix potentiel</dt>
                            <dd class="text-xs font-semibold text-emerald-800">+{{ number_format($revenueMetrics['total_potential_gain_price'] ?? 0, 0, ',', ' ') }} F/m</dd>
                        </div>
                        <div class="rounded-xl bg-amber-50 px-3 py-1.5">
                            <dt class="text-[10px] font-medium uppercase tracking-wider text-amber-800">Cash dormant</dt>
                            <dd class="text-xs font-semibold text-amber-900">{{ number_format($revenueMetrics['total_dormant_cash_unlockable'] ?? 0, 0, ',', ' ') }} F</dd>
                        </div>
                        @if(($revenueMetrics['at_risk_turnover'] ?? 0) > 0)
                            <div class="rounded-xl bg-rose-50 px-3 py-1.5">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-rose-700">Risque rupture CA</dt>
                                <dd class="text-xs font-semibold text-rose-800">{{ number_format($revenueMetrics['at_risk_turnover'], 0, ',', ' ') }} F</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="mt-4">
                    @if(isset($topSellersAnalyzed) && $topSellersAnalyzed->isNotEmpty())
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($topSellersAnalyzed as $item)
                                @php $p = $item['product']; $critical = in_array($item['risk_level'], ['rupture', 'critique']); @endphp
                                <article x-data="{ lever: 'price' }" class="flex flex-col rounded-xl border border-[#E2E8F0] p-3.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-semibold text-[#0B0F14]" title="{{ $p->name }}">
                                                <span class="text-[#64748B]">#{{ $item['rank'] }}</span> {{ $p->name }}
                                            </h3>
                                            <p class="truncate text-[11px] text-[#64748B]">{{ $p->domain?->name ?? 'Général' }} · {{ $p->sku }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-md border px-2 py-0.5 text-[10px] font-semibold {{ $item['risk_badge_class'] }}">{{ $item['risk_label'] }}</span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-lg bg-[#F5F7FA] p-2">
                                            <span class="block text-[10px] uppercase tracking-wider text-[#64748B]">Volume & CA</span>
                                            <span class="font-semibold">{{ $item['exit_volume'] }} sorties</span>
                                            <span class="block text-[10px] text-[#64748B]">{{ number_format($item['revenue_generated'], 0, ',', ' ') }} F</span>
                                        </div>
                                        <div class="rounded-lg bg-[#F5F7FA] p-2">
                                            <span class="block text-[10px] uppercase tracking-wider text-[#64748B]">Prix & marge</span>
                                            <span class="font-semibold">{{ number_format($item['selling_price'], 0, ',', ' ') }} F</span>
                                            <span class="block text-[10px] font-medium text-emerald-700">{{ $item['margin_percent'] }}% ({{ number_format($item['margin_unit'], 0, ',', ' ') }} F)</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex gap-1 rounded-lg bg-[#F5F7FA] p-1 text-[11px]">
                                        <button type="button" @click="lever = 'price'" :class="lever === 'price' ? 'bg-white text-[#0066FF] font-semibold shadow-sm' : 'text-[#64748B]'" class="flex-1 rounded-md py-1 transition">Prix</button>
                                        <button type="button" @click="lever = 'bundle'" :class="lever === 'bundle' ? 'bg-white text-[#0066FF] font-semibold shadow-sm' : 'text-[#64748B]'" class="flex-1 rounded-md py-1 transition">Pack</button>
                                        <button type="button" @click="lever = 'security'" :class="lever === 'security' ? 'bg-white text-[#0066FF] font-semibold shadow-sm' : 'text-[#64748B]'" class="flex-1 rounded-md py-1 transition">Anti-rupture</button>
                                    </div>

                                    {{-- Levier prix --}}
                                    <div x-show="lever === 'price'" class="mt-3 space-y-2">
                                        <p class="text-[11px] text-[#64748B]">
                                            <strong class="text-[#0B0F14]">+5% → {{ number_format($item['pricing_lever']['opt_price_5'], 0, ',', ' ') }} FCFA</strong><br>
                                            Gain estimé +{{ number_format($item['pricing_lever']['projected_monthly_gain_5'], 0, ',', ' ') }} F/mois · marge {{ $item['pricing_lever']['new_margin_percent_5'] }}%
                                        </p>
                                        <form action="{{ route('stock.revenue-booster.optimize-price') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $p->id }}">
                                            <input type="hidden" name="selling_price" value="{{ $item['pricing_lever']['opt_price_5'] }}">
                                            <input type="hidden" name="reason" value="Optimisation tarifaire recommandée IA (+5% sur produit vedette)">
                                            <button type="submit" class="w-full rounded-lg bg-[#0066FF] py-2 text-xs font-semibold text-white hover:bg-[#0052cc]">Appliquer le prix (+5%)</button>
                                        </form>
                                    </div>

                                    {{-- Levier pack --}}
                                    <div x-show="lever === 'bundle'" x-cloak class="mt-3 space-y-2">
                                        @if($item['bundle_lever'])
                                            @php $bundle = $item['bundle_lever']; @endphp
                                            <p class="text-[11px] text-[#64748B]">
                                                <strong class="text-[#0B0F14]">+ {{ $bundle['dormant_product']->name }}</strong> (dormant {{ $bundle['dormant_product']->days_without_movement }} j)<br>
                                                <span class="line-through">{{ number_format($bundle['normal_total_price'], 0, ',', ' ') }}</span>
                                                <strong class="text-[#0B0F14]">{{ number_format($bundle['bundle_price'], 0, ',', ' ') }} FCFA (-{{ $bundle['discount_percent'] }}%)</strong><br>
                                                Panier +{{ number_format($bundle['additional_cart_revenue'], 0, ',', ' ') }} F · cash libéré {{ number_format($bundle['dormant_cash_freed'], 0, ',', ' ') }} F
                                            </p>
                                            <form action="{{ route('stock.revenue-booster.create-bundle') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="primary_product_id" value="{{ $p->id }}">
                                                <input type="hidden" name="secondary_product_id" value="{{ $bundle['dormant_product']->id }}">
                                                <input type="hidden" name="code" value="{{ $bundle['suggested_code'] }}">
                                                <input type="hidden" name="name" value="{{ $bundle['bundle_name'] }}">
                                                <input type="hidden" name="discount_percent" value="{{ $bundle['discount_percent'] }}">
                                                <input type="hidden" name="min_amount" value="{{ $bundle['bundle_price'] }}">
                                                <button type="submit" class="w-full rounded-lg bg-[#0B0F14] py-2 text-xs font-semibold text-white hover:bg-slate-800">Activer l'offre ({{ $bundle['suggested_code'] }})</button>
                                            </form>
                                        @else
                                            <p class="rounded-lg bg-[#F5F7FA] p-3 text-center text-xs text-[#64748B]">Aucun article dormant compatible.</p>
                                        @endif
                                    </div>

                                    {{-- Levier anti-rupture --}}
                                    <div x-show="lever === 'security'" x-cloak class="mt-3 space-y-2">
                                        <p class="text-[11px] {{ $critical ? 'text-rose-800' : 'text-[#64748B]' }}">
                                            <strong class="text-[#0B0F14]">Autonomie : {{ $item['days_of_coverage'] }} j</strong> · {{ $p->current_stock }} unités<br>
                                            @if($critical)
                                                Manque à gagner estimé : <strong>{{ number_format($item['potential_stockout_loss'], 0, ',', ' ') }} FCFA</strong>
                                            @else
                                                Réappro préventif recommandé (45 jours).
                                            @endif
                                        </p>
                                        <form action="{{ route('stock.revenue-booster.secure-stock') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $p->id }}">
                                            <input type="hidden" name="requested_quantity" value="{{ $item['anti_stockout_lever']['recommended_reorder_qty'] }}">
                                            <input type="hidden" name="reason" value="Bouclier Anti-Rupture : Produit vedette {{ $p->name }} pour sécuriser {{ number_format($item['anti_stockout_lever']['secured_revenue_forecast'], 0, ',', ' ') }} FCFA de CA.">
                                            <button type="submit" class="w-full rounded-lg {{ $critical ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#0066FF] hover:bg-[#0052cc]' }} py-2 text-xs font-semibold text-white">Réappro VIP (+{{ $item['anti_stockout_lever']['recommended_reorder_qty'] }} unités)</button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="py-8 text-center text-xs text-[#64748B]">Aucun historique de sortie suffisant pour analyser les produits vedettes.</p>
                    @endif
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-7">

                    {{-- Suggestions d'approvisionnement --}}
                    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Approvisionnement & alertes</h2>
                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-medium text-[#0066FF]">{{ $replenishmentSuggestions->count() }} suggestions</span>
                        </div>

                        <div class="mt-3 divide-y divide-[#E2E8F0]">
                            @forelse($replenishmentSuggestions->take(6) as $item)
                                <div class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="truncate text-sm font-semibold text-[#0B0F14]">{{ $item->name }}</h3>
                                            <span class="font-mono text-[10px] text-slate-500">{{ $item->sku }}</span>
                                            @if($item->current_stock <= 0)
                                                <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Rupture</span>
                                            @else
                                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Faible</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-[11px] text-[#64748B]">
                                            Actuel <strong class="{{ $item->current_stock <= 0 ? 'text-rose-600' : 'text-[#0B0F14]' }}">{{ $item->current_stock }}</strong>
                                            · Min {{ $item->min_stock }} · Cible {{ $item->effective_max_stock }}
                                            · <strong class="text-[#0066FF]">Suggestion +{{ $item->suggested_order_qty }} {{ $item->unit ?? 'pièce' }}</strong>
                                        </p>
                                    </div>
                                    <form action="{{ route('stock.purchase-requests.store') }}" method="POST" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                        <input type="hidden" name="requested_quantity" value="{{ $item->suggested_order_qty }}">
                                        <input type="hidden" name="priority" value="{{ $item->current_stock <= 0 ? 'urgente' : 'haute' }}">
                                        <input type="hidden" name="reason" value="Suggestion IA : Stock actuel ({{ $item->current_stock }}) sous le seuil min ({{ $item->min_stock }})">
                                        <button type="submit" class="w-full rounded-lg bg-[#0066FF] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#0052cc] sm:w-auto">
                                            Créer demande d'achat ({{ $item->suggested_order_qty }})
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="py-6 text-center text-xs text-slate-400">Tous les stocks sont au-dessus des seuils minimums.</p>
                            @endforelse
                        </div>
                    </section>

                    {{-- Rotation & dormants --}}
                    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Rotation des stocks</h2>
                        <div class="mt-3 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <h3 class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-[#64748B]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Plus vendus
                                </h3>
                                <ul class="mt-2 divide-y divide-[#E2E8F0]">
                                    @forelse($topSellingProducts->take(4) as $top)
                                        <li class="flex items-center justify-between gap-2 py-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-medium text-[#0B0F14]">{{ $top->name }}</p>
                                                <p class="text-[11px] text-slate-400">{{ $top->domain?->name ?? 'Général' }}</p>
                                            </div>
                                            <span class="shrink-0 text-xs font-semibold text-[#0066FF]">{{ $top->exit_volume }} sorties</span>
                                        </li>
                                    @empty
                                        <li class="py-4 text-center text-xs text-slate-400">Aucune sortie enregistrée.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div>
                                <h3 class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-[#64748B]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Dormants (&gt; 90 j)
                                </h3>
                                <ul class="mt-2 divide-y divide-[#E2E8F0]">
                                    @forelse($dormantProducts->take(4) as $dormant)
                                        <li class="flex items-center justify-between gap-2 py-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-medium text-[#0B0F14]">{{ $dormant->name }}</p>
                                                <p class="text-[11px] text-slate-400">Immobilisé : {{ number_format($dormant->current_stock * ($dormant->purchase_price ?: $dormant->selling_price * 0.65), 0, ',', ' ') }} FCFA</p>
                                            </div>
                                            <span class="shrink-0 text-xs font-semibold text-amber-700">{{ $dormant->days_without_movement }} j</span>
                                        </li>
                                    @empty
                                        <li class="py-4 text-center text-xs text-slate-400">Aucun produit dormant.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Derniers mouvements --}}
                <aside class="lg:col-span-5">
                    <section class="h-full rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Derniers mouvements</h2>
                            <a href="{{ route('stock.movements.index') }}" class="text-xs font-medium text-[#0066FF] hover:underline">Voir tout →</a>
                        </div>

                        <ul class="mt-3 divide-y divide-[#E2E8F0]">
                            @forelse($recentMovements->take(7) as $move)
                                @php
                                    $isPositive = in_array($move->type, ['entree', 'retour']);
                                    $isNegative = $move->type === 'sortie';
                                    $badgeClass = match($move->type) {
                                        'entree', 'retour' => 'bg-emerald-50 text-emerald-700',
                                        'sortie' => 'bg-rose-50 text-rose-700',
                                        'transfert' => 'bg-blue-50 text-[#0066FF]',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    $typeLabel = match($move->type) {
                                        'entree' => 'Entrée', 'retour' => 'Retour', 'sortie' => 'Sortie', 'transfert' => 'Transfert',
                                        default => ucfirst($move->type),
                                    };
                                @endphp
                                <li class="flex items-start justify-between gap-3 py-2.5">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $badgeClass }}">{{ $typeLabel }}</span>
                                            <span class="truncate text-xs font-medium text-[#0B0F14]">{{ $move->product?->name ?? 'Produit' }}</span>
                                        </div>
                                        <p class="mt-1 truncate text-[11px] text-slate-500">
                                            {{ $move->warehouse?->name }}@if($move->destinationWarehouse) → {{ $move->destinationWarehouse->name }}@endif
                                            · {{ $move->stock_before }} → <strong class="text-slate-700">{{ $move->stock_after }}</strong>
                                        </p>
                                        @if($move->reason_motif || $move->reference)
                                            <p class="truncate text-[10px] text-slate-400">
                                                {{ str_replace('_', ' ', $move->reason_motif ?? 'standard') }}@if($move->reference) · <span class="font-mono">{{ $move->reference }}</span>@endif
                                            </p>
                                        @endif
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="font-mono text-xs font-semibold {{ $isPositive ? 'text-emerald-600' : ($isNegative ? 'text-rose-600' : 'text-[#0066FF]') }}">
                                            {{ $isPositive ? '+' : ($isNegative ? '-' : '') }}{{ $move->quantity }}
                                        </span>
                                        <span class="block max-w-[80px] truncate text-[10px] text-slate-400">{{ $move->user?->name ?? 'Système' }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="py-6 text-center text-xs text-slate-400">Aucun mouvement enregistré.</li>
                            @endforelse
                        </ul>
                    </section>
                </aside>
            </div>
        </div>

        {{-- ==================== TAB 2 : CATALOGUE ==================== --}}
        <div x-show="activeTab === 'disponibilite'" x-cloak class="space-y-5">

            {{-- Entrepôts --}}
            <section>
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Entrepôts ({{ $warehouses->count() }})</h3>
                    <a href="{{ route('stock.warehouses.index') }}" class="text-xs font-medium text-[#0066FF] hover:underline">Gérer →</a>
                </div>
                <div class="no-scrollbar flex gap-2.5 overflow-x-auto pb-1 md:grid md:grid-cols-3 md:overflow-visible lg:grid-cols-5">
                    @foreach($warehouses as $wh)
                        <div class="min-w-[180px] shrink-0 rounded-xl border border-[#E2E8F0] bg-white p-3 md:min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-[#0B0F14]">{{ $wh->code }}</span>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('stock.warehouses.show', $wh) }}" class="text-slate-400 hover:text-[#0066FF]" title="Consulter">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" class="text-slate-400 hover:text-rose-600" title="Supprimer"
                                            @click="$dispatch('open-delete-warehouse', {
                                                id: {{ $wh->id }},
                                                name: '{{ addslashes($wh->name) }}',
                                                code: '{{ $wh->code }}',
                                                totalStock: {{ (int) $wh->warehouseStocks->sum('physical_quantity') }},
                                                productCount: {{ (int) $wh->warehouseStocks->where('physical_quantity', '>', 0)->count() }}
                                            })">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            <a href="{{ route('stock.warehouses.show', $wh) }}" class="mt-2 block truncate text-xs font-semibold text-[#0B0F14] hover:text-[#0066FF]">{{ $wh->name }}</a>
                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Physique <strong class="text-[#0B0F14]">{{ $wh->warehouseStocks->sum('physical_quantity') }}</strong>
                                · Réservé <strong class="text-amber-700">{{ $wh->warehouseStocks->sum('reserved_quantity') }}</strong>
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Catalogue --}}
            <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                <div class="space-y-3 border-b border-[#E2E8F0] p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Catalogue produits</h2>
                            <p class="text-[11px] text-[#64748B]">Disponible = Physique − Réservé</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                            <a href="{{ route('stock.products.print-catalog', request()->query()) }}" target="_blank" class="rounded-lg border border-[#E2E8F0] px-3 py-1.5 text-[#0B0F14] hover:bg-slate-50">Imprimer PDF</a>
                            <a href="{{ route('stock.products.bulk-edit') }}" class="rounded-lg border border-[#E2E8F0] px-3 py-1.5 text-[#0B0F14] hover:bg-slate-50">Prix & images</a>
                            <a href="{{ route('stock.products.bulk-create') }}" class="rounded-lg border border-[#E2E8F0] px-3 py-1.5 text-[#0B0F14] hover:bg-slate-50">Ajout groupé</a>
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
                </div>

                {{-- Barre de sélection --}}
                <div x-show="selectedProductIds.length > 0" x-cloak
                     class="flex flex-col gap-2 bg-[#0B0F14] px-4 py-3 text-white sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs"><strong x-text="selectedProductIds.length"></strong> article(s) sélectionné(s)</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="selectedProductIds = []" class="rounded-lg px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800 hover:text-white">Désélectionner</button>
                        <form action="{{ route('stock.products.bulk-edit') }}" method="POST" class="inline">
                            @csrf
                            <template x-for="id in selectedProductIds" :key="id">
                                <input type="hidden" name="product_ids[]" :value="id">
                            </template>
                            <button type="submit" class="rounded-lg bg-[#0066FF] px-3 py-1.5 text-xs font-semibold hover:bg-[#0052cc]">Modifier</button>
                        </form>
                        <button type="button" @click="$dispatch('open-bulk-delete-products', { product_ids: selectedProductIds })" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold hover:bg-rose-700">Supprimer</button>
                    </div>
                </div>

                {{-- En-tête de colonnes (desktop) --}}
                <div class="hidden items-center gap-3 border-b border-[#E2E8F0] bg-[#F5F7FA] px-4 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-[#64748B] md:grid md:grid-cols-12">
                    <div class="col-span-5 flex items-center gap-3">
                        <input type="checkbox" @change="toggleSelectAll()" :checked="isAllSelected" class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]" title="Tout sélectionner">
                        <span>Produit</span>
                    </div>
                    <div class="col-span-2 text-right">Physique / Réservé</div>
                    <div class="col-span-1 text-right text-[#0066FF]">Dispo</div>
                    <div class="col-span-2 text-right">Prix</div>
                    <div class="col-span-2 text-right">Actions</div>
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

                        <div x-show="matches({{ Js::from($searchString) }}, {{ $avail }}, {{ $minVal }})"
                             :class="{ 'bg-blue-50/40': selectedProductIds.includes({{ $product->id }}) }"
                             class="px-4 py-3 transition-colors hover:bg-slate-50/60 md:grid md:grid-cols-12 md:items-center md:gap-3">

                            {{-- Produit --}}
                            <div class="flex items-center gap-3 md:col-span-5">
                                <input type="checkbox" :value="{{ $product->id }}" x-model.number="selectedProductIds"
                                       class="shrink-0 rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]">

                                <button type="button" @click="$dispatch('open-product-photo', {{ Js::from($photoPayload) }})"
                                        class="relative h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-[#E2E8F0] hover:ring-2 hover:ring-[#0066FF]"
                                        title="Changer la photo">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="truncate text-sm font-semibold text-[#0B0F14]">{{ $product->name }}</span>
                                        @if(($product->exit_volume ?? 0) >= 15 || ($product->rotation_class ?? '') === 'tres_vendu')
                                            <span class="shrink-0 rounded bg-amber-50 px-1.5 py-0.5 text-[9px] font-semibold text-amber-800">⭐ Top</span>
                                        @endif
                                    </div>
                                    <p class="truncate font-mono text-[10px] text-slate-500">
                                        <span class="font-semibold text-[#0066FF]">{{ $product->sku }}</span> · EAN {{ $product->formatted_ean }}
                                    </p>
                                    <p class="truncate text-[11px] text-slate-400">
                                        {{ strtoupper($product->domain?->name ?? 'Général') }} · {{ $product->brand?->name ?? 'IVOSPHERE' }} · {{ $product->unit ?? 'pièce' }}
                                    </p>
                                </div>

                                {{-- Dispo (mobile) --}}
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold md:hidden
                                    {{ $avail <= 0 ? 'bg-rose-50 text-rose-700' : ($avail <= $minVal ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800') }}">
                                    {{ $avail <= 0 ? 'Rupture' : 'Dispo ' . $avail }}
                                </span>
                            </div>

                            {{-- Stock --}}
                            <div class="mt-3 grid grid-cols-3 gap-2 rounded-lg bg-[#F5F7FA] p-2 text-center text-xs md:contents">
                                <div class="md:col-span-2 md:text-right">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-slate-400 md:hidden">Physique / Réservé</span>
                                    <span class="font-semibold text-[#0B0F14]">{{ $product->current_stock }}</span>
                                    <span class="text-slate-400"> / </span>
                                    <span class="font-semibold text-amber-700">{{ $product->reserved_stock }}</span>
                                    <span class="block text-[10px] text-slate-400">Min {{ $product->min_stock }} · Max {{ $product->effective_max_stock }} · Cmd +{{ $product->incoming_stock }}</span>
                                    @if($product->warehouseStocks->where('physical_quantity', '>', 0)->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1 md:justify-end">
                                            @foreach($product->warehouseStocks as $ws)
                                                @if($ws->physical_quantity > 0)
                                                    <span class="rounded bg-slate-100 px-1.5 text-[10px] text-slate-600" title="{{ $ws->warehouse?->name }}">
                                                        {{ $ws->warehouse?->code ?: Str::limit($ws->warehouse?->name, 8) }}: <strong class="text-[#0B0F14]">{{ $ws->physical_quantity }}</strong>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="hidden md:col-span-1 md:block md:text-right">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold {{ $avail <= 0 ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-[#0066FF]' }}">{{ $avail }}</span>
                                </div>

                                <div class="md:col-span-2 md:text-right">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-slate-400 md:hidden">Prix vente</span>
                                    <span class="font-semibold text-[#0B0F14]">{{ number_format((float) $product->selling_price, 0, ',', ' ') }} F</span>
                                    <span class="block text-[10px] text-slate-400">Coût {{ number_format((float) ($product->purchase_price ?: $product->selling_price * 0.65), 0, ',', ' ') }} F</span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-2 flex items-center gap-4 text-xs font-medium md:col-span-2 md:mt-0 md:justify-end md:gap-1.5">
                                <button type="button" @click="$dispatch('open-stock-adjust', {{ Js::from($adjustPayload) }})"
                                        class="text-emerald-600 hover:underline md:rounded-lg md:p-1.5 md:hover:bg-emerald-50 md:hover:no-underline" title="Ajuster le stock">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    <span class="md:hidden">Ajuster</span>
                                </button>
                                <button type="button" @click="$dispatch('open-product-qr', {{ Js::from($qrPayload) }})"
                                        class="text-[#0066FF] hover:underline md:rounded-lg md:p-1.5 md:hover:bg-blue-50 md:hover:no-underline" title="QR Code & EAN">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    <span class="md:hidden">QR</span>
                                </button>
                                <a href="{{ route('stock.products.edit', $product) }}"
                                   class="text-slate-600 hover:underline md:rounded-lg md:p-1.5 md:hover:bg-slate-100 md:hover:no-underline" title="Modifier la fiche">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span class="md:hidden">Fiche</span>
                                </a>
                                <button type="button" @click="$dispatch('open-delete-product', {{ Js::from($deletePayload) }})"
                                        class="text-rose-600 hover:underline md:rounded-lg md:p-1.5 md:hover:bg-rose-50 md:hover:no-underline" title="Supprimer">
                                    <svg class="hidden h-4 w-4 md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="md:hidden">Suppr.</span>
                                </button>
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