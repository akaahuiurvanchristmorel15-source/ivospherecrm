<x-layouts.app title="Suivi Logistique & Livraisons">
    <div class="space-y-6" x-data="{ newDeliveryModal: false }">

        <!-- Top Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-800 font-bold uppercase tracking-wider">Logistique & Flotte</span>
                    <span class="text-xs text-slate-500">Expéditions Commandes</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Suivi des Livraisons</h1>
                <p class="text-xs text-slate-500">Gestion des tournées, assignation des chauffeurs et bordereaux de réception.</p>
            </div>

            <button @click="newDeliveryModal = true" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Programmer une Livraison</span>
            </button>
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Livraisons</span>
                <p class="text-2xl font-extrabold text-[#0B0F14] mt-2">{{ $counts['total'] }}</p>
                <span class="text-[11px] text-slate-400">Toutes expéditions</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">En Préparation</span>
                <p class="text-2xl font-extrabold text-amber-600 mt-2">{{ $counts['preparation'] }}</p>
                <span class="text-[11px] text-slate-400">Conditionnement atelier</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">En Transit / Tournée</span>
                <p class="text-2xl font-extrabold text-[#0066FF] mt-2">{{ $counts['in_transit'] }}</p>
                <span class="text-[11px] text-slate-400">Chauffeur en cours de route</span>
            </div>

            <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Livrées avec Succès</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $counts['delivered'] }}</p>
                <span class="text-[11px] text-slate-400">Bordereaux signés</span>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a 
                href="{{ route('deliveries.index') }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ !request('status') ? 'bg-[#0B0F14] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Toutes les livraisons
            </a>
            <a 
                href="{{ route('deliveries.index', ['status' => 'en_preparation']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('status') === 'en_preparation' ? 'bg-[#0066FF] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                En préparation
            </a>
            <a 
                href="{{ route('deliveries.index', ['status' => 'en_cours']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('status') === 'en_cours' ? 'bg-[#0066FF] text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                En cours de route
            </a>
            <a 
                href="{{ route('deliveries.index', ['status' => 'livree']) }}" 
                class="px-3.5 py-1.5 rounded-full font-medium transition-colors {{ request('status') === 'livree' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50' }}"
            >
                Livrées
            </a>
        </div>

        <!-- Deliveries Table & Mobile Cards -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-[#0B0F14]">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3.5 px-4">Tracking & Commande</th>
                            <th class="py-3.5 px-4">Client & Destinataire</th>
                            <th class="py-3.5 px-4">Adresse & Ville</th>
                            <th class="py-3.5 px-4">Chauffeur</th>
                            <th class="py-3.5 px-4">Planification</th>
                            <th class="py-3.5 px-4">Statut</th>
                            <th class="py-3.5 px-4 text-right">Mise à Jour</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($deliveries as $del)
                            <tr class="hover:bg-[#F5F7FA] transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-[#0066FF] block font-mono">{{ $del->tracking_number }}</span>
                                    <span class="text-slate-500 text-[11px]">
                                        {{ $del->order ? 'Cde : ' . $del->order->reference : 'Colis indépendant' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold block">
                                        {{ $del->customer ? ($del->customer->company_name ?: ($del->customer->first_name . ' ' . $del->customer->last_name)) : 'Client comptoir' }}
                                    </span>
                                    @if($del->recipient_name)
                                        <span class="text-[10px] text-emerald-600 font-medium">Reçu par : {{ $del->recipient_name }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="block truncate max-w-xs">{{ $del->delivery_address }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $del->city ?? 'Abidjan' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium block">{{ $del->driver_name ?? 'Non assigné' }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $del->driver_phone }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                    {{ $del->scheduled_at ? $del->scheduled_at->format('d/m/Y H:i') : 'À fixer' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $del->status === 'livree' ? 'bg-emerald-100 text-emerald-700' : ($del->status === 'en_cours' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800') }}">
                                        {{ str_replace('_', ' ', $del->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($del->status !== 'livree')
                                        <form action="{{ route('deliveries.status', $del) }}" method="POST" class="inline-flex items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            @if($del->status === 'en_preparation')
                                                <input type="hidden" name="status" value="en_cours" />
                                                <button type="submit" class="px-2.5 py-1 rounded bg-[#0066FF] text-white text-[11px] font-semibold hover:bg-[#0052cc]">
                                                    Expédier
                                                </button>
                                            @else
                                                <input type="hidden" name="status" value="livree" />
                                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-600 text-white text-[11px] font-semibold hover:bg-emerald-700">
                                                    Marquer Livrée
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-emerald-600 font-bold text-xs">Terminée</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Aucune expédition enregistrée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($deliveries as $del)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono font-bold text-sm text-[#0066FF] flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                                <span>{{ $del->tracking_number }}</span>
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $del->status === 'livree' ? 'bg-emerald-100 text-emerald-700' : ($del->status === 'en_cours' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800') }}">
                                {{ str_replace('_', ' ', $del->status) }}
                            </span>
                        </div>

                        <div>
                            <p class="text-[11px] text-slate-400 font-medium">Destinataire</p>
                            <p class="text-sm font-semibold text-[#0B0F14] leading-snug">
                                {{ $del->customer ? ($del->customer->company_name ?: ($del->customer->first_name . ' ' . $del->customer->last_name)) : 'Client comptoir' }}
                            </p>
                            @if($del->order)
                                <p class="text-xs text-[#0066FF] font-mono mt-0.5">Commande : {{ $del->order->reference }}</p>
                            @endif
                        </div>

                        <div class="bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] space-y-1.5 text-xs">
                            <div class="flex items-start gap-1.5 text-slate-700">
                                <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $del->delivery_address }} ({{ $del->city ?? 'Abidjan' }})</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-200">
                                <span>Chauffeur : <strong class="text-slate-800">{{ $del->driver_name ?? 'Non assigné' }}</strong></span>
                                <span>{{ $del->scheduled_at ? $del->scheduled_at->format('d/m H:i') : 'À fixer' }}</span>
                            </div>
                        </div>

                        @if($del->status !== 'livree')
                            <div class="pt-2 border-t border-slate-100">
                                <form action="{{ route('deliveries.status', $del) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @if($del->status === 'en_preparation')
                                        <input type="hidden" name="status" value="en_cours" />
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>Mettre en cours de tournée</span>
                                        </button>
                                    @else
                                        <input type="hidden" name="status" value="livree" />
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Confirmer Livraison Effectuée</span>
                                        </button>
                                    @endif
                                </form>
                            </div>
                        @else
                            <div class="text-center text-xs font-bold text-emerald-700 bg-emerald-50 py-1.5 rounded-lg border border-emerald-200 flex items-center justify-center gap-1">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Livraison Terminée avec Succès</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Aucune expédition enregistrée.
                    </div>
                @endforelse
            </div>

            @if($deliveries->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Programmer Livraison -->
        <div x-show="newDeliveryModal" class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/60 backdrop-blur-xs" style="display: none;">
            <div @click.outside="newDeliveryModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E8F0] text-xs">
                <h3 class="text-base font-bold text-[#0B0F14] mb-4">Programmer une Expédition</h3>
                <form action="{{ route('deliveries.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="delivery-order-select" class="block font-semibold text-[#0B0F14] mb-1">Commande Client Associée</label>
                        <select id="delivery-order-select" name="order_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                            <option value="">Sélectionner une commande (facultatif)</option>
                            @foreach($orders as $o)
                                <option value="{{ $o->id }}">{{ $o->reference }} — {{ number_format($o->total, 0, ',', ' ') }} FCFA</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="delivery-customer-select" class="block font-semibold text-[#0B0F14] mb-1">Client Destinataire</label>
                        <select id="delivery-customer-select" name="customer_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                            <option value="">Sélectionner le client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name ?: ($c->first_name . ' ' . $c->last_name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="delivery-address-input" class="block font-semibold text-[#0B0F14] mb-1">Adresse de livraison *</label>
                            <input id="delivery-address-input" type="text" name="delivery_address" placeholder="Rue, quartier, repère..." class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                        </div>
                        <div>
                            <label for="delivery-city-input" class="block font-semibold text-[#0B0F14] mb-1">Ville / Commune</label>
                            <input id="delivery-city-input" type="text" name="city" value="Abidjan" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="delivery-driver-name" class="block font-semibold text-[#0B0F14] mb-1">Nom du Chauffeur / Livreur</label>
                            <input id="delivery-driver-name" type="text" name="driver_name" placeholder="Ex: Kouamé Marc" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                        </div>
                        <div>
                            <label for="delivery-driver-phone" class="block font-semibold text-[#0B0F14] mb-1">Téléphone Livreur</label>
                            <input id="delivery-driver-phone" type="text" name="driver_phone" placeholder="+225 07..." class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="newDeliveryModal = false" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0066FF] text-white font-bold hover:bg-[#0052cc]">Enregistrer l'expédition</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
