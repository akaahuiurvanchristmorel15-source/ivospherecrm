<x-layouts.app :title="'Événement ' . $event->reference . ' — MEDIA'">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('media.events.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la liste des événements</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        {{ $event->name }}
                    </h1>
                    @php
                        $badge = match($event->status) {
                            'clôturé', 'termine' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'en_cours' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'annulé' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-blue-50 text-[#0066FF] border-blue-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $badge }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5 font-mono">
                    Réf : {{ $event->reference }} &bull; Type : {{ $event->type ?? 'Général' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('media.events.edit', $event) }}" class="px-4 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>
            </div>
        </div>

        <!-- Détails de l'événement -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Colonne 1 & 2 : Informations logistiques -->
            <div class="md:col-span-2 space-y-6">

                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Fiche Opérationnelle & Logistique
                    </h3>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Lieu de réception :</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ $event->location ?? 'Non précisé' }}</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Invités attendus :</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ $event->guests_count ?? 'N/A' }} convives</span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Date de début :</span>
                            <span class="font-medium text-[#0B0F14]">
                                {{ $event->date ? \Carbon\Carbon::parse($event->date)->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[#64748B] block mb-0.5">Date de fin :</span>
                            <span class="font-medium text-[#0B0F14]">
                                {{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>
                    </div>

                    @if($event->notes)
                        <div class="pt-3 border-t border-[#E2E8F0] text-xs">
                            <span class="text-[#64748B] font-semibold block mb-1">Cahier des Charges & Régie :</span>
                            <div class="p-3 bg-[#F5F7FA] rounded-lg text-[#0B0F14] whitespace-pre-line leading-relaxed">
                                {{ $event->notes }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Prestations & Services Événementiels -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Prestations & Services Mobilisés ({{ $event->services->count() }})
                    </h3>

                    @if($event->services->isNotEmpty())
                        <div class="divide-y divide-[#E2E8F0]">
                            @foreach($event->services as $serv)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <span class="font-medium text-[#0B0F14]">{{ $serv->name }}</span>
                                    <span class="font-bold text-[#0066FF]">{{ number_format($serv->price, 0, ',', ' ') }} FCFA</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-xs text-[#64748B]">
                            Aucun service spécifique détaillé pour cet événement.
                        </div>
                    @endif
                </div>

            </div>

            <!-- Colonne 3 : Client & Budget -->
            <div class="space-y-6">

                <!-- Client -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Organisateur
                    </h3>

                    @if($event->customer)
                        <div class="text-xs space-y-2">
                            <div>
                                <span class="text-[#64748B] block text-[11px]">Nom :</span>
                                <span class="font-bold text-[#0B0F14] text-sm">{{ $event->customer->name }}</span>
                            </div>
                            @if($event->customer->phone)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Téléphone :</span>
                                    <a href="tel:{{ $event->customer->phone }}" class="text-[#0B0F14] font-medium">{{ $event->customer->phone }}</a>
                                </div>
                            @endif
                            @if($event->customer->email)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Email :</span>
                                    <a href="mailto:{{ $event->customer->email }}" class="text-[#0066FF] hover:underline">{{ $event->customer->email }}</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-xs text-[#64748B] italic">
                            Organisateur non lié à une fiche client
                        </div>
                    @endif
                </div>

                <!-- Budget -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Enveloppe Budgétaire
                    </h3>

                    <div class="text-xs space-y-2">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Budget Prévisionnel :</span>
                            <span class="text-xl font-bold text-[#0066FF]">
                                {{ number_format($event->budget, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        <div class="pt-2 border-t border-[#E2E8F0] text-[#64748B] flex justify-between">
                            <span>Pôle gestionnaire :</span>
                            <span class="font-medium text-[#0B0F14]">{{ $event->domain->name ?? 'MEDIA & EVENTS' }}</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
