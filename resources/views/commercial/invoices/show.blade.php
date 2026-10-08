<x-layouts.app>
    <x-slot:title>Facture {{ $invoice->reference }} — IVOSPHERE ERP</x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="print:hidden">
            <x-page-header 
                title="Facture : {{ $invoice->reference }}" 
                description="Facture client émise le {{ $invoice->date->format('d/m/Y') }} pour {{ $invoice->customer->name ?? 'Client' }}"
            >
                <x-slot:breadcrumbs>
                    <x-breadcrumb :items="[
                        ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                        ['label' => 'Factures', 'url' => route('commercial.invoices.index')],
                        ['label' => $invoice->reference]
                    ]" />
                </x-slot:breadcrumbs>

                <x-slot:actions>
                    <a href="{{ route('commercial.invoices.print', $invoice) }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] hover:bg-[#F5F7FA] text-[#0B0F14] text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Imprimer la Facture</span>
                    </a>
                </x-slot:actions>
            </x-page-header>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 print:block">
            <!-- Colonne Document Facture (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                <x-card>
                    <div class="flex flex-col sm:flex-row justify-between items-start border-b border-[#E2E8F0] pb-6 mb-6 gap-4">
                        <div>
                            <span class="text-xs font-bold text-[#0066FF] uppercase tracking-wider">Document Comptable</span>
                            <h2 class="text-2xl font-bold text-[#0B0F14] mt-0.5">FACTURE CLIENT</h2>
                            <p class="text-xs font-mono text-[#64748B] mt-1">{{ $invoice->reference }}</p>
                        </div>
                        <div class="sm:text-right text-xs">
                            <p class="font-bold text-[#0B0F14] text-sm">IVOSPHERE ERP</p>
                            <p class="text-[#64748B]">Abidjan, Côte d'Ivoire</p>
                            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14]">
                                Statut : {{ str_replace('_', ' ', ucfirst($invoice->status)) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-xs">
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                            <p class="text-[10px] font-bold uppercase text-[#64748B] tracking-wider mb-1.5">Facturé à</p>
                            <p class="font-bold text-[#0B0F14] text-sm">{{ $invoice->customer->name ?? 'Client Particulier' }}</p>
                            <p class="text-[#64748B] mt-0.5">{{ $invoice->customer->address ?? 'Adresse non renseignée' }}</p>
                            <p class="text-[#64748B]">{{ $invoice->customer->phone ?? '' }}</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] sm:text-right">
                            <p class="text-[10px] font-bold uppercase text-[#64748B] tracking-wider mb-1.5">Dates & Échéance</p>
                            <p class="text-[#64748B]">Date de facturation : <span class="font-bold text-[#0B0F14]">{{ $invoice->date->format('d/m/Y') }}</span></p>
                            <p class="text-[#64748B] mt-0.5">Date d'échéance : <span class="font-medium text-[#0B0F14]">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'À réception' }}</span></p>
                        </div>
                    </div>

                    <!-- Table des articles Desktop (>= md) -->
                    <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0] mb-6">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                                <tr>
                                    <th class="py-3 px-4">Description</th>
                                    <th class="py-3 px-4 text-center">Quantité</th>
                                    <th class="py-3 px-4 text-right">Prix Unitaire</th>
                                    <th class="py-3 px-4 text-right">Total HT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                                @foreach($invoice->items as $item)
                                    <tr class="hover:bg-[#F5F7FA]/50 transition">
                                        <td class="py-3 px-4 font-medium">{{ $item->description }}</td>
                                        <td class="py-3 px-4 text-center text-[#64748B]">{{ $item->quantity }}</td>
                                        <td class="py-3 px-4 text-right text-[#64748B]">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                        <td class="py-3 px-4 text-right font-bold">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Cartes Articles Mobile (< md) -->
                    <div class="block md:hidden space-y-2.5 mb-6">
                        @foreach($invoice->items as $item)
                            <div class="p-3 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0] space-y-1.5">
                                <div class="flex items-start justify-between gap-2">
                                    <h5 class="font-bold text-[#0B0F14] text-xs">{{ $item->description }}</h5>
                                    <span class="font-bold text-[#0066FF] text-xs shrink-0">{{ number_format($item->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-[#64748B]">
                                    <span>Quantité : <strong class="text-[#0B0F14]">{{ $item->quantity }}</strong></span>
                                    <span>P.U : <strong class="text-[#0B0F14]">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Récapitulatif financier -->
                    <div class="flex justify-end">
                        <div class="w-72 space-y-2 text-xs bg-[#F5F7FA] p-4 rounded-xl border border-[#E2E8F0]">
                            <div class="flex justify-between text-[#64748B]">
                                <span>Total HT</span>
                                <span class="font-semibold text-[#0B0F14]">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-[#64748B]">
                                <span>Taxes (TVA)</span>
                                <span class="font-semibold text-[#0B0F14]">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-[#0B0F14] pt-2 border-t border-[#E2E8F0]">
                                <span>Total TTC</span>
                                <span class="text-[#0066FF]">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-xs font-semibold text-emerald-700 pt-1">
                                <span>Montant Réglé</span>
                                <span>- {{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-base font-bold text-[#0B0F14] pt-2 border-t border-[#E2E8F0]">
                                <span>Reste à payer</span>
                                <span class="{{ $invoice->remaining > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ number_format($invoice->remaining, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Colonne Actions & Règlements (4 cols) -->
            <div class="lg:col-span-4 space-y-6 print:hidden">
                @if($invoice->remaining > 0)
                    <x-card title="Enregistrer un Paiement">
                        <form action="{{ route('commercial.payments.store') }}" method="POST" class="space-y-4 text-xs">
                            @csrf
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                            
                            <div>
                                <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date du règlement *</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Montant à encaisser *</label>
                                <input type="number" step="0.01" name="amount" value="{{ $invoice->remaining }}" max="{{ $invoice->remaining }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] font-bold focus:outline-none focus:border-[#0066FF] text-xs transition">
                                <span class="text-[10px] text-[#64748B] block mt-1">Solde restant : {{ number_format($invoice->remaining, 0, ',', ' ') }} FCFA</span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Mode de règlement *</label>
                                <select name="method" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                    <option value="especes">Espèces (Caisse)</option>
                                    <option value="virement">Virement bancaire</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="mobile_money">Mobile Money (Orange / MTN / Moov / Wave)</option>
                                    <option value="carte">Carte bancaire</option>
                                </select>
                            </div>

                            <x-button variant="primary" type="submit" class="w-full justify-center">
                                Encaisser le règlement
                            </x-button>
                        </form>
                    </x-card>
                @endif

                @if($invoice->payments->count() > 0)
                    <x-card title="Historique des Règlements">
                        <div class="space-y-2.5 text-xs">
                            @foreach($invoice->payments as $payment)
                                <div class="p-3 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0]">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-[#0B0F14]">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span>
                                        <span class="text-[#64748B] font-mono text-[11px]">{{ $payment->date->format('d/m/Y') }}</span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] mt-1 capitalize">
                                        {{ str_replace('_', ' ', $payment->method) }} &bull; Ref: {{ $payment->reference }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
