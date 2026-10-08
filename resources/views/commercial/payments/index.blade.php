<x-layouts.app>
    <x-slot:title>Journal des Règlements — IVOSPHERE ERP</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Paiements & Encaissements" 
            description="Journal d'audit des flux de règlements clients et encaissements de factures"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Règlements']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Référence</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Facture Liée</th>
                            <th class="py-3 px-4">Client</th>
                            <th class="py-3 px-4">Mode de Paiement</th>
                            <th class="py-3 px-4 text-right">Montant Encaissé</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($payments as $p)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono font-medium text-[#0B0F14]">
                                    {{ $p->reference }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-[#64748B]">
                                    {{ $p->date?->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($p->invoice)
                                        <a href="{{ route('commercial.invoices.show', $p->invoice) }}" class="font-bold text-[#0066FF] hover:underline font-mono">
                                            {{ $p->invoice->reference }}
                                        </a>
                                    @else
                                        <span class="text-[#64748B]">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#0B0F14]">
                                    {{ $p->customer->name ?? 'Client Particulier' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] capitalize">
                                        {{ str_replace('_', ' ', $p->method) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-emerald-700">
                                    + {{ number_format($p->amount, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun règlement enregistré"
                                        description="Les paiements enregistrés sur les factures et encaissements POS s'afficheront ici."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($payments as $p)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-mono font-bold text-[#0B0F14] text-xs">{{ $p->reference }}</span>
                                <h4 class="font-semibold text-[#0B0F14] text-xs mt-0.5">{{ $p->customer->name ?? 'Client Particulier' }}</h4>
                            </div>
                            <span class="font-mono text-[11px] text-[#64748B]">{{ $p->date?->format('d/m/Y') }}</span>
                        </div>

                        <!-- 2-col info grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Mode</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-white text-[#0B0F14] border border-[#E2E8F0] capitalize">
                                    {{ str_replace('_', ' ', $p->method) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Facture Liée</span>
                                @if($p->invoice)
                                    <a href="{{ route('commercial.invoices.show', $p->invoice) }}" class="font-bold text-[#0066FF] hover:underline font-mono text-xs block">
                                        {{ $p->invoice->reference }}
                                    </a>
                                @else
                                    <span class="text-[#64748B] text-xs">—</span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between">
                            <span class="text-[10px] text-[#64748B] uppercase font-bold">Montant Encaissé</span>
                            <span class="text-sm font-bold text-emerald-700">
                                + {{ number_format($p->amount, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun règlement enregistré"
                        description="Les paiements enregistrés sur les factures et encaissements POS s'afficheront ici."
                    />
                @endforelse
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $payments->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
