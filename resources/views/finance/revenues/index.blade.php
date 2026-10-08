<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Registre des Recettes" 
            subtitle="Encaissements clients, règlements hors factures et autres produits d'exploitation">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Recettes']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('finance.revenues.create') }}" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle Recette
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Table des Recettes -->
    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Origine / Source</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Caisse</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($revenues as $revenue)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 text-xs font-medium text-[#0B0F14]">{{ $revenue->date->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 text-xs font-mono font-medium text-[#0B0F14]">{{ $revenue->reference }}</td>
                            <td class="py-3.5 px-4">
                                <div class="text-sm font-semibold text-[#0B0F14]">{{ $revenue->source }}</div>
                                <div class="text-xs text-[#64748B]">{{ Str::limit($revenue->description, 50) }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14] font-medium">{{ $revenue->cashRegister ? $revenue->cashRegister->name : '-' }}</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-emerald-600 text-right">
                                {{ number_format($revenue->amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('finance.revenues.edit', $revenue) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0066FF]">
                                    Éditer
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    title="Aucune recette trouvée" 
                                    description="Aucune recette financière n'a encore été enregistrée."
                                    action-label="Nouvelle recette"
                                    :action-url="route('finance.revenues.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-[#F5F7FA] border-t border-[#E2E8F0]">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-right text-xs font-bold uppercase tracking-wider text-[#0B0F14]">Total des Recettes :</td>
                        <td class="py-3.5 px-4 text-right text-base font-extrabold text-emerald-600">{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($revenues as $revenue)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-[#0B0F14] text-xs">{{ $revenue->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $revenue->source }}</h4>
                        </div>
                        <span class="font-mono text-xs text-[#64748B]">{{ $revenue->date->format('d/m/Y') }}</span>
                    </div>

                    @if($revenue->description)
                        <p class="text-xs text-[#64748B] leading-relaxed">{{ $revenue->description }}</p>
                    @endif

                    <!-- 2-col info grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Caisse</span>
                            <span class="font-medium text-[#0B0F14]">{{ $revenue->cashRegister ? $revenue->cashRegister->name : 'Non affectée' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Montant</span>
                            <span class="text-sm font-extrabold text-emerald-600">
                                + {{ number_format($revenue->amount, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-end">
                        <a href="{{ route('finance.revenues.edit', $revenue) }}" class="py-1.5 px-3 rounded-lg border border-[#E2E8F0] text-xs font-semibold text-[#0066FF] hover:bg-[#F5F7FA] transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Éditer</span>
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucune recette trouvée" 
                    description="Aucune recette financière n'a encore été enregistrée."
                    action-label="Nouvelle recette"
                    :action-url="route('finance.revenues.create')"
                />
            @endforelse

            <div class="p-4 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0] flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0B0F14]">Total Recettes :</span>
                <span class="text-base font-extrabold text-emerald-600">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
        
        @if($revenues->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $revenues->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
