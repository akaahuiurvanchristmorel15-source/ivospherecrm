<x-layouts.app :title="$isEdit ? 'Modifier le Contrat' : 'Nouveau Contrat'">
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('contracts.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la liste des contrats</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier Contrat ' . $contract->reference : 'Enregistrer un Nouveau Contrat' }}
                </h1>
            </div>
        </div>

        <form action="{{ $isEdit ? route('contracts.update', $contract) : route('contracts.store') }}" method="POST" class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-5 text-xs">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="contract-ref-input" class="block font-bold text-[#0B0F14] mb-1">Référence Unique *</label>
                    <input id="contract-ref-input" type="text" name="reference" value="{{ old('reference', $contract->reference) }}" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono" required />
                </div>
                <div>
                    <label for="contract-type-select" class="block font-bold text-[#0B0F14] mb-1">Type de Contrat *</label>
                    <select id="contract-type-select" name="type" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required>
                        <option value="client" {{ old('type', $contract->type) === 'client' ? 'selected' : '' }}>Contrat Client / Prestation</option>
                        <option value="fournisseur" {{ old('type', $contract->type) === 'fournisseur' ? 'selected' : '' }}>Contrat Fournisseur</option>
                        <option value="employe" {{ old('type', $contract->type) === 'employe' ? 'selected' : '' }}>Contrat Salarié / RH (CDI, CDD)</option>
                        <option value="partenaire" {{ old('type', $contract->type) === 'partenaire' ? 'selected' : '' }}>Partenariat / Accord-cadre</option>
                        <option value="location" {{ old('type', $contract->type) === 'location' ? 'selected' : '' }}>Bail / Location Équipement</option>
                        <option value="assurance" {{ old('type', $contract->type) === 'assurance' ? 'selected' : '' }}>Police d'Assurance</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="contract-name-input" class="block font-bold text-[#0B0F14] mb-1">Intitulé du Contrat *</label>
                <input id="contract-name-input" type="text" name="name" value="{{ old('name', $contract->name) }}" placeholder="Ex: Maintenance applicative annuelle" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="contract-party-input" class="block font-bold text-[#0B0F14] mb-1">Nom du Tiers (Partie contractante) *</label>
                    <input id="contract-party-input" type="text" name="party_name" value="{{ old('party_name', $contract->party_name) }}" placeholder="Ex: Orange CI, SODECI..." class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                </div>
                <div>
                    <label for="contract-domain-select" class="block font-bold text-[#0B0F14] mb-1">Pôle Associé</label>
                    <select id="contract-domain-select" name="domain_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <option value="">Tous / Direction Générale</option>
                        @foreach($domains as $d)
                            <option value="{{ $d->id }}" {{ old('domain_id', $contract->domain_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="contract-start-input" class="block font-bold text-[#0B0F14] mb-1">Date d'effet (Début) *</label>
                    <input id="contract-start-input" type="date" name="start_date" value="{{ old('start_date', $contract->start_date ? $contract->start_date->format('Y-m-d') : date('Y-m-d')) }}" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                </div>
                <div>
                    <label for="contract-end-input" class="block font-bold text-[#0B0F14] mb-1">Date d'échéance (Fin) <span class="text-slate-400 font-normal">(Laisser vide si CDI)</span></label>
                    <input id="contract-end-input" type="date" name="end_date" value="{{ old('end_date', $contract->end_date ? $contract->end_date->format('Y-m-d') : '') }}" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="contract-amount-input" class="block font-bold text-[#0B0F14] mb-1">Montant Contractuel *</label>
                    <input id="contract-amount-input" type="number" step="0.01" name="amount" value="{{ old('amount', $contract->amount ?? 0) }}" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                </div>
                <div>
                    <label for="contract-currency-input" class="block font-bold text-[#0B0F14] mb-1">Devise</label>
                    <input id="contract-currency-input" type="text" name="currency" value="{{ old('currency', $contract->currency ?? 'FCFA') }}" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                </div>
                <div>
                    <label for="contract-status-select" class="block font-bold text-[#0B0F14] mb-1">Statut *</label>
                    <select id="contract-status-select" name="status" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required>
                        <option value="actif" {{ old('status', $contract->status) === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="brouillon" {{ old('status', $contract->status) === 'brouillon' ? 'selected' : '' }}>Brouillon / En négociation</option>
                        <option value="expire" {{ old('status', $contract->status) === 'expire' ? 'selected' : '' }}>Expiré</option>
                        <option value="resilie" {{ old('status', $contract->status) === 'resilie' ? 'selected' : '' }}>Résilié</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="contract-notes-textarea" class="block font-bold text-[#0B0F14] mb-1">Clauses spécifiques / Notes</label>
                <textarea id="contract-notes-textarea" name="notes" rows="3" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" placeholder="Clauses de résiliation, reconduction tacite...">{{ old('notes', $contract->notes) }}</textarea>
            </div>

            <div class="pt-4 border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                <a href="{{ route('contracts.index') }}" class="px-4 py-2 rounded-xl border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-bold shadow-md shadow-[#0066FF]/20">
                    {{ $isEdit ? 'Mettre à jour le contrat' : 'Enregistrer le contrat' }}
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
