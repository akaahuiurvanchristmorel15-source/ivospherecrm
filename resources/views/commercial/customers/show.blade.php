<x-layouts.app>
    <x-slot:title>Fiche 360° : {{ $customer->name }} — IVOSPHERE ERP</x-slot>

    <!-- En-tête standardisé avec fil d'Ariane et actions hiérarchisées -->
    <x-page-header 
        :title="$customer->name" 
        :description="($customer->company ? $customer->company . ' • ' : '') . 'Client ' . ucfirst($customer->type) . ' • Créé le ' . $customer->created_at->format('d/m/Y')"
        :breadcrumbs="[['label' => 'Gestion Commerciale'], ['label' => 'Clients', 'url' => route('commercial.customers.index')], ['label' => $customer->name]]"
    >
        <x-slot:actions>
            @php
                $cleanWhatsapp = preg_replace('/[^0-9]/', '', $customer->whatsapp ?? $customer->phone);
            @endphp

            @if($cleanWhatsapp)
                <a 
                    href="https://wa.me/{{ $cleanWhatsapp }}?text={{ urlencode('Bonjour ' . $customer->name . ', nous vous contactons depuis IVOSPHERE.') }}" 
                    target="_blank" 
                    class="px-3.5 py-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center gap-1.5 transition-colors"
                >
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <span>WhatsApp</span>
                </a>
            @endif

            <x-button :href="route('commercial.customers.edit', $customer)" variant="secondary" size="md">
                Modifier
            </x-button>

            <x-button :href="route('commercial.quotations.create', ['customer_id' => $customer->id])" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Devis</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Grille Cohérente (12 colonnes) : 4 Métriques Clés Financières & Fidélité -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6">
        
        <!-- Total Facturé (CA) -->
        <div class="lg:col-span-3">
            <x-stat-card 
                title="Total Facturé (CA)"
                :value="number_format($customer->total_spent, 0, ',', ' ') . ' FCFA'"
                :change="$customer->invoices->count() . ' facture(s) émise(s)'"
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Total Encaissé -->
        <div class="lg:col-span-3">
            <x-stat-card 
                title="Total Encaissé"
                :value="number_format($customer->invoices->sum('paid_amount'), 0, ',', ' ') . ' FCFA'"
                :change="$customer->payments->count() . ' règlement(s) perçu(s)'"
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Encours / Reste à Payer -->
        @php
            $unpaidTotal = $customer->invoices->whereIn('status', ['non_payee', 'partielle'])->sum(function ($inv) {
                return max(0, $inv->total - $inv->paid_amount);
            });
        @endphp
        <div class="lg:col-span-3">
            <x-stat-card 
                title="Encours Impayé"
                :value="number_format($unpaidTotal, 0, ',', ' ') . ' FCFA'"
                :change="$customer->invoices->whereIn('status', ['non_payee', 'partielle'])->count() . ' facture(s) en attente'"
                :changeType="$unpaidTotal > 0 ? 'down' : 'neutral'"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Programme Fidélité (§38) -->
        <div class="lg:col-span-3">
            <x-stat-card 
                title="Fidélité (§38)"
                :value="number_format($customer->loyalty_points ?? 0, 0, ',', ' ') . ' pts'"
                :change="'Palier ' . ($customer->loyalty_level ?? 'BRONZE')"
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

    </div>

    <!-- Grille Principale (12 colonnes) : Fiche d'Identité (4 cols) + Espace Opérations 360° (8 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Colonne Gauche : Identité Client (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <x-card title="Fiche d'Identité" subtitle="Données d'enregistrement et contacts">
                <dl class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                        <dt class="text-[#64748B]">Code Client</dt>
                        <dd class="font-mono font-bold text-[#0B0F14]">{{ $customer->code }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                        <dt class="text-[#64748B]">Type de Compte</dt>
                        <dd class="font-medium text-[#0B0F14] capitalize">{{ $customer->type }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                        <dt class="text-[#64748B]">Catégorie</dt>
                        <dd class="font-semibold text-[#0B0F14] uppercase">{{ $customer->category ?? 'Standard' }}</dd>
                    </div>
                    @if($customer->nif)
                        <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                            <dt class="text-[#64748B]">NIF / N° Fiscal</dt>
                            <dd class="font-mono font-bold text-[#0B0F14]">{{ $customer->nif }}</dd>
                        </div>
                    @endif
                    @if($customer->company)
                        <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                            <dt class="text-[#64748B]">Entreprise</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $customer->company }}</dd>
                        </div>
                    @endif
                    @if($customer->contact_person)
                        <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                            <dt class="text-[#64748B]">Interlocuteur Clé</dt>
                            <dd class="font-medium text-[#0066FF]">{{ $customer->contact_person }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                        <dt class="text-[#64748B]">Téléphone</dt>
                        <dd class="font-medium text-[#0B0F14]">{{ $customer->phone ?: 'Non renseigné' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                        <dt class="text-[#64748B]">Email</dt>
                        <dd class="font-medium text-[#0B0F14] truncate max-w-[180px]">{{ $customer->email ?: 'Non renseigné' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-[#E2E8F0]">
                        <dt class="text-[#64748B]">Domaine</dt>
                        <dd class="font-medium text-[#0B0F14]">{{ $customer->domain->name ?? 'Tous les domaines' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-[#64748B]">Statut</dt>
                        <dd>
                            <x-badge :variant="$customer->status === 'actif' ? 'success' : 'neutral'" size="sm" :dot="true">
                                {{ ucfirst($customer->status) }}
                            </x-badge>
                        </dd>
                    </div>
                </dl>

                @if($customer->address)
                    <div class="mt-4 pt-3 border-t border-[#E2E8F0] text-xs">
                        <span class="text-[#64748B] block mb-1 font-medium">Adresse Géographique :</span>
                        <p class="text-[#0B0F14] bg-[#F5F7FA] p-2.5 rounded-lg border border-[#E2E8F0]">
                            {{ $customer->address }}
                        </p>
                    </div>
                @endif

                @if($customer->notes)
                    <div class="mt-4 pt-3 border-t border-[#E2E8F0] text-xs">
                        <span class="text-[#64748B] block mb-1 font-medium">Remarques & Notes Internes :</span>
                        <p class="text-[#0B0F14] whitespace-pre-line bg-[#F5F7FA] p-2.5 rounded-lg border border-[#E2E8F0]">
                            {{ $customer->notes }}
                        </p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Colonne Droite : Espace Opérations 360° par Onglets Alpine.js (8 cols) -->
        <div class="lg:col-span-8">
            <x-card x-data="{ tab: 'factures' }">
                <x-slot:title>
                    <!-- Onglets Horizontaux Épurés -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-2 px-2 sm:mx-0 sm:px-0 sm:flex-wrap">
                        <button 
                            type="button" 
                            @click="tab = 'factures'" 
                            :class="tab === 'factures' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-[#F5F7FA] border border-[#E2E8F0]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
                        >
                            <span>Factures</span>
                            <span :class="tab === 'factures' ? 'bg-white/20 text-white' : 'bg-white text-slate-700'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $customer->invoices->count() }}
                            </span>
                        </button>

                        <button 
                            type="button" 
                            @click="tab = 'devis'" 
                            :class="tab === 'devis' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-[#F5F7FA] border border-[#E2E8F0]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
                        >
                            <span>Devis</span>
                            <span :class="tab === 'devis' ? 'bg-white/20 text-white' : 'bg-white text-slate-700'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $customer->quotations->count() }}
                            </span>
                        </button>

                        <button 
                            type="button" 
                            @click="tab = 'commandes'" 
                            :class="tab === 'commandes' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-[#F5F7FA] border border-[#E2E8F0]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
                        >
                            <span>Commandes</span>
                            <span :class="tab === 'commandes' ? 'bg-white/20 text-white' : 'bg-white text-slate-700'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $customer->orders->count() }}
                            </span>
                        </button>

                        <button 
                            type="button" 
                            @click="tab = 'reglements'" 
                            :class="tab === 'reglements' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-[#F5F7FA] border border-[#E2E8F0]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
                        >
                            <span>Règlements</span>
                            <span :class="tab === 'reglements' ? 'bg-white/20 text-white' : 'bg-white text-slate-700'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $customer->payments->count() }}
                            </span>
                        </button>

                        <button 
                            type="button" 
                            @click="tab = 'rdv'" 
                            :class="tab === 'rdv' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-[#F5F7FA] border border-[#E2E8F0]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
                        >
                            <span>Rendez-vous</span>
                            <span :class="tab === 'rdv' ? 'bg-white/20 text-white' : 'bg-white text-slate-700'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                                {{ $customer->appointments->count() }}
                            </span>
                        </button>
                    </div>
                </x-slot:title>

                <!-- 1. Onglet Factures -->
                <div x-show="tab === 'factures'" class="space-y-3">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-[#64748B]">Historique des factures de vente</span>
                        <a href="{{ route('commercial.invoices.create') }}?customer_id={{ $customer->id }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                            + Nouvelle Facture
                        </a>
                    </div>
                    @forelse($customer->invoices as $f)
                        <div class="p-3.5 bg-white border border-[#E2E8F0] rounded-xl hover:border-slate-300 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('commercial.invoices.show', $f) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] font-mono transition-colors">
                                        {{ $f->reference }}
                                    </a>
                                    <x-badge :variant="$f->status === 'payee' ? 'success' : ($f->status === 'partielle' ? 'warning' : 'danger')" size="sm">
                                        {{ str_replace('_', ' ', ucfirst($f->status)) }}
                                    </x-badge>
                                </div>
                                <p class="text-[#64748B] mt-1">
                                    Émise le {{ $f->date->format('d/m/Y') }} 
                                    @if($f->due_date) &bull; Échéance : {{ $f->due_date->format('d/m/Y') }} @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="font-bold text-[#0B0F14] text-sm">{{ number_format($f->total, 0, ',', ' ') }} FCFA</div>
                                    @if($f->remaining > 0)
                                        <div class="text-[11px] text-rose-600 font-medium">Reste : {{ number_format($f->remaining, 0, ',', ' ') }} FCFA</div>
                                    @else
                                        <div class="text-[11px] text-emerald-600 font-medium">Soldée</div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('commercial.invoices.print', $f) }}" target="_blank" class="p-1.5 bg-[#F5F7FA] border border-[#E2E8F0] hover:bg-slate-200/80 text-[#0B0F14] rounded-lg transition-colors" title="Imprimer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <x-button :href="route('commercial.invoices.show', $f)" variant="secondary" size="sm">
                                        Détails
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#64748B] text-xs">
                            Aucune facture enregistrée pour ce client.
                        </div>
                    @endforelse
                </div>

                <!-- 2. Onglet Devis -->
                <div x-show="tab === 'devis'" style="display: none;" class="space-y-3">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-[#64748B]">Propositions et devis</span>
                        <a href="{{ route('commercial.quotations.create') }}?customer_id={{ $customer->id }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                            + Nouveau Devis
                        </a>
                    </div>
                    @forelse($customer->quotations as $q)
                        <div class="p-3.5 bg-white border border-[#E2E8F0] rounded-xl hover:border-slate-300 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('commercial.quotations.show', $q) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] font-mono transition-colors">
                                        {{ $q->reference }}
                                    </a>
                                    <x-badge variant="neutral" size="sm">
                                        {{ ucfirst($q->status) }}
                                    </x-badge>
                                </div>
                                <p class="text-[#64748B] mt-1">Date : {{ $q->date->format('d/m/Y') }} &bull; Validité : {{ $q->valid_until ? $q->valid_until->format('d/m/Y') : '30 jours' }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($q->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('commercial.quotations.print', $q) }}" target="_blank" class="p-1.5 bg-[#F5F7FA] border border-[#E2E8F0] hover:bg-slate-200/80 text-[#0B0F14] rounded-lg transition-colors" title="Imprimer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <x-button :href="route('commercial.quotations.show', $q)" variant="secondary" size="sm">
                                        Détails
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#64748B] text-xs">
                            Aucun devis créé pour ce client.
                        </div>
                    @endforelse
                </div>

                <!-- 3. Onglet Commandes -->
                <div x-show="tab === 'commandes'" style="display: none;" class="space-y-3">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-[#64748B]">Commandes passées</span>
                        <a href="{{ route('commercial.orders.create') }}?customer_id={{ $customer->id }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                            + Nouvelle Commande
                        </a>
                    </div>
                    @forelse($customer->orders as $o)
                        <div class="p-3.5 bg-white border border-[#E2E8F0] rounded-xl hover:border-slate-300 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('commercial.orders.show', $o) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] font-mono transition-colors">
                                        {{ $o->reference }}
                                    </a>
                                    <x-badge variant="neutral" size="sm">
                                        {{ str_replace('_', ' ', ucfirst($o->status)) }}
                                    </x-badge>
                                </div>
                                <p class="text-[#64748B] mt-1">Date : {{ $o->date->format('d/m/Y') }} &bull; {{ $o->items->count() }} article(s)</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($o->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <x-button :href="route('commercial.orders.show', $o)" variant="secondary" size="sm">
                                    Détails
                                </x-button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#64748B] text-xs">
                            Aucune commande pour le moment.
                        </div>
                    @endforelse
                </div>

                <!-- 4. Onglet Règlements -->
                <div x-show="tab === 'reglements'" style="display: none;" class="space-y-3">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-[#64748B]">Règlements reçus et validés</span>
                    </div>
                    @forelse($customer->payments as $p)
                        <div class="p-3.5 bg-white border border-[#E2E8F0] rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="font-mono font-bold text-[#0B0F14]">{{ $p->reference }}</span>
                                    <p class="text-[#64748B] text-[11px] mt-0.5">
                                        {{ $p->date->format('d/m/Y') }} &bull; 
                                        Mode: <span class="capitalize text-[#0B0F14] font-semibold">{{ str_replace('_', ' ', $p->method) }}</span>
                                        @if($p->invoice) &bull; Facture: <a href="{{ route('commercial.invoices.show', $p->invoice) }}" class="text-[#0066FF] hover:underline font-medium">{{ $p->invoice->reference }}</a> @endif
                                    </p>
                                </div>
                            </div>
                            <div class="sm:text-right">
                                <span class="font-bold text-emerald-600 text-sm">+{{ number_format($p->amount, 0, ',', ' ') }} FCFA</span>
                                <span class="block text-[10px] text-[#64748B]">{{ ucfirst($p->status) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#64748B] text-xs">
                            Aucun paiement perçu pour le moment.
                        </div>
                    @endforelse
                </div>

                <!-- 5. Onglet Rendez-vous -->
                <div x-show="tab === 'rdv'" style="display: none;" class="space-y-3">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-[#64748B]">Agenda des échanges & rendez-vous</span>
                        <a href="{{ route('commercial.appointments.index') }}?customer_id={{ $customer->id }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                            + Planifier un RDV
                        </a>
                    </div>
                    @forelse($customer->appointments as $apt)
                        <div class="p-4 bg-white border border-[#E2E8F0] rounded-xl text-xs space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-[#0B0F14] text-sm">{{ $apt->title }}</h4>
                                    <p class="text-[#64748B] text-[11px] mt-0.5">
                                        Type : <span class="text-[#0B0F14] uppercase font-semibold">{{ $apt->type_label }}</span> &bull; 
                                        Date : <span class="text-[#0B0F14] font-semibold">{{ $apt->start_time->format('d/m/Y à H:i') }}</span>
                                        @if($apt->location) &bull; Lieu : {{ $apt->location }} @endif
                                    </p>
                                </div>
                                <x-badge :variant="$apt->status === 'realise' ? 'success' : ($apt->status === 'annule' ? 'danger' : 'neutral')" size="sm">
                                    {{ $apt->status_label }}
                                </x-badge>
                            </div>
                            @if($apt->description)
                                <p class="text-[#64748B] bg-[#F5F7FA] p-2.5 rounded-lg border border-[#E2E8F0] text-[11px]">{{ $apt->description }}</p>
                            @endif
                            @if($apt->outcome_notes)
                                <p class="text-emerald-700 bg-emerald-50 p-2.5 rounded-lg border border-emerald-200 text-[11px]">
                                    <strong>Compte-rendu :</strong> {{ $apt->outcome_notes }}
                                </p>
                            @endif
                            <div class="text-[10px] text-[#64748B] flex justify-between items-center pt-1">
                                <span>Commercial: {{ $apt->commercial?->name ?? 'Non assigné' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#64748B] text-xs">
                            Aucun rendez-vous commercial planifié avec ce client.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>

    </div>
</x-layouts.app>
