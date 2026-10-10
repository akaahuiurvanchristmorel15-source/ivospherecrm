<x-layouts.app title="Gestion des Stocks & Entrepôts (WMS)">
    <div x-data="{ 
        activeTab: 'synthese',
        showMoreKpis: false,
        mobileSearch: '',
        mobileFilterStatus: 'all'
    }" class="space-y-4 sm:space-y-6 pb-12">

        {{-- Flash Feedback --}}
        @if(session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-medium text-emerald-900">
                <div class="flex items-center gap-2.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm font-medium text-rose-900">
                <div class="flex items-center gap-2.5">
                    <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- SECTION 1: En-tête Unifié & Épuré --}}
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0B0F14] px-2.5 py-0.5 text-[10px] font-medium tracking-wide text-white">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        IVOSPHERE WMS
                    </span>
                    <span class="text-xs text-[#64748B]">{{ $activeWarehousesCount }} entrepôt(s) actif(s)</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-[#0B0F14]">
                    Gestion des Stocks & Logistique
                </h1>
                <p class="text-xs sm:text-sm text-[#64748B]">
                    Plateforme centrale WMS multi-entrepôts, mouvements et inventaire
                </p>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-2.5">
                <a href="{{ route('stock.scanner') }}"
                   class="h-10 px-3.5 rounded-xl border border-[#E2E8F0] bg-white text-xs font-medium text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 text-[#0066FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2m4-10h10v10H7V7z" />
                    </svg>
                    <span>Mode Scan / Magasinier</span>
                </a>

                <a href="{{ route('stock.warehouses.index') }}"
                   class="h-10 px-3.5 rounded-xl border border-[#E2E8F0] bg-white text-xs font-medium text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Entrepôts</span>
                </a>

                <a href="{{ route('stock.movements.create') }}"
                   class="h-10 px-3.5 rounded-xl border border-[#E2E8F0] bg-white text-xs font-medium text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Mouvement</span>
                </a>

                <a href="{{ route('stock.products.create') }}"
                   class="h-10 px-4 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-medium transition flex items-center justify-center gap-1.5 shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nouveau Produit</span>
                </a>
            </div>
        </header>

        {{-- Domain Filter Strip --}}
        <div class="flex items-center justify-between gap-2 rounded-2xl border border-[#E2E8F0] bg-white p-1.5 sm:px-3 sm:py-2">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar w-full py-0.5">
                <span class="hidden sm:inline-block mr-2 text-[11px] font-medium uppercase tracking-wider text-[#64748B] shrink-0">Domaine</span>
                <a href="{{ route('stock.index') }}"
                   class="rounded-xl px-3 py-1.5 text-xs font-medium transition shrink-0 {{ !request('domain_id') ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] bg-[#F5F7FA] hover:text-[#0B0F14]' }}">
                    Tous les domaines
                </a>
                @foreach($domains as $domain)
                    <a href="{{ route('stock.index', ['domain_id' => $domain->id]) }}"
                       class="rounded-xl px-3 py-1.5 text-xs font-medium transition shrink-0 {{ (string) request('domain_id') === (string) $domain->id ? 'bg-[#0066FF] text-white' : 'text-[#64748B] bg-[#F5F7FA] hover:text-[#0B0F14]' }}">
                        {{ strtoupper($domain->name) }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- SECTION 2: Indicateurs Clés Épurés --}}
        <div class="space-y-3">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                {{-- KPI 1: Stock Physique --}}
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Stock Physique</span>
                        <span class="text-[11px] text-[#64748B] font-mono">{{ $totalProductsCount }} réf.</span>
                    </div>
                    <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-[#0B0F14]">
                        {{ number_format($totalStockItems, 0, ',', ' ') }}
                        <span class="text-xs sm:text-sm font-normal text-slate-400">unités</span>
                    </div>
                    <p class="mt-1 text-xs text-[#64748B] hidden sm:block">Articles disponibles en stock</p>
                </div>

                {{-- KPI 2: Valeur Stock --}}
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Stock Disponible</span>
                        <span class="text-[11px] text-[#0066FF] font-medium">Achat</span>
                    </div>
                    <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-[#0B0F14]">
                        @if($availableStockValue >= 1000000)
                            {{ number_format($availableStockValue / 1000000, 1, ',', ' ') }}M <span class="text-xs sm:text-sm font-normal text-slate-400">FCFA</span>
                        @else
                            {{ number_format($availableStockValue, 0, ',', ' ') }} <span class="text-xs sm:text-sm font-normal text-slate-400">FCFA</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-[#64748B] hidden sm:block">
                        Vente : 
                        @if($potentialSellingValue >= 1000000)
                            {{ number_format($potentialSellingValue / 1000000, 1, ',', ' ') }}M FCFA
                        @else
                            {{ number_format($potentialSellingValue, 0, ',', ' ') }} FCFA
                        @endif
                    </p>
                </div>

                {{-- KPI 3: Stock Faible --}}
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-amber-700">Stock Faible</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-amber-600">
                        {{ $lowStockCount }}
                    </div>
                    <p class="mt-1 text-xs text-[#64748B] hidden sm:block">Sous le seuil d'alerte</p>
                </div>

                {{-- KPI 4: Ruptures --}}
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-rose-700">Ruptures</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </div>
                    <div class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight tabular-nums text-rose-600">
                        {{ $outOfStockCount }}
                    </div>
                    <p class="mt-1 text-xs text-[#64748B] hidden sm:block">Articles épuisés (stock = 0)</p>
                </div>
            </div>

            {{-- Indicateurs Secondaires pliables --}}
            <div>
                <button type="button" @click="showMoreKpis = !showMoreKpis"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg text-xs font-medium text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition">
                    <span x-text="showMoreKpis ? 'Masquer flux d\'activité (entrées, sorties, surstock)' : 'Afficher flux d\'activité (entrées, sorties, surstock)...'"></span>
                    <svg class="h-3.5 w-3.5 transition-transform" :class="{ 'rotate-180': showMoreKpis }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="showMoreKpis" x-cloak class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-2">
                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5 sm:p-4">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Entrées</span>
                        <div class="mt-1 text-xl font-semibold tabular-nums text-[#0B0F14]">{{ $entriesCount }}</div>
                        <p class="mt-0.5 text-xs text-[#64748B]">Réceptions & achats</p>
                    </div>

                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5 sm:p-4">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Sorties</span>
                        <div class="mt-1 text-xl font-semibold tabular-nums text-[#0B0F14]">{{ $exitsCount }}</div>
                        <p class="mt-0.5 text-xs text-[#64748B]">Expéditions & ventes</p>
                    </div>

                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5 sm:p-4">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Transferts</span>
                        <div class="mt-1 text-xl font-semibold tabular-nums text-[#0B0F14]">{{ $transfersCount }}</div>
                        <p class="mt-0.5 text-xs text-[#64748B]">Inter-dépôts</p>
                    </div>

                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3.5 sm:p-4">
                        <span class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Immobilisation</span>
                        <div class="mt-1 text-xl font-semibold tabular-nums text-[#0B0F14]">{{ number_format($immobilizedCostValue, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></div>
                        <p class="mt-0.5 text-xs text-[#64748B]">Surstock : {{ $overstockCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: WMS Navigation Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar rounded-2xl border border-[#E2E8F0] bg-white p-1.5">
            <button type="button" @click="activeTab = 'synthese'"
                    :class="activeTab === 'synthese' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]'"
                    class="rounded-xl px-3.5 py-2 text-xs font-medium whitespace-nowrap shrink-0 transition">
                <span class="sm:hidden">Synthèse</span>
                <span class="hidden sm:inline">1. Synthèse, Alertes & Suggestions IA</span>
            </button>
            <button type="button" @click="activeTab = 'disponibilite'"
                    :class="activeTab === 'disponibilite' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]'"
                    class="rounded-xl px-3.5 py-2 text-xs font-medium whitespace-nowrap shrink-0 transition">
                <span class="sm:hidden">Catalogue & Stock</span>
                <span class="hidden sm:inline">2. Catalogue Produits & Disponibilité</span>
            </button>
            <button type="button" @click="activeTab = 'transferts'"
                    :class="activeTab === 'transferts' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]'"
                    class="rounded-xl px-3.5 py-2 text-xs font-medium whitespace-nowrap shrink-0 transition">
                <span class="sm:hidden">Transferts</span>
                <span class="hidden sm:inline">3. Transferts & Inventaires</span>
            </button>
            <button type="button" @click="activeTab = 'achats'"
                    :class="activeTab === 'achats' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]'"
                    class="rounded-xl px-3.5 py-2 text-xs font-medium whitespace-nowrap shrink-0 transition">
                <span class="sm:hidden">Achats & BL</span>
                <span class="hidden sm:inline">4. Demandes d'Achat & BL</span>
            </button>
            <button type="button" @click="activeTab = 'tracabilite'"
                    :class="activeTab === 'tracabilite' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]'"
                    class="rounded-xl px-3.5 py-2 text-xs font-medium whitespace-nowrap shrink-0 transition">
                <span class="sm:hidden">Traçabilité</span>
                <span class="hidden sm:inline">5. Lots, Séries & Maintenance</span>
            </button>
        </div>

        {{-- =======================================================================
             TAB 1: SYNTHÈSE, ACCÉLÉRATEUR DE REVENUS, ROTATION & SUGGESTIONS D'ACHAT
             ======================================================================= --}}
        <div x-show="activeTab === 'synthese'" class="space-y-6">

            {{-- 🚀 HUB DE MONÉTISATION DES STOCKS : PRODUITS VEDETTES & CROISSANCE DES REVENUS --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6 shadow-xs">
                {{-- Header --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#E2E8F0] pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-blue-50 text-[#0066FF] border border-blue-200/60 font-bold text-xs">
                                🚀
                            </span>
                            <h2 class="text-base sm:text-lg font-bold text-[#0B0F14]">
                                Booster de Revenus : Optimisation des Produits Vedettes (Top Ventes)
                            </h2>
                        </div>
                        <p class="text-xs sm:text-sm text-[#64748B] mt-1">
                            Faites fructifier vos revenus sur vos articles les plus vendus : élasticité tarifaire, offres packs synergie avec les stocks dormants et protection anti-rupture.
                        </p>
                    </div>

                    {{-- Portfolio Metrics Badges --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="rounded-xl border border-[#E2E8F0] bg-[#F5F7FA] px-3 py-1.5">
                            <span class="block text-[10px] font-semibold uppercase tracking-wider text-[#64748B]">CA Top Ventes</span>
                            <span class="text-xs font-bold text-[#0B0F14]">{{ number_format($revenueMetrics['total_top_revenue'] ?? 0, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="rounded-xl border border-emerald-200/60 bg-emerald-50 px-3 py-1.5">
                            <span class="block text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Gain Prix Potentiel</span>
                            <span class="text-xs font-bold text-emerald-800">+{{ number_format($revenueMetrics['total_potential_gain_price'] ?? 0, 0, ',', ' ') }} FCFA/m</span>
                        </div>
                        <div class="rounded-xl border border-amber-200/60 bg-amber-50 px-3 py-1.5">
                            <span class="block text-[10px] font-semibold uppercase tracking-wider text-amber-800">Cash Dormant Rattachable</span>
                            <span class="text-xs font-bold text-amber-900">{{ number_format($revenueMetrics['total_dormant_cash_unlockable'] ?? 0, 0, ',', ' ') }} FCFA</span>
                        </div>
                        @if(($revenueMetrics['at_risk_turnover'] ?? 0) > 0)
                            <div class="rounded-xl border border-rose-200/60 bg-rose-50 px-3 py-1.5">
                                <span class="block text-[10px] font-semibold uppercase tracking-wider text-rose-700">Risque Rupture CA</span>
                                <span class="text-xs font-bold text-rose-800">{{ number_format($revenueMetrics['at_risk_turnover'], 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Cards Grid for Best Sellers --}}
                <div class="mt-5">
                    @if(isset($topSellersAnalyzed) && $topSellersAnalyzed->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                            @foreach($topSellersAnalyzed as $item)
                                @php
                                    $p = $item['product'];
                                    $rankColors = match($item['rank']) {
                                        1 => 'bg-amber-50 text-amber-800 border-amber-300 font-bold',
                                        2 => 'bg-slate-100 text-slate-700 border-slate-300 font-bold',
                                        3 => 'bg-amber-100/50 text-amber-900 border-amber-200 font-bold',
                                        default => 'bg-blue-50 text-[#0066FF] border-blue-200 font-semibold',
                                    };
                                @endphp

                                <div x-data="{ activeLever: 'price' }"
                                     class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5 flex flex-col justify-between hover:border-blue-300/80 hover:shadow-xs transition-all">
                                    {{-- Card Top: Rank & Product Details --}}
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="h-6 px-2 rounded-lg border text-xs flex items-center justify-center shrink-0 {{ $rankColors }}">
                                                    #{{ $item['rank'] }}
                                                </span>
                                                <div class="min-w-0">
                                                    <h3 class="font-bold text-sm text-[#0B0F14] truncate" title="{{ $p->name }}">
                                                        {{ $p->name }}
                                                    </h3>
                                                    <p class="text-[11px] text-[#64748B] truncate">
                                                        {{ $p->domain?->name ?? 'Général' }} • SKU: {{ $p->sku }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="rounded-md border px-2 py-0.5 text-[10px] font-semibold shrink-0 {{ $item['risk_badge_class'] }}">
                                                {{ $item['risk_label'] }}
                                            </span>
                                        </div>

                                        {{-- Metric chips --}}
                                        <div class="mt-3.5 grid grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-xl border border-[#E2E8F0] bg-[#F5F7FA] p-2">
                                                <span class="block text-[10px] font-medium text-[#64748B] uppercase tracking-wider">Volume & CA</span>
                                                <span class="font-bold text-[#0B0F14]">{{ $item['exit_volume'] }} sorties</span>
                                                <span class="block text-[10px] text-[#64748B]">{{ number_format($item['revenue_generated'], 0, ',', ' ') }} FCFA</span>
                                            </div>
                                            <div class="rounded-xl border border-[#E2E8F0] bg-[#F5F7FA] p-2">
                                                <span class="block text-[10px] font-medium text-[#64748B] uppercase tracking-wider">Prix & Marge</span>
                                                <span class="font-bold text-[#0B0F14]">{{ number_format($item['selling_price'], 0, ',', ' ') }} FCFA</span>
                                                <span class="block text-[10px] text-emerald-700 font-semibold">{{ $item['margin_percent'] }}% marge ({{ number_format($item['margin_unit'], 0, ',', ' ') }} F)</span>
                                            </div>
                                        </div>

                                        {{-- Levers Selector Tabs --}}
                                        <div class="mt-4 border-t border-[#E2E8F0] pt-3">
                                            <div class="flex items-center gap-1 rounded-xl bg-[#F5F7FA] p-1">
                                                <button type="button"
                                                        @click="activeLever = 'price'"
                                                        :class="activeLever === 'price' ? 'bg-white text-[#0066FF] shadow-2xs font-semibold' : 'text-[#64748B] hover:text-[#0B0F14]'"
                                                        class="flex-1 py-1 px-1.5 text-[11px] rounded-lg text-center transition cursor-pointer">
                                                    1. Prix & Marge
                                                </button>
                                                <button type="button"
                                                        @click="activeLever = 'bundle'"
                                                        :class="activeLever === 'bundle' ? 'bg-white text-[#0066FF] shadow-2xs font-semibold' : 'text-[#64748B] hover:text-[#0B0F14]'"
                                                        class="flex-1 py-1 px-1.5 text-[11px] rounded-lg text-center transition cursor-pointer">
                                                    2. Pack Synergie
                                                </button>
                                                <button type="button"
                                                        @click="activeLever = 'security'"
                                                        :class="activeLever === 'security' ? 'bg-white text-[#0066FF] shadow-2xs font-semibold' : 'text-[#64748B] hover:text-[#0B0F14]'"
                                                        class="flex-1 py-1 px-1.5 text-[11px] rounded-lg text-center transition cursor-pointer">
                                                    3. Anti-Rupture
                                                </button>
                                            </div>

                                            {{-- Lever 1 Content: Optimisation Tarifaire --}}
                                            <div x-show="activeLever === 'price'" class="mt-3 space-y-2.5">
                                                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-2.5">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="font-semibold text-blue-900">Scénario Modéré (+5%)</span>
                                                        <span class="font-bold text-[#0066FF]">{{ number_format($item['pricing_lever']['opt_price_5'], 0, ',', ' ') }} FCFA</span>
                                                    </div>
                                                    <p class="text-[11px] text-blue-800 mt-1">
                                                        Gain estimé : <strong class="font-semibold">+{{ number_format($item['pricing_lever']['projected_monthly_gain_5'], 0, ',', ' ') }} FCFA/mois</strong> (Marge: {{ $item['pricing_lever']['new_margin_percent_5'] }}%)
                                                    </p>
                                                </div>

                                                <form action="{{ route('stock.revenue-booster.optimize-price') }}" method="POST" class="pt-1">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                                    <input type="hidden" name="selling_price" value="{{ $item['pricing_lever']['opt_price_5'] }}">
                                                    <input type="hidden" name="reason" value="Optimisation tarifaire recommandée IA (+5% sur produit vedette)">
                                                    <button type="submit"
                                                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white px-3 py-2 text-xs font-semibold shadow-2xs transition cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                                        <span>Appliquer le prix optimisé (+5%)</span>
                                                    </button>
                                                </form>
                                            </div>

                                            {{-- Lever 2 Content: Offre Pack Synergie --}}
                                            <div x-show="activeLever === 'bundle'" class="mt-3 space-y-2.5" style="display: none;">
                                                @if($item['bundle_lever'])
                                                    @php $bundle = $item['bundle_lever']; @endphp
                                                    <div class="rounded-xl border border-amber-200/60 bg-amber-50/50 p-2.5">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="font-semibold text-amber-950 truncate max-w-[170px]" title="{{ $bundle['dormant_product']->name }}">
                                                                + {{ $bundle['dormant_product']->name }}
                                                            </span>
                                                            <span class="rounded bg-amber-100 text-amber-900 text-[10px] font-bold px-1.5 py-0.5">
                                                                Dormant {{ $bundle['dormant_product']->days_without_movement }}j
                                                            </span>
                                                        </div>
                                                        <div class="mt-1.5 flex items-baseline justify-between text-xs">
                                                            <span class="text-slate-400 line-through text-[11px]">{{ number_format($bundle['normal_total_price'], 0, ',', ' ') }} FCFA</span>
                                                            <span class="font-bold text-amber-900">{{ number_format($bundle['bundle_price'], 0, ',', ' ') }} FCFA (-{{ $bundle['discount_percent'] }}%)</span>
                                                        </div>
                                                        <p class="text-[10px] text-amber-800 mt-1">
                                                            Augmente le panier de <strong>+{{ number_format($bundle['additional_cart_revenue'], 0, ',', ' ') }} FCFA</strong> et libère <strong>{{ number_format($bundle['dormant_cash_freed'], 0, ',', ' ') }} FCFA</strong> de cash immobilisé.
                                                        </p>
                                                    </div>

                                                    <form action="{{ route('stock.revenue-booster.create-bundle') }}" method="POST" class="pt-1">
                                                        @csrf
                                                        <input type="hidden" name="primary_product_id" value="{{ $p->id }}">
                                                        <input type="hidden" name="secondary_product_id" value="{{ $bundle['dormant_product']->id }}">
                                                        <input type="hidden" name="code" value="{{ $bundle['suggested_code'] }}">
                                                        <input type="hidden" name="name" value="{{ $bundle['bundle_name'] }}">
                                                        <input type="hidden" name="discount_percent" value="{{ $bundle['discount_percent'] }}">
                                                        <input type="hidden" name="min_amount" value="{{ $bundle['bundle_price'] }}">
                                                        <button type="submit"
                                                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0B0F14] hover:bg-slate-800 text-white px-3 py-2 text-xs font-semibold shadow-2xs transition cursor-pointer">
                                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                                            <span>Activer Offre Pack ({{ $bundle['suggested_code'] }})</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="rounded-xl border border-slate-200 bg-[#F5F7FA] p-3 text-center text-xs text-[#64748B]">
                                                        Aucun article dormant compatible pour l'appariement.
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Lever 3 Content: Bouclier Anti-Rupture --}}
                                            <div x-show="activeLever === 'security'" class="mt-3 space-y-2.5" style="display: none;">
                                                <div class="rounded-xl border {{ in_array($item['risk_level'], ['rupture', 'critique']) ? 'border-rose-200/80 bg-rose-50/50' : 'border-slate-200 bg-[#F5F7FA]' }} p-2.5">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="font-semibold {{ in_array($item['risk_level'], ['rupture', 'critique']) ? 'text-rose-900' : 'text-[#0B0F14]' }}">
                                                            Autonomie : {{ $item['days_of_coverage'] }} jours
                                                        </span>
                                                        <span class="text-[11px] text-[#64748B]">
                                                            {{ $item['product']->current_stock }} unités en rayon
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] {{ in_array($item['risk_level'], ['rupture', 'critique']) ? 'text-rose-800' : 'text-[#64748B]' }} mt-1">
                                                        @if(in_array($item['risk_level'], ['rupture', 'critique']))
                                                            ⚠️ Manque à gagner estimé à <strong>{{ number_format($item['potential_stockout_loss'], 0, ',', ' ') }} FCFA</strong> en cas de rupture prolongée.
                                                        @else
                                                            Réapprovisionnement préventif recommandé pour garantir 45 jours de trésorerie sans interruption.
                                                        @endif
                                                    </p>
                                                </div>

                                                <form action="{{ route('stock.revenue-booster.secure-stock') }}" method="POST" class="pt-1">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                                    <input type="hidden" name="requested_quantity" value="{{ $item['anti_stockout_lever']['recommended_reorder_qty'] }}">
                                                    <input type="hidden" name="reason" value="Bouclier Anti-Rupture : Produit vedette {{ $p->name }} pour sécuriser {{ number_format($item['anti_stockout_lever']['secured_revenue_forecast'], 0, ',', ' ') }} FCFA de CA.">
                                                    <button type="submit"
                                                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl {{ in_array($item['risk_level'], ['rupture', 'critique']) ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#0066FF] hover:bg-[#0052cc]' }} text-white px-3 py-2 text-xs font-semibold shadow-2xs transition cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                        <span>Réappro VIP (+{{ $item['anti_stockout_lever']['recommended_reorder_qty'] }} unités)</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-[#64748B]">
                            Aucun historique de sortie suffisant pour analyser les produits vedettes.
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                {{-- Left 7 cols: Approvisionnement Automatique & Alertes Stock --}}
                <div class="space-y-6 lg:col-span-7">
                    {{-- 1. Suggestions d'Approvisionnement IA & Alertes --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                                    <h2 class="text-base font-bold text-[#0B0F14]">
                                        Suggestions d'Approvisionnement & Alertes Rupture
                                    </h2>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-[#0066FF] border border-blue-200/60 self-start sm:self-auto">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                                {{ $replenishmentSuggestions->count() }} suggestions actives
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse($replenishmentSuggestions->take(6) as $item)
                                <div class="rounded-xl border border-slate-200/80 bg-white p-4 transition hover:border-slate-300 hover:shadow-2xs">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        {{-- Nom du produit, SKU et statut --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-sm font-bold text-[#0B0F14] truncate">{{ $item->name }}</h3>
                                                <span class="font-mono text-[10px] text-slate-500 px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200">
                                                    {{ $item->sku }}
                                                </span>
                                                @if($item->current_stock <= 0)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                        RUPTURE (0)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        STOCK FAIBLE
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Mini-grille d'indicateurs structurée --}}
                                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-4">
                                                <div class="rounded-lg border border-slate-100 bg-[#F5F7FA] px-2.5 py-1.5">
                                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Actuel</span>
                                                    <span class="font-bold {{ $item->current_stock <= 0 ? 'text-rose-600' : 'text-[#0B0F14]' }}">
                                                        {{ $item->current_stock }}
                                                    </span>
                                                </div>
                                                <div class="rounded-lg border border-slate-100 bg-[#F5F7FA] px-2.5 py-1.5">
                                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Seuil Min</span>
                                                    <span class="font-semibold text-slate-700">{{ $item->min_stock }}</span>
                                                </div>
                                                <div class="rounded-lg border border-slate-100 bg-[#F5F7FA] px-2.5 py-1.5">
                                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Cible Max</span>
                                                    <span class="font-semibold text-slate-700">{{ $item->effective_max_stock }}</span>
                                                </div>
                                                <div class="rounded-lg border border-blue-200/60 bg-blue-50/80 px-2.5 py-1.5">
                                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-[#0066FF]">Suggestion IA</span>
                                                    <span class="font-bold text-[#0066FF]">+{{ $item->suggested_order_qty }} {{ $item->unit ?? 'pièce' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Bouton d'action --}}
                                        <div class="shrink-0 sm:self-center">
                                            <form action="{{ route('stock.purchase-requests.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                <input type="hidden" name="requested_quantity" value="{{ $item->suggested_order_qty }}">
                                                <input type="hidden" name="priority" value="{{ $item->current_stock <= 0 ? 'urgente' : 'haute' }}">
                                                <input type="hidden" name="reason" value="Suggestion IA : Stock actuel ({{ $item->current_stock }}) sous le seuil min ({{ $item->min_stock }})">
                                                <button type="submit"
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span class="sm:hidden">Commander (+{{ $item->suggested_order_qty }})</span>
                                                    <span class="hidden sm:inline">Créer demande d'achat ({{ $item->suggested_order_qty }})</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-slate-400">
                                    Tous les stocks sont au-dessus des seuils minimums de sécurité.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- 2. Rotation des Stocks & Détection des Produits Dormants --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base font-bold text-[#0B0F14]">
                                    Rotation des Stocks & Détection des Produits Dormants
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Analyse des articles à forte rotation vs immobilisations financières (&gt; 90 jours sans mouvement)
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            {{-- Top Rotation --}}
                            <div class="rounded-xl border border-slate-200/80 bg-[#F5F7FA]/40 p-4">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Produits à Forte Rotation (Plus Vendus)
                                        </h3>
                                    </div>
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                        Top flux
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2.5">
                                    @forelse($topSellingProducts->take(4) as $top)
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-slate-200/70 hover:border-slate-300 transition-colors">
                                            <div class="min-w-0 pr-2">
                                                <span class="font-semibold text-xs text-[#0B0F14] truncate block">{{ $top->name }}</span>
                                                <span class="text-[11px] text-slate-400 block">{{ $top->domain?->name ?? 'Général' }}</span>
                                            </div>
                                            <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-bold text-[#0066FF] border border-blue-200/60 shrink-0">
                                                {{ $top->exit_volume }} sorties
                                            </span>
                                        </div>
                                    @empty
                                        <p class="py-4 text-center text-xs text-slate-400">Aucune sortie enregistrée.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Dormant Products --}}
                            <div class="rounded-xl border border-slate-200/80 bg-[#F5F7FA]/40 p-4">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Produits Dormants (&gt; 90j sans sortie)
                                        </h3>
                                    </div>
                                    <span class="text-[10px] font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">
                                        À déstocker
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2.5">
                                    @forelse($dormantProducts->take(4) as $dormant)
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-slate-200/70 hover:border-slate-300 transition-colors">
                                            <div class="min-w-0 pr-2">
                                                <span class="font-semibold text-xs text-[#0B0F14] truncate block">{{ $dormant->name }}</span>
                                                <span class="text-[11px] text-slate-400 block">
                                                    Valeur immobilisée : {{ number_format($dormant->current_stock * ($dormant->purchase_price ?: $dormant->selling_price * 0.65), 0, ',', ' ') }} FCFA
                                                </span>
                                            </div>
                                            <span class="rounded-md bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800 border border-amber-200/60 shrink-0">
                                                {{ $dormant->days_without_movement }} j
                                            </span>
                                        </div>
                                    @empty
                                        <p class="py-4 text-center text-xs text-slate-400">Aucun produit dormant détecté.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right 5 cols: Journal d'Audit & Derniers Mouvements --}}
                <div class="lg:col-span-5">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div>
                                    <h2 class="text-base font-bold text-[#0B0F14]">
                                        Historique & Traçabilité des Mouvements
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Journal d'audit temps réel (Produit, Entrepôt, Avant/Après, Utilisateur)
                                    </p>
                                </div>
                                <a href="{{ route('stock.movements.index') }}" class="text-xs font-semibold text-[#0066FF] hover:underline flex items-center gap-1 shrink-0">
                                    <span>Voir tout</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>

                            <div class="mt-4 divide-y divide-slate-100">
                                @forelse($recentMovements->take(7) as $move)
                                    @php
                                        $isPositive = in_array($move->type, ['entree', 'retour']);
                                        $isNegative = $move->type === 'sortie';
                                        $badgeClass = match($move->type) {
                                            'entree', 'retour' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                            'sortie' => 'bg-rose-50 text-rose-700 border border-rose-200/60',
                                            'transfert' => 'bg-blue-50 text-[#0066FF] border border-blue-200/60',
                                            default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                        };
                                        $typeLabel = match($move->type) {
                                            'entree' => 'ENTRÉE',
                                            'retour' => 'RETOUR',
                                            'sortie' => 'SORTIE',
                                            'transfert' => 'TRANSFERT',
                                            default => strtoupper($move->type),
                                        };
                                    @endphp
                                    <div class="py-3 hover:bg-[#F5F7FA]/50 px-2 rounded-xl transition-colors">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold tracking-wide {{ $badgeClass }}">
                                                        {{ $typeLabel }}
                                                    </span>
                                                    <span class="text-xs font-bold text-[#0B0F14] truncate">
                                                        {{ $move->product?->name ?? 'Produit' }}
                                                    </span>
                                                </div>

                                                <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-500">
                                                    <span>{{ $move->warehouse?->name }}</span>
                                                    @if($move->destinationWarehouse)
                                                        <svg class="inline h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                                        <span>{{ $move->destinationWarehouse->name }}</span>
                                                    @endif
                                                    <span class="text-slate-300">•</span>
                                                    <span>Stock : {{ $move->stock_before }} → <strong class="text-slate-700">{{ $move->stock_after }}</strong></span>
                                                </div>

                                                @if($move->reason_motif || $move->reference)
                                                    <div class="mt-0.5 text-[10px] text-slate-400">
                                                        Motif : {{ str_replace('_', ' ', $move->reason_motif ?? 'standard') }}
                                                        @if($move->reference) • Réf : <span class="font-mono text-slate-600">{{ $move->reference }}</span> @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="text-right shrink-0">
                                                <span class="text-xs font-bold font-mono {{ $isPositive ? 'text-emerald-600' : ($isNegative ? 'text-rose-600' : 'text-[#0066FF]') }}">
                                                    {{ $isPositive ? '+' : ($isNegative ? '-' : '') }}{{ $move->quantity }}
                                                </span>
                                                <span class="block text-[10px] text-slate-400 mt-0.5 max-w-[90px] truncate">
                                                    {{ $move->user?->name ?? 'Système' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-6 text-center text-xs text-slate-400">Aucun mouvement enregistré.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 text-center">
                            <a href="{{ route('stock.movements.index') }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                                Accéder au grand livre complet des stocks →
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- =======================================================================
             TAB 2: STOCK DISPONIBLE RÉEL & MULTI-ENTREPÔTS (Physique - Réservé)
             ======================================================================= --}}
        <div x-show="activeTab === 'disponibilite'" x-cloak class="space-y-6">
            {{-- Multi-Warehouse Cards --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Sites de Stockage & Dépôts ({{ $warehouses->count() }})
                    </h3>
                    <a href="{{ route('stock.warehouses.index') }}" class="text-xs font-semibold text-[#0066FF] hover:underline flex items-center gap-1">
                        <span>Gérer tous les entrepôts</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="flex overflow-x-auto no-scrollbar gap-2.5 pb-1 md:grid md:grid-cols-3 lg:grid-cols-5 md:gap-4 md:pb-0">
                    @foreach($warehouses as $wh)
                        <div class="min-w-[190px] shrink-0 md:min-w-0 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs hover:border-[#0066FF]/30 transition relative group">
                            <div class="flex items-center justify-between">
                                <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-[#0B0F14]">{{ $wh->code }}</span>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('stock.warehouses.show', $wh) }}" class="text-slate-400 hover:text-[#0066FF] transition" title="Consulter l'entrepôt">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button 
                                        type="button" 
                                        @click="$dispatch('open-delete-warehouse', { 
                                            id: {{ $wh->id }}, 
                                            name: '{{ addslashes($wh->name) }}', 
                                            code: '{{ $wh->code }}', 
                                            totalStock: {{ (int) $wh->warehouseStocks->sum('physical_quantity') }},
                                            productCount: {{ (int) $wh->warehouseStocks->where('physical_quantity', '>', 0)->count() }}
                                        })"
                                        class="text-slate-400 hover:text-rose-600 transition" 
                                        title="Supprimer cet entrepôt"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            <h3 class="mt-2 text-xs font-bold text-[#0B0F14] truncate">
                                <a href="{{ route('stock.warehouses.show', $wh) }}" class="hover:text-[#0066FF]">{{ $wh->name }}</a>
                            </h3>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Physique : <strong class="text-[#0B0F14]">{{ $wh->warehouseStocks->sum('physical_quantity') }}</strong> •
                                Réservé : <strong class="text-amber-700">{{ $wh->warehouseStocks->sum('reserved_quantity') }}</strong>
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Detailed Product Matrix --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0B0F14]">
                            Catalogue Officiel des Produits & Disponibilité Réelle
                        </h2>
                        <p class="text-xs text-slate-500">
                            Gestion exclusive du catalogue d'articles, traçabilité des codes-barres, tarification et suivi en temps réel (Disponible = Physique − Réservé)
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('stock.products.print-catalog', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-[#0066FF] hover:border-[#0066FF] transition shadow-2xs">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Imprimer Catalogue PDF (A4 Paysage)</span>
                        </a>

                        <a href="{{ route('stock.products.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#0066FF] px-3.5 py-2 text-xs font-bold text-white hover:bg-blue-700 transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m8-8H4"/></svg>
                            <span>+ Nouveau Produit</span>
                        </a>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5">Référence / Code-Barres</th>
                                <th class="px-5 py-3.5">Produit & Marque</th>
                                <th class="px-5 py-3.5">Domaine</th>
                                <th class="px-5 py-3.5 text-right">Stock Physique</th>
                                <th class="px-5 py-3.5 text-right">Stock Réservé</th>
                                <th class="px-5 py-3.5 text-right text-[#0066FF]">Disponible Réel</th>
                                <th class="px-5 py-3.5 text-right">En Commande</th>
                                <th class="px-5 py-3.5 text-right">Min / Max</th>
                                <th class="px-5 py-3.5 text-right">Prix Achat / Vente</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($products as $product)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 font-mono text-[11px] font-semibold text-slate-700">
                                        <div class="text-[#0066FF] font-bold">{{ $product->sku }}</div>
                                        <div class="text-[10px] text-slate-500 font-semibold flex items-center gap-1 mt-0.5">
                                            <span class="text-slate-400">EAN:</span> {{ $product->formatted_ean }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            {{-- Vignette interactive : Clic pour prise de photo ou téléversement --}}
                                            <button 
                                                type="button" 
                                                @click="$dispatch('open-product-photo', { 
                                                    id: {{ $product->id }}, 
                                                    name: '{{ addslashes($product->name) }}', 
                                                    sku: '{{ $product->sku }}', 
                                                    current_image: '{{ $product->image ? asset('storage/' . $product->image) : '' }}', 
                                                    action_url: '{{ route('stock.products.quick-image', $product) }}' 
                                                })"
                                                class="relative group/photo cursor-pointer rounded-lg overflow-hidden border border-[#E2E8F0] shrink-0 hover:ring-2 hover:ring-[#0066FF] hover:ring-offset-1 transition-all focus:outline-hidden"
                                                title="Cliquer pour changer ou prendre la photo de l'article"
                                            >
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 object-cover">
                                                @else
                                                    <div class="w-10 h-10 bg-slate-100 flex items-center justify-center text-slate-400">
                                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    </div>
                                                @endif

                                                {{-- Overlay au survol avec icône caméra --}}
                                                <div class="absolute inset-0 bg-[#0B0F14]/50 opacity-0 group-hover/photo:opacity-100 transition-opacity flex items-center justify-center text-white">
                                                    <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                        <circle cx="12" cy="13" r="3" stroke-width="2" />
                                                    </svg>
                                                </div>

                                                {{-- Badge discret caméra --}}
                                                <div class="absolute bottom-0 right-0 p-0.5 bg-[#0066FF] text-white rounded-tl shadow-xs">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                    </svg>
                                                </div>
                                            </button>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-[#0B0F14] truncate">{{ $product->name }}</span>
                                                    @if(($product->exit_volume ?? 0) >= 15 || ($product->rotation_class ?? '') === 'tres_vendu')
                                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold text-amber-800 border border-amber-200 shrink-0">
                                                            ⭐ Top Vente
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-400">
                                                    {{ $product->brand?->name ?? 'IVOSPHERE' }} • Unité : {{ $product->unit ?? 'pièce' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                            {{ strtoupper($product->domain?->name ?? 'Général') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-[#0B0F14]">
                                        <div class="text-sm font-bold">{{ $product->current_stock }}</div>
                                        @if($product->warehouseStocks->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 justify-end mt-1">
                                                @foreach($product->warehouseStocks as $ws)
                                                    @if($ws->physical_quantity > 0)
                                                        <span class="inline-flex items-center text-[10px] font-medium px-1.5 py-0.2 rounded bg-slate-100 text-slate-700" title="{{ $ws->warehouse?->name ?? 'Entrepôt' }}">
                                                            {{ $ws->warehouse?->code ?: Str::limit($ws->warehouse?->name, 8) }}: <strong class="ml-0.5 text-[#0B0F14]">{{ $ws->physical_quantity }}</strong>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-amber-700">
                                        {{ $product->reserved_stock }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold {{ $product->available_stock <= 0 ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-[#0066FF]' }}">
                                            {{ $product->available_stock }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-slate-600">
                                        +{{ $product->incoming_stock }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-slate-500">
                                        {{ $product->min_stock }} / {{ $product->effective_max_stock }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="font-semibold text-[#0B0F14]">{{ number_format((float) $product->selling_price, 0, ',', ' ') }} FCFA</div>
                                        <div class="text-[10px] text-slate-400">Coût : {{ number_format((float) ($product->purchase_price ?: $product->selling_price * 0.65), 0, ',', ' ') }} FCFA</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Bouton Ajuster Stock & Entrepôt --}}
                                            <button 
                                                type="button" 
                                                @click="$dispatch('open-stock-adjust', { 
                                                    id: {{ $product->id }}, 
                                                    name: '{{ addslashes($product->name) }}', 
                                                    sku: '{{ $product->sku }}', 
                                                    unit: '{{ addslashes($product->unit ?? 'pièce') }}', 
                                                    current_stock: {{ $product->current_stock }}, 
                                                    stocks: {{ json_encode($product->warehouseStocks->map(fn($ws) => [
                                                        'warehouse_id' => $ws->warehouse_id,
                                                        'warehouse_name' => $ws->warehouse?->name ?? 'Entrepôt',
                                                        'warehouse_code' => $ws->warehouse?->code ?? '',
                                                        'physical_quantity' => $ws->physical_quantity,
                                                    ])) }} 
                                                })"
                                                class="p-1.5 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition border border-transparent hover:border-emerald-200"
                                                title="Ajuster le stock & entrepôt"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                                </svg>
                                            </button>

                                            {{-- Bouton Modal QR Code Produit --}}
                                            <button 
                                                type="button" 
                                                @click="$dispatch('open-product-qr', { 
                                                    id: {{ $product->id }}, 
                                                    name: '{{ addslashes($product->name) }}', 
                                                    sku: '{{ $product->sku }}', 
                                                    barcode: '{{ $product->ean }}', 
                                                    price: '{{ number_format((float) $product->selling_price, 0, ',', ' ') }} FCFA', 
                                                    domain: '{{ addslashes($product->domain?->name ?? 'Général') }}', 
                                                    unit: '{{ addslashes($product->unit ?? 'pièce') }}', 
                                                    qrDownloadUrl: '{{ route('stock.products.qr-download', $product) }}',
                                                    labelDownloadUrl: '{{ route('stock.products.qr-download', ['product' => $product, 'label' => 1]) }}'
                                                })"
                                                class="p-1.5 rounded-lg text-[#0066FF] hover:bg-blue-50 transition border border-transparent hover:border-blue-200"
                                                title="Afficher le Code QR et barcode EAN"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                                </svg>
                                            </button>

                                            {{-- Téléchargement Direct PNG --}}
                                            <a 
                                                href="{{ route('stock.products.qr-download', $product) }}" 
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-[#0066FF] hover:bg-slate-100 transition border border-transparent hover:border-slate-200" 
                                                title="Télécharger le QR Code en PNG"
                                                download
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>

                                            <a href="{{ route('stock.products.edit', $product) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-[#0B0F14] hover:bg-slate-100 transition border border-transparent hover:border-slate-200" title="Modifier la fiche article">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Minimalist, Épurée & Élégante Cards View (< md) -->
                <div class="block md:hidden">
                    {{-- Minimalist Mobile Search & Filter Toolbar --}}
                    <div class="p-3 bg-slate-50/80 border-b border-slate-100 space-y-2.5">
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" x-model="mobileSearch" placeholder="Rechercher par nom, SKU, code-barres..."
                                   class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 bg-white placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] shadow-2xs">
                            <button x-show="mobileSearch.length > 0" @click="mobileSearch = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">✕</button>
                        </div>
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 text-[11px]">
                            <button type="button" @click="mobileFilterStatus = 'all'"
                                    :class="mobileFilterStatus === 'all' ? 'bg-[#0B0F14] text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200/80'"
                                    class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition">
                                Tous ({{ $products->count() }})
                            </button>
                            <button type="button" @click="mobileFilterStatus = 'in_stock'"
                                    :class="mobileFilterStatus === 'in_stock' ? 'bg-[#0066FF] text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200/80'"
                                    class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition">
                                En stock
                            </button>
                            <button type="button" @click="mobileFilterStatus = 'low_stock'"
                                    :class="mobileFilterStatus === 'low_stock' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200/80'"
                                    class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition">
                                Faible ({{ $lowStockCount }})
                            </button>
                            <button type="button" @click="mobileFilterStatus = 'out_of_stock'"
                                    :class="mobileFilterStatus === 'out_of_stock' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200/80'"
                                    class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition">
                                Rupture ({{ $outOfStockCount }})
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-200/60">
                            <a href="{{ route('stock.products.print-catalog', request()->query()) }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg border border-slate-200 bg-white text-[11px] font-bold text-slate-700 hover:text-[#0066FF] shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Imprimer Catalogue PDF (A4 Paysage)</span>
                            </a>
                        </div>
                    </div>

                    {{-- Products List --}}
                    <div class="p-3 space-y-2.5">
                        @forelse($products as $product)
                            @php
                                $searchString = strtolower($product->name . ' ' . $product->sku . ' ' . ($product->barcode ?? '') . ' ' . ($product->brand?->name ?? ''));
                                $minStockVal = max(1, (int)$product->min_stock);
                                $availStock = (int)$product->available_stock;
                            @endphp
                            <div x-show="(mobileSearch === '' || '{{ addslashes($searchString) }}'.includes(mobileSearch.toLowerCase())) && (mobileFilterStatus === 'all' || (mobileFilterStatus === 'in_stock' && {{ $availStock }} > {{ $minStockVal }}) || (mobileFilterStatus === 'low_stock' && {{ $availStock }} > 0 && {{ $availStock }} <= {{ $minStockVal }}) || (mobileFilterStatus === 'out_of_stock' && {{ $availStock }} <= 0))"
                                 class="rounded-2xl border border-slate-200/70 bg-white p-3.5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] transition hover:border-slate-300">
                                
                                {{-- Card Header: Thumbnail + Title + Status Pill --}}
                                <div class="flex items-start gap-3">
                                    {{-- Vignette interactive Mobile : Clic pour prise de photo ou téléversement --}}
                                    <button 
                                        type="button" 
                                        @click="$dispatch('open-product-photo', { 
                                            id: {{ $product->id }}, 
                                            name: '{{ addslashes($product->name) }}', 
                                            sku: '{{ $product->sku }}', 
                                            current_image: '{{ $product->image ? asset('storage/' . $product->image) : '' }}', 
                                            action_url: '{{ route('stock.products.quick-image', $product) }}' 
                                        })"
                                        class="relative group/photo cursor-pointer rounded-xl overflow-hidden border border-slate-200 shrink-0 hover:ring-2 hover:ring-[#0066FF] hover:ring-offset-1 transition-all focus:outline-hidden"
                                        title="Prendre une photo ou importer un visuel"
                                    >
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover">
                                        @else
                                            <div class="w-12 h-12 bg-slate-100 flex items-center justify-center text-slate-400">
                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                        @endif
                                        <div class="absolute bottom-0 right-0 p-0.5 bg-[#0066FF] text-white rounded-tl shadow-xs">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            </svg>
                                        </div>
                                    </button>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1.5">
                                            <span class="font-mono text-[10px] font-bold text-slate-500 uppercase tracking-tight">
                                                {{ $product->sku }}
                                            </span>
                                            @if($availStock <= 0)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200/60 shrink-0">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                    Rupture
                                                </span>
                                            @elseif($availStock <= $minStockVal)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200/60 shrink-0">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    Dispo : {{ $availStock }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200/60 shrink-0">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Dispo : {{ $availStock }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <h3 class="text-xs font-bold text-[#0B0F14] leading-snug line-clamp-1">
                                                {{ $product->name }}
                                            </h3>
                                            @if(($product->exit_volume ?? 0) >= 15 || ($product->rotation_class ?? '') === 'tres_vendu')
                                                <span class="inline-flex items-center rounded bg-amber-50 px-1 py-0.2 text-[9px] font-bold text-amber-800 border border-amber-200 shrink-0">
                                                    ⭐ Top
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-slate-400">
                                            <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[9px] font-semibold text-slate-600">
                                                {{ strtoupper($product->domain?->name ?? 'Général') }}
                                            </span>
                                            <span>•</span>
                                            <span>{{ $product->brand?->name ?? 'IVOSPHERE' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sleek Minimalist 3-Column Micro-Stats Bar --}}
                                <div class="mt-3 grid grid-cols-3 divide-x divide-slate-100 rounded-xl bg-slate-50/70 p-2 text-center text-xs">
                                    <div class="px-1">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Physique</span>
                                        <span class="text-xs font-bold text-[#0B0F14]">{{ $product->current_stock }}</span>
                                    </div>
                                    <div class="px-1">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Réservé</span>
                                        <span class="text-xs font-bold text-amber-700">{{ $product->reserved_stock }}</span>
                                    </div>
                                    <div class="px-1">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Prix Vente</span>
                                        <span class="text-xs font-bold text-[#0066FF]">{{ number_format((float) $product->selling_price, 0, ',', ' ') }} F</span>
                                    </div>
                                </div>

                                @if($product->warehouseStocks->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-1 items-center px-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Dépôts :</span>
                                        @foreach($product->warehouseStocks as $ws)
                                            @if($ws->physical_quantity > 0)
                                                <span class="inline-flex items-center text-[10px] font-medium px-1.5 py-0.2 rounded bg-slate-100 text-slate-700">
                                                    {{ $ws->warehouse?->code ?: Str::limit($ws->warehouse?->name, 8) }}: <strong class="ml-1 text-[#0B0F14]">{{ $ws->physical_quantity }}</strong>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Card Footer Action --}}
                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <div class="font-mono text-[10px] text-slate-500 font-semibold">
                                        EAN: {{ $product->formatted_ean }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            @click="$dispatch('open-stock-adjust', { 
                                                id: {{ $product->id }}, 
                                                name: '{{ addslashes($product->name) }}', 
                                                sku: '{{ $product->sku }}', 
                                                unit: '{{ addslashes($product->unit ?? 'pièce') }}', 
                                                current_stock: {{ $product->current_stock }}, 
                                                stocks: {{ json_encode($product->warehouseStocks->map(fn($ws) => [
                                                    'warehouse_id' => $ws->warehouse_id,
                                                    'warehouse_name' => $ws->warehouse?->name ?? 'Entrepôt',
                                                    'warehouse_code' => $ws->warehouse?->code ?? '',
                                                    'physical_quantity' => $ws->physical_quantity,
                                                ])) }} 
                                            })"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:underline"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                            <span>Ajuster</span>
                                        </button>

                                        <button 
                                            type="button" 
                                            @click="$dispatch('open-product-qr', { 
                                                id: {{ $product->id }}, 
                                                name: '{{ addslashes($product->name) }}', 
                                                sku: '{{ $product->sku }}', 
                                                barcode: '{{ $product->ean }}', 
                                                price: '{{ number_format((float) $product->selling_price, 0, ',', ' ') }} FCFA', 
                                                domain: '{{ addslashes($product->domain?->name ?? 'Général') }}', 
                                                unit: '{{ addslashes($product->unit ?? 'pièce') }}', 
                                                qrDownloadUrl: '{{ route('stock.products.qr-download', $product) }}',
                                                labelDownloadUrl: '{{ route('stock.products.qr-download', ['product' => $product, 'label' => 1]) }}'
                                            })"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-[#0066FF] hover:underline"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            <span>QR Code</span>
                                        </button>

                                        <a href="{{ route('stock.products.edit', $product) }}"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-[#0066FF] transition">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Fiche</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                Aucun produit répertorié.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- =======================================================================
             TAB 3: TRANSFERTS INTER-ENTREPÔTS & INVENTAIRE PHYSIQUE
             ======================================================================= --}}
        <div x-show="activeTab === 'transferts'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Column 1: Inter-Warehouse Transfers --}}
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                    <h2 class="text-base font-bold text-[#0B0F14]">Nouveau Transfert entre Entrepôts</h2>
                    <p class="mt-1 text-xs text-slate-500">Déplacer du stock de l'Entrepôt Général vers une boutique ou un studio métier.</p>

                    <form action="{{ route('stock.transfers.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Entrepôt Source</label>
                                <select name="source_warehouse_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Entrepôt Destination</label>
                                <select name="destination_warehouse_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                    @foreach($warehouses->reverse() as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">Produit à transférer</label>
                                <select name="product_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (Dispo: {{ $p->current_stock }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Quantité</label>
                                <input type="number" name="quantity" min="1" value="5" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Motif / Observation</label>
                            <input type="text" name="notes" placeholder="Ex: Réapprovisionnement Boutique PRINT ou Studio MEDIA" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="execute_immediately" value="1" checked class="rounded border-slate-300 text-[#0066FF]">
                                <span>Exécuter & réceptionner immédiatement</span>
                            </label>
                            <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                Valider le Transfert
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Transfers History --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-[#0B0F14]">Ordres de Transferts Récents</h3>
                    <div class="mt-4 divide-y divide-slate-100">
                        @forelse($transfers as $trf)
                            <div class="flex items-center justify-between py-3 text-xs">
                                <div>
                                    <span class="font-bold text-[#0B0F14]">{{ $trf->reference }}</span>
                                    <span class="ml-2 text-slate-600">{{ $trf->product?->name }} (x{{ $trf->quantity }})</span>
                                    <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                        <span>{{ $trf->sourceWarehouse?->name }}</span>
                                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                        <span>{{ $trf->destinationWarehouse?->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase text-slate-700">
                                        {{ $trf->status }}
                                    </span>
                                    @if($trf->status !== 'receptionne')
                                        <form action="{{ route('stock.transfers.status', $trf) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="receptionne">
                                            <button type="submit" class="rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[11px] font-semibold text-white">
                                                Réceptionner
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-xs text-slate-400">Aucun transfert enregistré.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Column 2: Physical Inventory & Automatic Adjustment --}}
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                    <h2 class="text-base font-bold text-[#0B0F14]">Inventaire Physique & Régularisation d'Écart</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Comparez le <strong>Stock Système</strong> au <strong>Stock Compté Réel</strong>. L'écart est automatiquement régularisé.
                    </p>

                    <form action="{{ route('stock.inventories.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Entrepôt contrôlé</label>
                                <select name="warehouse_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Quantité Réelle Comptée</label>
                                <input type="number" name="counted_quantity" min="0" value="45" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Produit contrôlé</label>
                            <select name="product_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (Système actuel: {{ $p->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Justification de l'écart (Casse, perte, erreur saisie...)</label>
                            <input type="text" name="reason" placeholder="Ex: Comptage physique mensuel - écart constaté en rayon" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>

                        <input type="hidden" name="auto_validate" value="1">

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-[#0B0F14] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0066FF]">
                                Valider Comptage & Ajuster le Stock
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Recent Inventories --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-[#0B0F14]">Procès-Verbaux d'Inventaires Physiques</h3>
                    <div class="mt-4 divide-y divide-slate-100">
                        @forelse($inventories as $inv)
                            <div class="py-3 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[#0B0F14]">{{ $inv->reference }} — {{ $inv->warehouse?->name }}</span>
                                    <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">{{ $inv->status }}</span>
                                </div>
                                @foreach($inv->items as $item)
                                    <div class="mt-1.5 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5 text-[11px]">
                                        <span class="font-medium text-slate-700">{{ $item->product?->name }}</span>
                                        <span>
                                            Système : <strong>{{ $item->system_quantity }}</strong> •
                                            Compté : <strong>{{ $item->real_quantity }}</strong> •
                                            Écart :
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
                </div>
            </div>
        </div>

        {{-- =======================================================================
             TAB 4: DEMANDES D'ACHAT (WORKFLOW) & RÉCEPTIONS FOURNISSEURS (BL)
             ======================================================================= --}}
        <div x-show="activeTab === 'achats'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Purchase Requests Workflow --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0B0F14]">Workflow Demandes d'Achat (DA)</h2>
                        <p class="inline-flex items-center gap-1 text-xs text-slate-500">
                            <span>Employé</span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            <span>Responsable</span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            <span>Finance</span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            <span>Commande Fournisseur</span>
                        </p>
                    </div>
                </div>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($purchaseRequests as $pr)
                        <div class="py-3.5 text-xs">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-[#0B0F14]">{{ $pr->reference }}</span>
                                    <span class="ml-2 font-semibold text-slate-800">{{ $pr->product?->name }}</span>
                                    <span class="ml-1 text-[#0066FF] font-bold">(x{{ $pr->quantity }})</span>
                                </div>
                                <span class="rounded-md bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase text-[#0066FF]">
                                    {{ str_replace('_', ' ', $pr->status) }}
                                </span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Fournisseur : {{ $pr->supplier?->name ?? 'À définir' }} • Coût estimé : {{ number_format($pr->quantity * (float) $pr->estimated_unit_price, 0, ',', ' ') }} FCFA</span>
                                @php
                                    $nextStatus = match($pr->status) {
                                        'en_attente_responsable', 'soumis' => 'en_attente_finance',
                                        'en_attente_finance', 'valide_responsable' => 'approuve_achat',
                                        'approuve_achat', 'valide_finance' => 'commande_passee',
                                        'commande_passee', 'commande_fournisseur' => 'receptionne',
                                        default => null,
                                    };
                                @endphp
                                @if($nextStatus)
                                    <form action="{{ route('stock.purchase-requests.status', $pr) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-[#0B0F14] px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-[#0066FF]">
                                            <span>Valider</span>
                                            <svg class="h-3 w-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                            <span>{{ str_replace('_', ' ', $nextStatus) }}</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-xs text-slate-400">Aucune demande d'achat en cours.</p>
                    @endforelse
                </div>
            </div>

            {{-- Supplier Reception & Quality Control --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[#0B0F14]">Réception Fournisseur & Contrôle Qualité</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Contrôlez les quantités commandées vs reçues, les produits endommagés et générez automatiquement l'entrée en stock + numéro de lot.
                </p>

                <form action="{{ route('stock.receptions.store') }}" method="POST" class="mt-4 space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Entrepôt de réception</label>
                            <select name="warehouse_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Fournisseur</label>
                            <select name="supplier_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Produit réceptionné</label>
                        <select name="product_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Qté Commandée</label>
                            <input type="number" name="ordered_quantity" min="1" value="50" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Qté Reçue Conforme</label>
                            <input type="number" name="received_quantity" min="0" value="50" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Qté Endommagée</label>
                            <input type="number" name="damaged_quantity" min="0" value="0" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Contrôle Qualité</label>
                            <select name="quality_status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                <option value="conforme">Conforme (100%)</option>
                                <option value="partiel">Réception Partielle</option>
                                <option value="non_conforme">Non Conforme</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">N° de Lot / Batch</label>
                            <input type="text" name="batch_number" placeholder="LOT-2026-X" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Date d'expiration</label>
                            <input type="date" name="expiration_date" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                            Valider Réception & Entrer en Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =======================================================================
             TAB 5: LOTS, SÉRIES/IMEI, DATES D'EXPIRATION & MAINTENANCE PARC (MEDIA/TECH)
             ======================================================================= --}}
        <div x-show="activeTab === 'tracabilite'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Batches, Serials & Expirations --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[#0B0F14]">Traçabilité Lots, N° de Série / IMEI & Dates d'Expiration</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Suivi unitaire des équipements (TECH / MEDIA) et dates de péremption encres/chimie (PRINT).
                </p>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <div class="flex items-center justify-between py-3 text-xs">
                            <div>
                                <div class="font-bold text-[#0B0F14]">
                                    {{ $batch->product?->name }}
                                    <span class="ml-1 rounded bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-700">
                                        Lot: {{ $batch->batch_number }}
                                    </span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-slate-500">
                                    @if($batch->serial_number) N° Série : <strong>{{ $batch->serial_number }}</strong> • @endif
                                    @if($batch->imei) IMEI : <strong>{{ $batch->imei }}</strong> • @endif
                                    Garantie : {{ $batch->warranty_months }} mois
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-700">
                                    {{ str_replace('_', ' ', $batch->status) }}
                                </span>
                                @if($batch->expiration_date)
                                    @php $daysLeft = (int) now()->diffInDays($batch->expiration_date, false); @endphp
                                    <div class="mt-1 text-[11px] font-semibold {{ $daysLeft <= 30 ? 'text-rose-600' : ($daysLeft <= 90 ? 'text-amber-600' : 'text-slate-500') }}">
                                        Exp: {{ $batch->expiration_date->format('d/m/Y') }} ({{ $daysLeft }}j)
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-xs text-slate-400">Aucun lot ou numéro de série enregistré.</p>
                    @endforelse
                </div>
            </div>

            {{-- Equipment Maintenance & Rental Fleet Status --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[#0B0F14]">Maintenance du Parc & Matériel Louable (MEDIA / TECH)</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Suivi des réparations, calibrations et remise en disponibilité du matériel de location.
                </p>

                <form action="{{ route('stock.maintenances.store') }}" method="POST" class="mt-4 space-y-3 border-b border-slate-100 pb-5">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Équipement / Produit</label>
                            <select name="product_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Type d'intervention</label>
                            <select name="type" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                <option value="preventive">Maintenance Préventive / Révision</option>
                                <option value="reparation">Réparation Panne</option>
                                <option value="calibration">Calibration Optique / Audio</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input type="text" name="serial_number" placeholder="N° de Série (ex: SN-SONY-884920)" class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        <input type="text" name="issue_description" required placeholder="Diagnostic / Motif d'immobilisation" class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-[#0B0F14] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0066FF]">
                            Placer en Maintenance
                        </button>
                    </div>
                </form>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($maintenances as $m)
                        <div class="flex items-center justify-between py-3 text-xs">
                            <div>
                                <div class="font-bold text-[#0B0F14]">
                                    {{ $m->reference }} — {{ $m->product?->name }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $m->issue_description }} • Technicien : {{ $m->technician_name }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $m->status === 'termine' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ str_replace('_', ' ', $m->status) }}
                                </span>
                                @if($m->status !== 'termine')
                                    <form action="{{ route('stock.maintenances.complete', $m) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg bg-[#0066FF] px-2.5 py-1 text-[10px] font-semibold text-white">
                                            Remettre Disponible
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-xs text-slate-400">Aucune maintenance en cours.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- Modal d'Ajustement Rapide de Stock & Entrepôt --}}
    <x-stock-adjust-modal :warehouses="$warehouses" />

    {{-- Modal de confirmation / transfert pour suppression d'entrepôt --}}
    <x-delete-warehouse-modal :warehouses="$warehouses" />

    {{-- Modal QR Code Produit --}}
    <x-product-qr-modal />

    {{-- Modal Prise de Photo Directe & Upload Image Produit --}}
    <x-product-quick-photo-modal />
</x-layouts.app>