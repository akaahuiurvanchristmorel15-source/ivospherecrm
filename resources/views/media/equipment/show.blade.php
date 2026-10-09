<x-layouts.app :title="'Matériel ' . $equipment->name . ' — MEDIA'">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('media.equipment.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour au parc matériel</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        {{ $equipment->name }}
                    </h1>
                    @php
                        $statusClasses = match($equipment->status) {
                            'disponible' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'en_location' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                            'maintenance' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusClasses }}">
                        {{ ucfirst($equipment->status) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Catégorie : {{ $equipment->category }} &bull; S/N : {{ $equipment->serial_number ?? 'Non renseigné' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('media.equipment.edit', $equipment) }}" class="px-4 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>
            </div>
        </div>

        <!-- Détails du matériel -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                    Fiche Technique & Équipement
                </h3>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[#64748B] block mb-0.5">Catégorie :</span>
                        <span class="font-bold text-[#0B0F14]">{{ $equipment->category }}</span>
                    </div>
                    <div>
                        <span class="text-[#64748B] block mb-0.5">Numéro de Série :</span>
                        <span class="font-mono font-bold text-[#0B0F14]">{{ $equipment->serial_number ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-[#64748B] block mb-0.5">État de conservation :</span>
                        <span class="font-medium text-[#0B0F14] capitalize">{{ str_replace('_', ' ', $equipment->condition) }}</span>
                    </div>
                    <div>
                        <span class="text-[#64748B] block mb-0.5">Statut actuel :</span>
                        <span class="font-medium text-[#0B0F14] capitalize">{{ $equipment->status }}</span>
                    </div>
                </div>

                @if($equipment->description)
                    <div class="pt-3 border-t border-[#E2E8F0] text-xs">
                        <span class="text-[#64748B] font-semibold block mb-1">Description & Kit Fourni :</span>
                        <div class="p-3 bg-[#F5F7FA] rounded-lg text-[#0B0F14] whitespace-pre-line leading-relaxed">
                            {{ $equipment->description }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Colonne Tarification & Caution -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B] border-b border-[#E2E8F0] pb-2">
                    Conditions Locatives
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[#64748B] block mb-0.5">Tarif de Location :</span>
                        <span class="text-xl font-bold text-[#0066FF]">
                            {{ number_format($equipment->daily_rate, 0, ',', ' ') }} FCFA / jour
                        </span>
                    </div>

                    @if($equipment->value > 0)
                        <div>
                            <span class="text-[#64748B] block mb-0.5">Valeur / Caution recommandée :</span>
                            <span class="font-semibold text-[#0B0F14]">
                                {{ number_format($equipment->value, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
