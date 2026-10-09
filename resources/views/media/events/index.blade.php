<x-layouts.app :title="'Événements & Célébrations — MEDIA'">
    <x-slot name="header">
        <x-page-header 
            title="Organisation Événementielle (MEDIA & EVENTS)" 
            subtitle="Conception, régie, couverture audiovisuelle et gestion logistique de cérémonies et conférences">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'MEDIA', 'url' => route('media.index')],
                    ['label' => 'Événements']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('media.events.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvel Événement
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
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Réf. / Intitulé</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Client Organisateur</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Type & Lieu</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date(s)</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Budget Estimé</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($events as $event)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-xs font-semibold text-[#0066FF] block">{{ $event->reference }}</span>
                                <span class="text-sm font-bold text-[#0B0F14]">{{ $event->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-sm font-medium text-[#0B0F14]">
                                {{ $event->customer->name ?? 'Client Particulier' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                <span class="font-semibold block">{{ $event->type ?? 'Général' }}</span>
                                <span class="text-[#64748B]">{{ $event->location ?? 'Non défini' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                {{ $event->date ? \Carbon\Carbon::parse($event->date)->format('d/m/Y') : '-' }}
                                @if($event->end_date && $event->end_date !== $event->date)
                                    &rarr; {{ \Carbon\Carbon::parse($event->end_date)->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">
                                {{ number_format($event->budget, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badge = match($event->status) {
                                        'clôturé', 'termine' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'en_cours' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'annulé' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badge }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('media.events.show', $event) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Détails</a>
                                    <a href="{{ route('media.events.edit', $event) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    title="Aucun événement planifié" 
                                    description="Organisez des mariages, conférences, séminaires ou concerts avec régie complète."
                                    action-label="Nouvel événement"
                                    :action-url="route('media.events.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($events as $event)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono font-bold text-[#0066FF] text-xs">{{ $event->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $event->name }}</h4>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded font-semibold border bg-blue-50 text-[#0066FF] border-blue-200">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-[#64748B]">
                        <span>Client : {{ $event->customer->name ?? 'Particulier' }}</span> &bull; 
                        <span>{{ $event->location ?? 'Lieu non défini' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-[#E2E8F0] text-xs">
                        <span class="font-bold text-[#0066FF]">{{ number_format($event->budget, 0, ',', ' ') }} FCFA</span>
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('media.events.show', $event) }}" class="font-semibold text-[#0066FF]">Voir</a>
                            &bull;
                            <a href="{{ route('media.events.edit', $event) }}" class="text-[#64748B]">Modifier</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[#64748B]">
                    Aucun événement pour le moment.
                </div>
            @endforelse
        </div>

        @if($events->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
