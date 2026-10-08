<x-layouts.app>
    <x-slot:title>Tableau de Bord - ASSURANCE — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Tableau de Bord - ASSURANCE" 
        description="IVOSPHERE ASSURANCE • Cabinet de courtage conseil, gestion des polices d'assurance, souscriptions et rétrocession de commissions"
        :breadcrumbs="[['label' => 'Activités'], ['label' => 'ASSURANCE']]"
    >
        <x-slot:actions>
            <x-button :href="route('assurance.appointments.create')" variant="secondary" size="md">
                <span>Nouveau RDV</span>
            </x-button>

            <x-button :href="route('assurance.contracts.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Contrat</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- KPI ASSURANCE -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card 
            title="Contrats Actifs" 
            :value="$stats['contrats_actifs'] ?? 0" 
            change="Portefeuille en cours" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Commissions (en attente)" 
            :value="number_format($stats['commissions_attente'] ?? 0, 0, ',', ' ') . ' FCFA'" 
            change="À percevoir des assureurs" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Primes Collectées" 
            :value="number_format($stats['primes_collectees'] ?? 0, 0, ',', ' ') . ' FCFA'" 
            change="Volume d'affaires" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Cycle de Courtage & Partenaires (§13 & §24) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Workflow Assurance -->
        <x-card title="Cycle de Souscription Conseil" subtitle="Parcours client de l'expression du besoin à la commission">
            <div class="space-y-2.5 text-xs">
                @php
                    $steps = [
                        ['num' => '1', 'title' => 'Prospect & Diagnostic', 'desc' => 'Recueil des besoins santé, auto, habitation, prévoyance'],
                        ['num' => '2', 'title' => 'Étude & Sélection Produit', 'desc' => 'Comparatif des offres partenaires les plus adaptées'],
                        ['num' => '3', 'title' => 'Constitution du Dossier', 'desc' => 'Pièces justificatives, formulaire de souscription'],
                        ['num' => '4', 'title' => 'Transmission Assureur', 'desc' => 'Validation technique par la compagnie partenaire'],
                        ['num' => '5', 'title' => 'Émission & Commission', 'desc' => 'Contrat finalisé, encaissement de prime et rétrocession'],
                    ];
                @endphp

                @foreach($steps as $s)
                    <div class="p-2.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-[#0066FF] text-white flex items-center justify-center font-bold text-[11px] shrink-0">
                            {{ $s['num'] }}
                        </span>
                        <div>
                            <span class="font-semibold text-[#0B0F14] block">{{ $s['title'] }}</span>
                            <span class="text-[#64748B] text-[11px]">{{ $s['desc'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Actions Directes & Catalogue Partenaires -->
        <x-card title="Opérations de Courtage" subtitle="Gestion des contrats et rendez-vous">
            <div class="flex flex-col gap-3">
                <x-button :href="route('assurance.contracts.create')" variant="primary" size="md">
                    <span>Nouveau Contrat</span>
                </x-button>
                <x-button :href="route('assurance.appointments.create')" variant="secondary" size="md">
                    <span>Nouveau RDV</span>
                </x-button>
                <x-button :href="route('assurance.contracts.index')" variant="secondary" size="md">
                    <span>Consulter tous les Contrats</span>
                </x-button>
            </div>
        </x-card>

    </div>
</x-layouts.app>
