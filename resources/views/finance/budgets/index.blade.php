<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Suivi des Budgets" 
            subtitle="Plafonds prévisionnels de dépenses, allocations par pôle et taux de consommation">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Budgets']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('finance.budgets.create') }}" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau Budget
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Grille des Budgets -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($budgets as $budget)
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs hover:border-[#0066FF] transition-all flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-base font-semibold text-[#0B0F14]">{{ $budget->name }}</h3>
                            <p class="text-xs text-[#64748B] mt-0.5">
                                {{ $budget->period_start->format('M Y') }} &minus; {{ $budget->period_end->format('M Y') }}
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $budget->status === 'actif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-[#64748B] border-[#E2E8F0]' }}">
                            {{ $budget->status === 'actif' ? 'Actif' : 'Clôturé' }}
                        </span>
                    </div>

                    @if($budget->domain)
                        <div class="mt-1 text-xs text-[#64748B]">
                            Pôle : <span class="font-medium text-[#0B0F14]">{{ $budget->domain->name }}</span>
                        </div>
                    @endif
                    
                    <dl class="mt-4 space-y-2 border-t border-[#E2E8F0] pt-4">
                        <div class="flex justify-between text-xs">
                            <span class="text-[#64748B]">Enveloppe allouée :</span>
                            <span class="font-bold text-[#0B0F14]">{{ number_format($budget->amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-[#64748B]">Montant engagé / dépensé :</span>
                            <span class="font-bold text-rose-600">{{ number_format($budget->spent, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-[#64748B]">Solde restant disponible :</span>
                            <span class="font-bold text-[#0066FF]">{{ number_format($budget->remaining, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </dl>

                    @php
                        $percent = min(100, $budget->progress_percent);
                        $barColor = $percent < 70 ? 'bg-emerald-500' : ($percent < 90 ? 'bg-amber-500' : 'bg-rose-500');
                    @endphp
                    <div class="mt-4">
                        <div class="flex justify-between text-[11px] font-medium text-[#64748B] mb-1">
                            <span>Consommation</span>
                            <span>{{ number_format($budget->progress_percent, 1) }}%</span>
                        </div>
                        <div class="w-full bg-[#F5F7FA] border border-[#E2E8F0] rounded-full h-2 overflow-hidden">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 pt-4 border-t border-[#E2E8F0] flex justify-end">
                    <a href="{{ route('finance.budgets.edit', $budget) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0066FF] hover:underline">
                        <span>Modifier le budget</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Aucun budget configuré" 
                    description="Créez votre premier budget de gestion prévisionnelle pour maîtriser vos dépenses."
                    action-label="Créer un budget"
                    :action-url="route('finance.budgets.create')"
                />
            </div>
        @endforelse
    </div>

    @if($budgets->hasPages())
        <div class="mt-6">
            {{ $budgets->links() }}
        </div>
    @endif
</x-layouts.app>
