@php
    $n = fn ($v) => number_format($v ?? 0, 0, ',', ' ');

    $domainFilters = [['label' => 'Tous les domaines', 'value' => 'all', 'active' => !$currentDomain]];
    foreach ($domains as $d) {
        $domainFilters[] = ['label' => $d->name, 'value' => $d->code, 'active' => $currentDomain && $currentDomain->id === $d->id];
    }

    $periods = [
        'today' => "Aujourd'hui",
        'this_week' => 'Cette semaine',
        'this_month' => 'Ce mois',
        'this_quarter' => 'Ce trimestre',
        'this_year' => 'Cette année',
    ];

    $tabs = [
        'orders' => ['label' => 'Commandes', 'items' => $pendingOrders, 'route' => 'commercial.orders.show', 'total' => 'Montant', 'empty' => 'Aucune commande à traiter.'],
        'quotes' => ['label' => 'Devis', 'items' => $pendingQuotations, 'route' => 'commercial.quotations.show', 'total' => 'Montant', 'empty' => 'Aucun devis en attente.'],
    ];

    $kpis = [
        ['Nombre de ventes', 'Ventes', $nombreVentes, 'text-[#0B0F14]', 'bg-slate-400'],
        ['Commandes', 'Commandes', $nombreCommandes, 'text-[#0B0F14]', 'bg-slate-400'],
        ['Cmd en attente', 'Cmd attente', $commandesEnAttenteCount, 'text-amber-600', 'bg-amber-500'],
        ['Devis en attente', 'Devis attente', $devisEnAttenteCount, 'text-[#0066FF]', 'bg-[#0066FF]'],
        ['Clients actifs', 'Clients', $nombreClients, 'text-[#0B0F14]', 'bg-emerald-500'],
        ['Prospects', 'Prospects', $nombreProspects, 'text-[#0B0F14]', 'bg-purple-500'],
    ];

    $workshops = [
        ['PRINT', 'Imprimerie et BAT', 'print.index', 'bg-blue-50 text-blue-700', 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
        ['SPORT', 'Flocage et articles', 'sport.index', 'bg-emerald-50 text-emerald-700', 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['TECH', 'Apps et projets IA', 'tech.index', 'bg-indigo-50 text-indigo-700', 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
        ['MEDIA', 'Photo et événements', 'media.index', 'bg-amber-50 text-amber-700', 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z'],
        ['ASSURANCE', 'Polices et contrats', 'assurance.index', 'bg-rose-50 text-rose-700', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    ];

    // Raccourcis mobiles (rangée d'icônes sous le CA)
    $quickActions = [
        ['commercial.pos.index', 'Vente POS', 'bg-emerald-50 text-emerald-600', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['commercial.quotations.create', 'Devis', 'bg-blue-50 text-[#0066FF]', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['commercial.invoices.create', 'Facture', 'bg-amber-50 text-amber-600', 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
        ['commercial.customers.create', 'Client', 'bg-purple-50 text-purple-600', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
    ];

    $firstName = \Illuminate\Support\Str::of(auth()->user()->name ?? '')->explode(' ')->first();
    $greeting = now()->hour < 18 ? 'Bonjour' : 'Bonsoir';

    $card = 'rounded-2xl border border-[#E2E8F0] bg-white';
    $pill = 'shrink-0 snap-start rounded-full px-3.5 py-2 text-xs font-medium whitespace-nowrap transition-colors md:rounded-xl md:px-3 md:py-2 md:text-center';
    $pillIdle = 'bg-white text-[#64748B] ring-1 ring-[#E2E8F0] hover:text-[#0B0F14] md:bg-[#F5F7FA] md:ring-0 md:hover:bg-slate-200/60';
@endphp

<x-layouts.app title="Tableau de bord — IVOSPHERE ERP">
    <div class="mx-auto max-w-7xl space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        {{-- 1. En-tête --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <p class="text-xs font-medium capitalize text-[#64748B] md:hidden">{{ now()->locale('fr')->isoFormat('dddd D MMMM') }}</p>
                <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">
                    <span class="md:hidden">{{ $greeting }}{{ $firstName ? ', '.$firstName : '' }} 👋</span>
                    <span class="hidden md:inline">Vue générale</span>
                </h1>
                <p class="text-xs sm:text-sm text-[#64748B]">
                    <span class="md:hidden">Voici l'activité de {{ $currentDomain->name ?? 'tous les domaines' }}.</span>
                    <span class="hidden md:inline">Performances financières et opérationnelles de l'entreprise.</span>
                </p>
            </div>
            <div class="hidden items-center gap-2.5 md:flex">
                <a href="{{ route('commercial.pos.index') }}"
                   class="h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-xs font-medium text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Vente POS</span>
                </a>
                <a href="{{ route('commercial.quotations.create') }}"
                   class="h-10 px-4 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-xs font-medium text-white transition flex items-center justify-center gap-2 shadow-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouveau devis</span>
                </a>
            </div>
        </header>

        {{-- 2. Filtres : chips défilantes sur mobile, grilles sur desktop --}}
        <section class="-mx-3.5 space-y-2.5 sm:-mx-6 md:mx-0 md:space-y-0 md:divide-y md:divide-[#E2E8F0] md:rounded-2xl md:border md:border-[#E2E8F0] md:bg-white"
                 x-data="{ custom: {{ $period === 'custom' ? 'true' : 'false' }} }" aria-label="Filtres">
            <div class="md:space-y-2 md:p-4">
                <span class="hidden text-[11px] font-medium uppercase tracking-wider text-[#64748B] md:block">Domaines</span>
                <div class="no-scrollbar flex snap-x scroll-px-3.5 gap-2 overflow-x-auto px-3.5 sm:px-6 md:grid md:grid-cols-4 md:overflow-visible md:px-0 lg:grid-cols-6 xl:grid-cols-7">
                    @foreach($domainFilters as $f)
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['domain' => $f['value']])) }}"
                           @if($f['active']) aria-current="true" @endif
                           class="{{ $pill }} md:truncate {{ $f['active'] ? 'bg-[#0B0F14] text-white shadow-xs' : $pillIdle }}">
                            {{ $f['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="md:space-y-2 md:p-4">
                <div class="hidden items-center justify-between gap-1 md:flex">
                    <span class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Période</span>
                    <p class="text-xs text-[#64748B]">
                        Du <strong class="font-medium text-[#0B0F14]">{{ $startDate->format('d/m/Y') }}</strong>
                        au <strong class="font-medium text-[#0B0F14]">{{ $endDate->format('d/m/Y') }}</strong>
                    </p>
                </div>
                <div class="no-scrollbar flex snap-x scroll-px-3.5 gap-2 overflow-x-auto px-3.5 sm:px-6 md:grid md:grid-cols-6 md:overflow-visible md:px-0">
                    @foreach($periods as $key => $label)
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => $key])) }}"
                           @if($period === $key) aria-current="true" @endif
                           class="{{ $pill }} {{ $period === $key ? 'bg-[#0066FF] text-white shadow-xs' : $pillIdle }}">
                            {!! $label !!}
                        </a>
                    @endforeach
                    <button type="button" @click="custom = !custom" :aria-expanded="custom"
                            class="{{ $pill }} inline-flex items-center justify-center gap-1.5 {{ $period === 'custom' ? 'bg-[#0066FF] text-white shadow-xs' : $pillIdle }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Personnalisée</span>
                    </button>
                </div>
                <p class="px-3.5 pt-2 text-[11px] text-[#64748B] sm:px-6 md:hidden">
                    Du <strong class="font-medium text-[#0B0F14]">{{ $startDate->format('d/m/Y') }}</strong>
                    au <strong class="font-medium text-[#0B0F14]">{{ $endDate->format('d/m/Y') }}</strong>
                </p>
            </div>

            <form x-show="custom" x-cloak method="GET" action="{{ route('dashboard') }}"
                  class="mx-3.5 grid grid-cols-2 gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-3 text-xs sm:mx-6 md:mx-0 md:flex md:flex-wrap md:items-end md:rounded-none md:border-0 md:px-4">
                @if(request('domain')) <input type="hidden" name="domain" value="{{ request('domain') }}"> @endif
                <input type="hidden" name="period" value="custom">
                <label class="flex flex-col gap-1 text-[#64748B]">Du
                    <input type="date" name="date_from" value="{{ request('date_from', $startDate->toDateString()) }}"
                           class="rounded-lg border border-[#E2E8F0] bg-[#F5F7FA] px-3 py-2 text-xs text-[#0B0F14] focus:outline-none focus:ring-2 focus:ring-[#0066FF]">
                </label>
                <label class="flex flex-col gap-1 text-[#64748B]">Au
                    <input type="date" name="date_to" value="{{ request('date_to', $endDate->toDateString()) }}"
                           class="rounded-lg border border-[#E2E8F0] bg-[#F5F7FA] px-3 py-2 text-xs text-[#0B0F14] focus:outline-none focus:ring-2 focus:ring-[#0066FF]">
                </label>
                <button type="submit" class="col-span-2 rounded-lg bg-[#0066FF] px-4 py-2 font-medium text-white hover:bg-blue-600 md:col-span-1">Appliquer</button>
            </form>
        </section>

        {{-- 3. Indicateurs financiers --}}
        <section aria-labelledby="fin-title" class="space-y-3">
            <div class="flex items-baseline justify-between px-1">
                <h2 id="fin-title" class="text-sm font-semibold text-[#0B0F14]">Chiffre d'Affaires & Trésorerie</h2>
                <span class="text-xs text-[#64748B]">En {{ $currency }}</span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                {{-- CA : carte principale --}}
                <article class="relative flex flex-col justify-between overflow-hidden rounded-3xl bg-[#0B0F14] p-5 text-white shadow-[0_20px_40px_-20px_rgba(0,102,255,0.55)] sm:col-span-2 lg:col-span-1 lg:rounded-2xl lg:shadow-xs">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[#0066FF]/40 blur-3xl lg:hidden"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-400">Chiffre d'Affaires · {{ $periodLabel }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-white/10 text-slate-200 uppercase tracking-wider">
                                {{ $period === 'this_quarter' ? 'T'.now()->quarter : ($period === 'today' ? 'Jour' : ($period === 'this_week' ? 'Semaine' : ($period === 'this_year' ? now()->year : 'Mois'))) }}
                            </span>
                        </div>
                        <p class="mt-2 text-[2rem] leading-tight font-semibold tracking-tight sm:text-3xl">{{ $n($caPeriod) }}<span class="ml-1.5 text-xs font-normal text-slate-400">{{ $currency }}</span></p>
                    </div>
                    <dl class="relative mt-4 grid grid-cols-2 gap-1.5 border-t border-white/10 pt-3 text-[11px]">
                        @foreach([['today', 'CA Jour', $caToday], ['this_week', 'CA Semaine', $caWeek], ['this_quarter', 'CA Trimestre', $caQuarter], ['this_year', 'CA Annuel', $caYear]] as [$key, $label, $value])
                            <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => $key])) }}"
                               class="rounded-xl p-2.5 transition-all {{ $period === $key ? 'bg-white/20 ring-1 ring-white/40' : 'bg-white/5 hover:bg-white/10 active:bg-white/10' }}"
                               title="Filtrer sur {{ $periods[$key] }}">
                                <dt class="text-slate-400 text-[10px] uppercase font-medium tracking-wider">{{ $label }}</dt>
                                <dd class="mt-0.5 font-semibold text-white text-xs truncate">{{ $n($value) }} <span class="text-[9px] font-normal text-slate-400">F</span></dd>
                            </a>
                        @endforeach
                    </dl>
                </article>

                {{-- Raccourcis (mobile) --}}
                <nav aria-label="Actions rapides" class="grid grid-cols-4 gap-2 sm:col-span-2 md:hidden">
                    @foreach($quickActions as [$routeName, $label, $tint, $icon])
                        <a href="{{ route($routeName) }}" class="flex flex-col items-center gap-1.5 rounded-2xl py-2 active:bg-white">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $tint }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            <span class="text-[11px] font-medium text-[#0B0F14]">{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Cartes secondaires : carrousel horizontal sur mobile --}}
                <div class="no-scrollbar -mx-3.5 flex snap-x snap-mandatory scroll-px-3.5 gap-3 overflow-x-auto px-3.5 pb-1 sm:col-span-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-3 md:gap-4 md:overflow-visible md:px-0 md:pb-0 lg:col-span-3">
                    {{-- Trésorerie --}}
                    <article class="{{ $card }} flex w-[78%] shrink-0 snap-center flex-col justify-between p-5 sm:w-[45%] md:w-auto">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-[#0066FF]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </span>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Trésorerie</p>
                            </div>
                            <p class="mt-3 text-2xl font-semibold tracking-tight text-[#0B0F14] sm:text-3xl">{{ $n($tresorerie) }}<span class="ml-1.5 text-xs font-normal text-[#64748B]">{{ $currency }}</span></p>
                        </div>
                        <p class="mt-4 border-t border-[#E2E8F0] pt-3 text-[11px] text-[#64748B]">Caisses et banques cumulées</p>
                    </article>

                    {{-- Bénéfice --}}
                    <article class="{{ $card }} flex w-[78%] shrink-0 snap-center flex-col justify-between p-5 sm:w-[45%] md:w-auto">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $beneficeEstime >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $beneficeEstime >= 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}"/></svg>
                                </span>
                                <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Bénéfice estimé</p>
                            </div>
                            <p class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl {{ $beneficeEstime >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $n($beneficeEstime) }}<span class="ml-1.5 text-xs font-normal text-[#64748B]">{{ $currency }}</span></p>
                        </div>
                        <p class="mt-4 flex justify-between gap-2 border-t border-[#E2E8F0] pt-3 text-[11px] text-[#64748B]">
                            Dépenses engagées <span class="font-medium text-[#0B0F14]">{{ $n($depensesPeriod) }}</span>
                        </p>
                    </article>

                    {{-- Impayés --}}
                    <article class="{{ $card }} flex w-[78%] shrink-0 snap-center flex-col justify-between p-5 sm:w-[45%] md:w-auto">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $facturesImpayeesCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Impayés</p>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $facturesImpayeesCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                    {{ $facturesImpayeesCount }} en retard
                                </span>
                            </div>
                            <p class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl {{ $facturesImpayeesCount > 0 ? 'text-rose-600' : 'text-[#0B0F14]' }}">{{ $n($facturesImpayeesMontant) }}<span class="ml-1.5 text-xs font-normal text-[#64748B]">{{ $currency }}</span></p>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-[#E2E8F0] pt-3 text-[11px] text-[#64748B]">
                            Recouvrement
                            <a href="{{ route('commercial.invoices.index') }}" class="font-medium text-[#0066FF] hover:underline">Gérer les créances</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- 4. Domaines et santé opérationnelle --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-12">
            <section class="{{ $card }} order-2 p-4 sm:p-6 lg:order-1 lg:col-span-7">
                <x-domain-ca-chart
                    :domain-performance="$domainPerformance"
                    :total-consolidated-ca="$totalConsolidatedCa"
                    :currency="$currency"
                    :year="$now->year"
                />
            </section>

            <section class="{{ $card }} order-1 flex flex-col p-4 sm:p-6 lg:order-2 lg:col-span-5">
                <div class="flex items-start justify-between gap-3 border-b border-[#E2E8F0] pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-[#0B0F14]">Santé opérationnelle</h3>
                        <p class="text-xs text-[#64748B]">Flux clients et activité de l'atelier</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-[#0066FF]">RDV du jour : {{ $rdvDuJourCount }}</span>
                </div>
                <dl class="mt-4 grid grid-cols-3 gap-2 sm:gap-2.5 lg:grid-cols-2">
                    @foreach($kpis as [$label, $shortLabel, $value, $color, $dot])
                        <div class="rounded-2xl bg-[#F5F7FA] p-3 sm:p-3.5">
                            <dt class="flex items-center gap-1.5 text-[10px] leading-tight text-[#64748B] sm:text-[11px]">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dot }}"></span>
                                <span class="truncate sm:hidden">{{ $shortLabel }}</span>
                                <span class="hidden truncate sm:inline">{{ $label }}</span>
                            </dt>
                            <dd class="mt-1.5 text-xl font-bold {{ $color }}">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <a href="{{ route('stock.alerts.index') }}"
                   class="mt-4 flex items-center justify-between gap-2 rounded-2xl px-3.5 py-3 text-xs transition-colors {{ $produitsEnRupture > 0 ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-[#F5F7FA] text-[#64748B] hover:bg-slate-200/60' }}">
                    <span class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $produitsEnRupture > 0 ? 'bg-rose-600 animate-pulse' : 'bg-slate-300' }}"></span>
                        <span><strong class="font-semibold">{{ $produitsEnRupture }}</strong> ruptures · <strong class="font-semibold">{{ $produitsBientotEnRupture }}</strong> sous seuil</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </section>
        </div>

        {{-- 5. Flux opérationnels --}}
        <section class="{{ $card }} overflow-hidden" x-data="{ tab: 'orders' }">
            <div class="flex flex-col gap-2 border-b border-[#E2E8F0] bg-[#F5F7FA]/60 px-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                {{-- Contrôle segmenté pleine largeur sur mobile --}}
                <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-200/60 p-1 sm:flex sm:bg-transparent sm:p-0" role="tablist">
                    @foreach($tabs as $key => $t)
                        <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'bg-white text-[#0066FF] font-semibold shadow-xs sm:border-[#E2E8F0]' : 'border-transparent text-[#64748B] hover:text-[#0B0F14]'"
                                class="whitespace-nowrap rounded-lg border border-transparent px-2 py-2 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] sm:px-3.5 sm:py-1.5">
                            {{ $t['label'] }} <span class="opacity-70">({{ $t['items']->count() }})</span>
                        </button>
                    @endforeach
                    <button type="button" role="tab" @click="tab = 'logs'" :aria-selected="tab === 'logs'"
                            :class="tab === 'logs' ? 'bg-white text-[#0066FF] font-semibold shadow-xs sm:border-[#E2E8F0]' : 'border-transparent text-[#64748B] hover:text-[#0B0F14]'"
                            class="whitespace-nowrap rounded-lg border border-transparent px-2 py-2 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] sm:px-3.5 sm:py-1.5">
                        Activité
                    </button>
                </div>
                <p class="px-1 text-[11px] text-[#64748B] sm:text-xs">
                    {{ $tachesARealiserCount }} Tâches à réaliser · {{ $evenementsAVenirCount }} Évènements à venir
                </p>
            </div>

            @foreach($tabs as $key => $t)
                <div x-show="tab === '{{ $key }}'" @if(!$loop->first) x-cloak @endif class="p-3 sm:p-5" role="tabpanel">
                    {{-- Tableau desktop --}}
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-[#E2E8F0] text-[#64748B]">
                                    <th class="pb-2.5 font-semibold">Référence</th>
                                    <th class="pb-2.5 font-semibold">Client</th>
                                    <th class="pb-2.5 font-semibold">Date</th>
                                    <th class="pb-2.5 text-right font-semibold">{{ $t['total'] }}</th>
                                    <th class="pb-2.5"><span class="sr-only">Action</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @forelse($t['items'] as $row)
                                    <tr class="hover:bg-[#F5F7FA]/70">
                                        <td class="py-3 font-mono font-semibold text-[#0066FF]">{{ $row->reference }}</td>
                                        <td class="py-3 font-medium text-[#0B0F14]">{{ $row->customer->name ?? 'Client' }}</td>
                                        <td class="py-3 text-[#64748B]">{{ $row->date?->format('d/m/Y') }}</td>
                                        <td class="py-3 text-right font-bold text-[#0B0F14]">{{ $n($row->total) }} {{ $currency }}</td>
                                        <td class="py-3 text-right">
                                            <a href="{{ route($t['route'], $row) }}" class="font-semibold text-[#0066FF] hover:underline">Consulter</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-10 text-center text-[#64748B]">{{ $t['empty'] }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Liste mobile --}}
                    <ul class="divide-y divide-[#E2E8F0] md:hidden">
                        @forelse($t['items'] as $row)
                            <li>
                                <a href="{{ route($t['route'], $row) }}" class="-mx-1 flex items-center gap-3 rounded-xl px-1 py-3 active:bg-[#F5F7FA]">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F5F7FA] text-xs font-semibold text-[#0B0F14]">
                                        {{ strtoupper(mb_substr($row->customer->name ?? 'C', 0, 2)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-[#0B0F14]">{{ $row->customer->name ?? 'Client' }}</p>
                                        <p class="mt-0.5 truncate text-[11px] text-[#64748B]"><span class="font-mono text-[#0066FF]">{{ $row->reference }}</span> · {{ $row->date?->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-bold text-[#0B0F14]">{{ $n($row->total) }}</p>
                                        <p class="text-[10px] text-[#64748B]">{{ $currency }}</p>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="py-8 text-center text-xs text-[#64748B]">{{ $t['empty'] }}</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach

            <div x-show="tab === 'logs'" x-cloak class="p-3 sm:p-5" role="tabpanel">
                <ul class="divide-y divide-[#E2E8F0]">
                    @forelse($recentActivities as $activity)
                        <li class="flex gap-3 py-3 text-xs sm:items-center sm:justify-between sm:gap-4">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#0066FF]/60 sm:hidden"></span>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <p class="min-w-0">
                                    <span class="font-semibold text-[#0B0F14]">{{ $activity->user->name ?? 'Système' }}</span>
                                    <span class="ml-1.5 text-[#64748B]">{{ $activity->description }}</span>
                                </p>
                                <time class="shrink-0 text-[11px] text-[#64748B]">{{ $activity->created_at->diffForHumans() }}</time>
                            </div>
                        </li>
                    @empty
                        <li class="py-8 text-center text-xs text-[#64748B]">Aucune activité récente.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        {{-- 6. Accès aux ateliers : carrousel sur mobile --}}
        <section aria-labelledby="ws-title">
            <h2 id="ws-title" class="mb-3 px-1 text-sm font-semibold text-[#0B0F14]">Ateliers et pôles métiers</h2>
            <div class="no-scrollbar -mx-3.5 flex snap-x scroll-px-3.5 gap-3 overflow-x-auto px-3.5 pb-1 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0 lg:grid-cols-5">
                @foreach($workshops as [$name, $desc, $routeName, $badge, $icon])
                    <a href="{{ route($routeName) }}"
                       class="group {{ $card }} w-36 shrink-0 snap-start p-4 transition-colors hover:border-[#0066FF] active:bg-[#F5F7FA] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] md:w-auto">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $badge }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <p class="mt-3 text-xs font-semibold tracking-wide text-[#0B0F14]">{{ $name }}</p>
                        <p class="mt-0.5 text-[11px] text-[#64748B] group-hover:text-[#0B0F14]">{{ $desc }}</p>
                    </a>
                @endforeach
            </div>
        </section>

    </div>
</x-layouts.app>
