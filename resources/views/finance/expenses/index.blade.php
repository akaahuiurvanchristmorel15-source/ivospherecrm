<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Registre des Dépenses" 
            subtitle="Contrôle des décaissements, validations préalables et suivi budgétaire">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Dépenses']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('finance.expenses.create') }}" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle Dépense
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Filtres -->
    <div class="mb-6 rounded-xl bg-white border border-[#E2E8F0] p-4 shadow-xs">
        <form method="GET" action="{{ route('finance.expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label for="domain_id" class="block text-xs font-semibold text-[#0B0F14] mb-1">Domaine</label>
                <select name="domain_id" id="domain_id" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                    <option value="">Tous les domaines</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" {{ request('domain_id') == $domain->id ? 'selected' : '' }}>{{ $domain->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category" class="block text-xs font-semibold text-[#0B0F14] mb-1">Catégorie</label>
                <input type="text" name="category" id="category" value="{{ request('category') }}" placeholder="Ex: Fournitures" 
                    class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
            </div>
            <div>
                <label for="status" class="block text-xs font-semibold text-[#0B0F14] mb-1">Statut</label>
                <select name="status" id="status" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" {{ request('status') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="validee" {{ request('status') == 'validee' ? 'selected' : '' }}>Validée</option>
                    <option value="rejetee" {{ request('status') == 'rejetee' ? 'selected' : '' }}>Rejetée</option>
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-xs font-semibold text-[#0B0F14] mb-1">Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                    class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
            </div>
            <div class="flex items-end gap-2">
                <x-button type="submit" variant="secondary" size="md" class="w-full justify-center">
                    Filtrer
                </x-button>
                @if(request()->anyFilled(['domain_id', 'category', 'status', 'start_date']))
                    <a href="{{ route('finance.expenses.index') }}" class="inline-flex items-center justify-center p-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA]" title="Réinitialiser">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table des dépenses -->
    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Description & Catégorie</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Caisse</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 text-xs font-medium text-[#0B0F14]">{{ $expense->date->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 text-xs font-mono font-medium text-[#0B0F14]">{{ $expense->reference }}</td>
                            <td class="py-3.5 px-4">
                                <div class="text-sm font-semibold text-[#0B0F14]">{{ $expense->category }}</div>
                                <div class="text-xs text-[#64748B]">{{ Str::limit($expense->description, 50) }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14] font-medium">{{ $expense->cashRegister ? $expense->cashRegister->name : '-' }}</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-rose-600 text-right">
                                {{ number_format($expense->amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($expense->status === 'validee')
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        Validée
                                    </span>
                                @elseif($expense->status === 'en_attente')
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                        En attente
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                        Rejetée
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if($expense->status === 'en_attente')
                                        <form action="{{ route('finance.expenses.approve', $expense) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center justify-center h-7 px-2.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors" title="Valider la dépense">
                                                ✓ Valider
                                            </button>
                                        </form>
                                        <a href="{{ route('finance.expenses.edit', $expense) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">
                                            Éditer
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    title="Aucune dépense trouvée" 
                                    description="Aucune dépense ne correspond aux critères de recherche actuels."
                                    action-label="Nouvelle dépense"
                                    :action-url="route('finance.expenses.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-[#F5F7FA] border-t border-[#E2E8F0]">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-right text-xs font-bold uppercase tracking-wider text-[#0B0F14]">Total des dépenses filtrées :</td>
                        <td class="py-3.5 px-4 text-right text-base font-extrabold text-rose-600">{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($expenses as $expense)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-[#0B0F14] text-xs">{{ $expense->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $expense->category }}</h4>
                        </div>
                        @if($expense->status === 'validee')
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200">
                                Validée
                            </span>
                        @elseif($expense->status === 'en_attente')
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200">
                                En attente
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 border border-rose-200">
                                Rejetée
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-[#64748B] leading-relaxed">{{ $expense->description }}</p>

                    <!-- 2-col date & cash register grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Date</span>
                            <span class="font-mono font-medium text-[#0B0F14]">{{ $expense->date->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Caisse</span>
                            <span class="font-medium text-[#0B0F14]">{{ $expense->cashRegister ? $expense->cashRegister->name : 'Non affectée' }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block">Montant</span>
                            <span class="text-sm font-extrabold text-rose-600">
                                - {{ number_format($expense->amount, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($expense->status === 'en_attente')
                                <form action="{{ route('finance.expenses.approve', $expense) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="py-1.5 px-3 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Valider</span>
                                    </button>
                                </form>
                                <a href="{{ route('finance.expenses.edit', $expense) }}" class="py-1.5 px-3 rounded-lg border border-[#E2E8F0] text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">
                                    Éditer
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucune dépense trouvée" 
                    description="Aucune dépense ne correspond aux critères de recherche actuels."
                    action-label="Nouvelle dépense"
                    :action-url="route('finance.expenses.create')"
                />
            @endforelse

            <div class="p-4 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0] flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0B0F14]">Total filtré :</span>
                <span class="text-base font-extrabold text-rose-600">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
        
        @if($expenses->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
