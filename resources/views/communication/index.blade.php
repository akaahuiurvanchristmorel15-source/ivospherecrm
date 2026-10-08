<x-layouts.app>
    <x-slot:title>Pôle Communication & Réseaux — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Communication & Marketing" 
        description="Supervision des campagnes publicitaires, réseaux sociaux et calendrier éditorial hebdomadaire"
        :breadcrumbs="[['label' => 'Administration'], ['label' => 'Communication']]"
    >
        <x-slot:actions>
            <x-badge variant="primary" size="md">
                Responsable Communication
            </x-badge>
        </x-slot:actions>
    </x-page-header>

    <!-- Planning Éditorial Hebdomadaire -->
    <x-card title="Planning Éditorial Hebdomadaire" subtitle="Programmation des publications quotidiennes par domaine d'activité">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            
            <!-- Lundi: PRINT -->
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#0B0F14] text-xs">Lundi</span>
                    <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                </div>
                <div class="text-[11px] font-semibold text-[#0066FF] uppercase">IVOSPHERE PRINT</div>
                <p class="text-xs text-[#64748B]">Publication Imprimerie, packaging et offres scolaires.</p>
                <x-badge variant="success" size="sm">Publié</x-badge>
            </div>

            <!-- Mardi: SPORT -->
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#0B0F14] text-xs">Mardi</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-[11px] font-semibold text-emerald-600 uppercase">IVOSPHERE SPORT</div>
                <p class="text-xs text-[#64748B]">Articles sportifs, maillots clubs et nouveaux arrivages.</p>
                <x-badge variant="success" size="sm">Publié</x-badge>
            </div>

            <!-- Mercredi: TECH -->
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#0B0F14] text-xs">Mercredi</span>
                    <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                </div>
                <div class="text-[11px] font-semibold text-[#0066FF] uppercase">IVOSPHERE TECH</div>
                <p class="text-xs text-[#64748B]">Solutions IA, développement et innovations informatiques.</p>
                <x-badge variant="primary" size="sm">En cours</x-badge>
            </div>

            <!-- Jeudi: MEDIA -->
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#0B0F14] text-xs">Jeudi</span>
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                </div>
                <div class="text-[11px] font-semibold text-purple-600 uppercase">IVOSPHERE MEDIA</div>
                <p class="text-xs text-[#64748B]">Galerie photo, location évènementielle et forfaits week-end.</p>
                <x-badge variant="warning" size="sm">Planifié</x-badge>
            </div>

            <!-- Vendredi: ASSURANCE -->
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#0B0F14] text-xs">Vendredi</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-[11px] font-semibold text-amber-600 uppercase">IVOSPHERE ASSURANCE</div>
                <p class="text-xs text-[#64748B]">Sensibilisation, protection santé et contrats prévoyance.</p>
                <x-badge variant="warning" size="sm">Planifié</x-badge>
            </div>

        </div>
    </x-card>

    <!-- Pôles d'Action Communication -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <x-card>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm">📱</div>
                <h3 class="font-semibold text-sm text-[#0B0F14]">Réseaux Sociaux</h3>
            </div>
            <p class="text-xs text-[#64748B] leading-relaxed">Gestion centralisée des pages Facebook, Instagram, LinkedIn, TikTok pour tous les domaines du groupe.</p>
        </x-card>

        <x-card>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm">🎨</div>
                <h3 class="font-semibold text-sm text-[#0B0F14]">Affiches & Flyers</h3>
            </div>
            <p class="text-xs text-[#64748B] leading-relaxed">Création de visuels, déclinaisons pour les promotions commerciales et formats d'impression PRINT.</p>
        </x-card>

        <x-card>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm">✉️</div>
                <h3 class="font-semibold text-sm text-[#0B0F14]">Newsletters & Emailing</h3>
            </div>
            <p class="text-xs text-[#64748B] leading-relaxed">Diffusion d'actualités aux clients, offres exclusives et annonces de nouveaux partenariats.</p>
        </x-card>
    </div>
</x-layouts.app>
