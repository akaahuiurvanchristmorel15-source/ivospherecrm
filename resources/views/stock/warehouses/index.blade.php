@php
    $canPrintCatalog = auth()->user()->hasPermission('stocks.catalog');
@endphp
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
                @if($canPrintCatalog)
                <x-button href="{{ route('stock.products.print-catalog', ['per_warehouse' => 1]) }}" target="_blank" variant="secondary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer les catalogues
                </x-button>
                <x-button href="{{ route('stock.products.download-catalog', ['per_warehouse' => 1]) }}" variant="secondary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Télécharger (PDF)
                </x-button>
                @endif
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
            <article class="flex flex-col overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-xs transition hover:border-[#0066FF]/40 hover:shadow-md">
                {{-- En-tête : identité et statut --}}
                <header class="flex items-start gap-3 p-5">
                    <x-warehouse-label :warehouse="$warehouse" size="lg" />
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-base font-semibold text-[#0B0F14]" title="{{ $warehouse->name }}">{{ $warehouse->name }}</h3>
                        <p class="mt-0.5 truncate text-xs text-[#64748B]">{{ $warehouse->domain->name ?? 'Général' }}</p>
                    </div>
                    @if($warehouse->is_active)
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Actif
                        </span>
                    @else
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            Inactif
                        </span>
                    @endif
                </header>

                {{-- Fiche : informations clés --}}
                <dl class="mx-5 divide-y divide-[#E2E8F0] rounded-xl border border-[#E2E8F0] text-xs">
                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                        <dt class="text-[#64748B]">Code</dt>
                        <dd class="font-mono font-semibold text-[#0B0F14]">{{ $warehouse->code }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                        <dt class="text-[#64748B]">Responsable</dt>
                        <dd class="truncate font-medium text-[#0B0F14]">{{ $warehouse->manager ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                        <dt class="text-[#64748B]">Adresse</dt>
                        <dd class="truncate font-medium text-[#0B0F14]" title="{{ $warehouse->address }}">{{ $warehouse->address ?: '—' }}</dd>
                    </div>
                </dl>

                {{-- Indicateurs --}}
                <div class="grid grid-cols-2 gap-3 p-5">
                    <div class="rounded-xl bg-[#F5F7FA] p-3.5">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Réf. produits</p>
                        <p class="mt-1 text-xl font-bold text-[#0B0F14]">{{ number_format($warehouse->product_count, 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-xl bg-[#F5F7FA] p-3.5">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Total articles</p>
                        <p class="mt-1 text-xl font-bold text-[#0066FF]">{{ number_format($warehouse->total_items, 0, ',', ' ') }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <footer class="mt-auto space-y-3 border-t border-[#E2E8F0] p-5">
                    <a href="{{ route('stock.warehouses.show', $warehouse) }}" class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#0066FF] text-sm font-semibold text-white transition hover:bg-[#0052CC]">
                        Accéder au stock
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('stock.warehouses.qr-codes', $warehouse) }}" title="Télécharger le PDF A4 des QR codes des produits de l'entrepôt"
                       class="flex h-9 w-full items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/></svg>
                        PDF des QR codes
                    </a>
                    <div class="grid gap-2 {{ $canPrintCatalog ? 'grid-cols-4' : 'grid-cols-2' }}">
                        <a href="{{ route('stock.warehouses.edit', $warehouse) }}" title="Paramètres de l'entrepôt"
                           class="flex flex-col items-center gap-1 rounded-xl border border-[#E2E8F0] py-2 text-[11px] font-medium text-[#64748B] transition hover:bg-[#F5F7FA] hover:text-[#0B0F14]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Paramètres
                        </a>
                        @if($canPrintCatalog)
                        <a href="{{ route('stock.products.print-catalog', ['warehouse_id' => $warehouse->id]) }}" target="_blank" title="Imprimer le catalogue"
                           class="flex flex-col items-center gap-1 rounded-xl border border-[#E2E8F0] py-2 text-[11px] font-medium text-[#64748B] transition hover:bg-[#F5F7FA] hover:text-[#0B0F14]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Imprimer
                        </a>
                        <a href="{{ route('stock.products.download-catalog', ['warehouse_id' => $warehouse->id]) }}" title="Télécharger le catalogue (PDF)"
                           class="flex flex-col items-center gap-1 rounded-xl border border-[#E2E8F0] py-2 text-[11px] font-medium text-[#64748B] transition hover:bg-[#F5F7FA] hover:text-[#0B0F14]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            PDF
                        </a>
                        @endif
                        <button type="button" title="Supprimer l'entrepôt"
                                @click="$dispatch('open-delete-warehouse', {
                                    id: {{ $warehouse->id }},
                                    name: '{{ addslashes($warehouse->name) }}',
                                    code: '{{ $warehouse->code }}',
                                    totalStock: {{ (int) $warehouse->total_items }},
                                    productCount: {{ (int) $warehouse->product_count }}
                                })"
                                class="flex flex-col items-center gap-1 rounded-xl border border-rose-200 py-2 text-[11px] font-medium text-rose-600 transition hover:bg-rose-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Supprimer
                        </button>
                    </div>
                </footer>
            </article>
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
