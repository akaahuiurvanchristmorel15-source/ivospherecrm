<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Projets & Prestations Digitales (TECH)" 
            subtitle="Développement web/mobile, intégration, maintenance et solutions IT d'entreprise">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'TECH', 'url' => route('tech.index')],
                    ['label' => 'Projets']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('tech.projects.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Projet
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs hover:border-[#0066FF] transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-bold text-[#0066FF] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                            {{ $project->reference }}
                        </span>
                        @php
                            $statusBadge = $project->status === 'terminé' 
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                : 'bg-blue-50 text-[#0066FF] border-blue-200';
                        @endphp
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </div>
                    
                    <h3 class="text-base font-bold text-[#0B0F14] mb-0.5">{{ $project->name }}</h3>
                    <p class="text-xs text-[#64748B] mb-3">{{ $project->customer->name ?? 'Client interne' }}</p>
                    <p class="text-xs text-[#64748B] line-clamp-2 mb-4 leading-relaxed">{{ $project->description ?? 'Aucune description fournie.' }}</p>

                    <!-- Progression -->
                    <div class="space-y-1.5 mb-4 p-3 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]/60">
                        <div class="flex justify-between text-xs">
                            <span class="text-[#64748B] font-medium">Avancement du projet</span>
                            <span class="font-bold text-[#0B0F14]">{{ $project->progress }}%</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-[#E2E8F0] overflow-hidden">
                            <div class="h-full rounded-full bg-[#0066FF] transition-all duration-300" style="width: {{ $project->progress }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-[#64748B] border-t border-[#E2E8F0] pt-3">
                        <span>Budget alloué :</span>
                        <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($project->budget, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3 border-t border-[#E2E8F0] pt-4">
                    <a href="{{ route('tech.projects.edit', $project) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                    <a href="{{ route('tech.projects.show', $project) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0066FF] hover:underline">
                        <span>Détails & Tâches</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Aucun projet tech" 
                    description="Aucun projet de développement ou prestation informatique enregistré."
                    action-label="Nouveau projet"
                    :action-url="route('tech.projects.create')"
                />
            </div>
        @endforelse
    </div>

    @if($projects->hasPages())
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif
</x-layouts.app>
