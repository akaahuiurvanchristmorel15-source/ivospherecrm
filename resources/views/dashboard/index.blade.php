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
        ['Nombre de ventes', $nombreVentes, 'text-[#0B0F14]'],
        ['Commandes', $nombreCommandes, 'text-[#0B0F14]'],
        ['Cmd en attente', $commandesEnAttenteCount, 'text-amber-600'],
        ['Devis en attente', $devisEnAttenteCount, 'text-[#0066FF]'],
        ['Clients actifs', $nombreClients, 'text-[#0B0F14]'],
        ['Prospects', $nombreProspects, 'text-[#0B0F14]'],
    ];

    $workshops = [
        ['PRINT', 'Imprimerie et BAT', 'print.index', 'bg-blue-50 text-blue-700'],
        ['SPORT', 'Flocage et articles', 'sport.index', 'bg-emerald-50 text-emerald-700'],
        ['TECH', 'Apps et projets IA', 'tech.index', 'bg-indigo-50 text-indigo-700'],
        ['MEDIA', 'Photo et événements', 'media.index', 'bg-amber-50 text-amber-700'],
        ['ASSURANCE', 'Polices et contrats', 'assurance.index', 'bg-rose-50 text-rose-700'],
    ];

    $chip = 'shrink-0 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-1';
    $card = 'rounded-2xl border border-[#E2E8F0] bg-white';
@endphp

<x-layouts.app title="Tableau de bord — IVOSPHERE ERP">
    <div class="mx-auto max-w-7xl space-y-5 sm:space-y-6 [font-variant-numeric:tabular-nums]">

        {{-- 1. En-tête --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-[#0B0F14]">Vue générale</h1>
                <p class="text-xs sm:text-sm text-[#64748B]">Performances financières et opérationnelles de l'entreprise.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-2.5">
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

        {{-- 2. Filtres en grilles responsives --}}
        <section class="{{ $card }} divide-y divide-[#E2E8F0]"
                 x-data="{ custom: {{ $period === 'custom' ? 'true' : 'false' }} }" aria-label="Filtres">
            {{-- Grille des Domaines --}}
            <div class="p-3.5 sm:p-4 space-y-2">
                <span class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider block">Domaines</span>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7 gap-2">
                    @foreach($domainFilters as $f)
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['domain' => $f['value']])) }}"
                           @if($f['active']) aria-current="true" @endif
                           class="rounded-xl px-3 py-2 text-xs font-medium text-center transition-all flex items-center justify-center border {{ $f['active'] ? 'bg-[#0B0F14] text-white border-[#0B0F14] shadow-xs' : 'bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-200/60 border-transparent hover:border-slate-200' }}">
                            <span class="truncate">{{ $f['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Grille des Périodes Temporelles --}}
            <div class="p-3.5 sm:p-4 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <span class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider block">Période</span>
                    <p class="text-xs text-[#64748B]">
                        Du <strong class="font-medium text-[#0B0F14]">{{ $startDate->format('d/m/Y') }}</strong>
                        au <strong class="font-medium text-[#0B0F14]">{{ $endDate->format('d/m/Y') }}</strong>
                    </p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                    @foreach($periods as $key => $label)
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => $key])) }}"
                           @if($period === $key) aria-current="true" @endif
                           class="rounded-xl px-3 py-2 text-xs font-medium text-center transition-all flex items-center justify-center border {{ $period === $key ? 'bg-[#0066FF] text-white border-[#0066FF] shadow-xs' : 'bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-200/60 border-transparent hover:border-slate-200' }}">
                            {!! $label !!}
                        </a>
                    @endforeach
                    <button type="button" @click="custom = !custom" :aria-expanded="custom"
                            class="rounded-xl px-3 py-2 text-xs font-medium text-center transition-all flex items-center justify-center gap-1.5 border cursor-pointer {{ $period === 'custom' ? 'bg-[#0066FF] text-white border-[#0066FF] shadow-xs' : 'bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-200/60 border-transparent hover:border-slate-200' }}">
                        <span>Période personnalisée</span>
                        <svg class="h-3.5 w-3.5 transition-transform" :class="custom && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>

            <form x-show="custom" x-cloak method="GET" action="{{ route('dashboard') }}"
                  class="grid grid-cols-2 gap-3 p-3 text-xs sm:flex sm:flex-wrap sm:items-end sm:px-4">
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
                <button type="submit" class="col-span-2 rounded-lg bg-[#0066FF] px-4 py-2 font-medium text-white hover:bg-blue-600 sm:col-span-1">Appliquer</button>
            </form>
        </section>

        {{-- 3. Indicateurs financiers --}}
        <section aria-labelledby="fin-title">
            <div class="mb-3 flex items-baseline justify-between px-1">
                <h2 id="fin-title" class="text-sm font-semibold text-[#0B0F14]">Chiffre d'Affaires & Trésorerie</h2>
                <span class="text-xs text-[#64748B]">En {{ $currency }}</span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                {{-- CA : carte principale --}}
                <article class="flex flex-col justify-between rounded-2xl bg-[#0B0F14] p-5 text-white sm:col-span-2 lg:col-span-1 shadow-xs">
                    <div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-400">Chiffre d'Affaires · {{ $periodLabel }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-white/10 text-slate-200 uppercase tracking-wider">
                                {{ $period === 'this_quarter' ? 'T'.now()->quarter : ($period === 'today' ? 'Jour' : ($period === 'this_week' ? 'Semaine' : ($period === 'this_year' ? now()->year : 'Mois'))) }}
                            </span>
                        </div>
                        <p class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight">{{ $n($caPeriod) }}<span class="ml-1.5 text-xs font-normal text-slate-400">{{ $currency }}</span></p>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-1.5 border-t border-white/10 pt-3 text-[11px]">
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'today'])) }}" 
                           class="p-2 rounded-lg transition-all {{ $period === 'today' ? 'bg-white/20 ring-1 ring-white/40' : 'bg-white/5 hover:bg-white/10' }}"
                           title="Filtrer sur Aujourd'hui">
                            <dt class="text-slate-400 text-[10px] uppercase font-medium tracking-wider">CA Jour</dt>
                            <dd class="mt-0.5 font-semibold text-white text-xs truncate">{{ $n($caToday) }} <span class="text-[9px] font-normal text-slate-400">F</span></dd>
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'this_week'])) }}" 
                           class="p-2 rounded-lg transition-all {{ $period === 'this_week' ? 'bg-white/20 ring-1 ring-white/40' : 'bg-white/5 hover:bg-white/10' }}"
                           title="Filtrer sur Cette Semaine">
                            <dt class="text-slate-400 text-[10px] uppercase font-medium tracking-wider">CA Semaine</dt>
                            <dd class="mt-0.5 font-semibold text-white text-xs truncate">{{ $n($caWeek) }} <span class="text-[9px] font-normal text-slate-400">F</span></dd>
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'this_quarter'])) }}" 
                           class="p-2 rounded-lg transition-all {{ $period === 'this_quarter' ? 'bg-white/20 ring-1 ring-white/40' : 'bg-white/5 hover:bg-white/10' }}"
                           title="Filtrer sur Ce Trimestre">
                            <dt class="text-slate-400 text-[10px] uppercase font-medium tracking-wider">CA Trimestre</dt>
                            <dd class="mt-0.5 font-semibold text-white text-xs truncate">{{ $n($caQuarter) }} <span class="text-[9px] font-normal text-slate-400">F</span></dd>
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period' => 'this_year'])) }}" 
                           class="p-2 rounded-lg transition-all {{ $period === 'this_year' ? 'bg-white/20 ring-1 ring-white/40' : 'bg-white/5 hover:bg-white/10' }}"
                           title="Filtrer sur Cette Année">
                            <dt class="text-slate-400 text-[10px] uppercase font-medium tracking-wider">CA Annuel</dt>
                            <dd class="mt-0.5 font-semibold text-white text-xs truncate">{{ $n($caYear) }} <span class="text-[9px] font-normal text-slate-400">F</span></dd>
                        </a>
                    </dl>
                </article>

                {{-- Trésorerie --}}
                <article class="{{ $card }} flex flex-col justify-between p-5">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Trésorerie disponible</p>
                        <p class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight text-[#0B0F14]">{{ $n($tresorerie) }}<span class="ml-1.5 text-xs font-normal text-[#64748B]">{{ $currency }}</span></p>
                    </div>
                    <p class="mt-4 border-t border-[#E2E8F0] pt-3 text-[11px] text-[#64748B]">Caisses et banques cumulées</p>
                </article>

                {{-- Bénéfice --}}
                <article class="{{ $card }} flex flex-col justify-between p-5">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Bénéfice estimé</p>
                        <p class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight {{ $beneficeEstime >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $n($beneficeEstime) }}<span class="ml-1.5 text-xs font-normal text-[#64748B]">{{ $currency }}</span></p>
                    </div>
                    <p class="mt-4 flex justify-between gap-2 border-t border-[#E2E8F0] pt-3 text-[11px] text-[#64748B]">
                        Dépenses engagées <span class="font-medium text-[#0B0F14]">{{ $n($depensesPeriod) }}</span>
                    </p>
                </article>

                {{-- Impayés --}}
                <article class="{{ $card }} flex flex-col justify-between p-5">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Factures impayées</p>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $facturesImpayeesCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $facturesImpayeesCount }} en retard
                            </span>
                        </div>
                        <p class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight {{ $facturesImpayeesCount > 0 ? 'text-rose-600' : 'text-[#0B0F14]' }}">{{ $n($facturesImpayeesMontant) }}<span class="ml-1.5 text-xs font-normal text-[#64748B]">{{ $currency }}</span></p>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-[#E2E8F0] pt-3 text-[11px] text-[#64748B]">
                        Recouvrement
                        <a href="{{ route('commercial.invoices.index') }}" class="font-medium text-[#0066FF] hover:underline">Gérer les créances</a>
                    </div>
                </article>
            </div>
        </section>

        {{-- 4. Domaines et santé opérationnelle --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-12">
            <section class="{{ $card }} p-5 sm:p-6 lg:col-span-7">
                <x-domain-ca-chart 
                    :domain-performance="$domainPerformance" 
                    :total-consolidated-ca="$totalConsolidatedCa" 
                    :currency="$currency" 
                    :year="$now->year" 
                />
            </section>

            <section class="{{ $card }} flex flex-col p-5 sm:p-6 lg:col-span-5">
                <div class="border-b border-[#E2E8F0] pb-3">
                    <h3 class="text-sm font-semibold text-[#0B0F14]">Santé opérationnelle</h3>
                    <p class="text-xs text-[#64748B]">Flux clients et activité de l'atelier</p>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-2">
                    @foreach($kpis as [$label, $value, $color])
                        <div class="rounded-xl bg-[#F5F7FA] p-3.5">
                            <dt class="text-[11px] text-[#64748B]">{{ $label }}</dt>
                            <dd class="mt-1 text-xl font-bold {{ $color }}">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="mt-auto flex flex-col gap-1.5 border-t border-[#E2E8F0] pt-3 text-xs text-[#64748B] sm:flex-row sm:items-center sm:justify-between lg:flex-col lg:items-start xl:flex-row xl:items-center">
                    <span class="flex items-center gap-1.5 {{ $produitsEnRupture > 0 ? 'font-semibold text-rose-600' : '' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $produitsEnRupture > 0 ? 'bg-rose-600' : 'bg-slate-300' }}"></span>
                        {{ $produitsEnRupture }} Ruptures de stock · {{ $produitsBientotEnRupture }} Sous seuil minimal
                    </span>
                    <span>RDV du jour : <strong class="text-[#0066FF]">{{ $rdvDuJourCount }}</strong></span>
                </div>
            </section>
        </div>

        {{-- 5. Flux opérationnels --}}
        <section class="{{ $card }} overflow-hidden" x-data="{ tab: 'orders' }">
            <div class="flex flex-col gap-2 border-b border-[#E2E8F0] bg-[#F5F7FA]/60 px-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="-mx-1 flex gap-1.5 overflow-x-auto px-1 [scrollbar-width:none]" role="tablist">
                    @foreach($tabs as $key => $t)
                        <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'bg-white text-[#0066FF] font-semibold border-[#E2E8F0]' : 'border-transparent text-[#64748B] hover:text-[#0B0F14]'"
                                class="shrink-0 rounded-lg border px-3.5 py-1.5 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                            {{ $t['label'] }} ({{ $t['items']->count() }})
                        </button>
                    @endforeach
                    <button type="button" role="tab" @click="tab = 'logs'" :aria-selected="tab === 'logs'"
                            :class="tab === 'logs' ? 'bg-white text-[#0066FF] font-semibold border-[#E2E8F0]' : 'border-transparent text-[#64748B] hover:text-[#0B0F14]'"
                            class="shrink-0 rounded-lg border px-3.5 py-1.5 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                        Activité
                    </button>
                </div>
                <p class="text-xs text-[#64748B]">
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

                    {{-- Cartes mobile --}}
                    <ul class="space-y-2.5 md:hidden">
                        @forelse($t['items'] as $row)
                            <li>
                                <a href="{{ route($t['route'], $row) }}" class="flex items-center justify-between gap-3 rounded-xl border border-[#E2E8F0] p-3.5 active:bg-[#F5F7FA]">
                                    <div class="min-w-0">
                                        <p class="font-mono text-xs font-bold text-[#0066FF]">{{ $row->reference }}</p>
                                        <p class="mt-0.5 truncate text-sm font-medium text-[#0B0F14]">{{ $row->customer->name ?? 'Client' }}</p>
                                        <p class="mt-0.5 text-[11px] text-[#64748B]">{{ $row->date?->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-bold text-[#0B0F14]">{{ $n($row->total) }}</p>
                                        <p class="text-[11px] text-[#64748B]">{{ $currency }}</p>
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
                        <li class="flex flex-col gap-0.5 py-3 text-xs sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                            <p class="min-w-0">
                                <span class="font-semibold text-[#0B0F14]">{{ $activity->user->name ?? 'Système' }}</span>
                                <span class="ml-1.5 text-[#64748B]">{{ $activity->description }}</span>
                            </p>
                            <time class="shrink-0 text-[11px] text-[#64748B]">{{ $activity->created_at->diffForHumans() }}</time>
                        </li>
                    @empty
                        <li class="py-8 text-center text-xs text-[#64748B]">Aucune activité récente.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        {{-- 6. Accès aux ateliers --}}
        <section aria-labelledby="ws-title">
            <h2 id="ws-title" class="mb-3 px-1 text-sm font-semibold text-[#0B0F14]">Ateliers et pôles métiers</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($workshops as [$name, $desc, $routeName, $badge])
                    <a href="{{ route($routeName) }}"
                       class="group {{ $card }} p-4 transition-colors hover:border-[#0066FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] {{ $loop->last ? 'col-span-2 sm:col-span-1' : '' }}">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $badge }}">{{ $name }}</span>
                        <p class="mt-2.5 text-xs text-[#64748B] group-hover:text-[#0B0F14]">{{ $desc }}</p>
                    </a>
                @endforeach
            </div>
        </section>

    </div>
</x-layouts.app>