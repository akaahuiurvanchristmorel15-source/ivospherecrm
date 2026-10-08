<x-layouts.app title="Centre de Pilotage — Direction Générale">
    <div class="space-y-6">

        <!-- Top Header & Filter Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800 font-bold uppercase tracking-wider">Cockpit Exécutif</span>
                    <span class="text-xs text-slate-500">Direction Générale</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Centre de Commandement</h1>
            </div>

            <!-- Filters Toolbar (Responsive Desktop & Mobile) -->
            <div class="flex flex-wrap items-center gap-2 max-w-full">
                <!-- Period Filter (Horizontally Scrollable on Mobile) -->
                <div class="overflow-x-auto max-w-full [scrollbar-width:none] -mx-0 px-1 py-0.5">
                    <div class="inline-flex items-center rounded-xl border border-[#E2E8F0] bg-white p-1 text-xs shadow-xs shrink-0">
                        @foreach(['today' => 'Aujourd\'hui', 'week' => 'Semaine', 'month' => 'Ce mois', 'quarter' => 'Trimestre', 'year' => 'Année', 'all' => 'Tous'] as $key => $lbl)
                            <a 
                                href="{{ route('command-center.index', ['period' => $key, 'domain' => request('domain')]) }}"
                                class="px-2.5 sm:px-3 py-1.5 rounded-lg font-medium transition-colors shrink-0 {{ $period === $key ? 'bg-[#0066FF] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-[#0B0F14] hover:bg-slate-50' }}"
                            >
                                {{ $lbl }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Export / Print -->
                <button onclick="window.print()" class="px-3 py-2 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#0B0F14] hover:bg-slate-50 shadow-xs flex items-center gap-1.5 transition-colors shrink-0 touch-target">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Imprimer</span>
                </button>
            </div>
        </div>

        <!-- Domain Selection Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a 
                href="{{ route('command-center.index', ['period' => $period]) }}"
                class="px-3 py-1.5 rounded-full font-medium shrink-0 transition-colors {{ !request('domain') || request('domain') === 'all' ? 'bg-[#0B0F14] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Tous les pôles
            </a>
            @foreach($domains as $d)
                <a 
                    href="{{ route('command-center.index', ['period' => $period, 'domain' => $d->code]) }}"
                    class="px-3 py-1.5 rounded-full font-medium shrink-0 transition-colors flex items-center gap-1.5 {{ request('domain') === $d->code ? 'bg-[#0066FF] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
                >
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $d->color ?? '#0066FF' }}"></span>
                    <span>{{ $d->name }}</span>
                </a>
            @endforeach
        </div>

        <!-- Row 1: Key Executive KPIs (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CA Global -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/50 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Chiffre d'Affaires</span>
                    <span class="w-8 h-8 rounded-lg bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-[#0B0F14] mt-3 tracking-tight">
                    {{ number_format($totalRevenue, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span>
                </p>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ $ordersCount }} commande(s) conclue(s)</span>
                    <span class="text-emerald-600 font-semibold">Taux conv. {{ $conversionRate }}%</span>
                </div>
            </div>

            <!-- Bénéfice Net Estimé -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/50 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Bénéfice Net Estimé</span>
                    <span class="w-8 h-8 rounded-lg {{ $netProfit >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-extrabold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-3 tracking-tight">
                    {{ number_format($netProfit, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span>
                </p>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span>Dépenses : {{ number_format($totalExpenses, 0, ',', ' ') }} F</span>
                    <span class="font-semibold {{ $profitMargin >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Marge {{ $profitMargin }}%</span>
                </div>
            </div>

            <!-- Trésorerie Disponible -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/50 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Trésorerie Disponible</span>
                    <span class="w-8 h-8 rounded-lg bg-[#0B0F14]/10 text-[#0B0F14] flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-[#0B0F14] mt-3 tracking-tight">
                    {{ number_format($cashAvailable, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span>
                </p>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span>Soldes des caisses actives</span>
                    <span class="text-[#0066FF] font-medium">Liquidité OK</span>
                </div>
            </div>

            <!-- Créances & Retards -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/50 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Créances Clients Échues</span>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-rose-600 mt-3 tracking-tight">
                    {{ number_format($overdueAmount, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span>
                </p>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ $overdueCount }} facture(s) en retard</span>
                    <span class="text-slate-600">Total : {{ number_format($totalReceivables, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        <!-- Row 2: Performance par Pôle & Alertes Direction -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- CA par Domaine (2 colonnes) -->
            <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-sm font-bold text-[#0B0F14] uppercase tracking-wider">Contribution par Domaine d'Activité</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Répartition du chiffre d'affaires.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-md bg-[#F5F7FA] text-xs font-semibold text-[#0B0F14]">
                        100 % = {{ number_format($totalRevenue, 0, ',', ' ') }} FCFA
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach($domainRevenues as $dr)
                        <div class="p-3.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]/60 hover:border-[#0066FF]/40 transition-colors">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $dr['color'] }}"></span>
                                    <span class="font-bold text-[#0B0F14]">{{ $dr['name'] }}</span>
                                    <span class="text-slate-400 text-[11px]">({{ $dr['code'] }})</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-[#0B0F14]">{{ number_format($dr['revenue'], 0, ',', ' ') }} FCFA</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-[#0066FF] border border-[#E2E8F0]">{{ $dr['share'] }} %</span>
                                </div>
                            </div>
                            <!-- Bar progress -->
                            <div class="w-full bg-[#E2E8F0] h-2 rounded-full overflow-hidden">
                                <div 
                                    class="h-full rounded-full transition-all duration-500" 
                                    style="width: {{ $dr['share'] }}%; background-color: {{ $dr['color'] }}"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Direction Smart Alerts (1 colonne) -->
            <div class="bg-white rounded-xl p-6 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-[#0B0F14] uppercase tracking-wider">Alertes Prioritaires</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                {{ $alertsCount }} active(s)
                            </span>
                        </div>
                        <a href="{{ route('alerts.index') }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                            Voir tout
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($alerts as $alert)
                            <div class="p-3 rounded-xl border border-[#E2E8F0] hover:bg-[#F5F7FA] transition-colors text-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $alert->priority === 'urgente' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $alert->priority }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">{{ $alert->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="font-semibold text-[#0B0F14] leading-snug">{{ $alert->title }}</p>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $alert->description }}</p>
                                @if($alert->action_url)
                                    <a href="{{ $alert->action_url }}" class="inline-flex items-center gap-1 text-[11px] font-medium text-[#0066FF] mt-2 hover:underline">
                                        <span>Traiter immédiatement</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <p class="text-xs font-medium text-[#0B0F14]">Aucune alerte critique en cours</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tous les indicateurs sont au vert.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E2E8F0] mt-4">
                    <form method="POST" action="{{ route('alerts.run-checks') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white border border-[#E2E8F0] text-xs font-semibold text-[#0B0F14] transition-colors flex items-center justify-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Lancer l'audit automatisé des alertes</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Row 3: Opérations, Stocks & Top Débiteurs -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Stocks & Valorisation -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">État des Stocks</h3>
                    <a href="{{ route('stock.index') }}" class="text-xs text-[#0066FF] hover:underline font-medium">Gérer</a>
                </div>
                <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] mb-3">
                    <p class="text-[11px] text-slate-500">Valeur marchande des stocks :</p>
                    <p class="text-lg font-bold text-[#0B0F14]">{{ number_format($stockValue, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="text-slate-500">Articles sous seuil d'alerte :</span>
                    <span class="font-bold {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $lowStockCount }} article(s)</span>
                </div>
            </div>

            <!-- Projets & Événements en cours -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Opérations en Cours</h3>
                    <span class="text-[10px] text-slate-400">Pôles TECH & MEDIA</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <span class="text-slate-600 font-medium">Projets TECH actifs :</span>
                        <span class="font-bold text-[#0B0F14] px-2 py-0.5 bg-white rounded border border-[#E2E8F0]">{{ $activeProjectsCount }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <span class="text-slate-600 font-medium">Événements MEDIA programmés :</span>
                        <span class="font-bold text-[#0B0F14] px-2 py-0.5 bg-white rounded border border-[#E2E8F0]">{{ $upcomingEventsCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Top Débiteurs -->
            <div class="bg-white rounded-xl p-5 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Top Créances Clients</h3>
                    <a href="{{ route('commercial.invoices.index') }}" class="text-xs text-[#0066FF] hover:underline font-medium">Recouvrement</a>
                </div>
                <div class="space-y-2">
                    @forelse($topDebtors as $deb)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100 last:border-0">
                            <span class="font-medium text-[#0B0F14] truncate max-w-[140px]">
                                {{ $deb['company_name'] ?: ($deb['first_name'] . ' ' . $deb['last_name']) }}
                            </span>
                            <span class="font-bold text-rose-600">{{ number_format($deb['balance'], 0, ',', ' ') }} F</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-3 text-center">Aucune créance enregistrée.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
