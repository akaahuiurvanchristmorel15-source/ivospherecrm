<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Séances Photo & Vidéo (MEDIA)" 
            subtitle="Shootings studio, reportages corporate, mariages et galeries numériques">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'MEDIA', 'url' => route('media.index')],
                    ['label' => 'Séances']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('media.sessions.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle Séance
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
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Photographe / Cadreur</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Lieu / Formule</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Tarif Forfait</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                        <th scope="col" class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($sessions as $session)
                        <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#0066FF]">{{ $session->reference }}</td>
                            <td class="py-3.5 px-4 text-sm font-semibold text-[#0B0F14]">{{ $session->customer->name ?? 'Particulier' }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#0B0F14]">{{ $session->photographer ?? 'Équipe Média' }}</td>
                            <td class="py-3.5 px-4 text-xs text-[#64748B]">{{ $session->location ?? 'Studio' }} ({{ $session->package ?? 'Standard' }})</td>
                            <td class="py-3.5 px-4 text-sm font-bold text-[#0B0F14] text-right">{{ number_format($session->price, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $statusBadge = $session->status === 'livré' 
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                        : 'bg-blue-50 text-[#0066FF] border-blue-200';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('media.sessions.show', $session) }}" class="text-xs font-semibold text-[#0066FF] hover:underline">Galerie</a>
                                    <a href="{{ route('media.sessions.edit', $session) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    title="Aucune séance photo" 
                                    description="Aucune séance studio ou reportage n'a encore été planifiée."
                                    action-label="Nouvelle séance"
                                    :action-url="route('media.sessions.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vue Cartes Mobile (< md) -->
        <div class="block md:hidden p-3 sm:p-4 space-y-3">
            @forelse($sessions as $session)
                @php
                    $statusBadge = $session->status === 'livré' 
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                        : 'bg-blue-50 text-[#0066FF] border-blue-200';
                @endphp
                <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-[#0066FF] text-xs">{{ $session->reference }}</span>
                            <h4 class="font-bold text-[#0B0F14] text-sm mt-0.5">{{ $session->customer->name ?? 'Particulier' }}</h4>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border shrink-0 {{ $statusBadge }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>

                    <div class="text-xs text-[#64748B]">
                        <span>Photographe :</span>
                        <strong class="text-[#0B0F14]">{{ $session->photographer ?? 'Équipe Média' }}</strong>
                    </div>

                    <!-- 2-col info grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Formule / Lieu</span>
                            <span class="font-medium text-[#0B0F14] block truncate">{{ $session->location ?? 'Studio' }}</span>
                            <span class="text-[10px] text-[#64748B] block truncate">({{ $session->package ?? 'Standard' }})</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Tarif Forfait</span>
                            <span class="font-bold text-[#0B0F14] text-sm">{{ number_format($session->price, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    <!-- Actions bar -->
                    <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                        <a href="{{ route('media.sessions.show', $session) }}" class="flex-1 py-2 px-3 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Galerie Photo</span>
                        </a>
                        <a href="{{ route('media.sessions.edit', $session) }}" class="py-2 px-3 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Modifier</span>
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Aucune séance photo" 
                    description="Aucune séance studio ou reportage n'a encore été planifiée."
                    action-label="Nouvelle séance"
                    :action-url="route('media.sessions.create')"
                />
            @endforelse
        </div>
    </div>
</x-layouts.app>
