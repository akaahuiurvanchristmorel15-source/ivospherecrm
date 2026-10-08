<x-layouts.app :title="isset($salesTarget) ? 'Modifier l\'Objectif Commercial' : 'Fixer un Objectif de Vente — IVOSPHERE RH'">
    @php
        $isEdit = isset($salesTarget);
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('rh.sales-targets.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux objectifs des ventes</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier l\'Objectif : ' . $salesTarget->title : 'Assigner un Nouvel Objectif Commercial' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Définir le quota financier, la période de validité, le taux de commission et les primes d'atteinte.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('rh.sales-targets.update', $salesTarget) : route('rh.sales-targets.store') }}" 
            method="POST" 
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Collaborateur & Intitulé -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Collaborateur & Pôle Concerné</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="employee-select" class="block font-semibold text-[#0B0F14] mb-1">Commercial / Salarié *</label>
                        <select 
                            id="employee-select" 
                            name="employee_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required
                        >
                            <option value="">Sélectionnez un collaborateur</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', $salesTarget->employee_id ?? '') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->department ?? 'Général' }} - {{ $emp->position }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="domain-select" class="block font-semibold text-[#0B0F14] mb-1">Pôle Métier / Atelier</label>
                        <select 
                            id="domain-select" 
                            name="domain_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]"
                        >
                            <option value="">Tous les pôles (Transversal)</option>
                            @foreach($domains as $dom)
                                <option value="{{ $dom->id }}" {{ old('domain_id', $salesTarget->domain_id ?? '') == $dom->id ? 'selected' : '' }}>
                                    {{ $dom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="title-input" class="block font-semibold text-[#0B0F14] mb-1">Intitulé de l'Objectif *</label>
                        <input 
                            id="title-input" 
                            type="text" 
                            name="title" 
                            value="{{ old('title', $salesTarget->title ?? 'Objectif Ventes ' . now()->translatedFormat('F Y')) }}" 
                            placeholder="Ex: Quota Print & Tech T1" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-medium text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required 
                        />
                        @error('title') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Périodicité & Temporalité -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Période d'Évaluation</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="period-select" class="block font-semibold text-[#0B0F14] mb-1">Type de Période *</label>
                        <select 
                            id="period-select" 
                            name="period" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required
                        >
                            <option value="mensuel" {{ old('period', $salesTarget->period ?? 'mensuel') === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                            <option value="trimestriel" {{ old('period', $salesTarget->period ?? '') === 'trimestriel' ? 'selected' : '' }}>Trimestriel</option>
                            <option value="semestriel" {{ old('period', $salesTarget->period ?? '') === 'semestriel' ? 'selected' : '' }}>Semestriel</option>
                            <option value="annuel" {{ old('period', $salesTarget->period ?? '') === 'annuel' ? 'selected' : '' }}>Annuel</option>
                            <option value="personnalise" {{ old('period', $salesTarget->period ?? '') === 'personnalise' ? 'selected' : '' }}>Personnalisé (Campagne spéciale)</option>
                        </select>
                    </div>

                    <div>
                        <label for="start-date-input" class="block font-semibold text-[#0B0F14] mb-1">Date de Début *</label>
                        <input 
                            id="start-date-input" 
                            type="date" 
                            name="start_date" 
                            value="{{ old('start_date', isset($salesTarget->start_date) ? $salesTarget->start_date->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required 
                        />
                        @error('start_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="end-date-input" class="block font-semibold text-[#0B0F14] mb-1">Date de Fin *</label>
                        <input 
                            id="end-date-input" 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date', isset($salesTarget->end_date) ? $salesTarget->end_date->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required 
                        />
                        @error('end_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Quotas Financiers & Réalisé -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Quotas Financiers & Volumes</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="target-amount-input" class="block font-semibold text-[#0B0F14] mb-1">Montant Cible (FCFA) *</label>
                        <input 
                            id="target-amount-input" 
                            type="number" 
                            step="0.01" 
                            min="1" 
                            name="target_amount" 
                            value="{{ old('target_amount', $salesTarget->target_amount ?? 1000000) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-bold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required 
                        />
                        @error('target_amount') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="achieved-amount-input" class="block font-semibold text-[#0B0F14] mb-1">Montant Réalisé (FCFA)</label>
                        <input 
                            id="achieved-amount-input" 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="achieved_amount" 
                            value="{{ old('achieved_amount', $salesTarget->achieved_amount ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-bold text-[#0066FF] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                        />
                        @error('achieved_amount') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="target-sales-count-input" class="block font-semibold text-[#0B0F14] mb-1">Nombre de Ventes Cible</label>
                        <input 
                            id="target-sales-count-input" 
                            type="number" 
                            min="0" 
                            name="target_sales_count" 
                            value="{{ old('target_sales_count', $salesTarget->target_sales_count ?? '') }}" 
                            placeholder="Optionnel (ex: 15)" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                        />
                    </div>

                    <div>
                        <label for="status-select" class="block font-semibold text-[#0B0F14] mb-1">Statut d'Évaluation *</label>
                        <select 
                            id="status-select" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            required
                        >
                            <option value="en_cours" {{ old('status', $salesTarget->status ?? 'en_cours') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="atteint" {{ old('status', $salesTarget->status ?? '') === 'atteint' ? 'selected' : '' }}>Objectif Atteint</option>
                            <option value="partiel" {{ old('status', $salesTarget->status ?? '') === 'partiel' ? 'selected' : '' }}>Partiellement Atteint</option>
                            <option value="non_atteint" {{ old('status', $salesTarget->status ?? '') === 'non_atteint' ? 'selected' : '' }}>Non Atteint</option>
                            <option value="annule" {{ old('status', $salesTarget->status ?? '') === 'annule' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 4 : Rémunération Variable & Primes -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">4. Intéressement, Commissions & Primes</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="commission-rate-input" class="block font-semibold text-[#0B0F14] mb-1">Taux de Commission sur les Ventes (%)</label>
                        <div class="relative">
                            <input 
                                id="commission-rate-input" 
                                type="number" 
                                step="0.1" 
                                min="0" 
                                max="100" 
                                name="commission_rate" 
                                value="{{ old('commission_rate', $salesTarget->commission_rate ?? 0) }}" 
                                placeholder="Ex: 3.5" 
                                class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#64748B] font-bold">
                                %
                            </div>
                        </div>
                        <span class="text-[11px] text-[#64748B] mt-1 block">Pourcentage reversé au commercial sur le CA généré.</span>
                    </div>

                    <div>
                        <label for="bonus-amount-input" class="block font-semibold text-[#0B0F14] mb-1">Prime Forfaitaire si Objectif Atteint (FCFA)</label>
                        <input 
                            id="bonus-amount-input" 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="bonus_amount" 
                            value="{{ old('bonus_amount', $salesTarget->bonus_amount ?? 0) }}" 
                            placeholder="Ex: 50000" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]" 
                        />
                        <span class="text-[11px] text-[#64748B] mt-1 block">Bonus financier accordé dès validation de 100% de la cible.</span>
                    </div>
                </div>
            </div>

            <!-- Section 5 : Remarques & Instructions RH -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">5. Remarques & Contexte RH</h3>
                <div>
                    <label for="notes-textarea" class="block font-semibold text-[#0B0F14] mb-1">Notes Internes / Accords Particuliers</label>
                    <textarea 
                        id="notes-textarea" 
                        name="notes" 
                        rows="3" 
                        placeholder="Ex: Objectif convenu lors de l'entretien d'évaluation annuel. Focus particulier sur les contrats d'assurance et tech..." 
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]"
                    >{{ old('notes', $salesTarget->notes ?? '') }}</textarea>
                </div>
            </div>

            <!-- Actions de soumission -->
            <div class="pt-4 border-t border-[#E2E8F0] flex items-center justify-between">
                <a href="{{ route('rh.sales-targets.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:text-[#0B0F14] font-medium transition-colors">
                    Annuler
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#0B0F14] text-white hover:bg-[#0066FF] font-semibold transition-colors shadow-xs"
                >
                    {{ $isEdit ? 'Mettre à Jour l\'Objectif' : 'Enregistrer l\'Objectif de Vente' }}
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
