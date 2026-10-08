<x-layouts.app title="Espace Client — Portail Dédié">
    <div class="space-y-6">

        <!-- Top Header & Client Switcher for demonstration -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-800 font-bold uppercase tracking-wider">Portail Extranet Client</span>
                    <span class="text-xs text-slate-500">Vue Sécurisée</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">
                    Espace Client : {{ $customer->company_name ?: ($customer->first_name . ' ' . $customer->last_name) }}
                </h1>
                <p class="text-xs text-slate-500">Consultez en direct vos devis, commandes en atelier, factures et l'avancement de vos projets.</p>
            </div>

            <!-- Client Switcher (for ERP management demo) -->
            <form action="{{ route('client-portal.index') }}" method="GET" class="flex items-center gap-2">
                <label for="client-select" class="text-xs font-semibold text-slate-500 shrink-0">Changer de client :</label>
                <select id="client-select" name="customer_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF] shadow-xs">
                    @foreach($allCustomers as $c)
                        <option value="{{ $c->id }}" {{ $customer->id === $c->id ? 'selected' : '' }}>
                            {{ $c->company_name ?: ($c->first_name . ' ' . $c->last_name) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Row 1: Client Financial Cockpit (3 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Facturé</span>
                <p class="text-2xl font-extrabold text-[#0B0F14] mt-2">{{ number_format($totalInvoiced, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span></p>
                <span class="text-[11px] text-slate-400">Historique consolidé</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Règlements Effectués</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ number_format($totalPaid, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span></p>
                <span class="text-[11px] text-slate-400">Paiements validés</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Solde Restant Dû</span>
                <p class="text-2xl font-extrabold {{ $balanceDue > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-2">
                    {{ number_format($balanceDue, 0, ',', ' ') }} <span class="text-xs font-medium text-slate-400">FCFA</span>
                </p>
                <span class="text-[11px] text-slate-400">À régler selon échéance</span>
            </div>
        </div>

        <!-- Row 2: Devis à Valider & Commandes en Cours -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Devis Récents -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0] mb-4">
                    <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Devis & Propositions</h2>
                    <span class="text-xs text-slate-400">{{ $quotations->count() }} devis</span>
                </div>

                <div class="space-y-3">
                    @forelse($quotations as $quote)
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="font-bold text-[#0066FF] block">{{ $quote->reference }}</span>
                                <span class="text-slate-500 text-[11px]">Émis le {{ $quote->date ? $quote->date->format('d/m/Y') : $quote->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-[#0B0F14] block">{{ number_format($quote->total, 0, ',', ' ') }} FCFA</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $quote->status === 'validé' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $quote->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400">Aucun devis récent pour ce client.</p>
                    @endforelse
                </div>
            </div>

            <!-- Commandes en Production & Livraisons -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0] mb-4">
                    <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Commandes & Suivi de Fabrication</h2>
                    <span class="text-xs text-slate-400">{{ $orders->count() }} commande(s)</span>
                </div>

                <div class="space-y-3">
                    @forelse($orders as $order)
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="font-bold text-[#0B0F14] block">{{ $order->reference }}</span>
                                <span class="text-[11px] text-slate-500">Domaine : {{ $order->domain ? $order->domain->name : 'Général' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-[#0B0F14] block">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-[#0066FF]/10 text-[#0066FF]">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400">Aucune commande en cours.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Row 3: Projets TECH & Support Tickets -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Projets Tech en cours -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0] mb-4">
                    <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Avancement de vos Projets Tech</h2>
                    <span class="text-xs text-slate-400">Pôle IVOSPHERE TECH</span>
                </div>

                <div class="space-y-4 text-xs">
                    @forelse($projects as $p)
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-[#0B0F14]">{{ $p->name }}</span>
                                <span class="font-extrabold text-[#0066FF]">{{ $p->progress ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-[#0066FF] rounded-full" style="width: {{ $p->progress ?? 0 }}%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-2 text-[11px] text-slate-400">
                                <span>Statut : {{ $p->status }}</span>
                                <span>Livraison visée : {{ $p->deadline ? $p->deadline->format('d/m/Y') : 'À planifier' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400">Aucun projet tech actif pour ce compte.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tickets de Support -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0] mb-4">
                        <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Demandes d'Assistance (Tickets)</h2>
                        <a href="{{ route('support.create') }}" class="text-xs text-[#0066FF] font-semibold hover:underline">+ Nouveau Ticket</a>
                    </div>

                    <div class="space-y-3">
                        @forelse($tickets as $tk)
                            <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-[#0066FF] block">{{ $tk->ticket_number }}</span>
                                    <span class="font-semibold text-[#0B0F14] block mt-0.5">{{ $tk->subject }}</span>
                                    <span class="text-[10px] text-slate-400">Créé {{ $tk->created_at->diffForHumans() }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tk->status === 'resolu' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $tk->status }}
                                </span>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-400">Aucun ticket de support ouvert.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E2E8F0] mt-4 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Ligne directe assistance : +225 27 00 00 00</span>
                    <span>support@ivosphere.com</span>
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
