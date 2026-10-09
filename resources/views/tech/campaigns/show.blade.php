<x-layouts.app :title="'Campagne ' . $campaign->reference . ' — TECH'">
    @php
        $statusBadge = match($campaign->status) {
            'actif'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'planifie'  => 'bg-purple-50 text-purple-700 border-purple-200',
            'termine'   => 'bg-slate-50 text-slate-700 border-slate-200',
            'en_pause'  => 'bg-amber-50 text-amber-700 border-amber-200',
            default     => 'bg-blue-50 text-[#0066FF] border-blue-200',
        };
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('tech.campaigns.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2"/></svg>
                    <span>Retour aux campagnes IA</span>
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        {{ $campaign->name }}
                    </h1>
                    <span class="text-xs font-mono font-bold text-[#0066FF] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                        {{ $campaign->reference }}
                    </span>
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                        {{ ucfirst($campaign->status) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Créée le {{ $campaign->created_at->format('d/m/Y') }} • Pilote : {{ $campaign->user->name ?? 'Agent IA IVOSPHERE' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a 
                    href="{{ route('tech.campaigns.edit', $campaign) }}" 
                    class="px-4 py-2 rounded-xl bg-[#0B0F14] text-white hover:bg-[#0066FF] text-xs font-semibold transition-colors flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>

                <form action="{{ route('tech.campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de cette campagne ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- 3 Cartes Synthèse -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Client / Compte</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ $campaign->customer->name ?? 'Campagne Interne' }}
                </span>
                @if($campaign->customer?->company)
                    <span class="text-xs text-[#64748B] block">{{ $campaign->customer->company }}</span>
                @endif
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Budget Publicitaire</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ number_format($campaign->budget, 0, ',', ' ') }} FCFA
                </span>
                <span class="text-xs text-[#64748B] block mt-0.5">Budget Ads alloué</span>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Calendrier de Diffusion</span>
                <div class="text-xs text-[#0B0F14] font-medium mt-1.5 space-y-0.5">
                    <p>Début : <strong>{{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'Immédiat' }}</strong></p>
                    <p>Fin : <strong>{{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'Non définie' }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Canaux ciblés -->
        @if(!empty($campaign->channels))
            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block mb-2">Canaux Publicitaires Actifs</span>
                <div class="flex flex-wrap gap-2">
                    @foreach((array)$campaign->channels as $channel)
                        <span class="px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-[#0066FF] font-semibold text-xs capitalize">
                            {{ $channel }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Brief créatif -->
        <div class="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Brief Créatif & Consignes de Génération IA</h3>
            <p class="text-xs text-[#0B0F14] leading-relaxed whitespace-pre-line">
                {{ $campaign->brief ?: 'Aucun brief créatif renseigné.' }}
            </p>
        </div>

        <!-- Contenus générés par l'IA -->
        <div class="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <div>
                    <h3 class="text-sm font-bold text-[#0B0F14]">Contenus & Posts Générés (Agent IA)</h3>
                    <p class="text-xs text-[#64748B]">Publications, visuels, accroches et variantes copywriting.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    {{ $campaign->contents->count() }} contenu(s)
                </span>
            </div>

            @if($campaign->contents->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($campaign->contents as $content)
                        <div class="p-4 rounded-xl border border-[#E2E8F0] bg-[#F5F7FA] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-white border border-[#E2E8F0] text-[#0B0F14]">
                                    {{ $content->platform ?? 'Multi-canal' }}
                                </span>
                                <span class="text-[11px] text-[#64748B]">{{ ucfirst($content->status ?? 'généré') }}</span>
                            </div>
                            <p class="text-xs text-[#0B0F14] leading-relaxed line-clamp-4">{{ $content->content }}</p>
                            @if($content->media_path)
                                <img src="{{ asset('storage/' . $content->media_path) }}" alt="" class="w-full h-36 object-cover rounded-lg border border-[#E2E8F0]">
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="text-xs text-[#64748B]">Aucun contenu n'a encore été généré pour cette campagne.</p>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
