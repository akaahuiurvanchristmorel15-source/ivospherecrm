<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Travaux d'Impression (PRINT)" 
            subtitle="Suivi de la chaîne de fabrication, tirages machines, reliure et façonnage">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'PRINT', 'url' => route('print.index')],
                    ['label' => 'Travaux']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('print.jobs.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Travail
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Référence</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Client</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Type de tirage</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Format</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Quantité</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant Total</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($jobs as $job)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#0066FF]">{{ $job->reference }}</td>
                            <td class="py-3.5 px-4 text-sm font-semibold text-[#0B0F14]">{{ $job->customer->name ?? 'Client comptoir' }}</td>
                            <td class="py-3.5 px-4 text-xs uppercase font-medium text-[#64748B]">{{ $job->type }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">{{ $job->format->name ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14] text-right">{{ number_format($job->quantity, 0, ',', ' ') }}</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">{{ number_format($job->total, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $statusBadge = match($job->status) {
                                        'livré', 'terminé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'en_production' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                        default => 'bg-amber-50 text-amber-700 border-amber-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('print.jobs.show', $job) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Détails</a>
                                    <a href="{{ route('print.jobs.edit', $job) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    title="Aucun travail d'impression" 
                                    description="Aucune commande d'impression n'est actuellement enregistrée."
                                    action-label="Nouveau travail"
                                    :action-url="route('print.jobs.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($jobs as $job)
                @php
                    $statusBadge = match($job->status) {
                        'livré', 'terminé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'en_production' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200'
                    };
                @endphp
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-[#0066FF] text-xs">{{ $job->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $job->customer->name ?? 'Client comptoir' }}</h4>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border shrink-0 {{ $statusBadge }}">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>

                    <div class="text-xs text-[#64748B]">
                        <span>Tirage :</span>
                        <strong class="text-[#0B0F14] uppercase font-mono">{{ $job->type }}</strong>
                        @if($job->format)
                            &bull; <span>{{ $job->format->name }}</span>
                        @endif
                    </div>

                    <!-- 2-col quantity & total grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Quantité</span>
                            <span class="font-bold text-[#0B0F14]">{{ number_format($job->quantity, 0, ',', ' ') }} ex.</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Montant Total</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($job->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    <!-- Actions bar -->
                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                        <a href="{{ route('print.jobs.show', $job) }}" class="flex-1 py-2 px-3 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Détails & Atelier</span>
                        </a>
                        <a href="{{ route('print.jobs.edit', $job) }}" class="py-2 px-3 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Modifier</span>
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucun travail d'impression" 
                    description="Aucune commande d'impression n'est actuellement enregistrée."
                    action-label="Nouveau travail"
                    :action-url="route('print.jobs.create')"
                />
            @endforelse
        </div>

        @if($jobs->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
