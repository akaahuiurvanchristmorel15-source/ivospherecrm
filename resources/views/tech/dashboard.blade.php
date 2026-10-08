<x-layouts.app>
    <x-slot:title>Tableau de Bord - TECH — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Tableau de Bord - TECH" 
        description="IVOSPHERE TECH • Gestion des projets digitaux, développement d'applications, maintenance informatique et campagnes Agent IA"
        :breadcrumbs="[['label' => 'Activités'], ['label' => 'TECH']]"
    >
        <x-slot:actions>
            <x-button :href="route('tech.campaigns.create')" variant="secondary" size="md">
                <span>🤖 Nouvelle Campagne</span>
            </x-button>

            <x-button :href="route('tech.projects.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Projet</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- KPI TECH -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card 
            title="Projets en cours" 
            :value="$stats['projets_en_cours'] ?? 0" 
            change="Développement & Delivery" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Campagnes Actives" 
            :value="$stats['campagnes_actives'] ?? 0" 
            change="Agent IA Publicitaire" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Chiffre d'Affaires" 
            :value="number_format($stats['ca_tech'] ?? 0, 0, ',', ' ') . ' FCFA'" 
            change="Prestations & Licences" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Pôles Opérationnels TECH (§13) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-card title="Solutions & Services Informatiques" subtitle="Périmètre d'ingénierie logicielle">
            <div class="space-y-3">
                <div class="p-3.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-sm text-[#0B0F14] block">Développement Web & Mobile</span>
                        <span class="text-xs text-[#64748B]">Applications sur mesure, API REST et refontes de plateformes</span>
                    </div>
                    <x-badge variant="neutral" size="sm">Logiciel</x-badge>
                </div>

                <div class="p-3.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-sm text-[#0B0F14] block">Agent IA Publicitaire</span>
                        <span class="text-xs text-[#64748B]">Génération automatique de campagnes marketing multi-canaux</span>
                    </div>
                    <x-badge variant="primary" size="sm">IA IVOSPHERE</x-badge>
                </div>
            </div>
        </x-card>

        <x-card title="Opérations & Suivi" subtitle="Accès direct aux projets et campagnes">
            <div class="flex flex-col gap-3">
                <x-button :href="route('tech.projects.index')" variant="primary" size="md">
                    <span>Voir les Projets</span>
                </x-button>
                <x-button :href="route('tech.campaigns.index')" variant="secondary" size="md">
                    <span>Consulter les Campagnes</span>
                </x-button>
                <x-button :href="route('commercial.quotations.create')" variant="secondary" size="md">
                    <span>Établir un Devis Prestation</span>
                </x-button>
            </div>
        </x-card>
    </div>
</x-layouts.app>
