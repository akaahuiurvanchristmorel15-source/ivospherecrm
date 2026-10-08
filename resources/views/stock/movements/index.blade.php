<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Mouvements de Stock" 
            subtitle="Historique des flux de stock : entrées, sorties, transferts, retours et ajustements">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Mouvements']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('stock.movements.create') }}" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau Mouvement
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Filtres -->
    <div class="mb-6 rounded-xl bg-white border border-[#E2E8F0] p-4 shadow-xs">
        <form method="GET" action="{{ route('stock.movements.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label for="warehouse_id" class="block text-xs font-semibold text-[#0B0F14] mb-1">Entrepôt</label>
                <select name="warehouse_id" id="warehouse_id" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                    <option value="">Tous les entrepôts</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type" class="block text-xs font-semibold text-[#0B0F14] mb-1">Type de flux</label>
                <select name="type" id="type" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                    <option value="">Tous les types</option>
                    <option value="entree" {{ request('type') == 'entree' ? 'selected' : '' }}>Entrée (+)</option>
                    <option value="sortie" {{ request('type') == 'sortie' ? 'selected' : '' }}>Sortie (-)</option>
                    <option value="transfert" {{ request('type') == 'transfert' ? 'selected' : '' }}>Transfert</option>
                    <option value="retour" {{ request('type') == 'retour' ? 'selected' : '' }}>Retour (+)</option>
                    <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustement</option>
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-xs font-semibold text-[#0B0F14] mb-1">Date début</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
            </div>
            <div>
                <label for="end_date" class="block text-xs font-semibold text-[#0B0F14] mb-1">Date fin</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
            </div>
            <div class="flex items-end gap-2">
                <x-button type="submit" variant="secondary" size="md" class="w-full justify-center">
                    Filtrer
                </x-button>
                @if(request()->anyFilled(['warehouse_id', 'type', 'start_date', 'end_date']))
                    <a href="{{ route('stock.movements.index') }}" class="inline-flex items-center justify-center p-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]" title="Réinitialiser">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table des Mouvements -->
    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Type</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Entrepôt</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Qté (+/-)</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Stock Après</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Opérateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($movements as $movement)
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
                            <td class="py-3.5 px-4 text-xs font-mono font-medium text-[#0B0F14]">{{ $movement->reference }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badgeClass }}">
                                    {{ ucfirst($movement->type) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-[#0B0F14] font-medium">{{ $movement->warehouse->name }}</td>
                            <td class="py-3.5 px-4 text-sm text-[#0B0F14]">
                                {{ $movement->product->name ?? 'Produit #'.$movement->product_id }}
                            </td>
                            <td class="py-3.5 px-4 text-sm font-bold text-right {{ $sign === '+' ? 'text-emerald-600' : ($sign === '-' ? 'text-rose-600' : 'text-[#0B0F14]') }}">
                                {{ $sign }}{{ $movement->quantity }}
                            </td>
                            <td class="py-3.5 px-4 text-sm font-mono text-right text-[#64748B]">{{ $movement->stock_after }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#64748B]">{{ $movement->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    title="Aucun mouvement de stock" 
                                    description="Aucune transaction n'a été enregistrée pour les filtres sélectionnés."
                                    action-label="Nouveau mouvement"
                                    :action-url="route('stock.movements.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Amplified Cards View (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($movements as $movement)
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
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono font-bold text-xs text-slate-700">
                            {{ $movement->reference }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $badgeClass }}">
                            {{ ucfirst($movement->type) }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-[#0B0F14] leading-snug">{{ $movement->product->name ?? 'Produit #'.$movement->product_id }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Entrepôt : <span class="font-medium text-slate-800">{{ $movement->warehouse->name }}</span></p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Quantité Flux</span>
                            <span class="text-base font-extrabold {{ $sign === '+' ? 'text-emerald-600' : ($sign === '-' ? 'text-rose-600' : 'text-[#0B0F14]') }}">
                                {{ $sign }}{{ $movement->quantity }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Stock Après</span>
                            <span class="text-sm font-bold font-mono text-slate-700">{{ $movement->stock_after }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                        <span>Date : <strong class="text-slate-700 font-mono">{{ $movement->date->format('d/m/Y') }}</strong></span>
                        <span>Opérateur : <strong class="text-slate-700">{{ $movement->user->name ?? '-' }}</strong></span>
                    </div>
                </div>
            @empty
                <div class="py-8">
                    <x-empty-state 
                        title="Aucun mouvement de stock" 
                        description="Aucune transaction n'a été enregistrée pour les filtres sélectionnés."
                    />
                </div>
            @endforelse
        </div>
    </div>
    
    @if($movements->hasPages())
        <div class="mt-6">
            {{ $movements->links() }}
        </div>
    @endif
</x-layouts.app>
