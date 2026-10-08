<x-layouts.app>
    <x-slot:title>Tableau de Bord - MEDIA — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Tableau de Bord - MEDIA" 
        description="IVOSPHERE MEDIA & EVENTS • Organisation d'évènements, séances photo/vidéo professionnelles et parc de location avec cautions"
        :breadcrumbs="[['label' => 'Activités'], ['label' => 'MEDIA & EVENTS']]"
    >
        <x-slot:actions>
            <x-button :href="route('media.sessions.create')" variant="secondary" size="md">
                <span>📸 Nouvelle Session</span>
            </x-button>

            <x-button :href="route('media.events.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouvel Événement</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- KPI MEDIA -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card 
            title="Sessions Photo" 
            :value="$stats['sessions_photo'] ?? 0" 
            change="Studios & tournages" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Locations en cours" 
            :value="$stats['locations_cours'] ?? 0" 
            change="Avec caution consignée" 
            changeType="neutral"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Événements Planifiés" 
            :value="$stats['events_planifies'] ?? 0" 
            change="Mariages & conférences" 
            changeType="up"
            timeframe=""
        >
            <x-slot:icon>
                <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Pôles Opérationnels & Inventaire de Matériel (§22-23) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Parc de Matériel -->
        <x-card title="Inventaire Matériel & Audiovisuel" subtitle="Parc sonorisation, lumières et caméras en location">
            <x-slot:actions>
                <a href="{{ route('media.equipment.index') }}" class="inline-flex items-center gap-1 text-xs text-[#0066FF] hover:underline font-medium">
                    <span>Parc complet</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </x-slot:actions>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                @php
                    $equipments = [
                        ['name' => 'SONORISATION', 'statut' => 'Disponible', 'color' => 'success'],
                        ['name' => 'MICROS SANS FIL', 'statut' => 'En location', 'color' => 'neutral'],
                        ['name' => 'ENCEINTES PRO', 'statut' => 'Disponible', 'color' => 'success'],
                        ['name' => 'VIDÉO-PROJECTEUR', 'statut' => 'Réservé', 'color' => 'warning'],
                        ['name' => 'JEUX DE LUMIÈRES', 'statut' => 'Disponible', 'color' => 'success'],
                        ['name' => 'CAMÉRAS 4K', 'statut' => 'En location', 'color' => 'neutral'],
                        ['name' => 'APPAREILS PHOTO', 'statut' => 'Disponible', 'color' => 'success'],
                        ['name' => 'TRÉPIEDS PRO', 'statut' => 'Disponible', 'color' => 'success'],
                        ['name' => 'TABLES & CHAISES', 'statut' => 'Réservé', 'color' => 'warning'],
                    ];
                @endphp

                @foreach($equipments as $eq)
                    <div class="p-2.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <span class="font-bold text-[#0B0F14] text-[11px] block truncate">{{ $eq['name'] }}</span>
                        <x-badge :variant="$eq['color']" size="sm" class="mt-1">{{ $eq['statut'] }}</x-badge>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Event Manager -->
        <x-card title="Event Manager & Prestations" subtitle="Pilotage complet de réceptions et conférences">
            <div class="space-y-3">
                <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between text-xs">
                    <div>
                        <span class="font-semibold text-[#0B0F14] block">Mariages & Célébrations</span>
                        <span class="text-[#64748B]">Couverture photo, sono, animateurs et logistique</span>
                    </div>
                    <x-badge variant="neutral" size="sm">Particuliers</x-badge>
                </div>

                <div class="p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between text-xs">
                    <div>
                        <span class="font-semibold text-[#0B0F14] block">Séminaires & Conférences Pro</span>
                        <span class="text-[#64748B]">Scénographie, projection, captation live streaming</span>
                    </div>
                    <x-badge variant="primary" size="sm">Entreprises</x-badge>
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <x-button :href="route('media.events.index')" variant="primary" size="md">
                        <span>Voir les Événements</span>
                    </x-button>
                    <x-button :href="route('media.sessions.index')" variant="secondary" size="md">
                        <span>Galeries Photos & Livraisons</span>
                    </x-button>
                </div>
            </div>
        </x-card>

    </div>
</x-layouts.app>
