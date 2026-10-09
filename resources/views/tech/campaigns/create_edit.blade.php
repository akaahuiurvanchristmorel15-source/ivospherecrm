<x-layouts.app :title="isset($campaign) ? 'Modifier Campagne ' . $campaign->reference . ' — TECH' : 'Nouvelle Campagne IA — TECH'">
    @php
        $isEdit = isset($campaign);
        $defaultRef = 'CMP-IA-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $statuses = [
            'actif'     => 'Active / En diffusion',
            'planifie'  => 'Planifiée',
            'brouillon' => 'Brouillon / En création',
            'termine'   => 'Terminée',
            'en_pause'  => 'En pause',
        ];

        $availableChannels = [
            'facebook'  => 'Facebook Ads',
            'instagram' => 'Instagram',
            'linkedin'  => 'LinkedIn Ads',
            'google'    => 'Google Search / Display',
            'tiktok'    => 'TikTok Ads',
            'whatsapp'  => 'WhatsApp Business',
        ];

        $selectedChannels = old('channels', $campaign->channels ?? ['facebook', 'instagram']);
        if (!is_array($selectedChannels)) {
            $selectedChannels = (array) $selectedChannels;
        }
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex items-center justify-between">
            <div>
                <a href="{{ route('tech.campaigns.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux campagnes IA</span>
                </a>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                    {{ $isEdit ? 'Modifier la Campagne ' . $campaign->reference : 'Nouvelle Campagne Agent IA Publicitaire' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Génération de visuels, rédaction publicitaire automatique et diffusion multi-canaux.
                </p>
            </div>
        </div>

        <form 
            action="{{ $isEdit ? route('tech.campaigns.update', $campaign) : route('tech.campaigns.store') }}" 
            method="POST" 
            class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E2E8F0] shadow-xs space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1 : Informations Générales -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">1. Identification & Client</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block font-semibold text-[#0B0F14] mb-1">Référence Campagne *</label>
                        <input 
                            id="reference" 
                            type="text" 
                            name="reference" 
                            value="{{ old('reference', $campaign->reference ?? $defaultRef) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono font-semibold text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('reference') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="name" class="block font-semibold text-[#0B0F14] mb-1">Nom de la Campagne *</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            placeholder="Ex: Campagne Rentrée Scolaire, Promo Black Friday..."
                            value="{{ old('name', $campaign->name ?? '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required 
                        />
                        @error('name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block font-semibold text-[#0B0F14] mb-1">Client Associé</label>
                        <select 
                            id="customer_id" 
                            name="customer_id" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                        >
                            <option value="">Campagne interne / IVOSPHERE</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $campaign->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company ? '— ' . $c->company : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="status" class="block font-semibold text-[#0B0F14] mb-1">Statut de Diffusion *</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                            required
                        >
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $campaign->status ?? 'brouillon') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2 : Canaux & Budget -->
            <div class="border-t border-[#E2E8F0] pt-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">2. Canaux de Diffusion & Budget</h3>
                
                <div>
                    <label class="block font-semibold text-[#0B0F14] mb-2">Canaux Publicitaires Ciblés</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach($availableChannels as $key => $label)
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E2E8F0] bg-[#F5F7FA] hover:bg-white cursor-pointer select-none transition-colors">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="{{ $key }}" 
                                    {{ in_array($key, $selectedChannels) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-[#0066FF] focus:ring-[#0066FF]"
                                >
                                <span class="font-medium text-[#0B0F14] text-xs">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label for="budget" class="block font-semibold text-[#0B0F14] mb-1">Budget Ads Alloué (FCFA)</label>
                        <input 
                            id="budget" 
                            type="number" 
                            step="500" 
                            min="0" 
                            name="budget" 
                            value="{{ old('budget', $campaign->budget ?? 0) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] font-mono text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('budget') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="start_date" class="block font-semibold text-[#0B0F14] mb-1">Date de Lancement</label>
                        <input 
                            id="start_date" 
                            type="date" 
                            name="start_date" 
                            value="{{ old('start_date', isset($campaign->start_date) ? $campaign->start_date->format('Y-m-d') : date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('start_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block font-semibold text-[#0B0F14] mb-1">Date de Fin</label>
                        <input 
                            id="end_date" 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date', isset($campaign->end_date) ? $campaign->end_date->format('Y-m-d') : '') }}" 
                            class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]" 
                        />
                        @error('end_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3 : Brief & Objectif de Campagne -->
            <div class="border-t border-[#E2E8F0] pt-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">3. Brief Créatif pour l'Agent IA</h3>
                <div>
                    <label for="brief" class="block font-semibold text-[#0B0F14] mb-1">Brief / Instructions de Génération IA</label>
                    <textarea 
                        id="brief" 
                        name="brief" 
                        rows="4" 
                        placeholder="Description du produit ou service, cible visée, ton souhaité (professionnel, chaleureux, promotionnel), offre spéciale, hashtags..." 
                        class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF] focus:border-[#0066FF]"
                    >{{ old('brief', $campaign->brief ?? '') }}</textarea>
                    @error('brief') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Boutons d'Action -->
            <div class="border-t border-[#E2E8F0] pt-6 flex items-center justify-between">
                <a 
                    href="{{ route('tech.campaigns.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-slate-50 font-medium transition-colors"
                >
                    Annuler
                </a>

                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-semibold shadow-xs hover:shadow-sm active:scale-98 transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>{{ $isEdit ? 'Mettre à jour la campagne' : 'Lancer la campagne IA' }}</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.app>
