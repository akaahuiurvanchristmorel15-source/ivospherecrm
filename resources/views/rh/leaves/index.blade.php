<x-layouts.app>
    <x-slot:title>Demandes de Congés — IVOSPHERE RH</x-slot>

    <div class="space-y-6" x-data="{ showModal: false }">
        <x-page-header 
            title="Demandes de Congés & Absences" 
            description="Circuit de validation hiérarchique, suivi des soldes et gestion des absences du personnel"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Congés & Absences']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" type="button" @click="showModal = true" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouvelle Demande</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Filtres -->
        <x-card>
            <form method="GET" action="{{ route('rh.leaves.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
                <div class="w-64">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut de la demande</label>
                    <select name="status" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" @selected(request('status') == 'en_attente')>En attente</option>
                        <option value="approuvé" @selected(request('status') == 'approuvé')>Approuvé</option>
                        <option value="refusé" @selected(request('status') == 'refusé')>Refusé</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 pt-5">
                    <button type="submit" class="py-2 px-4 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-lg text-xs font-medium transition">
                        Filtrer
                    </button>
                    @if(request('status'))
                        <a href="{{ route('rh.leaves.index') }}" class="py-2 px-2.5 rounded-lg bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] border border-[#E2E8F0] text-xs">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Table des congés -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Employé</th>
                            <th class="py-3 px-4">Type d'absence</th>
                            <th class="py-3 px-4">Période</th>
                            <th class="py-3 px-4 text-center">Durée</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($leaves as $leave)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-semibold text-[#0B0F14]">
                                    {{ $leave->employee->full_name }}
                                </td>
                                <td class="py-3.5 px-4 font-medium">
                                    {{ str_replace('_', ' ', ucfirst($leave->type)) }}
                                </td>
                                <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">
                                    {{ $leave->start_date->format('d/m/Y') }} au {{ $leave->end_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {{ $leave->days }} jours
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $stLeave = match($leave->status) {
                                            'approuvé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'refusé' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $stLeave }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($leave->status === 'en_attente')
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('rh.leaves.approve', $leave) }}" method="POST" class="inline-block">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold border border-emerald-200 transition">
                                                    Approuver
                                                </button>
                                            </form>
                                            <form action="{{ route('rh.leaves.reject', $leave) }}" method="POST" class="inline-block" onsubmit="return prompt('Motif du refus:') ? (this.insertAdjacentHTML('beforeend', '<input type=\'hidden\' name=\'rejection_reason\' value=\'' + prompt('Motif du refus:') + '\'>'), true) : false;">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold border border-rose-200 transition">
                                                    Refuser
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[#64748B] text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucune demande de congé"
                                        description="Toutes les demandes de congés ou absences déposées apparaîtront ici pour arbitrage."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" type="button" @click="showModal = true">
                                                Créer une demande
                                            </x-button>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($leaves as $leave)
                    @php
                        $stLeave = match($leave->status) {
                            'approuvé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'refusé' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-[#0B0F14] text-sm">{{ $leave->employee->full_name }}</h4>
                                <span class="text-xs text-[#0066FF] font-semibold">{{ str_replace('_', ' ', ucfirst($leave->type)) }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $stLeave }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>

                        <!-- 2-col date & duration grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Période</span>
                                <span class="font-mono text-[#0B0F14] text-[11px] block">{{ $leave->start_date->format('d/m/Y') }}</span>
                                <span class="font-mono text-[#64748B] text-[10px] block">au {{ $leave->end_date->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-0.5">Durée</span>
                                <span class="font-bold text-[#0066FF] text-sm block">{{ $leave->days }} jours</span>
                                <span class="text-[10px] text-[#64748B]">Ouvrés</span>
                            </div>
                        </div>

                        @if($leave->reason)
                            <div class="text-[11px] text-[#64748B] bg-slate-50 px-2.5 py-1.5 rounded-lg border border-[#E2E8F0]">
                                <span class="font-medium text-[#0B0F14]">Motif :</span> {{ $leave->reason }}
                            </div>
                        @endif

                        @if($leave->status === 'en_attente')
                            <div class="pt-2 border-t border-[#E2E8F0] flex items-center gap-2">
                                <form action="{{ route('rh.leaves.approve', $leave) }}" method="POST" class="flex-1">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Approuver</span>
                                    </button>
                                </form>
                                <form action="{{ route('rh.leaves.reject', $leave) }}" method="POST" class="flex-1" onsubmit="return prompt('Motif du refus:') ? (this.insertAdjacentHTML('beforeend', '<input type=\'hidden\' name=\'rejection_reason\' value=\'' + prompt('Motif du refus:') + '\'>'), true) : false;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Refuser</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucune demande de congé"
                        description="Toutes les demandes de congés ou absences déposées apparaîtront ici pour arbitrage."
                    >
                        <x-slot:action>
                            <x-button variant="primary" type="button" @click="showModal = true">
                                Créer une demande
                            </x-button>
                        </x-slot:action>
                    </x-empty-state>
                @endforelse
            </div>

            @if($leaves->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $leaves->links() }}
                </div>
            @endif
        </x-card>

        <!-- Modal Création Congé -->
        <div 
            x-show="showModal" 
            x-transition 
            class="fixed inset-0 z-50 bg-[#0B0F14]/50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div @click.outside="showModal = false" class="bg-white border border-[#E2E8F0] rounded-xl max-w-lg w-full p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center border-b border-[#E2E8F0] pb-3">
                    <h3 class="text-base font-bold text-[#0B0F14]">Nouvelle Demande de Congé</h3>
                    <button type="button" @click="showModal = false" class="text-[#64748B] hover:text-[#0B0F14]">✕</button>
                </div>

                <form action="{{ route('rh.leaves.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Employé *</label>
                        <select name="employee_id" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="">Sélectionner un employé</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Type d'absence *</label>
                        <select name="type" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="congé_annuel">Congé Annuel</option>
                            <option value="congé_maladie">Congé Maladie</option>
                            <option value="congé_maternité">Congé Maternité</option>
                            <option value="sans_solde">Sans Solde</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date de début *</label>
                            <input type="date" name="start_date" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date de fin *</label>
                            <input type="date" name="end_date" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nombre de jours ouvrés *</label>
                        <input type="number" name="days" required min="1" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Motif / Justification</label>
                        <textarea name="reason" rows="3" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <x-button variant="secondary" type="button" @click="showModal = false">Annuler</x-button>
                        <x-button variant="primary" type="submit">Soumettre la demande</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
