<x-layouts.app :title="'Séance ' . $session->reference . ' — MEDIA'">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('media.sessions.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la liste des séances</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight font-mono">
                        {{ $session->reference }}
                    </h1>
                    @php
                        $statusClasses = match($session->status) {
                            'livré' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'en_cours' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'en_traitement' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'annulé' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-blue-50 text-[#0066FF] border-blue-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusClasses }}">
                        {{ ucfirst($session->status) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Prestation : {{ $session->package ?? 'Standard' }} &bull; Planifiée le {{ $session->date ? \Carbon\Carbon::parse($session->date)->translatedFormat('d F Y') : 'Date non définie' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('media.sessions.edit', $session) }}" class="px-4 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>
            </div>
        </div>

        <!-- Grille Principale -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Colonne Gauche : Détails de la séance (2 cols) -->
            <div class="md:col-span-2 space-y-6">

                <!-- Carte Informations Générales -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Détails de la Prestation Média
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Formule choisie :</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ $session->package ?? 'Formule Standard' }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Photographe / Équipe :</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ $session->photographer ?? 'Équipe Média IVOSPHERE' }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Lieu du Shooting :</span>
                            <span class="font-medium text-[#0B0F14]">{{ $session->location ?? 'Studio IVOSPHERE' }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Date de réalisation :</span>
                            <span class="font-medium text-[#0B0F14]">
                                {{ $session->date ? \Carbon\Carbon::parse($session->date)->translatedFormat('d F Y') : 'Non spécifiée' }}
                            </span>
                        </div>
                    </div>

                    @if($session->notes)
                        <div class="pt-3 border-t border-[#E2E8F0] text-xs">
                            <span class="text-[#64748B] font-semibold block mb-1">Notes & Consignes Particulières :</span>
                            <div class="p-3 bg-[#F5F7FA] rounded-lg text-[#0B0F14] whitespace-pre-line leading-relaxed">
                                {{ $session->notes }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Carte Galeries & Livrables -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">
                            Galeries Photos & Fichiers Livrés ({{ $session->galleries->count() }})
                        </h3>
                    </div>

                    @if($session->galleries->isNotEmpty())
                        <div class="divide-y divide-[#E2E8F0]">
                            @foreach($session->galleries as $gallery)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-bold text-[#0B0F14]">{{ $gallery->name }}</p>
                                        <p class="text-[#64748B]">
                                            {{ $gallery->photos_count }} photos &bull; Prévu pour le {{ $gallery->delivery_date ? \Carbon\Carbon::parse($gallery->delivery_date)->format('d/m/Y') : 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border bg-blue-50 text-[#0066FF] border-blue-200">
                                        {{ ucfirst($gallery->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-[#64748B]">
                            <svg class="w-8 h-8 mx-auto text-[#94A3B8] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="font-medium text-[#0B0F14]">Aucune galerie associée pour le moment</p>
                            <p class="mt-0.5">Les photos et visuels retouchés apparaîtront ici lors de la livraison.</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Colonne Droite : Client & Facturation (1 col) -->
            <div class="space-y-6">

                <!-- Carte Client -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Client / Demandeur
                    </h3>

                    @if($session->customer)
                        <div class="text-xs space-y-2">
                            <div>
                                <span class="text-[#64748B] block text-[11px]">Nom / Entreprise :</span>
                                <span class="font-bold text-[#0B0F14] text-sm">{{ $session->customer->name }}</span>
                            </div>
                            @if($session->customer->email)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Email :</span>
                                    <a href="mailto:{{ $session->customer->email }}" class="text-[#0066FF] hover:underline">{{ $session->customer->email }}</a>
                                </div>
                            @endif
                            @if($session->customer->phone)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Téléphone :</span>
                                    <a href="tel:{{ $session->customer->phone }}" class="text-[#0B0F14] font-medium">{{ $session->customer->phone }}</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-xs text-[#64748B]">
                            <span class="italic">Particulier ou Client non rattaché</span>
                        </div>
                    @endif
                </div>

                <!-- Carte Tarif & Facturation -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Tarification & Règlement
                    </h3>

                    <div class="text-xs space-y-3">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Montant du forfait :</span>
                            <span class="text-xl font-bold text-[#0B0F14]">
                                {{ number_format($session->price, 0, ',', ' ') }} FCFA
                            </span>
                        </div>

                        <div class="bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] space-y-1">
                            <div class="flex justify-between text-[#64748B]">
                                <span>Pôle :</span>
                                <span class="font-semibold text-[#0B0F14]">{{ $session->domain->name ?? 'IVOSPHERE MEDIA' }}</span>
                            </div>
                            <div class="flex justify-between text-[#64748B]">
                                <span>Créé par :</span>
                                <span class="font-medium text-[#0B0F14]">{{ $session->user->name ?? 'Système' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
