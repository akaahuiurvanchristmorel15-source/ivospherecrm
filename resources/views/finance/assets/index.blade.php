@php
    $field = 'w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-base text-[#0B0F14] placeholder:text-[#64748B]/60 transition focus:border-[#0066FF] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]/20 sm:text-sm min-h-[44px]';
    $label = 'mb-1.5 block text-xs font-medium text-[#64748B]';
    $btn = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2 min-h-[44px]';
    $btnSecondary = $btn . ' border border-[#E2E8F0] bg-white text-[#0B0F14] hover:bg-[#F5F7FA]';
    $btnPrimary = $btn . ' bg-[#0066FF] text-white hover:bg-[#0052CC]';
    $card = 'rounded-2xl border border-[#E2E8F0] bg-white';

    $statuses = [
        'en_service'     => ['label' => 'En service',     'badge' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
        'disponible'     => ['label' => 'Disponible',     'badge' => 'bg-blue-50 text-blue-700',       'dot' => 'bg-blue-500'],
        'loue'           => ['label' => 'Loué',           'badge' => 'bg-purple-50 text-purple-700',   'dot' => 'bg-purple-500'],
        'en_maintenance' => ['label' => 'En maintenance', 'badge' => 'bg-amber-50 text-amber-700',     'dot' => 'bg-amber-500'],
        'a_reparer'      => ['label' => 'À réparer',      'badge' => 'bg-amber-50 text-amber-700',     'dot' => 'bg-amber-500'],
        'hors_service'   => ['label' => 'Hors service',   'badge' => 'bg-rose-50 text-rose-700',       'dot' => 'bg-rose-500'],
        'vendu'          => ['label' => 'Vendu',          'badge' => 'bg-slate-100 text-[#64748B]',    'dot' => 'bg-slate-400'],
    ];

    $roiBadges = [
        'super_rentable'   => 'bg-emerald-50 text-emerald-700',
        'rentable'         => 'bg-blue-50 text-blue-700',
        'en_amortissement' => 'bg-amber-50 text-amber-700',
    ];

    $fmt = fn ($v) => number_format((float) ($v ?? 0), 0, ',', ' ');
    $fmtSigned = fn ($v) => ($v >= 0 ? '+' : '') . number_format((float) $v, 0, ',', ' ');

    $hasFilters = request()->hasAny(['search', 'domain_id', 'category_id', 'status', 'is_rental_eligible']);
@endphp

<x-layouts.app title="Immobilisations & Actifs — Finance">
    <div class="mx-auto w-full max-w-7xl space-y-6 pb-12 [font-variant-numeric:tabular-nums]">

        {{-- Fil d'Ariane et en-tête --}}
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <nav aria-label="Fil d'Ariane" class="mb-1.5 flex items-center gap-1.5 text-xs text-[#64748B]">
                    <a href="{{ route('finance.index') }}" class="hover:text-[#0066FF] transition-colors">Finance</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-semibold text-[#0B0F14]">Immobilisations</span>
                </nav>
                <h1 class="text-xl font-bold tracking-tight text-[#0B0F14] sm:text-2xl">
                    Immobilisations et actifs
                </h1>
                <p class="mt-1 max-w-2xl text-sm text-[#64748B]">
                    Registre des biens durables, calcul des amortissements, attribution du chiffre d'affaires et rentabilité nette.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                <a href="{{ route('finance.assets.rentals.index') }}" class="{{ $btnSecondary }}">
                    Gérer les locations
                </a>
                <a href="{{ route('finance.assets.export') }}" class="{{ $btnSecondary }}">
                    Exporter en CSV
                </a>
                <a href="{{ route('finance.assets.create') }}" class="{{ $btnPrimary }} col-span-2 sm:col-span-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Ajouter une immobilisation
                </a>
            </div>
        </header>

        {{-- Messages d'état --}}
        @if(session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900">
                {{ session('error') }}
            </div>
        @endif

        {{-- Cartes de synthèse financière --}}
        <section aria-label="Synthèse du parc" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- Carte 1 : Valeur des Actifs --}}
            <article class="{{ $card }} p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-medium text-[#64748B]">Valeur des Actifs</h2>
                    <span class="text-[11px] font-medium text-[#64748B]">Patrimoine</span>
                </div>
                <p class="mt-2 text-2xl font-bold tracking-tight text-[#0B0F14]">
                    {{ $fmt($totalAcquisitionValue) }} <span class="text-xs font-normal text-[#64748B]">FCFA</span>
                </p>
                <dl class="mt-4 space-y-2 border-t border-[#E2E8F0] pt-3 text-xs">
                    <div class="flex items-center justify-between">
                        <dt class="text-[#64748B]">Amortissements cumulés</dt>
                        <dd class="font-semibold text-rose-600">−{{ $fmt($totalDepreciationCumul) }} FCFA</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="font-medium text-[#0B0F14]">Valeur nette comptable (VNC)</dt>
                        <dd class="font-bold text-[#0066FF]">{{ $fmt($totalVnc) }} FCFA</dd>
                    </div>
                </dl>
            </article>

            {{-- Carte 2 : Chiffre d'affaires généré --}}
            <article class="{{ $card }} p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-medium text-[#64748B]">Chiffre d'affaires généré</h2>
                    <span class="text-[11px] font-medium text-emerald-700">Exploitation</span>
                </div>
                <p class="mt-2 text-2xl font-bold tracking-tight text-emerald-700">
                    {{ $fmt($totalRevenueGenerated) }} <span class="text-xs font-normal text-[#64748B]">FCFA</span>
                </p>
                <dl class="mt-4 space-y-2 border-t border-[#E2E8F0] pt-3 text-xs">
                    <div class="flex items-center justify-between">
                        <dt class="text-[#64748B]">Prestations de services</dt>
                        <dd class="font-semibold text-[#0B0F14]">{{ $fmt($totalUsageRevenue) }} FCFA</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#64748B]">Locations de matériel</dt>
                        <dd class="font-semibold text-purple-700">{{ $fmt($totalRentalRevenue) }} FCFA</dd>
                    </div>
                </dl>
            </article>

            {{-- Carte 3 (Carte sombre) : Rentabilité nette --}}
            <article class="rounded-2xl border border-[#0B0F14] bg-[#0B0F14] p-5 text-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-medium text-slate-300">Rentabilité nette estimée</h2>
                    <span class="text-[11px] font-medium text-slate-400">CA − coûts − amort.</span>
                </div>
                <p class="mt-2 text-2xl font-bold tracking-tight {{ $netContribution >= 0 ? 'text-white' : 'text-rose-400' }}">
                    {{ $fmtSigned($netContribution) }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </p>
                <dl class="mt-4 space-y-2 border-t border-white/10 pt-3 text-xs">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-400">Coûts de maintenance</dt>
                        <dd class="font-semibold text-rose-300">−{{ $fmt($totalMaintenanceCost) }} FCFA</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-300">Parc suivi</dt>
                        <dd class="font-medium text-white">{{ $countsByStatus['total'] }} matériels ({{ $countsByStatus['en_service'] + $countsByStatus['disponible'] }} actifs)</dd>
                    </div>
                </dl>
            </article>
        </section>

        {{-- Filtres de recherche --}}
        <form method="GET" action="{{ route('finance.assets.index') }}" class="{{ $card }} p-4" aria-label="Filtres des immobilisations">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-12">
                {{-- Recherche texte --}}
                <div class="col-span-2 lg:col-span-4">
                    <label for="search" class="{{ $label }}">Recherche</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" enterkeyhint="search"
                               placeholder="Code, nom, marque, n° série..." class="{{ $field }} pl-10">
                    </div>
                </div>

                {{-- Domaine --}}
                <div class="col-span-2 sm:col-span-1 lg:col-span-3">
                    <label for="domain_id" class="{{ $label }}">Domaine</label>
                    <select name="domain_id" id="domain_id" class="{{ $field }}" onchange="this.form.submit()">
                        <option value="">Tous les domaines</option>
                        @foreach($domains as $d)
                            <option value="{{ $d->id }}" @selected(request('domain_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Catégorie --}}
                <div class="col-span-1 lg:col-span-2">
                    <label for="category_id" class="{{ $label }}">Catégorie</label>
                    <select name="category_id" id="category_id" class="{{ $field }}" onchange="this.form.submit()">
                        <option value="">Toutes</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Statut --}}
                <div class="col-span-1 lg:col-span-2">
                    <label for="status" class="{{ $label }}">Statut</label>
                    <select name="status" id="status" class="{{ $field }}" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="en_service" @selected(request('status') === 'en_service')>En service</option>
                        <option value="disponible" @selected(request('status') === 'disponible')>Disponible</option>
                        <option value="loue" @selected(request('status') === 'loue')>Loué</option>
                        <option value="en_maintenance" @selected(request('status') === 'en_maintenance')>En maintenance</option>
                        <option value="hors_service" @selected(request('status') === 'hors_service')>Hors service</option>
                        <option value="vendu" @selected(request('status') === 'vendu')>Vendu</option>
                    </select>
                </div>

                {{-- Bouton réinitialiser --}}
                @if($hasFilters)
                    <div class="col-span-2 flex items-end sm:col-span-1 lg:col-span-1">
                        <a href="{{ route('finance.assets.index') }}" class="{{ $btnSecondary }} w-full !px-3" aria-label="Réinitialiser les filtres">
                            Effacer
                        </a>
                    </div>
                @endif
            </div>
        </form>

        {{-- Liste des immobilisations (5 colonnes sur desktop) --}}
        <section class="{{ $card }} overflow-hidden" aria-label="Registre des immobilisations">

            {{-- 1. Tableau desktop (exactement 5 colonnes) --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-[#E2E8F0] bg-[#F5F7FA]/80 text-xs font-semibold text-[#64748B]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Matériel</th>
                            <th scope="col" class="px-4 py-3.5 text-right">Valeur et VNC</th>
                            <th scope="col" class="px-4 py-3.5 text-right">CA généré</th>
                            <th scope="col" class="px-4 py-3.5 text-right">Rentabilité</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Statut et actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($assets as $asset)
                            @php
                                $statusInfo = $statuses[$asset->status] ?? ['label' => ucfirst($asset->status), 'badge' => 'bg-slate-100 text-[#64748B]', 'dot' => 'bg-slate-400'];
                                $roiBadgeClass = $roiBadges[$asset->profitability_status] ?? 'bg-slate-100 text-[#64748B]';
                            @endphp
                            <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                                {{-- 1. Matériel & Catégorie / Domaine --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs font-semibold text-[#64748B]">
                                            @if($asset->photo_path)
                                                <img src="{{ asset('storage/' . $asset->photo_path) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                            @else
                                                {{ substr($asset->code, 4, 3) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('finance.assets.show', $asset) }}" class="line-clamp-1 font-semibold text-[#0B0F14] hover:text-[#0066FF] transition-colors">
                                                    {{ $asset->name }}
                                                </a>
                                                @if($asset->is_rental_eligible)
                                                    <span class="rounded bg-purple-50 px-1.5 py-0.5 text-[10px] font-semibold text-purple-700 shrink-0">Location</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-[#64748B]">
                                                <span class="font-mono font-semibold text-[#0B0F14]">{{ $asset->code }}</span>
                                                <span>•</span>
                                                <span>{{ $asset->category?->name ?? 'Général' }}</span>
                                                <span>•</span>
                                                <span>{{ $asset->domain?->name ?? 'Tous pôles' }}</span>
                                                @if($asset->brand || $asset->model)
                                                    <span class="hidden xl:inline">• {{ $asset->brand }} {{ $asset->model }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Valeur & VNC --}}
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <p class="font-semibold text-[#0B0F14]">{{ $fmt($asset->acquisition_value) }} <span class="text-[11px] font-normal text-[#64748B]">FCFA</span></p>
                                    <p class="mt-0.5 text-xs font-medium text-[#0066FF]">VNC {{ $fmt($asset->net_book_value) }}</p>
                                    <p class="text-[11px] text-[#64748B]">{{ $asset->useful_life_years }} ans ({{ $asset->depreciation_method }})</p>
                                </td>

                                {{-- 3. CA Généré --}}
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <p class="font-bold text-emerald-700">{{ $fmt($asset->total_revenue_generated) }} <span class="text-[11px] font-normal text-[#64748B]">FCFA</span></p>
                                    <p class="mt-0.5 text-xs text-[#64748B]">Prestations {{ $fmt($asset->total_usage_revenue) }}</p>
                                    @if($asset->total_rental_revenue > 0)
                                        <p class="text-xs text-purple-700">Locations {{ $fmt($asset->total_rental_revenue) }}</p>
                                    @endif
                                </td>

                                {{-- 4. Rentabilité & ROI --}}
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <p class="font-bold {{ $asset->net_profitability >= 0 ? 'text-[#0B0F14]' : 'text-rose-600' }}">
                                        {{ $fmtSigned($asset->net_profitability) }} <span class="text-[11px] font-normal text-[#64748B]">FCFA</span>
                                    </p>
                                    <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $roiBadgeClass }}">
                                        ROI {{ $asset->roi_percentage }}%
                                    </span>
                                </td>

                                {{-- 5. Statut et Actions --}}
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="text-right">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusInfo['badge'] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $statusInfo['dot'] }}"></span>
                                                {{ $statusInfo['label'] }}
                                            </span>
                                            <p class="mt-0.5 text-[11px] text-[#64748B]">{{ $asset->location ?? 'Site principal' }}</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('finance.assets.show', $asset) }}" class="{{ $btnSecondary }} !px-3 !py-1.5 !min-h-[36px] text-xs">
                                                Fiche 360°
                                            </a>
                                            <a href="{{ route('finance.assets.edit', $asset) }}" aria-label="Modifier {{ $asset->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#64748B] hover:bg-[#F5F7FA] hover:text-[#0B0F14] transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-[#64748B]">
                                    <p class="font-medium text-[#0B0F14]">Aucune immobilisation trouvée</p>
                                    <p class="mt-1 text-xs text-[#64748B]">Modifiez vos critères de recherche ou enregistrez un nouvel équipement.</p>
                                    <a href="{{ route('finance.assets.create') }}" class="mt-4 {{ $btnPrimary }} !min-h-[40px] text-xs">
                                        Ajouter une immobilisation
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 2. Liste de cartes cliquables pour mobile et tablette (largeur < 1024px) --}}
            <ul class="divide-y divide-[#E2E8F0] lg:hidden">
                @forelse($assets as $asset)
                    @php
                        $statusInfo = $statuses[$asset->status] ?? ['label' => ucfirst($asset->status), 'badge' => 'bg-slate-100 text-[#64748B]', 'dot' => 'bg-slate-400'];
                        $roiBadgeClass = $roiBadges[$asset->profitability_status] ?? 'bg-slate-100 text-[#64748B]';
                    @endphp
                    <li>
                        <a href="{{ route('finance.assets.show', $asset) }}" class="block p-4 transition hover:bg-[#F5F7FA]/70 active:bg-[#F5F7FA]">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs font-semibold text-[#64748B]">
                                    @if($asset->photo_path)
                                        <img src="{{ asset('storage/' . $asset->photo_path) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        {{ substr($asset->code, 4, 3) }}
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-semibold text-[#0B0F14] line-clamp-1">{{ $asset->name }}</p>
                                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusInfo['badge'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $statusInfo['dot'] }}"></span>
                                            {{ $statusInfo['label'] }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-[#64748B]">
                                        <span class="font-mono font-semibold text-[#0B0F14]">{{ $asset->code }}</span> • {{ $asset->domain?->name ?? 'Tous pôles' }}
                                    </p>
                                </div>
                            </div>

                            <dl class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-[#F5F7FA] p-3 text-xs">
                                <div>
                                    <dt class="text-[11px] text-[#64748B]">VNC</dt>
                                    <dd class="mt-0.5 font-bold text-[#0066FF]">{{ $fmt($asset->net_book_value) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-[#64748B]">CA généré</dt>
                                    <dd class="mt-0.5 font-bold text-emerald-700">{{ $fmt($asset->total_revenue_generated) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-[#64748B]">Rentabilité</dt>
                                    <dd class="mt-0.5 font-bold {{ $asset->net_profitability >= 0 ? 'text-[#0B0F14]' : 'text-rose-600' }}">{{ $fmtSigned($asset->net_profitability) }}</dd>
                                </div>
                            </dl>

                            <div class="mt-2.5 flex items-center justify-between text-[11px] text-[#64748B]">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold {{ $roiBadgeClass }}">
                                    ROI {{ $asset->roi_percentage }}%
                                </span>
                                @if($asset->is_rental_eligible)
                                    <span class="font-semibold text-purple-700">Éligible location</span>
                                @else
                                    <span>{{ $asset->location ?? 'Site principal' }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center text-[#64748B]">
                        <p class="font-medium text-[#0B0F14]">Aucune immobilisation trouvée</p>
                        <p class="mt-1 text-xs text-[#64748B]">Modifiez vos critères de recherche ou enregistrez un nouvel équipement.</p>
                        <a href="{{ route('finance.assets.create') }}" class="mt-4 {{ $btnPrimary }} !min-h-[40px] text-xs">
                            Ajouter une immobilisation
                        </a>
                    </li>
                @endforelse
            </ul>

            {{-- Pagination --}}
            @if($assets->hasPages())
                <div class="border-t border-[#E2E8F0] px-4 py-3.5 sm:px-5">
                    {{ $assets->links() }}
                </div>
            @endif
        </section>

    </div>
</x-layouts.app>