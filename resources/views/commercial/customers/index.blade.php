<x-layouts.app>
    <x-slot:title>Clients & CRM — IVOSPHERE ERP</x-slot>

    <x-page-header 
        title="Portefeuille Clients & CRM" 
        description="Gestion unifiée des fiches clients 360°, suivi des encaissements, catégories et fidélité"
        :breadcrumbs="[['label' => 'Gestion Commerciale'], ['label' => 'Clients']]"
    >
        <x-slot:actions>
            <x-button :href="route('commercial.pos.index')" variant="secondary" size="md">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Vente POS</span>
            </x-button>

            <x-button :href="route('commercial.customers.create')" variant="primary" size="md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nouveau Client</span>
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtres & Recherche -->
    <x-card :padding="true" class="mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <x-search 
                name="search" 
                :value="request('search')" 
                placeholder="Rechercher par nom, code, entreprise, email..." 
                class="flex-1 min-w-[240px]"
            />

            <x-filter 
                name="status" 
                label="Tous les statuts" 
                :options="['actif' => 'Actifs', 'inactif' => 'Inactifs']" 
                :selected="request('status')"
            />

            <x-button type="submit" variant="secondary" size="md">
                Filtrer
            </x-button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('commercial.customers.index') }}" class="text-xs text-[#64748B] hover:text-[#0B0F14] ml-1">
                    Réinitialiser
                </a>
            @endif
        </form>
    </x-card>

    <!-- Tableau Professionnel des Clients -->
    <x-card :padding="false">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm text-[#0B0F14]">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-xs font-semibold text-[#64748B] uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Client / Entreprise</th>
                        <th class="px-6 py-3.5">Catégorie</th>
                        <th class="px-6 py-3.5">Contacts Directs</th>
                        <th class="px-6 py-3.5">Fidélité (§38)</th>
                        <th class="px-6 py-3.5">Statut</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] bg-white">
                    @forelse($customers as $customer)
                        <tr x-data="{ expanded: false }" class="transition-colors" :class="expanded ? 'bg-[#F5F7FA]/70' : 'hover:bg-[#F5F7FA]/40'">
                            <!-- Client / Nom -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <button 
                                        type="button" 
                                        @click="expanded = !expanded" 
                                        class="p-1 rounded-md text-slate-400 hover:text-[#0066FF] hover:bg-slate-100 transition-colors"
                                        :title="expanded ? 'Réduire les détails' : 'Déplier les détails'"
                                    >
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-90 text-[#0066FF]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    <div class="w-9 h-9 rounded-lg bg-[#0B0F14] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('commercial.customers.show', $customer) }}" class="font-semibold text-sm text-[#0B0F14] hover:text-[#0066FF] transition-colors">
                                            {{ $customer->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 text-xs text-[#64748B] mt-0.5">
                                            <span class="font-mono text-[11px]">{{ $customer->code }}</span>
                                            @if($customer->company)
                                                <span>&bull; {{ $customer->company }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Catégorie -->
                            <td class="px-6 py-4">
                                @if($customer->category === 'vip')
                                    <x-badge variant="warning" size="sm">VIP</x-badge>
                                @elseif($customer->category === 'revendeur')
                                    <x-badge variant="primary" size="sm">Revendeur</x-badge>
                                @elseif($customer->category === 'institutionnel')
                                    <x-badge variant="neutral" size="sm">Institutionnel</x-badge>
                                @else
                                    <x-badge variant="neutral" size="sm">Standard</x-badge>
                                @endif
                                <span class="block text-[11px] text-[#64748B] mt-1 capitalize">{{ $customer->type }}</span>
                            </td>

                            <!-- Contacts -->
                            <td class="px-6 py-4 text-xs">
                                <div class="space-y-1">
                                    @if($customer->email)
                                        <div class="text-[#64748B] flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span class="truncate max-w-[150px]">{{ $customer->email }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-2">
                                        @if($customer->phone)
                                            <span class="text-[#0B0F14] font-medium">{{ $customer->phone }}</span>
                                        @endif
                                        @php
                                            $cleanWa = preg_replace('/[^0-9]/', '', $customer->whatsapp ?? $customer->phone);
                                        @endphp
                                        @if($cleanWa)
                                            <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="text-emerald-600 hover:text-emerald-700" title="Écrire sur WhatsApp">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Fidélité -->
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800 border border-[#E2E8F0]">
                                    {{ $customer->loyalty_level ?? 'BRONZE' }}
                                </span>
                                <span class="block text-xs font-semibold text-[#0B0F14] mt-1">
                                    {{ number_format($customer->loyalty_points ?? 0, 0, ',', ' ') }} pts
                                </span>
                            </td>

                            <!-- Statut -->
                            <td class="px-6 py-4">
                                <x-badge :variant="$customer->status == 'actif' ? 'success' : 'neutral'" size="sm" :dot="true">
                                    {{ ucfirst($customer->status) }}
                                </x-badge>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button :href="route('commercial.customers.show', $customer)" variant="secondary" size="sm">
                                        Fiche 360°
                                    </x-button>

                                    <!-- Dropdown menu d'actions ⋮ -->
                                    <x-dropdown align="right" width="48">
                                        <x-slot:trigger>
                                            <button type="button" class="p-1.5 rounded-lg text-slate-400 hover:text-[#0B0F14] hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                            </button>
                                        </x-slot:trigger>
                                        <x-slot:content>
                                            <a href="{{ route('commercial.customers.show', $customer) }}" class="block px-4 py-2 text-slate-700 hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors">
                                                Voir Fiche 360°
                                            </a>
                                            <a href="{{ route('commercial.customers.edit', $customer) }}" class="block px-4 py-2 text-slate-700 hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors">
                                                Modifier informations
                                            </a>
                                            <a href="{{ route('commercial.quotations.create', ['customer_id' => $customer->id]) }}" class="block px-4 py-2 text-slate-700 hover:bg-[#F5F7FA] hover:text-[#0066FF] transition-colors">
                                                Créer un devis
                                            </a>
                                        </x-slot:content>
                                    </x-dropdown>
                                </div>
                            </td>
                        </tr>

                        <!-- Accordéon Détail Dépliable (§ Vues condensées avec expansion) -->
                        <tr x-show="expanded" x-collapse style="display: none;" class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                            <td colspan="6" class="px-8 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 text-xs">
                                    <!-- Bloc Identité & NIF (4 cols) -->
                                    <div class="md:col-span-4 space-y-1.5">
                                        <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider block">Coordonnées Légales</span>
                                        <p class="text-[#0B0F14]"><span class="text-[#64748B]">NIF :</span> {{ $customer->nif ?: 'Non renseigné' }}</p>
                                        <p class="text-[#0B0F14]"><span class="text-[#64748B]">Contact clé :</span> {{ $customer->contact_person ?: 'Direction commerciale' }}</p>
                                        <p class="text-[#0B0F14]"><span class="text-[#64748B]">Adresse :</span> {{ $customer->address ?: 'Non renseignée' }}</p>
                                    </div>

                                    <!-- Bloc Activité & Domaine (4 cols) -->
                                    <div class="md:col-span-4 space-y-1.5">
                                        <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider block">Activité & Rattachement</span>
                                        <p class="text-[#0B0F14]"><span class="text-[#64748B]">Domaine :</span> {{ $customer->domain->name ?? 'Tous les domaines' }}</p>
                                        <p class="text-[#0B0F14]"><span class="text-[#64748B]">Date d\'inscription :</span> {{ $customer->created_at->format('d/m/Y') }}</p>
                                        <p class="text-[#0B0F14]"><span class="text-[#64748B]">Programme :</span> Palier {{ $customer->loyalty_level }} ({{ number_format($customer->loyalty_points, 0, ',', ' ') }} pts)</p>
                                    </div>

                                    <!-- Bloc Raccourcis Directs (4 cols) -->
                                    <div class="md:col-span-4 flex flex-col justify-center gap-2">
                                        <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider block">Raccourcis Directs</span>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-button :href="route('commercial.quotations.create', ['customer_id' => $customer->id])" variant="primary" size="sm">
                                                + Nouveau devis
                                            </x-button>
                                            <x-button :href="route('commercial.customers.edit', $customer)" variant="secondary" size="sm">
                                                Modifier
                                            </x-button>
                                            @if($cleanWa)
                                                <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-medium transition-colors inline-flex items-center gap-1.5 text-xs">
                                                    WhatsApp direct
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-empty-state 
                                    title="Aucun client trouvé" 
                                    description="Aucun dossier client ne correspond à vos filtres actuels."
                                    actionText="Créer un premier client"
                                    :actionUrl="route('commercial.customers.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Amplified Cards View (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($customers as $customer)
                @php
                    $cleanWa = preg_replace('/[^0-9]/', '', $customer->whatsapp ?? $customer->phone);
                @endphp
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <!-- Top row: Avatar + Name + Status -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#0B0F14] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('commercial.customers.show', $customer) }}" class="font-bold text-sm text-[#0B0F14] hover:text-[#0066FF] transition-colors leading-snug">
                                    {{ $customer->name }}
                                </a>
                                <div class="flex items-center gap-1.5 text-xs text-[#64748B] mt-0.5">
                                    <span class="font-mono text-[11px] font-semibold text-[#0066FF]">{{ $customer->code }}</span>
                                    @if($customer->company)
                                        <span>&bull; {{ $customer->company }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <x-badge :variant="$customer->status == 'actif' ? 'success' : 'neutral'" size="sm" :dot="true">
                            {{ ucfirst($customer->status) }}
                        </x-badge>
                    </div>

                    <!-- Category & Loyalty Bar -->
                    <div class="flex items-center justify-between bg-[#F5F7FA] px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs">
                        <div class="flex items-center gap-1.5">
                            @if($customer->category === 'vip')
                                <x-badge variant="warning" size="sm">VIP</x-badge>
                            @elseif($customer->category === 'revendeur')
                                <x-badge variant="primary" size="sm">Revendeur</x-badge>
                            @elseif($customer->category === 'institutionnel')
                                <x-badge variant="neutral" size="sm">Institutionnel</x-badge>
                            @else
                                <x-badge variant="neutral" size="sm">Standard</x-badge>
                            @endif
                            <span class="text-[11px] text-[#64748B] capitalize">{{ $customer->type }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-[11px]">
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-slate-200 text-slate-700">
                                {{ $customer->loyalty_level ?? 'BRONZE' }}
                            </span>
                            <span class="font-bold text-[#0B0F14]">
                                {{ number_format($customer->loyalty_points ?? 0, 0, ',', ' ') }} pts
                            </span>
                        </div>
                    </div>

                    <!-- Direct contact channels -->
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 font-medium hover:bg-slate-100 transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $customer->phone }}</span>
                            </a>
                        @endif

                        @if($cleanWa)
                            <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 font-medium hover:bg-emerald-100 transition">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                        @endif

                        @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 font-medium hover:bg-slate-100 transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="truncate max-w-[120px]">{{ $customer->email }}</span>
                            </a>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('commercial.quotations.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Devis</span>
                        </a>
                        <a href="{{ route('commercial.customers.edit', $customer) }}" class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Modifier</span>
                        </a>
                        <a href="{{ route('commercial.customers.show', $customer) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Fiche 360°</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-8">
                    <x-empty-state 
                        title="Aucun client trouvé" 
                        description="Aucun dossier client ne correspond à vos filtres actuels."
                    />
                </div>
            @endforelse
        </div>

        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-[#E2E8F0] bg-[#F5F7FA]">
                <x-pagination :paginator="$customers" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
