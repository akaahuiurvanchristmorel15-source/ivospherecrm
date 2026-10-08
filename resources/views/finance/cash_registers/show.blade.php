<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ $cashRegister->name }}" 
            subtitle="Code : {{ $cashRegister->code }} &bull; Domaine : {{ $cashRegister->domain->name ?? 'Trésorerie Centrale' }}">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Caisses', 'url' => route('finance.cash-registers.index')],
                    ['label' => $cashRegister->name]
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('finance.cash-registers.index') }}" variant="secondary" class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux caisses</span>
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Colonne Gauche (4 cols) : Solde & Formulaire Transaction -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Carte Solde & Info -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#64748B] mb-1">Solde Actuel Disponible</h3>
                <p class="text-3xl font-extrabold text-[#0066FF] mb-5">{{ number_format($cashRegister->balance, 0, ',', ' ') }} FCFA</p>
                
                <dl class="space-y-3 pt-4 border-t border-[#E2E8F0]">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-[#64748B]">Code Caisse :</span>
                        <span class="font-mono font-medium text-[#0B0F14] bg-[#F5F7FA] px-2 py-0.5 rounded border border-[#E2E8F0]">{{ $cashRegister->code }}</span>
                    </div>
                    @if($cashRegister->domain)
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[#64748B]">Domaine d'Activité :</span>
                            <span class="font-medium text-[#0B0F14]">{{ $cashRegister->domain->name }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-[#64748B]">Statut :</span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $cashRegister->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                            {{ $cashRegister->is_active ? 'Active' : 'Inactif' }}
                        </span>
                    </div>
                </dl>
            </div>

            <!-- Formulaire Transaction Rapide -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-4">
                    Nouvelle Opération
                </h3>
                <form action="{{ route('finance.transactions.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="cash_register_id" value="{{ $cashRegister->id }}">
                    
                    <div>
                        <label for="type" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Type d'opération <span class="text-rose-500">*</span></label>
                        <select name="type" id="type" required class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="depot">Dépôt / Alimentation (+)</option>
                            <option value="retrait">Retrait / Décaissement (-)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="amount" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Montant (FCFA) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" id="amount" min="1" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Ex: 50 000">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Motif / Justification <span class="text-rose-500">*</span></label>
                        <textarea name="description" id="description" rows="2" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Détail de l'opération..."></textarea>
                    </div>
                    
                    <x-button type="submit" variant="primary" class="w-full justify-center">
                        Enregistrer l'opération
                    </x-button>
                </form>
            </div>
        </div>

        <!-- Colonne Droite (8 cols) : Table des Transactions -->
        <div class="lg:col-span-8">
            <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
                <div class="p-5 border-b border-[#E2E8F0] flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-semibold text-[#0B0F14]">Historique des Mouvements de Caisse</h3>
                        <p class="text-xs text-[#64748B]">Derniers dépôts, retraits et flux validés sur ce compte</p>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-[#F5F7FA] px-2.5 py-1 text-xs font-medium text-[#0B0F14] border border-[#E2E8F0]">
                        {{ $transactions->total() }} opération(s)
                    </span>
                </div>

                <!-- Vue Table Desktop (>= md) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                            <tr>
                                <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date</th>
                                <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Type</th>
                                <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Description</th>
                                <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @forelse($transactions as $tx)
                                @php
                                    $isCredit = in_array($tx->type, ['depot', 'encaissement']);
                                @endphp
                                <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                                    <td class="py-3.5 px-4 text-xs font-medium text-[#0B0F14]">{{ $tx->date->format('d/m/Y H:i') }}</td>
                                    <td class="py-3.5 px-4">
                                        @if($isCredit)
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                                Entrée
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                                Sortie
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-sm text-[#0B0F14]">{{ $tx->description }}</td>
                                    <td class="py-3.5 px-4 text-sm font-bold text-right {{ $isCredit ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $isCredit ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-empty-state 
                                            title="Aucune transaction" 
                                            description="Aucune transaction n'a encore été enregistrée sur cette caisse."
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
                            $isCredit = in_array($tx->type, ['depot', 'encaissement']);
                        @endphp
                        <div class="bg-white rounded-xl p-3.5 border border-[#E2E8F0] shadow-xs flex flex-col gap-2">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-mono text-xs text-[#64748B]">{{ $tx->date->format('d/m/Y H:i') }}</span>
                                @if($isCredit)
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200">
                                        Entrée (+)
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 border border-rose-200">
                                        Sortie (-)
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-[#0B0F14] font-medium leading-relaxed">{{ $tx->description }}</p>
                            <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between">
                                <span class="text-[10px] uppercase font-bold text-[#64748B]">Montant</span>
                                <span class="text-sm font-extrabold {{ $isCredit ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $isCredit ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    @empty
                        <x-empty-state 
                            title="Aucune transaction" 
                            description="Aucune transaction n'a encore été enregistrée sur cette caisse."
                        />
                    @endforelse
                </div>

                @if($transactions->hasPages())
                    <div class="p-4 border-t border-[#E2E8F0]">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
