<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Nouveau Mouvement de Stock" 
            subtitle="Enregistrez une entrée, une sortie, un transfert inter-sites ou un ajustement">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'Stocks', 'url' => route('stock.index')],
                    ['label' => 'Mouvements', 'url' => route('stock.movements.index')],
                    ['label' => 'Nouveau']
                ]" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="{ type: '{{ old('type', 'entree') }}' }">
        <form method="POST" action="{{ route('stock.movements.store') }}" class="space-y-6">
            @csrf

            <!-- Type de mouvement -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <label class="block text-xs font-semibold uppercase tracking-wider text-[#0B0F14] mb-3">
                    Type d'opération <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @php
                        $types = [
                            'entree' => ['label' => 'Entrée (+)', 'desc' => 'Réception fournisseur / Achat'],
                            'sortie' => ['label' => 'Sortie (-)', 'desc' => 'Vente / Expédition client'],
                            'transfert' => ['label' => 'Transfert', 'desc' => 'Déplacement entrepôts'],
                            'retour' => ['label' => 'Retour (+)', 'desc' => 'Retour client / SAV'],
                            'ajustement' => ['label' => 'Ajustement', 'desc' => 'Inventaire / Correction'],
                        ];
                    @endphp
                    @foreach($types as $key => $info)
                        <label class="relative flex flex-col p-3 rounded-lg border cursor-pointer transition-all text-center select-none"
                            :class="type === '{{ $key }}' ? 'border-[#0066FF] bg-[#0066FF]/5 ring-1 ring-[#0066FF]' : 'border-[#E2E8F0] bg-white hover:bg-[#F5F7FA]'">
                            <input type="radio" name="type" value="{{ $key }}" x-model="type" class="sr-only">
                            <span class="text-sm font-semibold text-[#0B0F14]">{{ $info['label'] }}</span>
                            <span class="text-[11px] text-[#64748B] mt-0.5 leading-tight">{{ $info['desc'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('type') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <!-- Détails de l'opération -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-6 shadow-xs">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#0B0F14] border-b border-[#E2E8F0] pb-3 mb-5">
                    Détails du flux
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Date d'enregistrement <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="date" id="date" required value="{{ old('date', date('Y-m-d')) }}" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                        @error('date') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="reference" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Numéro de référence (Bon de livraison, transfert...)
                        </label>
                        <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="Ex: BL-2026-089" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                        @error('reference') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="warehouse_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Entrepôt <span x-show="type === 'transfert'">Source </span><span class="text-rose-500">*</span>
                        </label>
                        <select id="warehouse_id" name="warehouse_id" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">Sélectionnez l'entrepôt...</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('warehouse_id', request('warehouse_id')) == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="type === 'transfert'" x-cloak>
                        <label for="destination_warehouse_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Entrepôt Destination <span class="text-rose-500">*</span>
                        </label>
                        <select id="destination_warehouse_id" name="destination_warehouse_id" :required="type === 'transfert'" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">Sélectionnez l'entrepôt récepteur...</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('destination_warehouse_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('destination_warehouse_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="product_id" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Article / Produit <span class="text-rose-500">*</span>
                        </label>
                        <select id="product_id" name="product_id" required 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <option value="">Sélectionnez un article du catalogue...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} — SKU: {{ $p->sku }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="quantity" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Quantité <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" required min="1" value="{{ old('quantity', 1) }}" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                        @error('quantity') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="reason_motif" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Motif réglementaire (WMS)
                        </label>
                        <select id="reason_motif" name="reason_motif"
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                            <optgroup label="Motifs d'Entrée (+)">
                                <option value="achat_fournisseur">Achat fournisseur</option>
                                <option value="retour_client">Retour client</option>
                                <option value="production_interne">Production interne</option>
                                <option value="don_echantillon">Don / Échantillon</option>
                                <option value="transfert_entrant">Transfert entrant</option>
                            </optgroup>
                            <optgroup label="Motifs de Sortie (-)">
                                <option value="vente_client">Vente client</option>
                                <option value="livraison_commande">Livraison commande</option>
                                <option value="utilisation_interne">Utilisation interne</option>
                                <option value="production_atelier">Production / Atelier</option>
                                <option value="casse_deterioration">Casse / Détérioration</option>
                                <option value="perte">Perte constatée</option>
                                <option value="retour_fournisseur">Retour fournisseur</option>
                                <option value="don_sponsoring">Don / Sponsoring</option>
                            </optgroup>
                            <optgroup label="Inventaire">
                                <option value="ajustement_inventaire">Ajustement d'inventaire</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="batch_number" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Numéro de Lot / Série / IMEI (Optionnel)
                        </label>
                        <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}" placeholder="Ex: LOT-2026-ENC-01 ou SN-SONY-884920"
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]">
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Observations complémentaires
                        </label>
                        <textarea id="notes" name="notes" rows="3" 
                            class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 py-2 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF]"
                            placeholder="Renseignez tout détail justificatif utile...">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button href="{{ route('stock.movements.index') }}" variant="secondary">
                    Annuler
                </x-button>
                <x-button type="submit" variant="primary">
                    Enregistrer le mouvement
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
