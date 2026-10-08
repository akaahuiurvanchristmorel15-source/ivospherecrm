<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ isset($budget) ? 'Modifier le Budget' : 'Nouveau Budget' }}" 
            subtitle="Définissez les dates, l'enveloppe allouée et le pôle concerné">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Budgets', 'url' => route('finance.budgets.index')],
                    ['label' => isset($budget) ? 'Modifier' : 'Créer']
                ]" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ isset($budget) ? route('finance.budgets.update', $budget) : route('finance.budgets.store') }}" 
              method="POST" class="space-y-6">
            @csrf
            @if(isset($budget))
                @method('PUT')
            @endif

            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-5">
                    Paramètres du budget
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="domain_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Pôle / Domaine d'affectation <span class="text-rose-500">*</span></label>
                        <select name="domain_id" id="domain_id" required class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">Sélectionner un pôle...</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" {{ (old('domain_id', $budget->domain_id ?? '')) == $domain->id ? 'selected' : '' }}>{{ $domain->name }}</option>
                            @endforeach
                        </select>
                        @error('domain_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Intitulé du Budget <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $budget->name ?? '') }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Ex: Budget Marketing T1, Frais généraux 2026">
                        @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="amount" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Enveloppe Allouée (FCFA) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" id="amount" min="0" value="{{ old('amount', $budget->amount ?? '') }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="0">
                        @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if(isset($budget))
                        <div>
                            <label for="spent" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Montant Consommé (FCFA)</label>
                            <input type="number" name="spent" id="spent" min="0" value="{{ old('spent', $budget->spent ?? '0') }}" required 
                                class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            @error('spent') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    
                    <div>
                        <label for="period_start" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date de début <span class="text-rose-500">*</span></label>
                        <input type="date" name="period_start" id="period_start" value="{{ old('period_start', isset($budget) ? $budget->period_start->format('Y-m-d') : date('Y-m-01')) }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                        @error('period_start') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="period_end" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date de fin <span class="text-rose-500">*</span></label>
                        <input type="date" name="period_end" id="period_end" value="{{ old('period_end', isset($budget) ? $budget->period_end->format('Y-m-d') : date('Y-m-t')) }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                        @error('period_end') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="status" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut de gestion</label>
                        <select name="status" id="status" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="actif" {{ (old('status', $budget->status ?? '')) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="cloturé" {{ (old('status', $budget->status ?? '')) == 'cloturé' ? 'selected' : '' }}>Clôturé</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('finance.budgets.index') }}" variant="secondary">
                    Annuler
                </x-button>
                <x-button type="submit" variant="primary">
                    {{ isset($budget) ? 'Mettre à jour le budget' : 'Créer le budget' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
