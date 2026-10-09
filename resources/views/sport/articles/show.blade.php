<x-layouts.app :title="'Article Sport #' . $article->id . ' — SPORT'">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('sport.articles.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux articles</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        {{ $article->product->name ?? 'Article Sport' }}
                    </h1>
                    @php
                        $statusClasses = match($article->status) {
                            'livré' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'pret' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                            'en_confection' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'annulé' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusClasses }}">
                        {{ ucfirst($article->status) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Équipe : {{ $article->team ?? 'Particulier' }} &bull; Créé le {{ $article->created_at->translatedFormat('d F Y') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sport.articles.edit', $article) }}" class="px-4 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>
            </div>
        </div>

        <!-- Grille Principale -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Colonne 1 & 2 : Flocage & Caractéristiques -->
            <div class="md:col-span-2 space-y-6">

                <!-- Détails du maillot / flocage -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Flocage & Spécifications Sportives
                    </h3>

                    <!-- Visuel Flocage Stylisé -->
                    @if($article->custom_name || $article->number)
                        <div class="p-6 rounded-xl bg-gradient-to-br from-slate-900 to-slate-800 text-white text-center shadow-inner relative overflow-hidden">
                            <span class="text-[10px] tracking-widest uppercase font-semibold text-slate-400 block mb-1">
                                {{ $article->team ?? 'IVOSPHERE SPORT' }}
                            </span>
                            <div class="font-mono text-3xl font-black tracking-wider uppercase text-amber-400">
                                {{ $article->custom_name ?? 'NOM' }}
                            </div>
                            <div class="font-mono text-6xl font-black text-white mt-1">
                                {{ $article->number ?? '00' }}
                            </div>
                            <div class="mt-3 inline-flex items-center gap-2 text-[11px] text-slate-300 bg-slate-800/80 px-3 py-1 rounded-full border border-slate-700">
                                <span>Taille : <strong>{{ $article->size ?? '-' }}</strong></span>
                                &bull;
                                <span>Couleur : <strong>{{ $article->color ?? '-' }}</strong></span>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs pt-2">
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Taille :</span>
                            <span class="font-bold text-[#0B0F14]">{{ $article->size ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Couleur :</span>
                            <span class="font-bold text-[#0B0F14]">{{ $article->color ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Club / Équipe :</span>
                            <span class="font-bold text-[#0B0F14]">{{ $article->team ?? 'Non spécifié' }}</span>
                        </div>
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Quantité :</span>
                            <span class="font-bold text-[#0B0F14]">{{ $article->quantity }} unité(s)</span>
                        </div>
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Prix Unitaire :</span>
                            <span class="font-medium text-[#0B0F14]">{{ number_format($article->unit_price, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Montant Total :</span>
                            <span class="font-extrabold text-[#0066FF]">{{ number_format($article->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    @if($article->notes)
                        <div class="pt-3 border-t border-[#E2E8F0] text-xs">
                            <span class="text-[#64748B] font-semibold block mb-1">Instructions de Confection :</span>
                            <div class="p-3 bg-[#F5F7FA] rounded-lg text-[#0B0F14] whitespace-pre-line leading-relaxed">
                                {{ $article->notes }}
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Colonne 3 : Client & Infos -->
            <div class="space-y-6">

                <!-- Client -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Client / Destinataire
                    </h3>

                    @if($article->customer)
                        <div class="text-xs space-y-2">
                            <div>
                                <span class="text-[#64748B] block text-[11px]">Nom :</span>
                                <span class="font-bold text-[#0B0F14] text-sm">{{ $article->customer->name }}</span>
                            </div>
                            @if($article->customer->phone)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Téléphone :</span>
                                    <a href="tel:{{ $article->customer->phone }}" class="text-[#0B0F14] font-medium">{{ $article->customer->phone }}</a>
                                </div>
                            @endif
                            @if($article->customer->email)
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Email :</span>
                                    <a href="mailto:{{ $article->customer->email }}" class="text-[#0066FF] hover:underline">{{ $article->customer->email }}</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-xs text-[#64748B] italic">
                            Aucun client rattaché
                        </div>
                    @endif
                </div>

                <!-- Récapitulatif Financier -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                        Total Commande
                    </h3>

                    <div class="text-xs space-y-2">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[#64748B]">Montant Total :</span>
                            <span class="text-xl font-bold text-[#0066FF]">
                                {{ number_format($article->total, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        <p class="text-[11px] text-[#64748B]">
                            Calculé sur la base de {{ $article->quantity }} x {{ number_format($article->unit_price, 0, ',', ' ') }} FCFA.
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
