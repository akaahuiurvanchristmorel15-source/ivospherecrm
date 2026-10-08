<x-layouts.app title="Créer un Ticket de Support">
    <div class="max-w-2xl mx-auto space-y-6">

        <div class="pb-4 border-b border-[#E2E8F0]">
            <a href="{{ route('support.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Retour aux tickets</span>
            </a>
            <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">Ouvrir un Nouveau Ticket de Support</h1>
            <p class="text-xs text-slate-500">Formulez la demande pour prise en charge et assignation prioritaire.</p>
        </div>

        <form action="{{ route('support.store') }}" method="POST" class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
            @csrf

            <div>
                <label for="ticket-subject-input" class="block font-bold text-[#0B0F14] mb-1">Sujet du Ticket *</label>
                <input id="ticket-subject-input" type="text" name="subject" placeholder="Ex: Erreur d'impression sur le lot 5000 flyers" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="ticket-cat-select" class="block font-bold text-[#0B0F14] mb-1">Catégorie *</label>
                    <select id="ticket-cat-select" name="category" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required>
                        <option value="technique">Technique / Panne / Bug</option>
                        <option value="commercial">Commercial / Devis / Tarif</option>
                        <option value="facturation">Facturation / Règlement</option>
                        <option value="livraison">Livraison / Expédition</option>
                        <option value="produit">Qualité Produit / Conformité</option>
                        <option value="maintenance">Maintenance / Entretien</option>
                    </select>
                </div>
                <div>
                    <label for="ticket-pri-select" class="block font-bold text-[#0B0F14] mb-1">Niveau d'Urgence (Priorité) *</label>
                    <select id="ticket-pri-select" name="priority" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required>
                        <option value="normale">Normale</option>
                        <option value="basse">Basse</option>
                        <option value="haute">Haute</option>
                        <option value="urgente">Urgente (SLA prioritaire)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="ticket-cust-select" class="block font-bold text-[#0B0F14] mb-1">Client Concerné</label>
                    <select id="ticket-cust-select" name="customer_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <option value="">Sélectionner un client (facultatif)</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} {{ $c->company ? '('.$c->company.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ticket-dom-select" class="block font-bold text-[#0B0F14] mb-1">Pôle Concerné</label>
                    <select id="ticket-dom-select" name="domain_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                        <option value="">Général</option>
                        @foreach($domains as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="ticket-desc-textarea" class="block font-bold text-[#0B0F14] mb-1">Description Détaillée de la Demande *</label>
                <textarea id="ticket-desc-textarea" name="description" rows="4" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" placeholder="Détaillez le problème ou la demande avec précision..." required></textarea>
            </div>

            <div class="pt-4 border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                <a href="{{ route('support.index') }}" class="px-4 py-2 rounded-xl border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-bold shadow-md shadow-[#0066FF]/20">
                    Ouvrir le Ticket
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
