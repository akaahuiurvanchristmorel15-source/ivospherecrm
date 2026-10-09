<x-layouts.app :title="'Contrat ' . $contract->reference . ' — ASSURANCE'">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('assurance.contracts.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la liste des contrats</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight font-mono">
                        {{ $contract->reference }}
                    </h1>
                    @php
                        $statusClasses = match($contract->status) {
                            'actif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'en_attente' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'resilie' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusClasses }}">
                        {{ ucfirst($contract->status) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Produit : {{ $contract->product->name ?? 'Police d\'assurance' }} &bull; Assureur : {{ $contract->partner }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('assurance.contracts.edit', $contract) }}" class="px-4 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>
            </div>
        </div>

        <!-- Grille Principale -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Colonne Gauche : Détails du Contrat & Commissions (2 cols) -->
            <div class="md:col-span-2 space-y-6">

                <!-- Détails Couverture & Produit -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Garanties & Couverture
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Police d'Assurance :</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ $contract->product->name ?? 'Assurance standard' }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Compagnie d'Assurance :</span>
                            <span class="font-bold text-[#0066FF] text-sm">{{ $contract->partner }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Date d'Effet :</span>
                            <span class="font-medium text-[#0B0F14]">
                                {{ $contract->start_date ? \Carbon\Carbon::parse($contract->start_date)->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Date d'Échéance :</span>
                            <span class="font-medium text-[#0B0F14]">
                                {{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->translatedFormat('d F Y') : 'Renouvellement tacite' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Périodicité des appels de prime :</span>
                            <span class="font-bold text-[#0B0F14] capitalize">{{ $contract->frequency }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Conseiller en charge :</span>
                            <span class="font-medium text-[#0B0F14]">{{ $contract->user->name ?? 'Équipe Assurance' }}</span>
                        </div>
                    </div>

                    @if($contract->notes)
                        <div class="pt-3 border-t border-[#E2E8F0] text-xs">
                            <span class="text-[#64748B] font-semibold block mb-1">Conditions Particulières & Clauses :</span>
                            <div class="p-3 bg-[#F5F7FA] rounded-lg text-[#0B0F14] whitespace-pre-line leading-relaxed">
                                {{ $contract->notes }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Commissions de Courtage -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Commissions Rétrocédées ({{ $contract->commissions->count() }})
                    </h3>

                    @if($contract->commissions->isNotEmpty())
                        <div class="divide-y divide-[#E2E8F0]">
                            @foreach($contract->commissions as $comm)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-bold text-[#0B0F14]">{{ number_format($comm->amount, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-[#64748B]">Taux : {{ $comm->rate }}% &bull; Date : {{ $comm->date ? \Carbon\Carbon::parse($comm->date)->format('d/m/Y') : '-' }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $comm->status === 'payé' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                        {{ ucfirst($comm->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-[#64748B]">
                            <svg class="w-8 h-8 mx-auto text-[#94A3B8] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-medium text-[#0B0F14]">Aucune rétrocession enregistrée</p>
                            <p class="mt-0.5">Les commissions reversées par l'assureur partenaire seront listées ici.</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Colonne Droite : Souscripteur & Prime (1 col) -->
            <div class="space-y-6">

                <!-- Carte Assuré -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Souscripteur / Assuré
                    </h3>

                    @if($contract->customer)
                        <div class="text-xs space-y-2">
                            <div>
                                <span class="text-[#64748B] block text-[11px]">Nom / Raison sociale :</span>
                                <span class="font-bold text-[#0B0F14] text-sm">{{ $contract->customer->name }}</span>
                            </div>
                            @if($contract->customer->phone)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Téléphone :</span>
                                    <a href="tel:{{ $contract->customer->phone }}" class="text-[#0B0F14] font-medium">{{ $contract->customer->phone }}</a>
                                </div>
                            @endif
                            @if($contract->customer->email)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Email :</span>
                                    <a href="mailto:{{ $contract->customer->email }}" class="text-[#0066FF] hover:underline">{{ $contract->customer->email }}</a>
                                </div>
                            @endif
                            @if($contract->customer->address)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Adresse :</span>
                                    <span class="text-[#0B0F14]">{{ $contract->customer->address }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-xs text-[#64748B] italic">
                            Aucun client associé
                        </div>
                    @endif
                </div>

                <!-- Montant Prime -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Prime d'Assurance
                    </h3>

                    <div class="text-xs space-y-3">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Montant de la prime :</span>
                            <span class="text-xl font-bold text-[#0B0F14]">
                                {{ number_format($contract->premium, 0, ',', ' ') }} FCFA
                            </span>
                            <span class="text-[11px] text-[#64748B] block mt-0.5 capitalize">
                                Par échéance {{ $contract->frequency }}
                            </span>
                        </div>

                        <div class="bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] space-y-1 text-xs">
                            <div class="flex justify-between text-[#64748B]">
                                <span>Taux commission estimé :</span>
                                <span class="font-semibold text-[#0066FF]">{{ $contract->product->commission_rate ?? '0' }}%</span>
                            </div>
                            @if($contract->product && $contract->product->commission_rate > 0)
                                <div class="flex justify-between text-[#64748B]">
                                    <span>Commission estimée :</span>
                                    <span class="font-bold text-emerald-600">
                                        {{ number_format(($contract->premium * $contract->product->commission_rate) / 100, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
