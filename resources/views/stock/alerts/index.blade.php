<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Alertes de Stock" 
            subtitle="Surveillance des seuils critiques, ruptures imminentes et réapprovisionnements requis">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Alertes']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('stock.movements.create', ['type' => 'entree']) }}" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Approvisionner
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Table des alertes -->
    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-[#0B0F14]">Articles en rupture ou sous seuil de réapprovisionnement</h3>
                <p class="text-xs text-[#64748B]">Suivi des alertes actives générées automatiquement par le moteur de stock</p>
            </div>
            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 border border-rose-200">
                {{ $alerts->total() }} alerte(s)
            </span>
        </div>

        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Entrepôt</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Stock Actuel</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Seuil Min.</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="text-sm font-semibold text-[#0B0F14]">
                                    {{ $alert->product->name ?? 'Produit #'.$alert->product_id }}
                                </div>
                                <div class="text-xs font-mono text-[#64748B]">
                                    {{ $alert->product->sku ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-[#0B0F14] font-medium">
                                {{ $alert->warehouse->name ?? 'Entrepôt #'.$alert->warehouse_id }}
                            </td>
                            <td class="py-3.5 px-4 text-sm font-bold text-rose-600 text-right">
                                {{ $alert->current_quantity }}
                            </td>
                            <td class="py-3.5 px-4 text-sm font-mono text-[#64748B] text-right">
                                {{ $alert->min_quantity }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($alert->status === 'active')
                                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        Résolue
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($alert->status === 'active')
                                    <form method="POST" action="{{ route('stock.alerts.resolve', $alert) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center text-xs font-semibold text-[#0066FF] hover:underline cursor-pointer">
                                            Marquer comme résolue
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-[#64748B]">Clôturée</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    title="Aucune alerte de stock" 
                                    description="Tous les niveaux de stock sont optimaux ou au-dessus de leurs seuils minimaux."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($alerts as $alert)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-[#0B0F14] text-sm">{{ $alert->product->name ?? 'Produit #'.$alert->product_id }}</h4>
                            <span class="font-mono text-xs text-[#64748B] block mt-0.5">SKU: {{ $alert->product->sku ?? '-' }}</span>
                        </div>
                        @if($alert->status === 'active')
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 border border-rose-200 shrink-0">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200 shrink-0">
                                Résolue
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-[#64748B]">
                        <span>Entrepôt :</span>
                        <strong class="text-[#0B0F14]">{{ $alert->warehouse->name ?? 'Entrepôt #'.$alert->warehouse_id }}</strong>
                    </div>

                    <!-- 2-col stock grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Stock Actuel</span>
                            <span class="text-base font-extrabold text-rose-600">{{ $alert->current_quantity }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Seuil Min.</span>
                            <span class="font-mono font-bold text-[#0B0F14] text-base">{{ $alert->min_quantity }}</span>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-end">
                        @if($alert->status === 'active')
                            <form method="POST" action="{{ route('stock.alerts.resolve', $alert) }}" class="w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Marquer comme résolue</span>
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-[#64748B]">Alerte clôturée</span>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucune alerte de stock" 
                    description="Tous les niveaux de stock sont optimaux ou au-dessus de leurs seuils minimaux."
                />
            @endforelse
        </div>
    </div>
    
    @if($alerts->hasPages())
        <div class="mt-6">
            {{ $alerts->links() }}
        </div>
    @endif
</x-layouts.app>
