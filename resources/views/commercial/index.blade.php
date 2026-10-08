<x-layouts.app>
    <x-slot:title>Tableau de bord Commercial — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Tableau de bord Commercial & Ventes" 
        description="Pipeline prospects, devis, commandes clients, facturation et performance de ventes"
        :breadcrumbs="[['label' => 'Gestion Commerciale']]"
    >
        <x-slot:actions>
            <x-button :href="route('commercial.products.index')" variant="secondary" size="md">
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Catalogue & QR Codes</span>
            </x-button>

            <x-button :href="route('commercial.pos.index')" variant="secondary" size="md">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Vente POS</span>
            </x-button>

            <x-button :href="route('commercial.quotations.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Devis</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtre de Périodes Commerciales -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 sm:p-3 rounded-2xl border border-[#E2E8F0] mb-6">
        <div class="flex items-center gap-2.5">
            <span class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Période</span>
            <div class="flex flex-wrap items-center rounded-xl bg-[#F5F7FA] p-1 text-xs font-medium gap-1">
                @php
                    $commPeriods = [
                        'today' => "Aujourd'hui",
                        'this_week' => 'Cette semaine',
                        'this_month' => 'Ce mois',
                        'this_quarter' => 'Ce trimestre',
                        'this_year' => 'Cette année',
                    ];
                @endphp
                @foreach($commPeriods as $pk => $plabel)
                    <a href="{{ route('commercial.index', ['period' => $pk]) }}"
                       class="rounded-lg px-3 py-1.5 transition {{ $period === $pk ? 'bg-white text-[#0B0F14] shadow-xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-[#0B0F14]' }}">
                        {{ $plabel }}
                    </a>
                @endforeach
            </div>
        </div>
        <div class="text-xs text-[#64748B] hidden sm:block">
            Du <span class="font-medium text-[#0B0F14]">{{ $startDate->format('d/m/Y') }}</span> au <span class="font-medium text-[#0B0F14]">{{ $endDate->format('d/m/Y') }}</span>
        </div>
    </div>

    <!-- KPI Commercial -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card 
            title="CA Encaissé ({{ $periodLabel }})" 
            :value="number_format($caPeriod, 0, ',', ' ') . ' FCFA'" 
            :change="'Jour: ' . number_format($caToday, 0, ',', ' ') . ' F · Sem: ' . number_format($caWeek, 0, ',', ' ') . ' F'" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Devis en cours" 
            :value="$devisEnCours" 
            change="À valider / relancer" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Commandes Actives" 
            :value="$commandesActives" 
            change="En préparation / cours" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Créances Impayées" 
            :value="number_format($impayes, 0, ',', ' ') . ' FCFA'" 
            change="À recouvrer" 
            changeType="down"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Grille Pipeline & Devis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Pipeline Prospect -->
        <x-card title="Pipeline des Prospects" subtitle="État d'avancement des opportunités commerciales">
            <x-slot:actions>
                <a href="{{ route('commercial.prospects.index') }}" class="inline-flex items-center gap-1 text-xs text-[#0066FF] hover:underline font-medium">
                    <span>Vue Kanban</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </x-slot:actions>

            <div class="space-y-3.5">
                @php
                    $stages = [
                        'nouveau' => ['label' => 'Nouveau', 'prob' => '10%'],
                        'contacte' => ['label' => 'Contacté', 'prob' => '25%'],
                        'interesse' => ['label' => 'Intéressé', 'prob' => '50%'],
                        'devis_envoye' => ['label' => 'Devis envoyé', 'prob' => '70%'],
                        'negociation' => ['label' => 'Négociation', 'prob' => '85%'],
                        'gagne' => ['label' => 'Gagné (Signé)', 'prob' => '100%'],
                    ];
                @endphp

                @foreach($stages as $stKey => $stData)
                    @php
                        $count = \App\Models\Prospect::where('stage', $stKey)->orWhere('status', $stKey)->count();
                    @endphp
                    <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-[#F5F7FA] border border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                            <span class="font-medium text-[#0B0F14]">{{ $stData['label'] }}</span>
                            <span class="text-[#64748B] text-[11px]">({{ $stData['prob'] }})</span>
                        </div>
                        <x-badge variant="neutral" size="sm">{{ $count }} prospects</x-badge>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Derniers Devis Émis -->
        <x-card title="Derniers Devis Émis" subtitle="Chiffrages et propositions récentes">
            <x-slot:actions>
                <a href="{{ route('commercial.quotations.index') }}" class="inline-flex items-center gap-1 text-xs text-[#0066FF] hover:underline font-medium">
                    <span>Tous les devis</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
                </a>
            </x-slot:actions>

            <div class="divide-y divide-slate-100">
                @forelse(\App\Models\Quotation::with('customer')->latest()->take(5)->get() as $q)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <a href="{{ route('commercial.quotations.show', $q) }}" class="font-medium text-[#0B0F14] hover:text-[#0066FF] transition-colors">
                                {{ $q->reference }}
                            </a>
                            <p class="text-[#64748B] mt-0.5">{{ $q->customer->name ?? 'Client Particulier' }} <span class="mx-1 text-slate-300">·</span> {{ $q->date?->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-[#0B0F14] block tabular-nums">{{ number_format($q->total, 0, ',', ' ') }} FCFA</span>
                            <x-badge variant="neutral" size="sm">{{ ucfirst($q->status) }}</x-badge>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#64748B] py-8 text-center">Aucun devis récent enregistré.</p>
                @endforelse
            </div>
        </x-card>

    </div>
</x-layouts.app>
