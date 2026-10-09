<x-layouts.app :title="'Commissions de Courtage — ASSURANCE'">
    <x-slot name="header">
        <x-page-header 
            title="Rétrocession des Commissions (ASSURANCE)" 
            subtitle="Suivi des commissions reversées par les compagnies d'assurance partenaires sur les polices souscrites">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'ASSURANCE', 'url' => route('assurance.index')],
                    ['label' => 'Commissions']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('assurance.contracts.index') }}" variant="secondary">
                    <span>Voir les Contrats</span>
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Contrat / Police</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date Émission</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Taux Appliqué</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant Commission</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($commissions as $comm)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#0066FF]">
                                <a href="{{ $comm->contract ? route('assurance.contracts.show', $comm->contract) : '#' }}" class="hover:underline">
                                    {{ $comm->contract->reference ?? 'Police #' . $comm->contract_id }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                {{ $comm->date ? \Carbon\Carbon::parse($comm->date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-semibold text-[#0B0F14] text-right">
                                {{ $comm->rate }}%
                            </td>
                            <td class="py-3.5 px-4 text-sm font-bold text-emerald-600 text-right">
                                {{ number_format($comm->amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($comm->status === 'payé' || $comm->status === 'payée')
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        Payée
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                        En attente
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($comm->status !== 'payé' && $comm->status !== 'payée')
                                    <form action="{{ route('assurance.commissions.pay', $comm) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold text-[#0066FF] hover:underline">
                                            Marquer encaissée
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-[#64748B]">Encaissée</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    title="Aucune commission enregistrée" 
                                    description="Les commissions rétrocédées sur les contrats apparaîtront ici."
                                    action-label="Voir les contrats"
                                    :action-url="route('assurance.contracts.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($commissions as $comm)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-[#0066FF] text-xs">
                            {{ $comm->contract->reference ?? 'Police #' . $comm->contract_id }}
                        </span>
                        <span class="text-xs font-bold text-emerald-600">
                            {{ number_format($comm->amount, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-[#64748B]">
                        <span>Taux : {{ $comm->rate }}%</span>
                        <span>Date : {{ $comm->date ? \Carbon\Carbon::parse($comm->date)->format('d/m/Y') : '-' }}</span>
                    </div>
                    @if($comm->status !== 'payé' && $comm->status !== 'payée')
                        <div class="pt-2 border-t border-[#E2E8F0] text-right">
                            <form action="{{ route('assurance.commissions.pay', $comm) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold text-[#0066FF] hover:underline">
                                    Marquer encaissée &rarr;
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[#64748B]">
                    Aucune commission pour le moment.
                </div>
            @endforelse
        </div>

        @if($commissions->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
