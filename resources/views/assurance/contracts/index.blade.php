<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Contrats d'Assurance (ASSURANCE)" 
            subtitle="Portefeuille des polices souscrites, primes périodiques et rétrocession de commissions">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'ASSURANCE', 'url' => route('assurance.index')],
                    ['label' => 'Contrats']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('assurance.contracts.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Contrat
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Police / Réf.</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Assuré / Client</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Produit & Compagnie</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Périodicité</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant Prime</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#0066FF]">{{ $contract->reference }}</td>
                            <td class="py-3.5 px-4 text-sm font-semibold text-[#0B0F14]">{{ $contract->customer->name ?? 'Assuré' }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                {{ $contract->product->name ?? 'Police standard' }} 
                                <span class="text-[#64748B]">({{ $contract->partner }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-xs capitalize text-[#64748B]">{{ $contract->frequency }}</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">{{ number_format($contract->premium, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('assurance.contracts.show', $contract) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Détails</a>
                                    <a href="{{ route('assurance.contracts.edit', $contract) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    title="Aucun contrat d'assurance" 
                                    description="Aucune police d'assurance souscrite n'est actuellement enregistrée."
                                    action-label="Nouveau contrat"
                                    :action-url="route('assurance.contracts.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($contracts as $contract)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-[#0066FF] text-xs">{{ $contract->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $contract->customer->name ?? 'Assuré' }}</h4>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200 shrink-0">
                            {{ ucfirst($contract->status) }}
                        </span>
                    </div>

                    <div class="text-xs text-[#0B0F14]">
                        <span class="font-medium">{{ $contract->product->name ?? 'Police standard' }}</span>
                        <span class="text-[#64748B]">({{ $contract->partner }})</span>
                    </div>

                    <!-- 2-col info grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Périodicité</span>
                            <span class="font-medium text-[#0B0F14] capitalize">{{ $contract->frequency }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Prime</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($contract->premium, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    <!-- Actions bar -->
                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                        <a href="{{ route('assurance.contracts.show', $contract) }}" class="flex-1 py-2 px-3 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Détails Police</span>
                        </a>
                        <a href="{{ route('assurance.contracts.edit', $contract) }}" class="py-2 px-3 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Modifier</span>
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucun contrat d'assurance" 
                    description="Aucune police d'assurance souscrite n'est actuellement enregistrée."
                    action-label="Nouveau contrat"
                    :action-url="route('assurance.contracts.create')"
                />
            @endforelse
        </div>
    </div>
</x-layouts.app>
