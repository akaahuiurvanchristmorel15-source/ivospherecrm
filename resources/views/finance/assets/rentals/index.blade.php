@php
    $inputClass = 'mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0066FF] focus:ring-2 focus:ring-[#0066FF]/20 focus:outline-none';
    $labelClass = 'block text-xs font-semibold uppercase tracking-wider text-slate-700';
@endphp

<x-layouts.app title="Suivi des Locations de Matériel — Finance">
    <div x-data="{
        newRentalModal: false,
        statusModal: false,
        currentRental: null,
        targetStatus: '',
        openStatusModal(rental, status) {
            this.currentRental = rental;
            this.targetStatus = status;
            this.statusModal = true;
        }
    }" class="mx-auto w-full max-w-7xl space-y-6 pb-20">

        {{-- ── En-tête de page ─────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                    <a href="{{ route('finance.index') }}" class="hover:text-[#0066FF] transition-colors">Finance</a>
                    <span>/</span>
                    <a href="{{ route('finance.assets.index') }}" class="hover:text-[#0066FF] transition-colors">Immobilisations & Actifs</a>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">Locations de Matériel</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-purple-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </span>
                    Locations de Matériel & Parcs
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Contrats de mise à disposition externe, suivi des sorties d'équipements, vérifications techniques et cautions.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('finance.assets.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    ← Catalogue Immobilisations
                </a>
                <button type="button" @click="newRentalModal = true" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-purple-600 px-4 text-sm font-semibold text-white shadow-xs hover:bg-purple-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Nouvelle Location
                </button>
            </div>
        </div>

        {{-- ── Messages flash ─────────────────────────────────────────────── --}}
        @if(session('success'))
            <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-xs">
                <svg class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        {{-- ── KPIs Locations ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">CA Total Locations</p>
                <p class="mt-2 text-2xl font-bold text-purple-700 tabular-nums">
                    {{ number_format($totalRentalRevenue, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Revenus cumulés sur contrats</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Locations Actives</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums">
                    {{ $activeRentalsCount }} <span class="text-xs font-normal text-slate-400">en cours</span>
                </p>
                <p class="text-[11px] text-purple-600 mt-1 font-medium">Matériels actuellement chez les clients</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Retours & Contrôles</p>
                <p class="mt-2 text-2xl font-bold text-amber-600 tabular-nums">
                    {{ $pendingReturnsCount }} <span class="text-xs font-normal text-slate-400">à inspecter</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Vérification technique avant remise en stock</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Cautions Détenues</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums">
                    {{ number_format($totalDepositsHeld, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span>
                </p>
                <p class="text-[11px] text-emerald-600 mt-1 font-medium">Garanties sous séquestre</p>
            </div>
        </div>

        {{-- ── Table des locations ─────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Contrats de location</span>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold">{{ $rentals->total() }}</span>
                </div>

                {{-- Filtre statut rapide --}}
                <div class="flex items-center gap-1.5 text-xs">
                    <a href="{{ route('finance.assets.rentals.index') }}" class="px-2.5 py-1 rounded-lg {{ !request('status') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-600 hover:bg-slate-100' }}">Tous</a>
                    <a href="{{ route('finance.assets.rentals.index', ['status' => 'loue']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'loue' ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-600 hover:bg-slate-100' }}">En cours</a>
                    <a href="{{ route('finance.assets.rentals.index', ['status' => 'retourne_controle']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'retourne_controle' ? 'bg-amber-50 text-amber-700 font-bold' : 'text-slate-600 hover:bg-slate-100' }}">En contrôle</a>
                    <a href="{{ route('finance.assets.rentals.index', ['status' => 'cloture']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'cloture' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-100' }}">Clôturés</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Réf Contrat</th>
                            <th class="px-4 py-3">Matériel Loué</th>
                            <th class="px-4 py-3">Client Locataire</th>
                            <th class="px-4 py-3">Période</th>
                            <th class="px-4 py-3 text-right">Montant (FCFA)</th>
                            <th class="px-4 py-3 text-right">Caution</th>
                            <th class="px-4 py-3 text-center">Statut</th>
                            <th class="px-5 py-3 text-right">Actions de Cycle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rentals as $rental)
                            @php
                                $badge = match($rental->status) {
                                    'loue' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'label' => 'En cours'],
                                    'reserve' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'label' => 'Réservé'],
                                    'retourne_controle' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'label' => 'Retour - À contrôler'],
                                    'cloture' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'Clôturé & Restitué'],
                                    'annule' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'label' => 'Annulé'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'label' => ucfirst($rental->status)],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $rental->reference }}</span>
                                    <span class="text-[11px] text-slate-400">Créé par {{ $rental->user?->name ?? 'Système' }}</span>
                                </td>

                                <td class="px-4 py-4">
                                    <a href="{{ route('finance.assets.show', $rental->asset) }}" class="font-semibold text-slate-900 hover:text-[#0066FF] transition line-clamp-1">
                                        {{ $rental->asset?->name }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                        <span class="font-mono font-semibold">{{ $rental->asset?->code }}</span>
                                        <span>•</span>
                                        <span>{{ $rental->asset?->domain?->name }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="font-medium text-slate-900 block">{{ $rental->customer?->company_name ?? $rental->customer?->full_name }}</span>
                                    <span class="text-xs text-slate-500">{{ $rental->customer?->phone ?? 'Pas de tél' }}</span>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs">
                                    <div class="font-semibold text-slate-800">
                                        Du {{ $rental->start_date?->format('d/m/Y') }} au {{ $rental->end_date?->format('d/m/Y') }}
                                    </div>
                                    <div class="text-slate-500 mt-0.5">
                                        {{ $rental->total_days }} jour(s) à {{ number_format((float) $rental->daily_rate, 0, ',', ' ') }} FCFA/j
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <div class="font-bold text-purple-700 tabular-nums">
                                        {{ number_format((float) $rental->total_amount, 0, ',', ' ') }} <span class="text-[11px] font-normal text-slate-400">FCFA</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap text-xs">
                                    <div class="font-semibold {{ $rental->deposit_returned ? 'text-slate-400 line-through' : 'text-slate-900' }} tabular-nums">
                                        {{ number_format((float) $rental->deposit_amount, 0, ',', ' ') }} FCFA
                                    </div>
                                    <div class="text-[11px] {{ $rental->deposit_returned ? 'text-emerald-600 font-semibold' : 'text-amber-600' }}">
                                        {{ $rental->deposit_returned ? 'Caution restituée' : 'Caution conservée' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($rental->status === 'reserve')
                                            <form method="POST" action="{{ route('finance.assets.rentals.status', $rental) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="loue">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-purple-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-purple-700 transition">
                                                    Départ Client
                                                </button>
                                            </form>
                                        @elseif($rental->status === 'loue')
                                            <form method="POST" action="{{ route('finance.assets.rentals.status', $rental) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="retourne_controle">
                                                <input type="hidden" name="actual_return_date" value="{{ date('Y-m-d') }}">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-amber-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-700 transition">
                                                    Retour & Contrôle
                                                </button>
                                            </form>
                                        @elseif($rental->status === 'retourne_controle')
                                            <form method="POST" action="{{ route('finance.assets.rentals.status', $rental) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cloture">
                                                <input type="hidden" name="deposit_returned" value="1">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                                    Clôturer & Rendre Caution
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">Terminé</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                    Aucune location enregistrée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rentals->hasPages())
                <div class="border-t border-slate-200 px-5 py-3.5">
                    {{ $rentals->links() }}
                </div>
            @endif
        </div>

        {{-- ═════════════ MODAL : NOUVELLE LOCATION ═════════════ --}}
        <div x-show="newRentalModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.outside="newRentalModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Nouveau contrat de location de matériel</h3>
                    <button type="button" @click="newRentalModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <form method="POST" action="{{ route('finance.assets.rentals.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="{{ $labelClass }}">Matériel à louer *</label>
                        <select name="fixed_asset_id" required class="{{ $inputClass }}">
                            <option value="">Sélectionner un équipement éligible...</option>
                            @foreach($eligibleAssets as $asset)
                                <option value="{{ $asset->id }}">
                                    [{{ $asset->code }}] {{ $asset->name }} ({{ number_format((float) $asset->rental_price_per_day, 0, ',', ' ') }} FCFA/j)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Client locataire *</label>
                        <select name="customer_id" required class="{{ $inputClass }}">
                            <option value="">Sélectionner un client...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name ?? $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Date de départ *</label>
                            <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Date de retour prévue *</label>
                            <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Tarif journalier (FCFA) *</label>
                            <input type="number" step="500" min="0" name="daily_rate" required placeholder="Ex: 35000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Caution exigée (FCFA)</label>
                            <input type="number" step="1000" min="0" name="deposit_amount" placeholder="Ex: 150000" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">État au départ</label>
                        <input type="text" name="condition_at_departure" placeholder="Ex: Matériel vérifié, complet avec câbles et housse" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Notes et observations</label>
                        <textarea name="notes" rows="2" placeholder="Numéros de série des accessoires..." class="{{ $inputClass }}"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="newRentalModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Annuler</button>
                        <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2 text-xs font-semibold text-white hover:bg-purple-700">Valider la Location</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
