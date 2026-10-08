<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Agent IA Publicitaire & Campagnes" 
            subtitle="Génération de contenu IA, programmation multi-réseaux et tracking publicitaire">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'TECH', 'url' => route('tech.index')],
                    ['label' => 'Campagnes IA']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('tech.campaigns.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle Campagne IA
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($campaigns as $campaign)
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs hover:border-[#0066FF] transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-bold text-[#0066FF] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                            {{ $campaign->reference }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $campaign->status === 'actif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-[#64748B] border-[#E2E8F0]' }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-[#0B0F14] mb-0.5">{{ $campaign->name }}</h3>
                    <p class="text-xs text-[#64748B] mb-3">{{ $campaign->customer->name ?? 'Campagne interne' }}</p>
                    <p class="text-xs text-[#64748B] line-clamp-2 mb-4 leading-relaxed">{{ $campaign->brief ?? 'Aucun brief renseigné.' }}</p>

                    <div class="flex justify-between items-center text-xs text-[#64748B] border-t border-[#E2E8F0] pt-3">
                        <span>Budget alloué :</span>
                        <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($campaign->budget, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3 border-t border-[#E2E8F0] pt-4">
                    <a href="{{ route('tech.campaigns.edit', $campaign) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                    <a href="{{ route('tech.campaigns.show', $campaign) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0066FF] hover:underline">
                        <span>Contenus & IA</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Aucune campagne IA" 
                    description="Créez votre première campagne avec l'Agent IA publicitaire."
                    action-label="Créer une campagne"
                    :action-url="route('tech.campaigns.create')"
                />
            </div>
        @endforelse
    </div>
</x-layouts.app>
