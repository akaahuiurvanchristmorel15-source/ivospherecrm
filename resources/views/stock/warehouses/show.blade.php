<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ $warehouse->name }}" 
            subtitle="Code : {{ $warehouse->code }} &bull; Domaine : {{ $warehouse->domain->name ?? 'Général' }}">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Entrepôts', 'url' => route('stock.warehouses.index')],
                    ['label' => $warehouse->name]
                ]" />
            </x-slot>
            <x-slot name="actions">
                <div class="flex items-center gap-3">
                    <button 
                        type="button" 
                        @click="$dispatch('open-delete-warehouse', { 
                            id: {{ $warehouse->id }}, 
                            name: '{{ addslashes($warehouse->name) }}', 
                            code: '{{ $warehouse->code }}', 
                            totalStock: {{ (int) $warehouse->warehouseStocks->sum('physical_quantity') }},
                            productCount: {{ (int) $warehouse->warehouseStocks->where('physical_quantity', '>', 0)->count() }}
                        })"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition border border-rose-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Supprimer</span>
                    </button>
                    <x-button href="{{ route('stock.warehouses.edit', $warehouse) }}" variant="secondary">
                        Modifier
                    </x-button>
                    <x-button href="{{ route('stock.movements.create', ['warehouse_id' => $warehouse->id]) }}" variant="primary">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau Mouvement
                    </x-button>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Informations Entrepôt -->
        <div class="lg:col-span-4 rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs h-fit">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-4">
                Informations du site
            </h3>
            
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Code Entrepôt</dt>
                    <dd class="mt-1 text-sm font-semibold font-mono text-[#0B0F14]">{{ $warehouse->code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Domaine d'Activité</dt>
                    <dd class="mt-1 text-sm font-medium text-[#0B0F14]">{{ $warehouse->domain->name ?? 'Général' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Responsable Dédié</dt>
                    <dd class="mt-1 text-sm font-medium text-[#0B0F14]">{{ $warehouse->manager ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-[#64748B]">Statut Opérationnel</dt>
                    <dd class="mt-1">
                        @if($warehouse->is_active)
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                Actif
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                Inactif
                            </span>
                        @endif
                    </dd>
                </div>
                @if($warehouse->address)
                    <div>
                        <dt class="text-xs font-medium text-[#64748B]">Localisation / Adresse</dt>
                        <dd class="mt-1 text-sm text-[#0B0F14]">{{ $warehouse->address }}</dd>
                    </div>
                @endif
                @if($warehouse->description)
                    <div>
                        <dt class="text-xs font-medium text-[#64748B]">Notes & Spécificités</dt>
                        <dd class="mt-1 text-sm text-[#64748B] leading-relaxed">{{ $warehouse->description }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- État du Stock -->
        <div class="lg:col-span-8 rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden flex flex-col">
            <div class="p-5 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-[#0B0F14]">État du Stock Disponible</h3>
                    <p class="text-xs text-[#64748B]">Inventaire actuel des produits référencés sur cet entrepôt</p>
                </div>
                <span class="inline-flex items-center rounded-md bg-[#F5F7FA] px-2.5 py-1 text-xs font-medium text-[#0B0F14] border border-[#E2E8F0]">
                    {{ count($stockLevels) }} référence(s)
                </span>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                        <tr>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">SKU</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Quantité</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Niveau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($stockLevels as $level)
                            @php 
                                $product = $level->product;
                                $minStock = $product->min_stock ?? 10;
                                $isAlert = $level->stock_after <= $minStock;
                            @endphp
                            <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                                <td class="py-3.5 px-4 text-sm font-semibold text-[#0B0F14]">
                                    {{ $product->name ?? 'Produit #'.$level->product_id }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-[#64748B]">{{ $product->sku ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">
                                    {{ number_format($level->stock_after, 0, ',', ' ') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($isAlert)
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                            Alerte (&le; {{ $minStock }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-sm text-[#64748B]">
                                    Aucun produit actuellement en stock dans cet entrepôt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($stockLevels as $level)
                    @php 
                        $product = $level->product;
                        $minStock = $product->min_stock ?? 10;
                        $isAlert = $level->stock_after <= $minStock;
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-[#0B0F14]">{{ $product->name ?? 'Produit #'.$level->product_id }}</span>
                                <span class="text-xs font-mono text-[#64748B] block mt-0.5">{{ $product->sku ?? '-' }}</span>
                            </div>
                            @if($isAlert)
                                <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200 shrink-0">
                                    Alerte (&le; {{ $minStock }})
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200 shrink-0">
                                    Normal
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <span class="text-[#64748B]">Quantité disponible</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($level->stock_after, 0, ',', ' ') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-[#64748B]">
                        Aucun produit actuellement en stock dans cet entrepôt.
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Mouvements Récents pour cet entrepôt -->
        <div class="col-span-1 lg:col-span-12 rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-[#0B0F14]">Mouvements Récents sur cet Entrepôt</h3>
                    <p class="text-xs text-[#64748B]">Journal des dernières entrées, sorties et réajustements</p>
                </div>
                <a href="{{ route('stock.movements.index', ['warehouse_id' => $warehouse->id]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0066FF] hover:underline">
                    <span>Consulter tout l'historique</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                        <tr>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Type</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Quantité</th>
                            <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Stock Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($recentMovements as $movement)
                            @php
                                $badgeClass = match($movement->type) {
                                    'entree' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'sortie' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'transfert' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                    'retour' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'ajustement' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                                };
                                $sign = match($movement->type) {
                                    'entree', 'retour' => '+',
                                    'sortie', 'transfert' => '-',
                                    default => ''
                                };
                            @endphp
                            <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                                <td class="py-3.5 px-4 text-xs font-medium text-[#0B0F14]">{{ $movement->date->format('d/m/Y') }}</td>
                                <td class="py-3.5 px-4 text-xs font-mono text-[#64748B]">{{ $movement->reference }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badgeClass }}">
                                        {{ ucfirst($movement->type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14]">
                                    {{ $movement->product->name ?? 'Produit #'.$movement->product_id }}
                                </td>
                                <td class="py-3.5 px-4 text-sm font-bold text-right {{ $sign === '+' ? 'text-emerald-600' : ($sign === '-' ? 'text-rose-600' : 'text-[#0B0F14]') }}">
                                    {{ $sign }}{{ $movement->quantity }}
                                </td>
                                <td class="py-3.5 px-4 text-sm font-mono text-right text-[#64748B]">{{ $movement->stock_after }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-[#64748B]">
                                    Aucun mouvement enregistré pour cet entrepôt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($recentMovements as $movement)
                    @php
                        $badgeClass = match($movement->type) {
                            'entree' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'sortie' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'transfert' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                            'retour' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'ajustement' => 'bg-purple-50 text-purple-700 border-purple-200',
                            default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                        };
                        $sign = match($movement->type) {
                            'entree', 'retour' => '+',
                            'sortie', 'transfert' => '-',
                            default => ''
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-[#0B0F14]">{{ $movement->product->name ?? 'Produit #'.$movement->product_id }}</span>
                                <div class="flex items-center gap-2 mt-0.5 text-xs text-[#64748B]">
                                    <span class="font-mono">{{ $movement->reference }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $movement->date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border shrink-0 {{ $badgeClass }}">
                                {{ ucfirst($movement->type) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Mouvement</span>
                                <span class="font-bold text-sm {{ $sign === '+' ? 'text-emerald-600' : ($sign === '-' ? 'text-rose-600' : 'text-[#0B0F14]') }}">
                                    {{ $sign }}{{ $movement->quantity }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[#64748B] block text-[10px] uppercase font-medium">Stock Final</span>
                                <span class="font-mono font-bold text-sm text-[#0B0F14]">{{ $movement->stock_after }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-[#64748B]">
                        Aucun mouvement enregistré pour cet entrepôt.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal de confirmation / transfert pour suppression d'entrepôt --}}
    <x-delete-warehouse-modal />
</x-layouts.app>
