<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Entrepôts de Stock" 
            subtitle="Gestion des sites de stockage, capacités et inventaires par domaine">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Entrepôts']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('stock.warehouses.create') }}" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvel Entrepôt
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Filtres -->
    <div class="mb-6 rounded-xl bg-white border border-[#E2E8F0] p-4 shadow-xs">
        <form method="GET" action="{{ route('stock.warehouses.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[240px]">
                <label for="domain_id" class="sr-only">Domaine</label>
                <select name="domain_id" id="domain_id" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                    <option value="">Tous les domaines d'activité</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" {{ request('domain_id') == $domain->id ? 'selected' : '' }}>{{ $domain->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <x-button type="submit" variant="secondary" size="md">
                    Filtrer
                </x-button>
                @if(request('domain_id'))
                    <a href="{{ route('stock.warehouses.index') }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14] px-2 py-1">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grille des entrepôts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $warehouse)
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs hover:border-[#0066FF]/40 transition-colors flex flex-col justify-between relative overflow-hidden">
                @if(!$warehouse->is_active)
                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                            Inactif
                        </span>
                    </div>
                @endif
                <div>
                    <div class="flex items-start justify-between pr-14">
                        <div>
                            <h3 class="text-base font-semibold text-[#0B0F14]">{{ $warehouse->name }}</h3>
                            <p class="text-xs text-[#64748B] mt-0.5">{{ $warehouse->domain->name ?? 'Général' }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <span class="inline-flex items-center rounded-md bg-[#F5F7FA] px-2.5 py-1 text-xs font-mono font-medium text-[#0B0F14] border border-[#E2E8F0]">
                            {{ $warehouse->code }}
                        </span>
                    </div>

                    @if($warehouse->manager)
                        <div class="mt-3 text-xs text-[#64748B] flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Responsable : {{ $warehouse->manager }}</span>
                        </div>
                    @endif
                    
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-[#E2E8F0] pt-4">
                        <div class="rounded-lg bg-[#F5F7FA] p-3 border border-[#E2E8F0]/60">
                            <dt class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider">Réf. Produits</dt>
                            <dd class="mt-1 text-lg font-bold text-[#0B0F14]">{{ $warehouse->product_count }}</dd>
                        </div>
                        <div class="rounded-lg bg-[#F5F7FA] p-3 border border-[#E2E8F0]/60">
                            <dt class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider">Total Articles</dt>
                            <dd class="mt-1 text-lg font-bold text-[#0066FF]">{{ number_format($warehouse->total_items, 0, ',', ' ') }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="mt-5 flex items-center justify-between border-t border-[#E2E8F0] pt-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('stock.warehouses.edit', $warehouse) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">
                            Paramètres
                        </a>
                        <button 
                            type="button" 
                            @click="$dispatch('open-delete-warehouse', { 
                                id: {{ $warehouse->id }}, 
                                name: '{{ addslashes($warehouse->name) }}', 
                                code: '{{ $warehouse->code }}', 
                                totalStock: {{ (int) $warehouse->total_items }},
                                productCount: {{ (int) $warehouse->product_count }}
                            })"
                            class="text-xs font-medium text-rose-500 hover:text-rose-700 transition"
                        >
                            Supprimer
                        </button>
                    </div>
                    <a href="{{ route('stock.warehouses.show', $warehouse) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0066FF] hover:underline">
                        <span>Accéder au stock</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Aucun entrepôt trouvé" 
                    description="Créez votre premier entrepôt pour suivre et organiser vos stocks par localisation."
                    action-label="Créer un entrepôt"
                    :action-url="route('stock.warehouses.create')"
                />
            </div>
        @endforelse
    </div>

    @if($warehouses->hasPages())
        <div class="mt-6">
            {{ $warehouses->links() }}
        </div>
    @endif

    {{-- Modal de confirmation / transfert pour suppression d'entrepôt --}}
    <x-delete-warehouse-modal :warehouses="$warehouses" />
</x-layouts.app>
