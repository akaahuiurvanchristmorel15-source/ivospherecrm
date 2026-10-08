<x-layouts.app>
    <x-slot name="header">
        <x-page-header 
            title="Parc Matériel & Équipements (MEDIA)" 
            subtitle="Inventaire des caméras, sonorisation, éclairage, projecteurs et matériel évènementiel en location">
            <x-slot name="breadcrumb">
                <x-breadcrumb :items="[
                    ['label' => 'MEDIA', 'url' => route('media.index')],
                    ['label' => 'Équipements']
                ]" />
            </x-slot>
            <x-slot name="actions">
                <x-button href="{{ route('media.equipment.create') }}" variant="primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajouter un Matériel
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($equipment as $item)
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-5 shadow-xs hover:border-[#0066FF] transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14]">
                            {{ $item->category }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold border {{ $item->status === 'disponible' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-[#0B0F14] mb-1">{{ $item->name }}</h3>
                    <p class="text-xs text-[#64748B] font-mono mb-2">S/N : {{ $item->serial_number ?? 'N/A' }}</p>

                    <div class="flex justify-between items-center text-xs text-[#64748B] border-t border-[#E2E8F0] pt-3 mt-3">
                        <span>Tarif journalier :</span>
                        <span class="font-bold text-[#0066FF] text-sm">{{ number_format($item->daily_rate, 0, ',', ' ') }} FCFA/j</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3 border-t border-[#E2E8F0] pt-3">
                    <a href="{{ route('media.equipment.edit', $item) }}" class="text-xs font-medium text-[#64748B] hover:text-[#0B0F14]">Modifier</a>
                    <a href="{{ route('media.equipment.show', $item) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0066FF] hover:underline">
                        <span>Détails</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state 
                    title="Aucun équipement enregistré" 
                    description="Ajoutez des appareils, projecteurs ou équipements de sonorisation au parc."
                    action-label="Ajouter un équipement"
                    :action-url="route('media.equipment.create')"
                />
            </div>
        @endforelse
    </div>
</x-layouts.app>
