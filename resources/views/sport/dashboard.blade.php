<x-layouts.app>
    <x-slot:title>Tableau de Bord - SPORT — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Tableau de Bord - SPORT" 
        description="IVOSPHERE SPORT • Vente d'équipements sportifs, maillots de football/basket personnalisés et gestion des clubs"
        :breadcrumbs="[['label' => 'Activités'], ['label' => 'SPORT']]"
    >
        <x-slot:actions>
            <x-button :href="route('sport.articles.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouvel Article</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- KPI SPORT -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card 
            title="Articles Commandés" 
            :value="$stats['articles_commandes'] ?? 0" 
            change="En préparation / flocage" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Articles Livrés" 
            :value="$stats['articles_livres'] ?? 0" 
            change="Remis aux clients / clubs" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Chiffre d'Affaires" 
            :value="number_format($stats['ca_sport'] ?? 0, 0, ',', ' ') . ' FCFA'" 
            change="Pôle Sport" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Catalogue & Personnalisations (§13) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-card title="Fonctionnalités SPORT" subtitle="Gestion des maillots et équipements personnalisés">
            <div class="space-y-3">
                <div class="p-3.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-sm text-[#0B0F14] block">Flocage & Personnalisation</span>
                        <span class="text-xs text-[#64748B]">Nom joueur, numéro dorsal, badge et logo d'équipe</span>
                    </div>
                    <x-badge variant="neutral" size="sm">Atelier Sport</x-badge>
                </div>

                <div class="p-3.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-sm text-[#0B0F14] block">Dotations de Clubs & Équipes</span>
                        <span class="text-xs text-[#64748B]">Packs complets maillots, shorts, bas, ballons et chasubles</span>
                    </div>
                    <x-badge variant="neutral" size="sm">B2B Clubs</x-badge>
                </div>
            </div>
        </x-card>

        <x-card title="Opérations & Catalogue" subtitle="Accès direct au stock d'articles">
            <div class="flex flex-col gap-3">
                <x-button :href="route('sport.articles.create')" variant="primary" size="md">
                    <span>Nouvel Article</span>
                </x-button>
                <x-button :href="route('sport.articles.index')" variant="secondary" size="md">
                    <span>Voir les Articles</span>
                </x-button>
                <x-button :href="route('commercial.pos.index')" variant="secondary" size="md">
                    <span>Vente Comptoir & Caisse (POS)</span>
                </x-button>
            </div>
        </x-card>
    </div>
</x-layouts.app>
