<x-layouts.app>
    <x-slot:title>Tableau de Bord - PRINT — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Tableau de Bord - PRINT" 
        description="IVOSPHERE PRINT • Atelier d'impression, formats, supports, maquettes et chaîne de production"
        :breadcrumbs="[['label' => 'Activités'], ['label' => 'PRINT']]"
    >
        <x-slot:actions>
            <x-button :href="route('print.jobs.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Job</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- KPI PRINT -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card 
            title="Jobs en cours" 
            :value="$stats['en_cours'] ?? 0" 
            change="En chaîne d'atelier" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Jobs terminés" 
            :value="$stats['termines'] ?? 0" 
            change="Production validée" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Chiffre d'Affaires" 
            :value="number_format($stats['ca_print'] ?? 0, 0, ',', ' ') . ' FCFA'" 
            change="Pôle Impression" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Workflow Visuel de Production (§13) -->
    <x-card title="Chaîne Opérationnelle d'Atelier" subtitle="Workflow de fabrication standard des tirages et imprimés" class="mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 text-center text-xs">
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-[#0B0F14] text-white flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">1</span>
                <span class="font-semibold text-[#0B0F14] block">Demande</span>
                <span class="text-[10px] text-[#64748B]">Cahier des charges</span>
            </div>
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">2</span>
                <span class="font-semibold text-[#0B0F14] block">Devis</span>
                <span class="text-[10px] text-[#64748B]">Chiffrage papier</span>
            </div>
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">3</span>
                <span class="font-semibold text-[#0B0F14] block">Validation</span>
                <span class="text-[10px] text-[#64748B]">Acompte client</span>
            </div>
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-[#0066FF] text-white flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">4</span>
                <span class="font-semibold text-[#0B0F14] block">Maquette</span>
                <span class="text-[10px] text-[#0066FF]">BAT approuvé</span>
            </div>
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">5</span>
                <span class="font-semibold text-[#0B0F14] block">Production</span>
                <span class="text-[10px] text-[#64748B]">Tirage machines</span>
            </div>
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">6</span>
                <span class="font-semibold text-[#0B0F14] block">Contrôle</span>
                <span class="text-[10px] text-[#64748B]">Façonnage & coupe</span>
            </div>
            <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto mb-1.5 font-bold text-[11px]">7</span>
                <span class="font-semibold text-[#0B0F14] block">Livraison</span>
                <span class="text-[10px] text-emerald-600">Remise & solde</span>
            </div>
        </div>
    </x-card>

    <!-- Actions & Suivi des Jobs -->
    <x-card title="Opérations & Enregistrements" subtitle="Accès direct à la gestion des impressions">
        <div class="flex flex-wrap items-center gap-3">
            <x-button :href="route('print.jobs.create')" variant="primary" size="md">
                <span>Nouveau Job</span>
            </x-button>
            <x-button :href="route('print.jobs.index')" variant="secondary" size="md">
                <span>Voir les Jobs</span>
            </x-button>
            <x-button :href="route('commercial.pos.index')" variant="secondary" size="md">
                <span>Caisse Librairie & Comptoir (POS)</span>
            </x-button>
        </div>
    </x-card>
</x-layouts.app>
