<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ isset($expense) ? 'Modifier la Dépense' : 'Nouvelle Dépense' }}" 
            subtitle="Enregistrez une sortie de caisse ou un engagement de dépense">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Dépenses', 'url' => route('finance.expenses.index')],
                    ['label' => isset($expense) ? 'Modifier' : 'Créer']
                ]" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ isset($expense) ? route('finance.expenses.update', $expense) : route('finance.expenses.store') }}" 
              method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if(isset($expense))
                @method('PUT')
            @endif

            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-5">
                    Détails du décaissement
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="domain_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine d'imputation</label>
                        <select name="domain_id" id="domain_id" class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">Sélectionner un domaine (ou Général)</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" {{ (old('domain_id', $expense->domain_id ?? '')) == $domain->id ? 'selected' : '' }}>{{ $domain->name }}</option>
                            @endforeach
                        </select>
                        @error('domain_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="cash_register_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Compte / Caisse de sortie <span class="text-rose-500">*</span></label>
                        <select name="cash_register_id" id="cash_register_id" required class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">Sélectionner une caisse...</option>
                            @foreach($cashRegisters as $caisse)
                                <option value="{{ $caisse->id }}" {{ (old('cash_register_id', $expense->cash_register_id ?? '')) == $caisse->id ? 'selected' : '' }}>
                                    {{ $caisse->name }} (Solde : {{ number_format($caisse->balance, 0, ',', ' ') }} FCFA)
                                </option>
                            @endforeach
                        </select>
                        @error('cash_register_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="category" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Catégorie de charge <span class="text-rose-500">*</span></label>
                        <input type="text" name="category" id="category" value="{{ old('category', $expense->category ?? '') }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]" 
                            placeholder="Ex: Fournitures, Loyer, Carburant, Entretien">
                        @error('category') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date de la dépense <span class="text-rose-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : date('Y-m-d')) }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                        @error('date') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="amount" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Montant (FCFA) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" id="amount" min="0" value="{{ old('amount', $expense->amount ?? '') }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="0">
                        @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mode de Paiement <span class="text-rose-500">*</span></label>
                        <select name="payment_method" id="payment_method" required class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="especes" {{ (old('payment_method', $expense->payment_method ?? '')) == 'especes' ? 'selected' : '' }}>Espèces (Caisse)</option>
                            <option value="cheque" {{ (old('payment_method', $expense->payment_method ?? '')) == 'cheque' ? 'selected' : '' }}>Chèque bancaire</option>
                            <option value="virement" {{ (old('payment_method', $expense->payment_method ?? '')) == 'virement' ? 'selected' : '' }}>Virement bancaire</option>
                            <option value="mobile_money" {{ (old('payment_method', $expense->payment_method ?? '')) == 'mobile_money' ? 'selected' : '' }}>Mobile Money (Orange/MTN/Wave)</option>
                        </select>
                        @error('payment_method') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Description & Justification <span class="text-rose-500">*</span></label>
                        <textarea name="description" id="description" rows="3" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Détaillez la nature de la dépense, bénéficiaire, motif...">{{ old('description', $expense->description ?? '') }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="receipt" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Facture d'achat / Pièce jointe / Reçu</label>
                        <input type="file" name="receipt" id="receipt" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0066FF]/10 file:text-[#0066FF] hover:file:bg-[#0066FF]/20">
                        @if(isset($expense) && $expense->receipt_path)
                            <p class="text-xs text-[#64748B] mt-2">Justificatif actuel : {{ basename($expense->receipt_path) }}</p>
                        @endif
                        @error('receipt') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('finance.expenses.index') }}" variant="secondary">
                    Annuler
                </x-button>
                <x-button type="submit" variant="primary">
                    {{ isset($expense) ? 'Mettre à jour la dépense' : 'Enregistrer la dépense' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
