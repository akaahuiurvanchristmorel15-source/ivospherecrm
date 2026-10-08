<x-layouts.app>
    <x-slot:title>{{ isset($customer) ? 'Modifier le Client' : 'Nouveau Client' }} — IVOSPHERE ERP</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header 
            :title="isset($customer) ? 'Modifier le Client : ' . $customer->name : 'Nouveau Client CRM'"
            :description="isset($customer) ? 'Mise à jour des coordonnées et paramètres du compte client' : 'Création d\'un nouveau dossier client 360° dans la base commerciale'"
            :breadcrumbs="[['label' => 'Clients', 'url' => route('commercial.customers.index')], ['label' => isset($customer) ? 'Modifier' : 'Créer']]"
        >
            <x-slot:actions>
                <x-button :href="route('commercial.customers.index')" variant="secondary" size="md" class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux clients</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <form action="{{ isset($customer) ? route('commercial.customers.update', $customer) : route('commercial.customers.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($customer)) @method('PUT') @endif

            <!-- Section 1 : Identification & Coordonnées Légales -->
            <x-card title="1. Identification & Coordonnées" subtitle="Dénomination légale, contacts opérationnels et localisation">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 text-sm">
                    
                    <!-- Code Client -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Code Client *</label>
                        <input 
                            type="text" 
                            name="code" 
                            value="{{ old('code', $customer->code ?? 'CLI-' . strtoupper(Str::random(6))) }}" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] font-mono focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Type de client -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Type de Client *</label>
                        <select 
                            name="type" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors cursor-pointer"
                        >
                            <option value="particulier" {{ old('type', $customer->type ?? '') == 'particulier' ? 'selected' : '' }}>Particulier</option>
                            <option value="entreprise" {{ old('type', $customer->type ?? '') == 'entreprise' ? 'selected' : '' }}>Entreprise / B2B</option>
                        </select>
                    </div>

                    <!-- NIF / Numéro Fiscal -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">NIF / Numéro Fiscal</label>
                        <input 
                            type="text" 
                            name="nif" 
                            value="{{ old('nif', $customer->nif ?? '') }}" 
                            placeholder="Ex: 1234567-X" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Nom & Prénom / Raison Sociale -->
                    <div class="md:col-span-12">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nom & Prénom / Raison Sociale *</label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $customer->name ?? '') }}" 
                            required 
                            placeholder="Ex: Jean Dupont ou SARL Ivoire Import" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Nom de l'entreprise -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Entreprise Affiliée</label>
                        <input 
                            type="text" 
                            name="company" 
                            value="{{ old('company', $customer->company ?? '') }}" 
                            placeholder="Ex: Groupe SIFCA" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Contact / Interlocuteur -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Interlocuteur Clé</label>
                        <input 
                            type="text" 
                            name="contact_person" 
                            value="{{ old('contact_person', $customer->contact_person ?? '') }}" 
                            placeholder="Ex: M. Bamba (DAF)" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Téléphone Principal -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Téléphone Principal *</label>
                        <input 
                            type="text" 
                            name="phone" 
                            value="{{ old('phone', $customer->phone ?? '') }}" 
                            placeholder="+225 07 00 00 00 00" 
                            required
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- WhatsApp Direct -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Numéro WhatsApp Direct</label>
                        <input 
                            type="text" 
                            name="whatsapp" 
                            value="{{ old('whatsapp', $customer->whatsapp ?? $customer->phone ?? '') }}" 
                            placeholder="+2250700000000" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Adresse Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email', $customer->email ?? '') }}" 
                            placeholder="client@domaine.com" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>

                    <!-- Adresse -->
                    <div class="md:col-span-12">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Adresse Géographique</label>
                        <input 
                            type="text" 
                            name="address" 
                            value="{{ old('address', $customer->address ?? '') }}" 
                            placeholder="Ex: Cocody Angré 8e Tranche, Rue L12" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >
                    </div>
                </div>
            </x-card>

            <!-- Section 2 : Paramètres Commerciaux & Fidélité -->
            <x-card title="2. Paramètres Commerciaux & Fidélité" subtitle="Classification de compte, domaine d'attribution et programme de fidélité">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 text-sm">
                    
                    <!-- Catégorie Client -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Catégorie Client</label>
                        <select 
                            name="category" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors cursor-pointer"
                        >
                            <option value="standard" {{ old('category', $customer->category ?? 'standard') == 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="vip" {{ old('category', $customer->category ?? '') == 'vip' ? 'selected' : '' }}>VIP / Grand Compte</option>
                            <option value="revendeur" {{ old('category', $customer->category ?? '') == 'revendeur' ? 'selected' : '' }}>Revendeur / Distributeur</option>
                            <option value="institutionnel" {{ old('category', $customer->category ?? '') == 'institutionnel' ? 'selected' : '' }}>Institutionnel / ONG</option>
                        </select>
                    </div>

                    <!-- Domaine Rattachement -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine de Rattachement</label>
                        <select 
                            name="domain_id" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors cursor-pointer"
                        >
                            <option value="">Tous les domaines (Transversal)</option>
                            @foreach($domains ?? [] as $dom)
                                <option value="{{ $dom->id }}" {{ old('domain_id', $customer->domain_id ?? '') == $dom->id ? 'selected' : '' }}>
                                    {{ $dom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Palier de Fidélité -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Niveau de Fidélité (§38)</label>
                        <select 
                            name="loyalty_level" 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors cursor-pointer"
                        >
                            <option value="BRONZE" {{ old('loyalty_level', $customer->loyalty_level ?? 'BRONZE') == 'BRONZE' ? 'selected' : '' }}>BRONZE (Standard)</option>
                            <option value="SILVER" {{ old('loyalty_level', $customer->loyalty_level ?? '') == 'SILVER' ? 'selected' : '' }}>SILVER (Privilège)</option>
                            <option value="GOLD" {{ old('loyalty_level', $customer->loyalty_level ?? '') == 'GOLD' ? 'selected' : '' }}>GOLD (Exclusif)</option>
                            <option value="PREMIUM" {{ old('loyalty_level', $customer->loyalty_level ?? '') == 'PREMIUM' ? 'selected' : '' }}>PREMIUM (VIP Ambassadeur)</option>
                        </select>
                    </div>

                    <!-- Statut -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut Client *</label>
                        <select 
                            name="status" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors cursor-pointer"
                        >
                            <option value="actif" {{ old('status', $customer->status ?? 'actif') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('status', $customer->status ?? '') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                    <!-- Notes internes -->
                    <div class="md:col-span-12">
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Notes Internes & Historique</label>
                        <textarea 
                            name="notes" 
                            rows="3" 
                            placeholder="Remarques commerciales, habitudes de paiement, conditions négociées..." 
                            class="w-full px-3.5 py-2.5 bg-white border border-[#E2E8F0] rounded-lg text-sm text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF] transition-colors"
                        >{{ old('notes', $customer->notes ?? '') }}</textarea>
                    </div>
                </div>
            </x-card>

            <!-- Actions du Formulaire : 1 Action principale prioritaire, 1 Action secondaire discrète -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <x-button :href="route('commercial.customers.index')" variant="secondary" size="md">
                    Annuler
                </x-button>

                <x-button type="submit" variant="primary" size="md">
                    {{ isset($customer) ? 'Mettre à jour le client' : 'Enregistrer le client' }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
