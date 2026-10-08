<x-layouts.app>
    <x-slot:title>Devis {{ $quotation->reference }} — IVOSPHERE ERP</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="print:hidden">
            <x-page-header 
                title="Devis : {{ $quotation->reference }}" 
                description="Proposition commerciale émise le {{ $quotation->date->format('d/m/Y') }} pour {{ $quotation->customer->name ?? 'Client' }}"
            >
                <x-slot:breadcrumbs>
                    <x-breadcrumb :items="[
                        ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                        ['label' => 'Devis', 'url' => route('commercial.quotations.index')],
                        ['label' => $quotation->reference]
                    ]" />
                </x-slot:breadcrumbs>

                <x-slot:actions>
                    @if($quotation->status !== 'accepté')
                        <form action="{{ route('commercial.quotations.convert', $quotation) }}" method="POST" class="inline-block">
                            @csrf
                            <x-button variant="primary" type="submit" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Convertir en Commande</span>
                            </x-button>
                        </form>
                    @endif

                    <a href="{{ route('commercial.quotations.print', $quotation) }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] hover:bg-[#F5F7FA] text-[#0B0F14] text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Imprimer</span>
                    </a>

                    <x-button variant="secondary" href="{{ route('commercial.quotations.edit', $quotation) }}">
                        Modifier
                    </x-button>
                </x-slot:actions>
            </x-page-header>
        </div>

        <!-- Fiche Proforma -->
        <x-card>
            <div class="flex flex-col sm:flex-row justify-between items-start border-b border-[#E2E8F0] pb-6 mb-6 gap-4">
                <div>
                    <span class="text-xs font-bold text-[#0066FF] uppercase tracking-wider">Proposition Commerciale</span>
                    <h2 class="text-2xl font-bold text-[#0B0F14] mt-0.5">DEVIS PROFORMA</h2>
                    <p class="text-xs font-mono text-[#64748B] mt-1">{{ $quotation->reference }}</p>
                </div>
                <div class="sm:text-right">
                    <p class="font-bold text-[#0B0F14] text-sm tracking-tight">IVOSPHERE ERP</p>
                    <p class="text-xs text-[#64748B]">Abidjan, Côte d'Ivoire</p>
                    <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14]">
                        Statut : {{ ucfirst($quotation->status) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 text-xs">
                <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <p class="text-[10px] font-bold uppercase text-[#64748B] tracking-wider mb-1.5">Destinataire (Client)</p>
                    <p class="font-bold text-[#0B0F14] text-sm">{{ $quotation->customer->name ?? 'Client Particulier' }}</p>
                    <p class="text-[#64748B] mt-0.5">{{ $quotation->customer->address ?? 'Adresse non renseignée' }}</p>
                    <p class="text-[#64748B]">{{ $quotation->customer->phone ?? '' }}</p>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] sm:text-right">
                    <p class="text-[10px] font-bold uppercase text-[#64748B] tracking-wider mb-1.5">Informations Document</p>
                    <p class="text-[#64748B]">Date d'émission : <span class="font-bold text-[#0B0F14]">{{ $quotation->date->format('d/m/Y') }}</span></p>
                    <p class="text-[#64748B] mt-0.5">Validité de l'offre : <span class="font-medium text-[#0B0F14]">30 jours</span></p>
                </div>
            </div>

            <!-- Table des lignes Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0] mb-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Désignation</th>
                            <th class="py-3 px-4 text-center">Quantité</th>
                            <th class="py-3 px-4 text-right">Prix Unitaire</th>
                            <th class="py-3 px-4 text-right">TVA</th>
                            <th class="py-3 px-4 text-right">Total HT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @foreach($quotation->items as $item)
                            <tr class="hover:bg-[#F5F7FA]/50 transition">
                                <td class="py-3 px-4 font-medium">{{ $item->description }}</td>
                                <td class="py-3 px-4 text-center text-[#64748B]">{{ $item->quantity }}</td>
                                <td class="py-3 px-4 text-right text-[#64748B]">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                <td class="py-3 px-4 text-right text-[#64748B]">{{ $item->tax_rate }}%</td>
                                <td class="py-3 px-4 text-right font-bold">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cartes Articles Mobile (< md) -->
            <div class="block md:hidden space-y-2.5 mb-6">
                @foreach($quotation->items as $item)
                    <div class="p-3 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0] space-y-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <h5 class="font-bold text-[#0B0F14] text-xs">{{ $item->description }}</h5>
                            <span class="font-bold text-[#0066FF] text-xs shrink-0">{{ number_format($item->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-[#64748B]">
                            <span>Quantité : <strong class="text-[#0B0F14]">{{ $item->quantity }}</strong></span>
                            <span>P.U : <strong class="text-[#0B0F14]">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</strong></span>
                            <span>TVA : <strong class="text-[#0B0F14]">{{ $item->tax_rate }}%</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Récapitulatif financier -->
            <div class="flex justify-end">
                <div class="w-72 space-y-2 text-xs bg-[#F5F7FA] p-4 rounded-xl border border-[#E2E8F0]">
                    <div class="flex justify-between text-[#64748B]">
                        <span>Total Hors Taxes (HT)</span>
                        <span class="font-semibold text-[#0B0F14]">{{ number_format($quotation->subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-[#64748B]">
                        <span>Montant TVA</span>
                        <span class="font-semibold text-[#0B0F14]">{{ number_format($quotation->tax_amount, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-[#0B0F14] pt-2 border-t border-[#E2E8F0]">
                        <span>Total TTC</span>
                        <span class="text-[#0066FF]">{{ number_format($quotation->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
