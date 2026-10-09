<x-layouts.app :title="'Locations de Matériel — MEDIA'">
    <x-slot name="header">
        <x-page-header 
            title="Locations de Matériel Audiovisuel (MEDIA)" 
            subtitle="Contrats de mise à disposition, cautions consignées, état des lieux et retours de matériel">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'MEDIA', 'url' => route('media.index')],
                    ['label' => 'Locations']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('media.rentals.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle Location
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="rounded-xl bg-white border border-[#E2E8F0] shadow-xs overflow-hidden">
        <!-- Vue Table Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Contrat / Réf.</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Matériel Loué</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Locataire / Client</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Période de Location</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Caution</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Montant Total</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($rentals as $rental)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#0066FF]">{{ $rental->reference }}</td>
                            <td class="py-3.5 px-4 text-sm font-semibold text-[#0B0F14]">{{ $rental->equipment->name ?? 'Équipement' }}</td>
                            <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14]">{{ $rental->customer->name ?? 'Client Particulier' }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                {{ \Carbon\Carbon::parse($rental->start_date)->format('d/m/Y') }} &rarr; {{ \Carbon\Carbon::parse($rental->end_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#64748B] text-right font-medium">
                                {{ number_format($rental->deposit, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">
                                {{ number_format($rental->total, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badge = match($rental->status) {
                                        'retourné', 'termine' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'en_retard' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badge }}">
                                    {{ ucfirst($rental->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    @if($rental->status !== 'retourné' && $rental->status !== 'termine')
                                        <form action="{{ route('media.rentals.return', $rental) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:underline">
                                                Enregistrer Retour
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('media.rentals.edit', $rental) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    title="Aucune location enregistrée" 
                                    description="Enregistrez les sorties de caméras, microphones, drones et projecteurs."
                                    action-label="Nouvelle location"
                                    :action-url="route('media.rentals.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($rentals as $rental)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono font-bold text-[#0066FF] text-xs">{{ $rental->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $rental->equipment->name ?? 'Équipement' }}</h4>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded font-semibold border bg-blue-50 text-[#0066FF] border-blue-200">
                            {{ ucfirst($rental->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-[#64748B]">
                        <span>Client : {{ $rental->customer->name ?? 'Particulier' }}</span> &bull; 
                        <span>{{ \Carbon\Carbon::parse($rental->start_date)->format('d/m') }} au {{ \Carbon\Carbon::parse($rental->end_date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-[#E2E8F0] text-xs">
                        <span class="font-bold text-[#0B0F14]">{{ number_format($rental->total, 0, ',', ' ') }} FCFA</span>
                        <div class="inline-flex items-center gap-2">
                            @if($rental->status !== 'retourné')
                                <form action="{{ route('media.rentals.return', $rental) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-semibold text-emerald-600">Retourner</button>
                                </form>
                                &bull;
                            @endif
                            <a href="{{ route('media.rentals.edit', $rental) }}" class="text-[#64748B]">Modifier</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[#64748B]">
                    Aucune location pour le moment.
                </div>
            @endforelse
        </div>

        @if($rentals->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $rentals->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
