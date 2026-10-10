<x-layouts.app>
    <x-slot:title>Catalogue & QR Codes Produits — Commercial IVOSPHERE</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Catalogue & Articles de Vente" 
            description="Gestion des références, codes-barres EAN intégrés, génération et téléchargement des QR Codes en PNG"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Catalogue & QR Codes']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('commercial.products.print-catalog', request()->query()) }}" target="_blank" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Imprimer Catalogue (A4 Paysage)</span>
                </x-button>

                <x-button variant="secondary" href="{{ route('commercial.pos.index') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Vente POS</span>
                </x-button>

                <x-button variant="primary" href="{{ route('commercial.products.create') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Nouveau Produit</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        {{-- Barre de Recherche & Filtres --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-3 sm:p-4 shadow-2xs">
            <form method="GET" action="{{ route('commercial.products.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Rechercher par nom, référence SKU ou code EAN..." 
                        class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/50 text-[#0B0F14] placeholder-slate-400 focus:outline-none focus:border-[#0066FF] focus:bg-white transition"
                    >
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select name="domain_id" onchange="this.form.submit()" class="h-9 px-3 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0066FF]">
                        <option value="">Tous les domaines</option>
                        @foreach($domains as $dom)
                            <option value="{{ $dom->id }}" @selected(request('domain_id') == $dom->id)>{{ $dom->name }}</option>
                        @endforeach
                    </select>

                    <select name="category_id" onchange="this.form.submit()" class="h-9 px-3 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:outline-none focus:border-[#0066FF]">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    @if(request()->hasAny(['search', 'domain_id', 'category_id']))
                        <a href="{{ route('commercial.products.index') }}" class="h-9 px-3 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-800 text-xs font-semibold inline-flex items-center gap-1 transition">
                            <span>Réinitialiser</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <x-card :noPadding="true">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Référence SKU & EAN</th>
                            <th class="py-3.5 px-4">Désignation</th>
                            <th class="py-3.5 px-4">Domaine & Catégorie</th>
                            <th class="py-3.5 px-4 text-right">Prix Vente</th>
                            <th class="py-3.5 px-4 text-center">Code QR & EAN</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($products as $p)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="font-bold text-[#0066FF]">{{ $p->sku }}</div>
                                    <div class="text-[10px] text-slate-500 tracking-wider font-semibold flex items-center gap-1 mt-0.5">
                                        <span class="text-slate-400">EAN:</span> {{ $p->formatted_ean }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#0B0F14]">
                                    <div class="font-bold text-sm text-[#0B0F14]">{{ $p->name }}</div>
                                    <div class="text-[11px] text-[#64748B]">
                                        {{ $p->brand?->name ?? 'IVOSPHERE' }} • Unité : {{ $p->unit ?? 'pièce' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                                        {{ strtoupper($p->domain?->name ?? 'Général') }}
                                    </span>
                                    <div class="text-[11px] text-[#64748B] mt-0.5">
                                        {{ $p->category->name ?? 'Général' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-[#0B0F14]">
                                    <div>{{ number_format((float) $p->selling_price, 0, ',', ' ') }} FCFA</div>
                                    <div class="text-[10px] font-normal text-slate-400">TVA {{ $p->tax_rate ?? 18 }}%</div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Bouton pour afficher le QR Code dans le Modal --}}
                                        <button 
                                            type="button" 
                                            @click="$dispatch('open-product-qr', { 
                                                id: {{ $p->id }}, 
                                                name: '{{ addslashes($p->name) }}', 
                                                sku: '{{ $p->sku }}', 
                                                barcode: '{{ $p->ean }}', 
                                                price: '{{ number_format((float) $p->selling_price, 0, ',', ' ') }} FCFA', 
                                                domain: '{{ addslashes($p->domain?->name ?? 'Général') }}', 
                                                unit: '{{ addslashes($p->unit ?? 'pièce') }}',
                                                qrDownloadUrl: '{{ route('commercial.products.qr-download', $p) }}',
                                                labelDownloadUrl: '{{ route('commercial.products.qr-download', ['product' => $p, 'label' => 1]) }}'
                                            })"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:border-[#0066FF] hover:text-[#0066FF] text-slate-700 text-xs font-semibold shadow-2xs transition-colors"
                                            title="Afficher le Code QR et barcode EAN"
                                        >
                                            <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                            </svg>
                                            <span>Voir QR</span>
                                        </button>

                                        {{-- Bouton téléchargement direct PNG --}}
                                        <a 
                                            href="{{ route('commercial.products.qr-download', $p) }}" 
                                            class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-blue-50 hover:border-blue-300 text-slate-600 hover:text-[#0066FF] transition-colors" 
                                            title="Télécharger le QR Code en PNG"
                                            download
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('commercial.products.edit', $p) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun produit au catalogue"
                                        description="Ajoutez des articles et prestations à votre catalogue pour les intégrer aux devis et ventes POS."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" href="{{ route('commercial.products.create') }}">
                                                Ajouter un produit
                                            </x-button>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($products as $p)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono font-bold text-xs text-[#0066FF] px-2 py-0.5 rounded bg-blue-50 border border-blue-200">
                                {{ $p->sku }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-500 font-semibold px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                EAN: {{ $p->formatted_ean }}
                            </span>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-[#0B0F14] leading-snug">{{ $p->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Catégorie : <span class="font-medium text-slate-700">{{ $p->category->name ?? 'Général' }}</span> • 
                                {{ $p->domain?->name ?? 'Commercial' }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Prix de Vente</span>
                                <span class="text-base font-extrabold text-[#0B0F14]">{{ number_format((float) $p->selling_price, 0, ',', ' ') }} FCFA</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                {{-- Bouton Modal QR Mobile --}}
                                <button 
                                    type="button" 
                                    @click="$dispatch('open-product-qr', { 
                                        id: {{ $p->id }}, 
                                        name: '{{ addslashes($p->name) }}', 
                                        sku: '{{ $p->sku }}', 
                                        barcode: '{{ $p->ean }}', 
                                        price: '{{ number_format((float) $p->selling_price, 0, ',', ' ') }} FCFA', 
                                        domain: '{{ addslashes($p->domain?->name ?? 'Général') }}', 
                                        unit: '{{ addslashes($p->unit ?? 'pièce') }}',
                                        qrDownloadUrl: '{{ route('commercial.products.qr-download', $p) }}',
                                        labelDownloadUrl: '{{ route('commercial.products.qr-download', ['product' => $p, 'label' => 1]) }}'
                                    })"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-[#0066FF] text-xs font-semibold shadow-2xs"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    <span>QR Code</span>
                                </button>

                                <a href="{{ route('commercial.products.edit', $p) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8">
                        <x-empty-state 
                            title="Aucun produit au catalogue"
                            description="Ajoutez des articles et prestations à votre catalogue pour les intégrer aux devis et ventes POS."
                        />
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $products->links() }}
                </div>
            @endif
        </x-card>
    </div>

    {{-- Modal QR Code Produit --}}
    <x-product-qr-modal />

    {{-- Modal Prise de Photo Directe & Upload Image Produit --}}
    <x-product-quick-photo-modal />
</x-layouts.app>
