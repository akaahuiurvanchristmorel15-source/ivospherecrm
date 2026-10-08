<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="{{ isset($revenue) ? 'Modifier la Recette' : 'Nouvelle Recette' }}" 
            subtitle="Enregistrez un encaissement direct sur un compte de trésorerie">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Finances', 'url' => route('finance.index')],
                    ['label' => 'Recettes', 'url' => route('finance.revenues.index')],
                    ['label' => isset($revenue) ? 'Modifier' : 'Créer']
                ]" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ isset($revenue) ? route('finance.revenues.update', $revenue) : route('finance.revenues.store') }}" 
              method="POST" class="space-y-6">
            @csrf
            @if(isset($revenue))
                @method('PUT')
            @endif

            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-5">
                    Informations de la recette
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(!isset($revenue))
                        <div>
                            <label for="cash_register_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Compte / Caisse réceptrice <span class="text-rose-500">*</span></label>
                            <select name="cash_register_id" id="cash_register_id" required class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                                <option value="">Sélectionner une caisse...</option>
                                @foreach($cashRegisters as $caisse)
                                    <option value="{{ $caisse->id }}">{{ $caisse->name }} (Solde : {{ number_format($caisse->balance, 0, ',', ' ') }} FCFA)</option>
                                @endforeach
                            </select>
                            @error('cash_register_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="amount" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Montant encaissé (FCFA) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" id="amount" min="0" required 
                                class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                                placeholder="0">
                            @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="date" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date d'encaissement <span class="text-rose-500">*</span></label>
                            <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required 
                                class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            @error('date') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mode de Paiement <span class="text-rose-500">*</span></label>
                            <select name="payment_method" id="payment_method" required class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                                <option value="especes">Espèces (Caisse)</option>
                                <option value="cheque">Chèque bancaire</option>
                                <option value="virement">Virement bancaire</option>
                                <option value="mobile_money">Mobile Money (Orange/MTN/Wave)</option>
                            </select>
                            @error('payment_method') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="{{ isset($revenue) ? 'md:col-span-2' : '' }}">
                        <label for="source" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Origine / Client / Source <span class="text-rose-500">*</span></label>
                        <input type="text" name="source" id="source" value="{{ old('source', $revenue->source ?? '') }}" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]" 
                            placeholder="Ex: Client XYZ, Subvention, Apport, Prestation diverse">
                        @error('source') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Description & Motif de l'encaissement <span class="text-rose-500">*</span></label>
                        <textarea name="description" id="description" rows="3" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Détails complémentaires sur la provenance des fonds...">{{ old('description', $revenue->description ?? '') }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('finance.revenues.index') }}" variant="secondary">
                    Annuler
                </x-button>
                <x-button type="submit" variant="primary">
                    {{ isset($revenue) ? 'Mettre à jour la recette' : 'Enregistrer la recette' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
