<x-layouts.app :title="isset($job) ? 'Modifier Travail d\'Impression' : 'Nouveau Travail d\'Impression — PRINT'">
    @php
        $isEdit = isset($job);
        $defaultRef = 'PJ-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('print.jobs.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux travaux d'impression</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier le Travail ' . $job->reference : 'Nouveau Travail d\'Impression (PRINT)' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Configurer les paramètres de fabrication, tirage, supports et délais de production.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('print.jobs.update', $job) : route('print.jobs.store') }}" 
            method="POST" 
            x-data="{
                quantity: {{ old('quantity', $job->quantity ?? 100) }},
                unit_price: {{ old('unit_price', $job->unit_price ?? 50) }},
                get total() {
                    return (parseFloat(this.quantity) || 0) * (parseFloat(this.unit_price) || 0);
                }
            }"
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Informations Générales -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Identification & Client</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="reference-input" class="block font-semibold text-[#0B0F14] mb-1">Référence Unique *</label>
                        <input 
                            id="reference-input" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $job->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer-select" class="block font-semibold text-[#0B0F14] mb-1">Client Associé *</label>
                        <select 
                            id="customer-select" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            <option value="">Sélectionnez un client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $job->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company ? '(' . $c->company . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="type-select" class="block font-semibold text-[#0B0F14] mb-1">Type de Tirage / Machine *</label>
                        <select 
                            id="type-select" 
                            name="type" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            <option value="numerique" {{ old('type', $job->type ?? '') === 'numerique' ? 'selected' : '' }}>Numérique (Court tirage)</option>
                            <option value="offset" {{ old('type', $job->type ?? '') === 'offset' ? 'selected' : '' }}>Offset (Grand tirage)</option>
                            <option value="grand_format" {{ old('type', $job->type ?? '') === 'grand_format' ? 'selected' : '' }}>Grand Format (Bâche, Vinyle)</option>
                            <option value="serigraphie" {{ old('type', $job->type ?? '') === 'serigraphie' ? 'selected' : '' }}>Sérigraphie</option>
                            <option value="packaging" {{ old('type', $job->type ?? '') === 'packaging' ? 'selected' : '' }}>Packaging / Cartonnage</option>
                            <option value="goodies" {{ old('type', $job->type ?? '') === 'goodies' ? 'selected' : '' }}>Goodies & Sublimation</option>
                        </select>
                        @error('type') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Spécifications Techniques -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">2. Caractéristiques Techniques</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="format-select" class="block font-semibold text-[#0B0F14] mb-1">Format de Tirage *</label>
                        <select 
                            id="format-select" 
                            name="format_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            <option value="">Sélectionnez un format</option>
                            @foreach($formats as $f)
                                <option value="{{ $f->id }}" {{ old('format_id', $job->format_id ?? '') == $f->id ? 'selected' : '' }}>
                                    {{ $f->name }} ({{ $f->width_mm }}x{{ $f->height_mm }} mm)
                                </option>
                            @endforeach
                        </select>
                        @error('format_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="support-select" class="block font-semibold text-[#0B0F14] mb-1">Support / Matière</label>
                        <select 
                            id="support-select" 
                            name="support_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Standard / Aucun support spécifique</option>
                            @foreach($supports as $s)
                                <option value="{{ $s->id }}" {{ old('support_id', $job->support_id ?? '') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->grammage ?? '-' }}g)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="finishing-select" class="block font-semibold text-[#0B0F14] mb-1">Façonnage & Finition</label>
                        <select 
                            id="finishing-select" 
                            name="finishing_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Sans finition spéciale</option>
                            @foreach($finishings as $fin)
                                <option value="{{ $fin->id }}" {{ old('finishing_id', $job->finishing_id ?? '') == $fin->id ? 'selected' : '' }}>
                                    {{ $fin->name }} ({{ $fin->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3 : Quantité, Tarification & Statut -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">3. Quantité, Délais & Montant</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="quantity-input" class="block font-semibold text-[#0B0F14] mb-1">Quantité d'exemplaires *</label>
                        <input 
                            id="quantity-input" 
                            type="number" 
                            min="1" 
                            name="quantity" 
                            x-model.number="quantity" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('quantity') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="unit-price-input" class="block font-semibold text-[#0B0F14] mb-1">Prix Unitaire (FCFA) *</label>
                        <input 
                            id="unit-price-input" 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="unit_price" 
                            x-model.number="unit_price" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('unit_price') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="status-select" class="block font-semibold text-[#0B0F14] mb-1">Statut Opérationnel *</label>
                        <select 
                            id="status-select" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            <option value="en_attente" {{ old('status', $job->status ?? '') === 'en_attente' ? 'selected' : '' }}>En attente de BAT</option>
                            <option value="bon_a_tirer" {{ old('status', $job->status ?? '') === 'bon_a_tirer' ? 'selected' : '' }}>BAT Validé</option>
                            <option value="en_production" {{ old('status', $job->status ?? 'en_production') === 'en_production' ? 'selected' : '' }}>En Production Machine</option>
                            <option value="terminé" {{ old('status', $job->status ?? '') === 'terminé' ? 'selected' : '' }}>Terminé / Prêt</option>
                            <option value="livré" {{ old('status', $job->status ?? '') === 'livré' ? 'selected' : '' }}>Livré au client</option>
                            <option value="annulé" {{ old('status', $job->status ?? '') === 'annulé' ? 'selected' : '' }}>Annulé</option>
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="deadline-input" class="block font-semibold text-[#0B0F14] mb-1">Date d'Échéance</label>
                        <input 
                            id="deadline-input" 
                            type="date" 
                            name="deadline" 
                            value="{{ old('deadline', isset($job->deadline) ? $job->deadline->format('Y-m-d') : '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                    </div>
                </div>

                <!-- Récapitulatif Total en direct -->
                <div class="mt-4 p-4 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-[#64748B] uppercase font-bold block">Montant Total Calculé</span>
                        <span class="text-xs text-[#64748B]">Quantité × Prix unitaire</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-extrabold text-[#0066FF]" x-text="new Intl.NumberFormat('fr-FR').format(total) + ' FCFA'">
                            0 FCFA
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 4 : Instructions & Remarques -->
            <div class="pt-4 border-t border-[#E2E8F0]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">4. Cahier des Charges & Remarques</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="specifications-textarea" class="block font-semibold text-[#0B0F14] mb-1">Spécifications Techniques</label>
                        <textarea 
                            id="specifications-textarea" 
                            name="specifications" 
                            rows="3" 
                            placeholder="Ex: Épaisseur tranche 8mm, rainage double, vernis sélectif sur le logo recto..." 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('specifications', $job->specifications ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="notes-textarea" class="block font-semibold text-[#0B0F14] mb-1">Notes de Fabrication / Livraison</label>
                        <textarea 
                            id="notes-textarea" 
                            name="notes" 
                            rows="3" 
                            placeholder="Ex: Emballage par paquets de 50, livraison express par coursier..." 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >{{ old('notes', $job->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions de validation -->
            <div class="pt-4 border-t border-[#E2E8F0] flex items-center justify-between">
                <a href="{{ route('print.jobs.index') }}" class="px-4 py-2 rounded-xl text-slate-600 hover:text-[#0B0F14] font-medium transition-colors">
                    Annuler
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#0B0F14] text-white hover:bg-[#0066FF] font-semibold transition-colors shadow-xs"
                >
                    {{ $isEdit ? 'Mettre à Jour le Travail' : 'Enregistrer le Travail d\'Impression' }}
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
