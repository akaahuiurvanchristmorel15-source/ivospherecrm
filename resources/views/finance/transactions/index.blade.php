<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Journal Général des Transactions" 
            subtitle="Grand livre des écritures financières, mouvements de trésorerie et soldes progressifs">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Transactions']
                ]" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Table du Journal -->
    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-[#0B0F14]">Écritures de trésorerie enregistrées</h3>
                <p class="text-xs text-[#64748B]">Traçabilité inaltérable de tous les flux entrants et sortants</p>
            </div>
            <span class="inline-flex items-center rounded-md bg-[#F5F7FA] px-2.5 py-1 text-xs font-medium text-[#0B0F14] border border-[#E2E8F0]">
                {{ $transactions->total() }} écriture(s)
            </span>
        </div>

        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date & Heure</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Nature du flux</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Description</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Caisse / Compte</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Solde Après</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($transactions as $tx)
                        @php
                            $badgeClass = match($tx->type) {
                                'depot' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'encaissement' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'retrait' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'virement' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                            };
                            $typeLabel = match($tx->type) {
                                'depot' => 'Dépôt',
                                'retrait' => 'Retrait',
                                'virement' => 'Virement',
                                'encaissement' => 'Encaissement',
                                default => ucfirst($tx->type)
                            };
                            $isCredit = in_array($tx->type, ['depot', 'encaissement']);
                        @endphp
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 text-xs font-medium text-[#0B0F14]">{{ $tx->date->format('d/m/Y H:i') }}</td>
                            <td class="py-3.5 px-4 text-xs font-mono font-medium text-[#0B0F14]">{{ $tx->reference }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badgeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-sm text-[#0B0F14]">{{ $tx->description }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14] font-medium">{{ $tx->cashRegister ? $tx->cashRegister->name : '-' }}</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-right {{ $isCredit ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isCredit ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-sm font-bold text-right text-[#0066FF]">
                                {{ number_format($tx->balance_after, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    title="Aucune transaction trouvée" 
                                    description="Le journal ne contient encore aucune écriture financière."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($transactions as $tx)
                @php
                    $badgeClass = match($tx->type) {
                        'depot' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'encaissement' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'retrait' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'virement' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                        default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                    };
                    $typeLabel = match($tx->type) {
                        'depot' => 'Dépôt',
                        'retrait' => 'Retrait',
                        'virement' => 'Virement',
                        'encaissement' => 'Encaissement',
                        default => ucfirst($tx->type)
                    };
                    $isCredit = in_array($tx->type, ['depot', 'encaissement']);
                @endphp
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-[#0B0F14] text-xs">{{ $tx->reference }}</span>
                            <div class="font-mono text-[11px] text-[#64748B]">{{ $tx->date->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border {{ $badgeClass }}">
                            {{ $typeLabel }}
                        </span>
                    </div>

                    <p class="text-xs text-[#0B0F14] font-medium leading-relaxed">{{ $tx->description }}</p>

                    <div class="text-xs text-[#64748B] flex items-center justify-between">
                        <span>Caisse / Compte :</span>
                        <span class="font-semibold text-[#0B0F14]">{{ $tx->cashRegister ? $tx->cashRegister->name : 'Transversal' }}</span>
                    </div>

                    <div class="pt-2 border-t border-[#E2E8F0] grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block">Flux</span>
                            <span class="text-sm font-extrabold {{ $isCredit ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isCredit ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block">Solde Après</span>
                            <span class="text-sm font-extrabold text-[#0066FF]">
                                {{ number_format($tx->balance_after, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucune transaction trouvée" 
                    description="Le journal ne contient encore aucune écriture financière."
                />
            @endforelse
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
