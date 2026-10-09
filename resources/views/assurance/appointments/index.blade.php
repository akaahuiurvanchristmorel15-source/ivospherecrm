<x-layouts.app :title="'Rendez-vous Conseil — ASSURANCE'">
    <x-slot name="header">
        <x-page-header 
            title="Rendez-vous Conseil & Souscription (ASSURANCE)" 
            subtitle="Entretiens d'audit des risques, bilan prévoyance et conseil en couverture assurantielle">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'ASSURANCE', 'url' => route('assurance.index')],
                    ['label' => 'Rendez-vous']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('assurance.appointments.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau RDV
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
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Prospect / Client</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Conseiller Dédié</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Date & Heure</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Notes / Objet</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 text-sm font-semibold text-[#0B0F14]">
                                {{ $app->customer->name ?? 'Prospect non enregistré' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                {{ $app->advisor->name ?? 'Équipe Conseil' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">
                                {{ $app->date ? \Carbon\Carbon::parse($app->date)->format('d/m/Y') : '-' }}
                                @if($app->time)
                                    <span class="text-[#64748B] font-mono">({{ substr($app->time, 0, 5) }})</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#64748B] max-w-xs truncate">
                                {{ $app->notes ?? 'Entretien conseil' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badge = match($app->status) {
                                        'effectué', 'termine' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'annule' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $badge }}">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('assurance.appointments.edit', $app) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    title="Aucun rendez-vous planifié" 
                                    description="Planifiez des séances de conseil et d'audit pour vos prospects et clients."
                                    action-label="Nouveau rendez-vous"
                                    :action-url="route('assurance.appointments.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($appointments as $app)
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-[#0B0F14] text-sm">{{ $app->customer->name ?? 'Prospect' }}</h4>
                        <span class="text-[10px] px-2 py-0.5 rounded font-semibold border bg-blue-50 text-[#0066FF] border-blue-200">
                            {{ ucfirst($app->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-[#64748B]">
                        <span>Conseiller : {{ $app->advisor->name ?? 'Équipe' }}</span> &bull; 
                        <span>{{ $app->date ? \Carbon\Carbon::parse($app->date)->format('d/m/Y') : '' }} {{ $app->time ? substr($app->time, 0, 5) : '' }}</span>
                    </div>
                    @if($app->notes)
                        <p class="text-xs text-[#0B0F14] line-clamp-2 bg-[#F5F7FA] p-2 rounded">
                            {{ $app->notes }}
                        </p>
                    @endif
                    <div class="text-right pt-2 border-t border-[#E2E8F0]">
                        <a href="{{ route('assurance.appointments.edit', $app) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">
                            Modifier &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[#64748B]">
                    Aucun rendez-vous pour le moment.
                </div>
            @endforelse
        </div>

        @if($appointments->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
