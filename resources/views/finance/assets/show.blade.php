@php
    $inputClass = 'mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 focus:outline-none';
    $labelClass = 'block text-xs font-semibold uppercase tracking-wider text-slate-700';

    $statusBadge = match($asset->status) {
        'en_service' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'En service'],
        'disponible' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'label' => 'Disponible'],
        'loue' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'label' => 'Actuellement loué'],
        'en_maintenance' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'label' => 'En maintenance'],
        'a_reparer' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'label' => 'À réparer'],
        'hors_service' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'label' => 'Hors service'],
        'vendu' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'label' => 'Vendu'],
        'cede' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'label' => 'Cédé'],
        default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'label' => ucfirst($asset->status)],
    };

    $roiBadge = match($asset->profitability_status) {
        'super_rentable' => ['badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'label' => 'Super Rentable (Amorti à 100%+)'],
        'rentable' => ['badge' => 'bg-blue-100 text-blue-800 border-blue-300', 'label' => 'Actif Rentable (Bénéfice positif)'],
        'en_amortissement' => ['badge' => 'bg-amber-100 text-amber-800 border-amber-300', 'label' => 'En cours d\'amortissement'],
        default => ['badge' => 'bg-slate-100 text-slate-700 border-slate-300', 'label' => 'Support interne'],
    };
@endphp

<x-layouts.app :title="'Fiche Immobilisation — ' . $asset->code">
    <div x-data="{
        activeTab: 'amortissement',
        usageModal: false,
        maintenanceModal: false,
        disposeModal: false,
        rentalModal: false,
    }" class="mx-auto w-full max-w-7xl space-y-6 pb-20">

        {{-- ── Fil d'Ariane & En-tête ─────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                    <a href="{{ route('finance.index') }}" class="hover:text-[#0066FF] transition-colors">Finance</a>
                    <span>/</span>
                    <a href="{{ route('finance.assets.index') }}" class="hover:text-[#0066FF] transition-colors">Immobilisations & Actifs</a>
                    <span>/</span>
                    <span class="font-mono font-bold text-slate-900">{{ $asset->code }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        {{ $asset->name }}
                    </h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $roiBadge['badge'] }}">
                        {{ $roiBadge['label'] }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                    <span class="font-mono font-semibold text-slate-700">Code: {{ $asset->code }}</span>
                    <span>•</span>
                    <span class="font-medium text-slate-700">{{ $asset->domain?->name ?? 'Général' }}</span>
                    <span>•</span>
                    <span>Catégorie : {{ $asset->category?->name ?? 'N/A' }}</span>
                    @if($asset->brand || $asset->model)
                        <span>•</span>
                        <span>{{ $asset->brand }} {{ $asset->model }}</span>
                    @endif
                    @if($asset->serial_number)
                        <span>•</span>
                        <span>S/N : <span class="font-mono text-slate-700">{{ $asset->serial_number }}</span></span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('finance.assets.edit', $asset) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Modifier
                </a>

                @if(!in_array($asset->status, ['vendu', 'cede', 'hors_service']))
                    <button type="button" @click="disposeModal = true" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 text-xs font-semibold text-rose-700 shadow-xs hover:bg-rose-100 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Sortie d'actif / Vente
                    </button>
                @endif

                <a href="{{ route('finance.assets.index') }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                    ← Retour au registre
                </a>
            </div>
        </div>

        {{-- ── Messages flash ─────────────────────────────────────────────── --}}
        @if(session('success'))
            <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-xs">
                <svg class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div role="alert" class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 shadow-xs">
                <svg class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ── 3 NOTIONS FONDAMENTALES DE L'ACTIF ───────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Notion A : Valeur de l'Actif --}}
            <div class="rounded-2xl border border-blue-200/80 bg-white p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-blue-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white">A</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-blue-900">Valeur de l'Actif</h2>
                    </div>
                    <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">Patrimoine</span>
                </div>
                <div class="mt-4 space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Valeur d'Acquisition (Achat + Frais)</p>
                        <p class="text-2xl font-bold text-slate-900 tabular-nums">
                            {{ number_format((float) $asset->acquisition_value, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-slate-100 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Amortissements cumulés :</span>
                            <span class="font-semibold text-rose-600 tabular-nums">-{{ number_format($asset->accumulated_depreciation, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700 font-medium">Valeur Nette Comptable (VNC) :</span>
                            <span class="font-bold text-blue-700 tabular-nums text-sm">{{ number_format($asset->net_book_value, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Durée & Méthode :</span>
                            <span class="font-medium text-slate-700">{{ $asset->useful_life_years }} ans ({{ ucfirst($asset->depreciation_method) }})</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notion B : Chiffre d'Affaires Généré --}}
            <div class="rounded-2xl border border-emerald-200/80 bg-white p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-600 text-xs font-bold text-white">B</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-900">CA Facturé Associé</h2>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">Revenus</span>
                </div>
                <div class="mt-4 space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Total Chiffre d'Affaires Généré</p>
                        <p class="text-2xl font-bold text-emerald-700 tabular-nums">
                            {{ number_format($asset->total_revenue_generated, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-slate-100 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Prestations de services :</span>
                            <span class="font-semibold text-slate-900 tabular-nums">{{ number_format($asset->total_usage_revenue, 0, ',', ' ') }} FCFA ({{ $asset->usages->count() }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Locations externes :</span>
                            <span class="font-semibold text-purple-700 tabular-nums">{{ number_format($asset->total_rental_revenue, 0, ',', ' ') }} FCFA ({{ $asset->rentals->count() }})</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Éligibilité location :</span>
                            <span class="font-medium {{ $asset->is_rental_eligible ? 'text-purple-700' : 'text-slate-500' }}">
                                {{ $asset->is_rental_eligible ? number_format((float) $asset->rental_price_per_day, 0, ',', ' ') . ' FCFA / jour' : 'Usage interne uniquement' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notion C : Rentabilité & ROI --}}
            <div class="rounded-2xl border border-amber-200/80 bg-white p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-amber-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-amber-600 text-xs font-bold text-white">C</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-amber-900">Rentabilité & ROI</h2>
                    </div>
                    <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">Bénéfice</span>
                </div>
                <div class="mt-4 space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Contribution Nette (CA − Maint. − Amort.)</p>
                        <p class="text-2xl font-bold {{ $asset->net_profitability >= 0 ? 'text-slate-900' : 'text-rose-600' }} tabular-nums">
                            {{ $asset->net_profitability >= 0 ? '+' : '' }}{{ number_format($asset->net_profitability, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                        </p>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-slate-100 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Coûts de maintenance :</span>
                            <span class="font-semibold text-rose-600 tabular-nums">-{{ number_format($asset->total_maintenance_costs, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-700 font-medium">Taux de Retour (ROI) :</span>
                            <span class="font-bold text-emerald-700 tabular-nums text-sm">{{ $asset->roi_percentage }}%</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Interventions :</span>
                            <span class="font-medium text-slate-700">{{ $asset->maintenances->count() }} maintenance(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BARRE D'ONGLETS DU MATÉRIEL ───────────────────────────────── --}}
        <div class="border-b border-slate-200">
            <nav class="flex gap-2 overflow-x-auto" aria-label="Onglets Fiche Matériel">
                <button type="button" @click="activeTab = 'amortissement'"
                        :class="activeTab === 'amortissement' ? 'border-[#0066FF] text-[#0066FF]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="whitespace-nowrap border-b-2 py-3 px-3.5 text-sm font-semibold transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    1. Tableau d'Amortissement
                </button>

                <button type="button" @click="activeTab = 'usages'"
                        :class="activeTab === 'usages' ? 'border-[#0066FF] text-[#0066FF]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="whitespace-nowrap border-b-2 py-3 px-3.5 text-sm font-semibold transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    2. Prestations & Usages
                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $asset->usages->count() }}</span>
                </button>

                @if($asset->is_rental_eligible)
                    <button type="button" @click="activeTab = 'rentals'"
                            :class="activeTab === 'rentals' ? 'border-[#0066FF] text-[#0066FF]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            class="whitespace-nowrap border-b-2 py-3 px-3.5 text-sm font-semibold transition flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        3. Locations Clients
                        <span class="ml-1 rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700">{{ $asset->rentals->count() }}</span>
                    </button>
                @endif

                <button type="button" @click="activeTab = 'maintenances'"
                        :class="activeTab === 'maintenances' ? 'border-[#0066FF] text-[#0066FF]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="whitespace-nowrap border-b-2 py-3 px-3.5 text-sm font-semibold transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                    4. Carnet de Maintenance
                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $asset->maintenances->count() }}</span>
                </button>

                <button type="button" @click="activeTab = 'identification'"
                        :class="activeTab === 'identification' ? 'border-[#0066FF] text-[#0066FF]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="whitespace-nowrap border-b-2 py-3 px-3.5 text-sm font-semibold transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                    5. Tag & Inventaire QR
                </button>
            </nav>
        </div>

        {{-- ═════════════ 1. TABLEAU D'AMORTISSEMENT ═════════════ --}}
        <div x-show="activeTab === 'amortissement'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Échéancier Prévisionnel d'Amortissement</h3>
                    <p class="text-xs text-slate-500">Calcul linéaire annuel basé sur une durée de {{ $asset->useful_life_years }} an(s) (Syscohada).</p>
                </div>
                <div class="text-xs text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl font-medium">
                    Dotation annuelle : <span class="font-bold text-slate-900">{{ number_format($asset->annual_depreciation, 0, ',', ' ') }} FCFA</span> / an
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Année</th>
                            <th class="px-4 py-3 text-right">Base Amortissable</th>
                            <th class="px-4 py-3 text-right">Dotation Annuelle</th>
                            <th class="px-4 py-3 text-right">Amort. Cumulés</th>
                            <th class="px-4 py-3 text-right">VNC Fin d'Exercice</th>
                            <th class="px-5 py-3 text-center">Statut Exercice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($asset->depreciations as $dep)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900">{{ $dep->year }}</td>
                                <td class="px-4 py-3.5 text-right font-medium tabular-nums">{{ number_format((float) $dep->base_value, 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-rose-600 tabular-nums">{{ number_format((float) $dep->depreciation_amount, 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3.5 text-right font-medium tabular-nums text-slate-700">{{ number_format((float) $dep->cumulative_amount, 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3.5 text-right font-bold text-blue-700 tabular-nums">{{ number_format((float) $dep->net_book_value, 0, ',', ' ') }} FCFA</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $dep->year <= date('Y') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-600 border border-slate-200' }}">
                                        {{ $dep->year <= date('Y') ? 'Exercice Échu' : 'Prévisionnel' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Cet actif est déclaré comme non amortissable ou la durée d'utilisation est nulle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═════════════ 2. PRESTATIONS & USAGES ═════════════ --}}
        <div x-show="activeTab === 'usages'" class="space-y-4" x-cloak>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Prestations de Service & Chiffre d'Affaires Associé</h3>
                    <p class="text-xs text-slate-500">Attribution des revenus commerciaux où ce matériel a été utilisé pour produire un service.</p>
                </div>
                <button type="button" @click="usageModal = true" class="inline-flex items-center gap-1.5 rounded-xl bg-[#0066FF] px-3.5 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    + Enregistrer une prestation
                </button>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-4 py-3">Prestation / Mission</th>
                            <th class="px-4 py-3">Client Associé</th>
                            <th class="px-4 py-3 text-center">Durée</th>
                            <th class="px-4 py-3 text-right">CA Facturé Associé</th>
                            <th class="px-5 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($asset->usages as $usage)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-medium text-slate-900 whitespace-nowrap">{{ $usage->date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $usage->title }}</td>
                                <td class="px-4 py-3.5">{{ $usage->customer?->company_name ?? $usage->customer?->full_name ?? 'Client comptoir' }}</td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">{{ $usage->duration_hours ? $usage->duration_hours . ' h' : '-' }}</td>
                                <td class="px-4 py-3.5 text-right font-bold text-emerald-700 tabular-nums whitespace-nowrap">
                                    {{ number_format((float) $usage->revenue_generated, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">{{ $usage->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Aucune prestation directe enregistrée sur ce matériel pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═════════════ 3. LOCATIONS CLIENTS ═════════════ --}}
        @if($asset->is_rental_eligible)
            <div x-show="activeTab === 'rentals'" class="space-y-4" x-cloak>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Historique des Locations Clients</h3>
                        <p class="text-xs text-slate-500">Mise à disposition externe avec tarification journalière et suivi des cautions.</p>
                    </div>
                    <button type="button" @click="rentalModal = true" class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-purple-700 transition shadow-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        + Louer ce matériel
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Contrat</th>
                                <th class="px-4 py-3">Client</th>
                                <th class="px-4 py-3">Période Location</th>
                                <th class="px-4 py-3 text-center">Jours</th>
                                <th class="px-4 py-3 text-right">Montant Total</th>
                                <th class="px-4 py-3 text-right">Caution</th>
                                <th class="px-5 py-3 text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($asset->rentals as $rental)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $rental->reference }}</td>
                                    <td class="px-4 py-3.5 font-medium text-slate-800">{{ $rental->customer?->company_name ?? $rental->customer?->full_name ?? 'Client' }}</td>
                                    <td class="px-4 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                                        Du {{ $rental->start_date?->format('d/m/Y') }} au {{ $rental->end_date?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-slate-700">{{ $rental->total_days }} j</td>
                                    <td class="px-4 py-3.5 text-right font-bold text-purple-700 tabular-nums whitespace-nowrap">
                                        {{ number_format((float) $rental->total_amount, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <span class="text-xs {{ $rental->deposit_returned ? 'text-slate-400 line-through' : 'text-slate-900 font-semibold' }}">
                                            {{ number_format((float) $rental->deposit_amount, 0, ',', ' ') }} FCFA
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ match($rental->status) { 'loue' => 'bg-purple-50 text-purple-700 border border-purple-200', 'cloture' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'retourne_controle' => 'bg-amber-50 text-amber-700 border border-amber-200', default => 'bg-slate-50 text-slate-600 border border-slate-200' } }}">
                                            {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                        Aucune location client enregistrée pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ═════════════ 4. CARNET DE MAINTENANCE ═════════════ --}}
        <div x-show="activeTab === 'maintenances'" class="space-y-4" x-cloak>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Carnet d'Entretien, Pannes & Réparations</h3>
                    <p class="text-xs text-slate-500">Traçabilité complète des interventions et des pièces d'usure remplacées.</p>
                </div>
                <button type="button" @click="maintenanceModal = true" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-amber-700 transition shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    + Enregistrer une intervention
                </button>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Réf / Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Prestataire / Réparateur</th>
                            <th class="px-4 py-3 text-right">Coût Facturé</th>
                            <th class="px-4 py-3">Description & Pièces</th>
                            <th class="px-4 py-3 text-center">Statut</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($asset->maintenances as $maint)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900 block text-xs">{{ $maint->reference }}</span>
                                    <span class="text-xs text-slate-500">{{ $maint->maintenance_date?->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $maint->type === 'curative' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ ucfirst($maint->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-800">{{ $maint->provider_name ?? $maint->supplier?->name ?? 'Interne' }}</td>
                                <td class="px-4 py-3.5 text-right font-bold text-rose-600 tabular-nums whitespace-nowrap">
                                    {{ number_format((float) $maint->cost, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-700">
                                    <p class="font-medium line-clamp-1">{{ $maint->description }}</p>
                                    @if($maint->parts_replaced)
                                        <p class="text-slate-500 mt-0.5"><span class="font-medium">Pièces :</span> {{ $maint->parts_replaced }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $maint->status === 'terminee' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ ucfirst($maint->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @if($maint->status !== 'terminee')
                                        <form method="POST" action="{{ route('finance.assets.maintenances.complete', $maint) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                                Terminer
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">Archivée</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    Aucune maintenance ni panne enregistrée pour ce matériel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═════════════ 5. TAG D'INVENTAIRE & QR CODE ═════════════ --}}
        <div x-show="activeTab === 'identification'" class="grid grid-cols-1 md:grid-cols-3 gap-6" x-cloak>
            {{-- Badge QR Code & Tag Physique --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs flex flex-col items-center text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tag d'Inventaire Physique</span>
                <div class="p-3 bg-white border-2 border-dashed border-slate-300 rounded-2xl shadow-inner my-2">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('finance.assets.show', $asset)) }}" alt="QR Code {{ $asset->code }}" class="h-44 w-44 rounded-lg">
                </div>
                <div class="font-mono text-base font-bold text-slate-900 mt-2">{{ $asset->code }}</div>
                <p class="text-xs text-slate-500 mt-1">{{ $asset->name }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode(route('finance.assets.show', $asset)) }}" download="{{ $asset->code }}_qr.png" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Télécharger QR (PNG)
                    </a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition shadow-xs">
                        Imprimer Tag
                    </button>
                </div>
            </div>

            {{-- Fiche Descriptive & Documents --}}
            <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Spécifications Logistiques & Documents</h3>
                
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Localisation physique</span>
                        <span class="font-semibold text-slate-800 text-sm">{{ $asset->location ?? 'Non précisé' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Collaborateur responsable</span>
                        <span class="font-semibold text-slate-800 text-sm">{{ $asset->responsibleEmployee?->full_name ?? 'Direction / Non assigné' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Fournisseur d'achat</span>
                        <span class="font-semibold text-slate-800">{{ $asset->supplier?->name ?? 'Fournisseur externe' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Date de mise en service</span>
                        <span class="font-semibold text-slate-800">{{ $asset->acquisition_date?->format('d/m/Y') }}</span>
                    </div>
                </div>

                @if($asset->purchase_document_path)
                    <div class="mt-4 p-4 rounded-xl bg-blue-50/50 border border-blue-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Facture d'achat / Justificatif légal</p>
                                <p class="text-[11px] text-slate-500">Document attaché à l'immobilisation</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $asset->purchase_document_path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-[#0066FF] border border-blue-200 hover:bg-blue-50 transition">
                            Consulter
                        </a>
                    </div>
                @endif

                @if($asset->notes)
                    <div class="mt-4 pt-4 border-t border-slate-100 text-xs">
                        <span class="text-slate-400 block font-semibold mb-1">Notes et observations</span>
                        <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">{{ $asset->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═════════════ MODAL : AJOUT PRESTATION / USAGE ═════════════ --}}
        <div x-show="usageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.outside="usageModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Enregistrer une prestation (Attribution CA)</h3>
                    <button type="button" @click="usageModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <form method="POST" action="{{ route('finance.assets.usages.store', $asset) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="{{ $labelClass }}">Intitulé de la mission / Prestation *</label>
                        <input type="text" name="title" required placeholder="Ex: Shooting Mariage VIP, Impression 300 affiches..." class="{{ $inputClass }}">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Date de réalisation *</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Chiffre d'Affaires Associé (FCFA) *</label>
                            <input type="number" step="100" min="0" name="revenue_generated" required placeholder="Ex: 500000" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Client</label>
                            <select name="customer_id" class="{{ $inputClass }}">
                                <option value="">Client comptoir / Non listé</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name ?? $c->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Durée d'utilisation (Heures)</label>
                            <input type="number" step="0.5" min="0" name="duration_hours" placeholder="Ex: 8" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Notes & Détails</label>
                        <textarea name="notes" rows="2" placeholder="Détails du projet..." class="{{ $inputClass }}"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="usageModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Annuler</button>
                        <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Enregistrer le CA</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═════════════ MODAL : NOUVELLE INTERVENTION MAINTENANCE ═════════════ --}}
        <div x-show="maintenanceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.outside="maintenanceModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Enregistrer une intervention de maintenance</h3>
                    <button type="button" @click="maintenanceModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <form method="POST" action="{{ route('finance.assets.maintenances.store', $asset) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Type d'intervention *</label>
                            <select name="type" required class="{{ $inputClass }}">
                                <option value="preventive">Préventive (Entretien régulier)</option>
                                <option value="curative">Curative (Panne / Réparation)</option>
                                <option value="revision">Révision générale</option>
                                <option value="etalonnage">Étalonnage / Calibration</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Date *</label>
                            <input type="date" name="maintenance_date" required value="{{ date('Y-m-d') }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Prestataire / Réparateur</label>
                            <input type="text" name="provider_name" placeholder="Nom du technicien ou atelier..." class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Coût de l'intervention (FCFA) *</label>
                            <input type="number" step="100" min="0" name="cost" required placeholder="Ex: 150000" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Description des travaux *</label>
                        <textarea name="description" required rows="2" placeholder="Diagnostic, pannes constatées..." class="{{ $inputClass }}"></textarea>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Pièces remplacées</label>
                        <input type="text" name="parts_replaced" placeholder="Ex: Têtes d'impression, courroie, condensateurs..." class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Statut de l'intervention *</label>
                        <select name="status" required class="{{ $inputClass }}">
                            <option value="terminee">Terminée (Matériel opérationnel)</option>
                            <option value="en_cours">En cours (Immobilise le matériel)</option>
                            <option value="planifiee">Planifiée</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="maintenanceModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Annuler</button>
                        <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2 text-xs font-semibold text-white hover:bg-amber-700">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═════════════ MODAL : SORTIE D'ACTIF / VENTE ═════════════ --}}
        <div x-show="disposeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.outside="disposeModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-rose-700">Sortie d'actif / Vente / Rebut</h3>
                    <button type="button" @click="disposeModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <form method="POST" action="{{ route('finance.assets.dispose', $asset) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="{{ $labelClass }}">Type de sortie *</label>
                        <select name="disposal_type" required class="{{ $inputClass }}">
                            <option value="vendu">Vendu (Cession à titre onéreux)</option>
                            <option value="mis_au_rebut">Mis au rebut (Inutilisable / Déclassé)</option>
                            <option value="cede">Cédé / Donné</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Date de sortie *</label>
                            <input type="date" name="disposal_date" required value="{{ date('Y-m-d') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Prix de cession (FCFA)</label>
                            <input type="number" step="100" min="0" name="disposal_price" value="0" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Motif de la sortie *</label>
                        <textarea name="disposal_reason" required rows="2" placeholder="Raison du déclassement ou informations sur l'acheteur..." class="{{ $inputClass }}"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="disposeModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Annuler</button>
                        <button type="submit" class="rounded-xl bg-rose-600 px-5 py-2 text-xs font-semibold text-white hover:bg-rose-700">Confirmer la Sortie</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═════════════ MODAL : NOUVELLE LOCATION CLIENT ═════════════ --}}
        @if($asset->is_rental_eligible)
            <div x-show="rentalModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.outside="rentalModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-purple-900">Nouveau contrat de location de matériel</h3>
                        <button type="button" @click="rentalModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>
                    <form method="POST" action="{{ route('finance.assets.rentals.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="fixed_asset_id" value="{{ $asset->id }}">
                        <div>
                            <label class="{{ $labelClass }}">Client locataire *</label>
                            <select name="customer_id" required class="{{ $inputClass }}">
                                <option value="">Sélectionner un client...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name ?? $c->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">Date de début *</label>
                                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Date de retour prévue *</label>
                                <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">Tarif journalier (FCFA) *</label>
                                <input type="number" step="500" min="0" name="daily_rate" required value="{{ $asset->rental_price_per_day }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Montant de la caution (FCFA)</label>
                                <input type="number" step="1000" min="0" name="deposit_amount" value="{{ $asset->rental_deposit_amount }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">État au départ</label>
                            <input type="text" name="condition_at_departure" value="{{ $asset->condition }}" placeholder="Ex: Matériel vérifié, complet avec accessoires" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Notes & Conditions particulières</label>
                            <textarea name="notes" rows="2" placeholder="Accessoires inclus, câblage..." class="{{ $inputClass }}"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="rentalModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Annuler</button>
                            <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2 text-xs font-semibold text-white hover:bg-purple-700">Délivrer le matériel</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</x-layouts.app>
