@php
    // ── Tokens de style partagés ────────────────────────────────────────────
    $input  = 'mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-hidden';
    $label  = 'block text-sm font-medium text-slate-700';
    $btn    = 'inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium transition focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 focus-visible:outline-hidden';
    $btnPri = $btn . ' bg-blue-600 text-white hover:bg-blue-700';
    $btnSec = $btn . ' border border-slate-300 bg-white text-slate-700 hover:bg-slate-50';
    $btnDark = $btn . ' bg-slate-900 text-white hover:bg-slate-800';
    $btnDanger = $btn . ' bg-rose-600 text-white hover:bg-rose-700';
    $card   = 'rounded-2xl border border-slate-200 bg-white';
    $cardHead = 'flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between';
    $empty  = 'px-5 py-10 text-center text-sm text-slate-400';
    $unit   = 'text-sm font-normal text-slate-400';

    $periods = [
        'today'   => ["Aujourd'hui", ['today', 'day']],
        'week'    => ['Semaine',     ['week', 'this_week']],
        'month'   => ['Ce mois',     ['month', 'this_month']],
        'quarter' => ['Trimestre',   ['quarter', 'this_quarter']],
        'year'    => ['Cette année', ['year', 'this_year']],
        'all'     => ['Tout',        ['all']],
    ];

    $tabs = [
        'comptes'             => 'Trésorerie',
        'creances_dettes'     => 'Créances & dettes',
        'rentabilite_budgets' => 'Rentabilité & budgets',
        'immobilisations'     => 'Immobilisations & Actifs',
        'depenses_validation' => 'Dépenses',
        'rapprochement_audit' => 'Rapprochement & audit',
        'clotures_assistant'  => 'Clôtures & IA',
    ];

    // Idéalement ces deux jeux de données viennent du contrôleur ($recentSessions, $recentExpenses)
    $recentSessions = $recentSessions ?? \App\Models\CashRegisterSession::with(['account', 'user'])->latest()->take(5)->get();
    $recentExpenses = $recentExpenses ?? \App\Models\Expense::with(['domain', 'supplier', 'user'])->latest()->take(10)->get();

    $overdueAmount = $overdueInvoices->sum(fn ($i) => max(0, $i->total - $i->paid_amount));
    $chartMax = max(1, collect($monthlyData)->max(fn ($m) => max($m['revenue'], $m['expense'])));
@endphp

<x-layouts.app title="Finance & Trésorerie">
    <div x-data="{
        activeTab: 'comptes',
        moreMenu: false,
        transferModal: false,
        paymentModal: false,
        sessionOpenModal: false,
        sessionCloseModal: false,   {{-- false ou id de la session à fermer --}}
        supplierInvoiceModal: false,
        reconciliationModal: false,
        closingModal: false,
        aiModal: false,
        aiQuestion: '',
        aiResponse: null,
        aiLoading: false,
        aiError: false,
        goTo(tab) {
            this.activeTab = tab;
            this.$nextTick(() => this.$refs.tabs.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        },
        askAi(queryText) {
            this.aiModal = true;
            this.aiQuestion = queryText || this.aiQuestion;
            if (!this.aiQuestion.trim()) return;
            this.aiLoading = true;
            this.aiError = false;
            this.aiResponse = null;
            fetch('{{ route('finance.ai-query') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ question: this.aiQuestion })
            })
            .then(res => { if (!res.ok) throw new Error(); return res.json(); })
            .then(data => { this.aiResponse = data; })
            .catch(() => { this.aiError = true; })
            .finally(() => { this.aiLoading = false; });
        }
    }" class="mx-auto w-full max-w-7xl space-y-6 pb-16">

        {{-- ── Messages flash ─────────────────────────────────────────────── --}}
        @if(session('success'))
            <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-600"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div role="alert" class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-rose-600"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ── En-tête ────────────────────────────────────────────────────── --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Finance & Trésorerie</h1>
                <p class="mt-1 text-sm text-slate-500">Comptes, caisses, règlements et clôtures.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('finance.expenses.create') }}" class="{{ $btnPri }} flex-1 sm:flex-none">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Dépense
                </a>
                <button type="button" @click="paymentModal = true" class="{{ $btnSec }} flex-1 sm:flex-none">Règlement</button>

                {{-- Menu secondaire --}}
                <div class="relative" @click.outside="moreMenu = false" @keydown.escape.window="moreMenu = false">
                    <button type="button" @click="moreMenu = !moreMenu" :aria-expanded="moreMenu" aria-haspopup="true"
                            class="{{ $btnSec }} w-10 px-0" aria-label="Plus d'actions">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="19" cy="12" r="1.75"/></svg>
                    </button>
                    <div x-show="moreMenu" x-cloak x-transition.origin.top.right
                         class="absolute right-0 z-40 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                        <button type="button" @click="moreMenu = false; transferModal = true" class="flex w-full items-center px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">Virement entre comptes</button>
                        <a href="{{ route('finance.cash-registers.index') }}" class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Gérer les caisses</a>
                        <a href="{{ route('finance.assets.index') }}" class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 font-medium text-[#0066FF]">🏢 Immobilisations & Actifs</a>
                        <a href="{{ route('finance.assets.rentals.index') }}" class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Locations de Matériel</a>
                        <button type="button" @click="moreMenu = false; aiModal = true" class="flex w-full items-center px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">Assistant IA</button>
                        <div class="my-1 border-t border-slate-100"></div>
                        <a href="{{ route('finance.export') }}" class="flex w-full items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Exporter en CSV</a>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Filtres & Navigation Temporelle ───────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 sm:p-4 shadow-xs space-y-3.5">
            {{-- Ligne 1 : Contrôle segmenté de période & Label période --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                {{-- Période : contrôle segmenté sans barre de défilement parasite --}}
                <nav class="flex items-center overflow-x-auto no-scrollbar py-0.5" aria-label="Période">
                    <div class="inline-flex gap-1 rounded-xl bg-slate-100/90 p-1">
                        @foreach($periods as $key => [$text, $aliases])
                            <a href="{{ route('finance.index', ['period' => $key, 'domain_id' => $domainId]) }}"
                               @if(in_array($period, $aliases, true)) aria-current="true" @endif
                               class="whitespace-nowrap rounded-lg px-3.5 py-1.5 text-xs font-semibold transition {{ in_array($period, $aliases, true) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                                {{ $text }}
                            </a>
                        @endforeach
                    </div>
                </nav>

                {{-- Date / Période courante --}}
                <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200/60 px-3 py-1.5 rounded-xl self-start sm:self-auto shrink-0">
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $periodLabel }}</span>
                    @if($selectedDomain)
                        <span class="text-slate-300">·</span>
                        <span class="font-bold text-[#0066FF]">{{ $selectedDomain->name }}</span>
                    @endif
                </div>
            </div>

            {{-- Ligne 2 : Filtre par Pôle d'Activité / Domaine --}}
            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-2.5">
                <span class="text-xs font-semibold text-slate-500 shrink-0 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Pôle d'activité :
                </span>

                {{-- Mobile : Sélecteur simple --}}
                <div class="sm:hidden w-full">
                    <select onchange="window.location = this.value"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] focus:outline-none">
                        <option value="{{ route('finance.index', ['period' => $period]) }}" @selected(! $domainId)>Tous les pôles</option>
                        @foreach($domains as $d)
                            <option value="{{ route('finance.index', ['period' => $period, 'domain_id' => $d->id]) }}" @selected((string) $domainId === (string) $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Desktop : Pastilles épurées sans répétition inutile du préfixe --}}
                <div class="hidden sm:flex items-center gap-1.5 flex-wrap">
                    <a href="{{ route('finance.index', ['period' => $period]) }}"
                       class="rounded-xl border px-3 py-1.5 text-xs font-semibold transition {{ ! $domainId ? 'border-[#0066FF] bg-blue-50 text-[#0066FF] shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">
                        Tous les pôles
                    </a>
                    @foreach($domains as $d)
                        @php
                            $shortName = trim(str_ireplace('IVOSPHERE', '', $d->name));
                        @endphp
                        <a href="{{ route('finance.index', ['period' => $period, 'domain_id' => $d->id]) }}"
                           title="{{ $d->name }}"
                           class="rounded-xl border px-3 py-1.5 text-xs font-semibold transition flex items-center gap-1.5 {{ (string) $domainId === (string) $d->id ? 'border-[#0066FF] bg-blue-50 text-[#0066FF] shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">
                            <span>{{ $shortName }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Indicateurs clés ───────────────────────────────────────────── --}}
        <section aria-label="Indicateurs clés" class="space-y-3">
            {{-- Flux de la période --}}
            <div class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 lg:grid-cols-4">
                <div class="col-span-2 bg-white p-5 lg:col-span-1">
                    <p class="text-sm text-slate-500">Trésorerie disponible</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-blue-700 tabular-nums">
                        {{ number_format($totalTresorerie, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Comptes, caisses et mobile money</p>
                </div>
                <div class="bg-white p-5">
                    <p class="text-sm text-slate-500">Encaissements</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900 tabular-nums sm:text-2xl">
                        {{ number_format($totalRecettes, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span>
                    </p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Entrées</p>
                </div>
                <div class="bg-white p-5">
                    <p class="text-sm text-slate-500">Dépenses</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900 tabular-nums sm:text-2xl">
                        {{ number_format($totalDepenses, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span>
                    </p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400"><span class="h-2 w-2 rounded-full bg-rose-400"></span>Sorties validées</p>
                </div>
                <div class="col-span-2 bg-white p-5 lg:col-span-1">
                    <p class="text-sm text-slate-500">Résultat net</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums {{ $benefice >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $benefice >= 0 ? '+' : '' }}{{ number_format($benefice, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Encaissements − dépenses</p>
                </div>
            </div>

            {{-- Engagements --}}
            <div class="grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-3">
                <div class="flex items-center justify-between gap-4 bg-white px-5 py-4 sm:block">
                    <div>
                        <p class="text-sm text-slate-500">Créances clients</p>
                        <p class="mt-0.5 text-xs text-slate-400 sm:hidden">À recevoir</p>
                    </div>
                    <p class="text-lg font-semibold text-slate-900 tabular-nums sm:mt-2">{{ number_format($totalCreances, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span></p>
                </div>
                <div class="flex items-center justify-between gap-4 bg-white px-5 py-4 sm:block">
                    <div>
                        <p class="text-sm text-slate-500">Dettes fournisseurs</p>
                        <p class="mt-0.5 text-xs text-slate-400 sm:hidden">À payer</p>
                    </div>
                    <p class="text-lg font-semibold text-slate-900 tabular-nums sm:mt-2">{{ number_format($totalDettes, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span></p>
                </div>
                <div class="flex items-center justify-between gap-4 bg-white px-5 py-4 sm:block">
                    <div>
                        <p class="text-sm text-slate-500">Factures impayées</p>
                        <p class="mt-0.5 text-xs text-rose-600 sm:hidden">{{ $overdueInvoices->count() }} en retard</p>
                    </div>
                    <p class="text-lg font-semibold text-slate-900 tabular-nums sm:mt-2">
                        {{ $facturesImpayeesCount }} <span class="{{ $unit }}">dont {{ $overdueInvoices->count() }} en retard</span>
                    </p>
                </div>
            </div>
        </section>

        {{-- ── Graphique + À traiter ──────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <section class="{{ $card }} lg:col-span-8">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Flux sur 12 mois</h2>
                        <p class="text-sm text-slate-500">Recettes et dépenses, exercice {{ now()->year }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-slate-600">
                        <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>Recettes</span>
                        <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-rose-400"></span>Dépenses</span>
                    </div>
                </div>

                <div class="overflow-x-auto px-5 py-5">
                    <div class="grid min-w-[34rem] grid-cols-12 gap-2 sm:min-w-0">
                        @foreach($monthlyData as $m)
                            @php
                                $revHeight = round(($m['revenue'] / $chartMax) * 100);
                                $expHeight = round(($m['expense'] / $chartMax) * 100);
                            @endphp
                            <div class="flex flex-col items-center gap-2">
                                <div class="flex h-40 w-full items-end justify-center gap-1">
                                    <div class="w-full max-w-3 rounded-t-sm bg-emerald-500 transition hover:bg-emerald-600"
                                         style="height: {{ max(2, $revHeight) }}%"
                                         title="Recettes : {{ number_format($m['revenue'], 0, ',', ' ') }} FCFA"></div>
                                    <div class="w-full max-w-3 rounded-t-sm bg-rose-400 transition hover:bg-rose-500"
                                         style="height: {{ max(2, $expHeight) }}%"
                                         title="Dépenses : {{ number_format($m['expense'], 0, ',', ' ') }} FCFA"></div>
                                </div>
                                <span class="text-xs text-slate-500">{{ $m['month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="{{ $card }} lg:col-span-4">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">À traiter</h2>
                </div>
                <div class="space-y-1 p-2">
                    <button type="button" @click="goTo('creances_dettes')"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-hidden">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-900">{{ $overdueInvoices->count() }} facture(s) en retard</span>
                            <span class="block text-sm text-slate-500 tabular-nums">{{ number_format($overdueAmount, 0, ',', ' ') }} FCFA en souffrance</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <button type="button" @click="goTo('depenses_validation')"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-hidden">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-900">{{ $pendingExpenses->count() }} dépense(s) à valider</span>
                            <span class="block text-sm text-slate-500 tabular-nums">{{ number_format($pendingExpenses->sum('amount'), 0, ',', ' ') }} FCFA en attente</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <button type="button" @click="goTo('comptes')"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-hidden">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-900">{{ $openSessions->count() }} caisse(s) ouverte(s)</span>
                            <span class="block text-sm text-slate-500">Sessions du jour en cours</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </section>
        </div>

        {{-- ── Onglets ────────────────────────────────────────────────────── --}}
        <div x-ref="tabs" class="scroll-mt-4 border-b border-slate-200">
            <div role="tablist" aria-label="Sections finance"
                 class="-mx-4 flex gap-1 overflow-x-auto px-4 sm:mx-0 sm:px-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach($tabs as $key => $text)
                    <button type="button" role="tab" @click="activeTab = '{{ $key }}'"
                            :aria-selected="activeTab === '{{ $key }}'"
                            :class="activeTab === '{{ $key }}' ? 'border-blue-600 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                            class="-mb-px shrink-0 whitespace-nowrap border-b-2 px-3 py-3 text-sm font-medium transition focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-hidden sm:px-4">
                        {{ $text }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ═════════════ 1. TRÉSORERIE ═════════════ --}}
        <div x-show="activeTab === 'comptes'" role="tabpanel" class="space-y-6">
            <section>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Comptes et caisses <span class="font-normal text-slate-400">({{ count($financialAccounts) }})</span></h2>
                    <a href="{{ route('finance.cash-registers.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Gérer les caisses</a>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($financialAccounts as $acc)
                        <article class="{{ $card }} flex flex-col justify-between gap-4 p-5 transition hover:border-slate-300">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-md border px-2 py-0.5 text-xs font-medium {{ $acc->type_badge['class'] }}">{{ $acc->type_badge['label'] }}</span>
                                    <span class="font-mono text-xs text-slate-400">{{ $acc->code }}</span>
                                </div>
                                <h3 class="mt-3 text-base font-semibold text-slate-900">{{ $acc->name }}</h3>
                                <p class="text-sm text-slate-500">{{ $acc->institution_name }}@if($acc->account_number) · {{ $acc->account_number }}@endif</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Solde disponible</p>
                                <p class="text-xl font-semibold text-slate-900 tabular-nums">{{ number_format($acc->balance, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span></p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Sessions de caisse</h2>
                        <p class="text-sm text-slate-500">Ouverture, comptage de clôture et contrôle des écarts.</p>
                    </div>
                    <button type="button" @click="sessionOpenModal = true" class="{{ $btnDark }} w-full sm:w-auto">Ouvrir la caisse du jour</button>
                </div>

                <ul class="divide-y divide-slate-100">
                    @forelse($recentSessions as $sess)
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900">{{ $sess->reference }}</span>
                                    <span class="text-sm text-slate-500">{{ $sess->account?->name }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sess->isOpen() ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $sess->isOpen() ? 'Ouverte' : 'Clôturée' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">
                                    Ouverte par {{ $sess->user?->name }} le {{ $sess->opened_at->format('d/m/Y H:i') }}
                                </p>
                                <dl class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm">
                                    <div class="flex gap-1.5"><dt class="text-slate-500">Fond initial</dt><dd class="font-medium text-slate-900 tabular-nums">{{ number_format($sess->opening_balance, 0, ',', ' ') }} FCFA</dd></div>
                                    @if(! $sess->isOpen())
                                        <div class="flex gap-1.5"><dt class="text-slate-500">Solde compté</dt><dd class="font-medium text-slate-900 tabular-nums">{{ number_format($sess->real_balance, 0, ',', ' ') }} FCFA</dd></div>
                                        @if($sess->discrepancy != 0)
                                            <div class="flex gap-1.5">
                                                <dt class="text-slate-500">Écart</dt>
                                                <dd class="font-medium tabular-nums {{ $sess->discrepancy < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $sess->discrepancy > 0 ? '+' : '' }}{{ number_format($sess->discrepancy, 0, ',', ' ') }} FCFA
                                                    @if($sess->discrepancy_reason)<span class="font-normal text-slate-500">({{ $sess->discrepancy_reason }})</span>@endif
                                                </dd>
                                            </div>
                                        @endif
                                    @endif
                                </dl>
                            </div>

                            @if($sess->isOpen())
                                <button type="button" @click="sessionCloseModal = {{ $sess->id }}" class="{{ $btnSec }} w-full shrink-0 sm:w-auto">Fermer la caisse</button>
                            @endif
                        </li>
                    @empty
                        <li class="{{ $empty }}">Aucune session enregistrée. Ouvrez la caisse du jour pour commencer.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- ═════════════ 2. CRÉANCES & DETTES ═════════════ --}}
        <div x-show="activeTab === 'creances_dettes'" x-cloak role="tabpanel" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Créances clients</h2>
                        <p class="text-sm text-slate-500">Impayé : <span class="font-medium text-slate-900 tabular-nums">{{ number_format($totalCreances, 0, ',', ' ') }} FCFA</span></p>
                    </div>
                    <button type="button" @click="paymentModal = true" class="{{ $btnPri }} w-full sm:w-auto">Encaisser</button>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($clientInvoices as $inv)
                        <li class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $inv->reference }}</p>
                                    <p class="truncate text-sm text-slate-500">{{ $inv->customer?->company_name }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $inv->days_overdue > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $inv->days_overdue > 0 ? 'Retard de '.$inv->days_overdue.' j' : 'À jour' }}
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 text-sm">
                                <span class="text-slate-500 tabular-nums">{{ number_format($inv->paid_amount, 0, ',', ' ') }} payés sur {{ number_format($inv->total, 0, ',', ' ') }}</span>
                                <span class="font-semibold text-slate-900 tabular-nums">Reste {{ number_format($inv->remaining_balance, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </li>
                    @empty
                        <li class="{{ $empty }}">Aucune créance en attente.</li>
                    @endforelse
                </ul>
            </section>

            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Dettes fournisseurs</h2>
                        <p class="text-sm text-slate-500">Total dû : <span class="font-medium text-slate-900 tabular-nums">{{ number_format($totalDettes, 0, ',', ' ') }} FCFA</span></p>
                    </div>
                    <button type="button" @click="supplierInvoiceModal = true" class="{{ $btnDark }} w-full sm:w-auto">Facture fournisseur</button>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($supplierInvoices as $si)
                        <li class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $si->reference }}</p>
                                    <p class="truncate text-sm text-slate-500">{{ $si->supplier?->name }}</p>
                                </div>
                                <span class="shrink-0 text-sm text-slate-500">Échéance {{ $si->due_date ? $si->due_date->format('d/m/Y') : '—' }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 text-sm">
                                <span class="text-slate-500 tabular-nums">{{ number_format($si->paid_amount, 0, ',', ' ') }} payés sur {{ number_format($si->total_amount, 0, ',', ' ') }}</span>
                                <span class="font-semibold text-slate-900 tabular-nums">Reste {{ number_format($si->remaining_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </li>
                    @empty
                        <li class="{{ $empty }}">Aucune dette fournisseur enregistrée.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- ═════════════ 3. RENTABILITÉ & BUDGETS ═════════════ --}}
        <div x-show="activeTab === 'rentabilite_budgets'" x-cloak role="tabpanel" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="{{ $card }}">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Rentabilité par pôle</h2>
                    <p class="text-sm text-slate-500">Revenus, dépenses et résultat net.</p>
                </div>

                {{-- Desktop --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-medium">Pôle</th>
                                <th scope="col" class="px-3 py-3 text-right font-medium">Revenus</th>
                                <th scope="col" class="px-3 py-3 text-right font-medium">Dépenses</th>
                                <th scope="col" class="px-3 py-3 text-right font-medium">Net</th>
                                <th scope="col" class="px-5 py-3 text-right font-medium">Marge</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 tabular-nums">
                            @foreach($domainProfitability as $row)
                                <tr>
                                    <th scope="row" class="px-5 py-3.5 font-medium text-slate-900">{{ $row['domain']->name }}</th>
                                    <td class="px-3 py-3.5 text-right text-slate-700">{{ number_format($row['revenues'], 0, ',', ' ') }}</td>
                                    <td class="px-3 py-3.5 text-right text-slate-700">{{ number_format($row['expenses'], 0, ',', ' ') }}</td>
                                    <td class="px-3 py-3.5 text-right font-semibold {{ $row['net'] >= 0 ? 'text-slate-900' : 'text-rose-600' }}">{{ number_format($row['net'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-medium {{ $row['margin'] >= 20 ? 'text-emerald-600' : 'text-slate-600' }}">{{ $row['margin'] }} %</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-400">Montants en FCFA.</p>
                </div>

                {{-- Mobile --}}
                <ul class="divide-y divide-slate-100 md:hidden">
                    @foreach($domainProfitability as $row)
                        <li class="px-5 py-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                    <span class="h-2 w-2 rounded-full {{ $row['net'] >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $row['domain']->name }}
                                </span>
                                <span class="text-sm font-medium {{ $row['margin'] >= 20 ? 'text-emerald-600' : 'text-slate-500' }}">Marge {{ $row['margin'] }} %</span>
                            </div>
                            <dl class="mt-3 grid grid-cols-3 gap-2 text-sm">
                                <div><dt class="text-xs text-slate-400">Revenus</dt><dd class="font-medium text-slate-900 tabular-nums">{{ number_format($row['revenues'], 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-slate-400">Dépenses</dt><dd class="font-medium text-slate-900 tabular-nums">{{ number_format($row['expenses'], 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-slate-400">Net</dt><dd class="font-semibold tabular-nums {{ $row['net'] >= 0 ? 'text-slate-900' : 'text-rose-600' }}">{{ number_format($row['net'], 0, ',', ' ') }}</dd></div>
                            </dl>
                        </li>
                    @endforeach
                    <li class="px-5 py-3 text-xs text-slate-400">Montants en FCFA.</li>
                </ul>
            </section>

            <section class="{{ $card }}">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Budget réel et prévisionnel</h2>
                    <p class="text-sm text-slate-500">Consommation et marge disponible par pôle.</p>
                </div>
                <ul class="divide-y divide-slate-100">
                    @foreach($budgets as $b)
                        <li class="px-5 py-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <span class="text-sm font-semibold text-slate-900">{{ $b['domain_name'] }}</span>
                                <span class="text-sm text-slate-500 tabular-nums">{{ number_format($b['real_spent'], 0, ',', ' ') }} / {{ number_format($b['budget_amount'], 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100"
                                 role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(100, $b['usage_percent']) }}">
                                <div class="h-full rounded-full {{ $b['is_overbudget'] ? 'bg-rose-500' : 'bg-blue-600' }}" style="width: {{ min(100, $b['usage_percent']) }}%"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="{{ $b['is_overbudget'] ? 'font-medium text-rose-600' : 'text-slate-500' }}">{{ $b['usage_percent'] }} % consommé</span>
                                <span class="text-slate-500">Disponible <span class="font-medium text-emerald-600 tabular-nums">{{ number_format($b['available'], 0, ',', ' ') }} FCFA</span></span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- ═════════════ IMMOBILISATIONS & ACTIFS ═════════════ --}}
        <div x-show="activeTab === 'immobilisations'" x-cloak role="tabpanel" class="space-y-6">
            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                            <span>🏢 Immobilisations, Amortissements & Rentabilité</span>
                        </h2>
                        <p class="text-sm text-slate-500">
                            Distinction entre valeur de l'actif, chiffre d'affaires commercial généré et rentabilité nette.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('finance.assets.rentals.index') }}" class="{{ $btnSec }} text-purple-700">
                            Locations Matériel
                        </a>
                        <a href="{{ route('finance.assets.index') }}" class="{{ $btnPri }}">
                            Consulter le Registre 360°
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Notion A --}}
                        <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-blue-600 text-xs font-bold text-white mb-2">A</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">Valeur de l'Actif</h3>
                            <p class="text-xs text-slate-600 mt-1">Ce que l'entreprise possède (Valeur brute d'acquisition, amortissements cumulés et VNC).</p>
                        </div>

                        {{-- Notion B --}}
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-600 text-xs font-bold text-white mb-2">B</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-900">Chiffre d'Affaires Généré</h3>
                            <p class="text-xs text-slate-600 mt-1">Ce que l'utilisation du matériel permet de facturer (Prestations directes & Locations externes).</p>
                        </div>

                        {{-- Notion C --}}
                        <div class="rounded-xl border border-amber-100 bg-amber-50/40 p-4">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-amber-600 text-xs font-bold text-white mb-2">C</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">Rentabilité & ROI</h3>
                            <p class="text-xs text-slate-600 mt-1">CA généré − coûts de maintenance − amortissements = Contribution nette réelle de chaque équipement.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Module complet Immobilisations & Actifs</p>
                            <p class="text-xs text-slate-500">Échéancier Syscohada, carnet de maintenance, locations clients et tags QR pour inventaire.</p>
                        </div>
                        <a href="{{ route('finance.assets.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#0066FF] px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition">
                            Ouvrir le module Immobilisations →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═════════════ 4. DÉPENSES ═════════════ --}}
        <section x-show="activeTab === 'depenses_validation'" x-cloak role="tabpanel" class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Validation des dépenses</h2>
                    <p class="text-sm text-slate-500">Moins de 50 000 FCFA : responsable · 50 000 à 250 000 : finance · plus de 250 000 : direction.</p>
                </div>
                <a href="{{ route('finance.expenses.create') }}" class="{{ $btnPri }} w-full sm:w-auto">Nouvelle dépense</a>
            </div>

            <ul class="divide-y divide-slate-100">
                @forelse($recentExpenses as $exp)
                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-slate-900">{{ $exp->reference }}</span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $exp->category }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $exp->status === 'validee' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $exp->status === 'validee' ? 'Validée' : 'En attente' }}
                                </span>
                                <span class="text-xs text-slate-400">Niveau : {{ ucfirst($exp->required_approval_level ?? 'responsable') }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{{ $exp->description }}</p>
                            <p class="text-sm text-slate-400">{{ $exp->supplier?->name ?? 'Sans fournisseur' }} · {{ $exp->date ? $exp->date->format('d/m/Y') : '—' }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <span class="text-base font-semibold text-slate-900 tabular-nums">{{ number_format($exp->amount, 0, ',', ' ') }} <span class="{{ $unit }}">FCFA</span></span>
                            @if($exp->status !== 'validee')
                                <form action="{{ route('finance.expenses.approve', $exp) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $btnDark }} h-9 px-3">Approuver</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="{{ $empty }}">Aucune dépense enregistrée.</li>
                @endforelse
            </ul>
        </section>

        {{-- ═════════════ 5. RAPPROCHEMENT & AUDIT ═════════════ --}}
        <div x-show="activeTab === 'rapprochement_audit'" x-cloak role="tabpanel" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Rapprochement bancaire</h2>
                        <p class="text-sm text-slate-500">Relevé de banque comparé au solde système.</p>
                    </div>
                    <button type="button" @click="reconciliationModal = true" class="{{ $btnDark }} w-full sm:w-auto">Nouveau rapprochement</button>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($reconciliations as $rec)
                        <li class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 truncate text-sm font-semibold text-slate-900">{{ $rec->reference }} <span class="font-normal text-slate-500">· {{ $rec->account?->name }}</span></p>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $rec->status === 'rapproche' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $rec->status === 'rapproche' ? 'Rapproché' : 'Écart à justifier' }}
                                </span>
                            </div>
                            <dl class="mt-2 grid grid-cols-3 gap-2 text-sm">
                                <div><dt class="text-xs text-slate-400">Relevé</dt><dd class="font-medium text-slate-900 tabular-nums">{{ number_format($rec->statement_balance, 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-slate-400">Système</dt><dd class="font-medium text-slate-900 tabular-nums">{{ number_format($rec->system_balance, 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-xs text-slate-400">Écart</dt><dd class="font-semibold tabular-nums {{ $rec->discrepancy == 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($rec->discrepancy, 0, ',', ' ') }}</dd></div>
                            </dl>
                        </li>
                    @empty
                        <li class="{{ $empty }}">Aucun rapprochement enregistré.</li>
                    @endforelse
                </ul>
            </section>

            <section class="{{ $card }}">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Journal d'audit</h2>
                    <p class="text-sm text-slate-500">Historique non modifiable des montants et validations.</p>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        <li class="px-5 py-4 text-sm">
                            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                <span class="font-medium text-slate-900">{{ $log->user?->name }} <span class="font-normal text-slate-500">· {{ $log->action }}</span></span>
                                <time class="text-xs text-slate-400">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '—' }}</time>
                            </div>
                            <p class="mt-1 text-slate-600">
                                {{ $log->reason }}
                                @if($log->amount_before || $log->amount_after)
                                    <span class="text-slate-500 tabular-nums">— {{ number_format($log->amount_before, 0, ',', ' ') }} → <span class="font-medium text-slate-900">{{ number_format($log->amount_after, 0, ',', ' ') }} FCFA</span></span>
                                @endif
                            </p>
                        </li>
                    @empty
                        <li class="{{ $empty }}">Aucune entrée d'audit.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- ═════════════ 6. CLÔTURES & IA ═════════════ --}}
        <div x-show="activeTab === 'clotures_assistant'" x-cloak role="tabpanel" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Clôtures mensuelles</h2>
                        <p class="text-sm text-slate-500">Un mois clôturé ne peut plus être modifié.</p>
                    </div>
                    <button type="button" @click="closingModal = true" class="{{ $btnDark }} w-full sm:w-auto">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Clôturer le mois
                    </button>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($closings as $c)
                        <li class="flex items-start justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">{{ $c->period_label }}
                                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Clôturé</span>
                                </p>
                                <p class="mt-1 text-sm text-slate-500 tabular-nums">
                                    Recettes {{ number_format($c->total_revenues, 0, ',', ' ') }} · Dépenses {{ number_format($c->total_expenses, 0, ',', ' ') }}
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-semibold tabular-nums {{ $c->net_result >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($c->net_result, 0, ',', ' ') }} FCFA</p>
                                <p class="text-xs text-slate-400">par {{ $c->closedBy?->name }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="{{ $empty }}">Aucune période clôturée pour l'instant.</li>
                    @endforelse
                </ul>
            </section>

            <section class="{{ $card }}">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Assistant financier</h2>
                    <p class="text-sm text-slate-500">Posez une question sur vos chiffres en langage naturel.</p>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex flex-col gap-2">
                        <button type="button" @click="askAi('Quelle est notre trésorerie disponible ?')" class="rounded-lg border border-slate-200 px-3 py-2.5 text-left text-sm text-slate-700 transition hover:border-blue-600 hover:text-blue-700">Quelle est notre trésorerie disponible ?</button>
                        <button type="button" @click="askAi('Quelles factures sont en retard ?')" class="rounded-lg border border-slate-200 px-3 py-2.5 text-left text-sm text-slate-700 transition hover:border-blue-600 hover:text-blue-700">Quelles factures sont en retard ?</button>
                        <button type="button" @click="askAi('Combien avons-nous dépensé en TECH ce mois-ci ?')" class="rounded-lg border border-slate-200 px-3 py-2.5 text-left text-sm text-slate-700 transition hover:border-blue-600 hover:text-blue-700">Combien avons-nous dépensé en TECH ce mois-ci ?</button>
                    </div>
                    <button type="button" @click="aiModal = true" class="{{ $btnSec }} w-full">Poser une autre question</button>
                </div>
            </section>
        </div>

        {{-- ═════════════ MODALES ═════════════ --}}

        {{-- Virement --}}
        <x-finance.modal show="transferModal" close="transferModal = false" title="Virement entre comptes">
            <form action="{{ route('finance.transfers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="from_account_id" class="{{ $label }}">Compte source (débit)</label>
                    <select id="from_account_id" name="from_account_id" required class="{{ $input }}">
                        @foreach($financialAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} — {{ number_format($acc->balance, 0, ',', ' ') }} FCFA</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="to_account_id" class="{{ $label }}">Compte destination (crédit)</label>
                    <select id="to_account_id" name="to_account_id" required class="{{ $input }}">
                        @foreach($financialAccounts->reverse() as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} — {{ number_format($acc->balance, 0, ',', ' ') }} FCFA</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="transfer_amount" class="{{ $label }}">Montant (FCFA)</label>
                        <input id="transfer_amount" type="number" inputmode="numeric" name="amount" min="1" required placeholder="500 000" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="transfer_fee" class="{{ $label }}">Frais</label>
                        <input id="transfer_fee" type="number" inputmode="numeric" name="fee" min="0" value="0" class="{{ $input }}">
                    </div>
                </div>
                <div>
                    <label for="transfer_reason" class="{{ $label }}">Motif</label>
                    <input id="transfer_reason" type="text" name="reason" placeholder="Ex. : alimentation de la caisse principale" class="{{ $input }}">
                </div>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="transferModal = false" class="{{ $btnSec }} w-full sm:w-auto">Annuler</button>
                    <button type="submit" class="{{ $btnPri }} w-full sm:w-auto">Valider le virement</button>
                </div>
            </form>
        </x-finance.modal>

        {{-- Règlement client --}}
        <x-finance.modal show="paymentModal" close="paymentModal = false" title="Enregistrer un règlement client">
            <form action="{{ route('finance.payments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="invoice_id" class="{{ $label }}">Facture client</label>
                    <select id="invoice_id" name="invoice_id" required class="{{ $input }}">
                        @foreach($clientInvoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->reference }} — {{ $inv->customer?->company_name }} (reste {{ number_format($inv->remaining_balance, 0, ',', ' ') }} FCFA)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="financial_account_id" class="{{ $label }}">Compte récepteur</label>
                    <select id="financial_account_id" name="financial_account_id" required class="{{ $input }}">
                        @foreach($financialAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="payment_amount" class="{{ $label }}">Montant reçu (FCFA)</label>
                        <input id="payment_amount" type="number" inputmode="numeric" name="amount" min="1" required placeholder="300 000" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="payment_method" class="{{ $label }}">Mode de règlement</label>
                        <select id="payment_method" name="method" class="{{ $input }}">
                            <option value="especes">Espèces</option>
                            <option value="mobile_money">Mobile money (Wave / OM)</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="cheque">Chèque</option>
                            <option value="carte">Carte bancaire</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="payment_date" class="{{ $label }}">Date d'encaissement</label>
                    <input id="payment_date" type="date" name="date" value="{{ now()->toDateString() }}" required class="{{ $input }}">
                </div>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="paymentModal = false" class="{{ $btnSec }} w-full sm:w-auto">Annuler</button>
                    <button type="submit" class="{{ $btnPri }} w-full sm:w-auto">Encaisser</button>
                </div>
            </form>
        </x-finance.modal>

        {{-- Facture fournisseur --}}
        <x-finance.modal show="supplierInvoiceModal" close="supplierInvoiceModal = false" title="Facture fournisseur">
            <form action="{{ route('finance.supplier-invoices.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="supplier_id" class="{{ $label }}">Fournisseur</label>
                    <select id="supplier_id" name="supplier_id" required class="{{ $input }}">
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="si_domain_id" class="{{ $label }}">Pôle concerné</label>
                    <select id="si_domain_id" name="domain_id" class="{{ $input }}">
                        <option value="">Général</option>
                        @foreach($domains as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="si_total" class="{{ $label }}">Montant total (FCFA)</label>
                        <input id="si_total" type="number" inputmode="numeric" name="total_amount" min="1" required placeholder="800 000" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="si_due" class="{{ $label }}">Date d'échéance</label>
                        <input id="si_due" type="date" name="due_date" required class="{{ $input }}">
                    </div>
                </div>
                <div>
                    <label for="si_date" class="{{ $label }}">Date de la facture</label>
                    <input id="si_date" type="date" name="date" value="{{ now()->toDateString() }}" required class="{{ $input }}">
                </div>
                <div>
                    <label for="si_notes" class="{{ $label }}">Notes ou référence papier</label>
                    <input id="si_notes" type="text" name="notes" placeholder="Ex. : réf. FF-0089, papier offset" class="{{ $input }}">
                </div>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="supplierInvoiceModal = false" class="{{ $btnSec }} w-full sm:w-auto">Annuler</button>
                    <button type="submit" class="{{ $btnDark }} w-full sm:w-auto">Enregistrer la dette</button>
                </div>
            </form>
        </x-finance.modal>

        {{-- Clôture de mois --}}
        <x-finance.modal show="closingModal" close="closingModal = false" title="Clôturer la période">
            <form action="{{ route('finance.closings.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-sm text-amber-900">
                    Une fois clôturée, la période ne peut plus être modifiée sans procédure de réouverture.
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="closing_year" class="{{ $label }}">Année</label>
                        <input id="closing_year" type="number" name="year" value="{{ now()->year }}" required class="{{ $input }}">
                    </div>
                    <div>
                        <label for="closing_month" class="{{ $label }}">Mois (1 à 12)</label>
                        <input id="closing_month" type="number" name="month" value="{{ now()->month }}" min="1" max="12" required class="{{ $input }}">
                    </div>
                </div>
                <div>
                    <label for="closing_notes" class="{{ $label }}">Observations</label>
                    <textarea id="closing_notes" name="notes" rows="3" placeholder="Ex. : caisses vérifiées, factures pointées" class="{{ $input }}"></textarea>
                </div>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="closingModal = false" class="{{ $btnSec }} w-full sm:w-auto">Annuler</button>
                    <button type="submit" class="{{ $btnDark }} w-full sm:w-auto">Confirmer la clôture</button>
                </div>
            </form>
        </x-finance.modal>

        {{-- Ouverture de caisse --}}
        <x-finance.modal show="sessionOpenModal" close="sessionOpenModal = false" title="Ouvrir la caisse du jour">
            <form action="{{ route('finance.sessions.open') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="open_account" class="{{ $label }}">Caisse</label>
                    <select id="open_account" name="financial_account_id" required class="{{ $input }}">
                        @foreach($financialAccounts->where('type', 'caisse') as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="opening_balance" class="{{ $label }}">Fond de caisse initial (FCFA)</label>
                    <input id="opening_balance" type="number" inputmode="numeric" name="opening_balance" min="0" value="500000" required class="{{ $input }}">
                </div>
                <div>
                    <label for="open_notes" class="{{ $label }}">Observations</label>
                    <input id="open_notes" type="text" name="notes" placeholder="Ex. : fond de caisse vérifié ce matin" class="{{ $input }}">
                </div>
                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="sessionOpenModal = false" class="{{ $btnSec }} w-full sm:w-auto">Annuler</button>
                    <button type="submit" class="{{ $btnDark }} w-full sm:w-auto">Ouvrir la caisse</button>
                </div>
            </form>
        </x-finance.modal>

        {{-- Fermeture de caisse : une modale par session ouverte --}}
        @foreach($openSessions as $openSession)
            <x-finance.modal :show="'sessionCloseModal === '.$openSession->id" close="sessionCloseModal = false"
                             :title="'Fermer la caisse — '.($openSession->account?->name ?? $openSession->reference)">
                <form action="{{ route('finance.sessions.close', $openSession) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Fond initial : <span class="font-semibold text-slate-900 tabular-nums">{{ number_format($openSession->opening_balance, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div>
                        <label for="real_balance_{{ $openSession->id }}" class="{{ $label }}">Solde réel compté (FCFA)</label>
                        <input id="real_balance_{{ $openSession->id }}" type="number" inputmode="numeric" name="real_balance" min="0" required class="{{ $input }}">
                    </div>
                    <div>
                        <label for="discrepancy_{{ $openSession->id }}" class="{{ $label }}">Justification de l'écart</label>
                        <input id="discrepancy_{{ $openSession->id }}" type="text" name="discrepancy_reason" placeholder="Obligatoire s'il y a un écart" class="{{ $input }}">
                    </div>
                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" @click="sessionCloseModal = false" class="{{ $btnSec }} w-full sm:w-auto">Annuler</button>
                        <button type="submit" class="{{ $btnDanger }} w-full sm:w-auto">Fermer la caisse</button>
                    </div>
                </form>
            </x-finance.modal>
        @endforeach

        {{-- Assistant IA --}}
        <x-finance.modal show="aiModal" close="aiModal = false" title="Assistant financier" maxWidth="sm:max-w-lg">
            <div class="space-y-4">
                <p class="text-sm text-slate-500">Posez une question sur vos finances en langage naturel.</p>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <input type="text" x-model="aiQuestion" @keydown.enter.prevent="askAi()"
                           placeholder="Ex. : quel est notre bénéfice net ?" aria-label="Votre question"
                           class="block h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-hidden">
                    <button type="button" @click="askAi()" :disabled="aiLoading" class="{{ $btnPri }} w-full disabled:opacity-50 sm:w-auto">Demander</button>
                </div>

                <div x-show="aiLoading" x-cloak class="flex items-center gap-2 py-2 text-sm text-slate-500" role="status">
                    <svg class="h-4 w-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Analyse en cours…
                </div>

                <div x-show="aiError" x-cloak class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-sm text-rose-900" role="alert">
                    La réponse n'a pas pu être obtenue. Vérifiez votre connexion et réessayez.
                </div>

                <div x-show="aiResponse" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="whitespace-pre-line text-sm leading-relaxed text-slate-800" x-text="aiResponse ? aiResponse.answer : ''"></p>
                </div>
            </div>
        </x-finance.modal>

        {{-- Rapprochement : le formulaire de la modale n'était pas fourni dans la vue d'origine ;
             brancher ici votre formulaire existant. --}}
    </div>
</x-layouts.app>