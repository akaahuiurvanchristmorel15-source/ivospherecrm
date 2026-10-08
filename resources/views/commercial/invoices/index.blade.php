<x-layouts.app>
    <x-slot:title>Facturation Clients — IVOSPHERE ERP</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Factures de Vente" 
            description="Gestion de la facturation, créances clients, échéances et encaissements"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Factures']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" href="{{ route('commercial.invoices.create') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Nouvelle Facture</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card :noPadding="true">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Référence</th>
                            <th class="py-3 px-4">Client</th>
                            <th class="py-3 px-4">Date / Échéance</th>
                            <th class="py-3 px-4">Total TTC</th>
                            <th class="py-3 px-4">Reste à payer</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($invoices as $i)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono">
                                    <a href="{{ route('commercial.invoices.show', $i) }}" class="font-bold text-[#0066FF] hover:underline">
                                        {{ $i->reference }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#0B0F14]">
                                    {{ $i->customer->name ?? 'Client Particulier' }}
                                </td>
                                <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">
                                    <div>{{ $i->date?->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-[#64748B]/70">Éch: {{ $i->due_date ? $i->due_date->format('d/m/Y') : '—' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#0B0F14]">
                                    {{ number_format($i->total, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="py-3.5 px-4 font-bold {{ $i->remaining > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ number_format($i->remaining, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $stNorm = strtolower($i->status);
                                        $stBadge = match(true) {
                                            str_contains($stNorm, 'payee') || str_contains($stNorm, 'payée') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            str_contains($stNorm, 'partiel') => 'bg-amber-50 text-amber-700 border-amber-200',
                                            str_contains($stNorm, 'annul') => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $stBadge }}">
                                        {{ str_replace('_', ' ', ucfirst($i->status)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('commercial.invoices.show', $i) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Consulter">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('commercial.invoices.print', $i) }}" target="_blank" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0066FF] hover:bg-[#0066FF]/5 transition border border-transparent hover:border-[#0066FF]/20" title="Imprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12">
                                    <x-empty-state 
                                        title="Aucune facture émise"
                                        description="Toutes les factures de vente apparaîtront dans cette liste."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" href="{{ route('commercial.invoices.create') }}">
                                                Créer une facture
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
                @forelse($invoices as $i)
                    @php
                        $stNorm = strtolower($i->status);
                        $stBadge = match(true) {
                            str_contains($stNorm, 'payee') || str_contains($stNorm, 'payée') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            str_contains($stNorm, 'partiel') => 'bg-amber-50 text-amber-700 border-amber-200',
                            str_contains($stNorm, 'annul') => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-[#F5F7FA] text-[#64748B] border-[#E2E8F0]'
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('commercial.invoices.show', $i) }}" class="font-mono font-bold text-sm text-[#0066FF] hover:underline flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>{{ $i->reference }}</span>
                            </a>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $stBadge }}">
                                {{ str_replace('_', ' ', ucfirst($i->status)) }}
                            </span>
                        </div>

                        <div>
                            <p class="text-[11px] text-slate-400 font-medium">Client</p>
                            <p class="text-sm font-semibold text-[#0B0F14] leading-snug">{{ $i->customer->name ?? 'Client Particulier' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total TTC</span>
                                <span class="text-xs font-bold text-[#0B0F14]">{{ number_format($i->total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Reste à payer</span>
                                <span class="text-xs font-bold {{ $i->remaining > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ number_format($i->remaining, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Émise le {{ $i->date?->format('d/m/Y') }}
                            </span>
                            <span>Éch: <span class="font-medium text-slate-700">{{ $i->due_date ? $i->due_date->format('d/m/Y') : '—' }}</span></span>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <a href="{{ route('commercial.invoices.print', $i) }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200 transition">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Imprimer</span>
                            </a>
                            <a href="{{ route('commercial.invoices.show', $i) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Consulter</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8">
                        <x-empty-state 
                            title="Aucune facture émise"
                            description="Toutes les factures de vente apparaîtront dans cette liste."
                        />
                    </div>
                @endforelse
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $invoices->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
