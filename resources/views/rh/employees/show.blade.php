<x-layouts.app>
    <x-slot:title>Profil Employé : {{ $employee->full_name }} — IVOSPHERE RH</x-slot>

    <div class="max-w-6xl mx-auto space-y-6" x-data="{ tab: 'info' }">
        <x-page-header 
            title="Dossier RH : {{ $employee->full_name }}" 
            description="Fiche individuelle du personnel, historique contractuel, congés et assiduité"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Employés', 'url' => route('rh.employees.index')],
                    ['label' => $employee->full_name]
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('rh.employees.edit', $employee) }}">
                    Modifier la fiche
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Fiche Header Employé -->
        <x-card>
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-[#0066FF]/10 text-[#0066FF] border border-[#0066FF]/20 flex items-center justify-center text-xl font-bold shrink-0">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-[#0B0F14]">{{ $employee->full_name }}</h2>
                            <span class="font-mono text-xs font-bold text-[#0066FF] bg-[#0066FF]/5 border border-[#0066FF]/20 px-2 py-0.5 rounded-md">
                                {{ $employee->employee_code }}
                            </span>
                        </div>
                        <p class="text-xs text-[#64748B] mt-0.5">{{ $employee->position ?? 'Poste non défini' }} &bull; {{ $employee->department ?? 'Département non défini' }}</p>
                        <div class="mt-2 flex flex-wrap gap-4 text-xs text-[#64748B]">
                            <span><strong class="text-[#0B0F14]">Email :</strong> {{ $employee->email }}</span>
                            <span><strong class="text-[#0B0F14]">Tél :</strong> {{ $employee->phone ?? '—' }}</span>
                            <span><strong class="text-[#0B0F14]">Domaine :</strong> {{ $employee->domain->name ?? 'Transversal' }}</span>
                            @if($employee->user)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-[#0066FF] border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $employee->user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>Compte ERP : {{ $employee->user->roles->first()?->name ?? 'Actif' }}</span>
                                </span>
                            @else
                                <span class="text-[11px] text-[#64748B] italic">Sans compte ERP</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    @php
                        $stClass = match($employee->status) {
                            'actif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'suspendu' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-slate-100 text-[#64748B] border-[#E2E8F0]'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $stClass }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
        </x-card>

        <!-- Onglets Horizontaux Épurés -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap">
            <button 
                type="button" 
                @click="tab = 'info'" 
                :class="tab === 'info' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-white border border-[#E2E8F0]'"
                class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors shrink-0"
            >
                Informations
            </button>
            <button 
                type="button" 
                @click="tab = 'contracts'" 
                :class="tab === 'contracts' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-white border border-[#E2E8F0]'"
                class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
            >
                <span>Contrats</span>
                <span :class="tab === 'contracts' ? 'bg-white/20 text-white' : 'bg-[#F5F7FA] text-[#0B0F14]'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                    {{ $employee->contracts->count() }}
                </span>
            </button>
            <button 
                type="button" 
                @click="tab = 'leaves'" 
                :class="tab === 'leaves' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-white border border-[#E2E8F0]'"
                class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
            >
                <span>Congés</span>
                <span :class="tab === 'leaves' ? 'bg-white/20 text-white' : 'bg-[#F5F7FA] text-[#0B0F14]'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                    {{ $employee->leaveRequests->count() }}
                </span>
            </button>
            <button 
                type="button" 
                @click="tab = 'attendance'" 
                :class="tab === 'attendance' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-white border border-[#E2E8F0]'"
                class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
            >
                <span>Présences</span>
                <span :class="tab === 'attendance' ? 'bg-white/20 text-white' : 'bg-[#F5F7FA] text-[#0B0F14]'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                    {{ $employee->attendances->count() }}
                </span>
            </button>
            <button 
                type="button" 
                @click="tab = 'targets'" 
                :class="tab === 'targets' ? 'bg-[#0066FF] text-white shadow-xs' : 'text-[#64748B] hover:text-[#0B0F14] bg-white border border-[#E2E8F0]'"
                class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5 shrink-0"
            >
                <span>Objectifs Ventes</span>
                <span :class="tab === 'targets' ? 'bg-white/20 text-white' : 'bg-[#F5F7FA] text-[#0B0F14]'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                    {{ $employee->salesTargets->count() }}
                </span>
            </button>
        </div>

        <!-- Contenu des Onglets -->
        <x-card>
            <!-- 1. Info Tab -->
            <div x-show="tab === 'info'" class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs">
                <div>
                    <h3 class="text-sm font-bold text-[#0B0F14] mb-3 pb-2 border-b border-[#E2E8F0]">Informations Personnelles</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Date de naissance</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $employee->date_of_birth ? $employee->date_of_birth->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Genre</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $employee->gender ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Adresse</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $employee->address ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">N° Pièce d'identité</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $employee->national_id ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-[#0B0F14] mb-3 pb-2 border-b border-[#E2E8F0]">Informations Professionnelles</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Date d'embauche</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $employee->hire_date ? $employee->hire_date->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Contrat initial</dt>
                            <dd class="font-medium text-[#0B0F14]">{{ $employee->contract_type ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Salaire de base</dt>
                            <dd class="font-bold text-[#0B0F14]">{{ $employee->salary ? number_format($employee->salary, 0, ',', ' ') . ' FCFA' : '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-1 border-b border-[#E2E8F0]/60">
                            <dt class="text-[#64748B]">Compte Utilisateur ERP</dt>
                            <dd class="font-medium text-[#0B0F14]">
                                @if($employee->user)
                                    <span class="text-[#0066FF] font-semibold">{{ $employee->user->email }}</span>
                                    <span class="text-[10px] text-[#64748B] block">({{ $employee->user->roles->pluck('name')->join(', ') }})</span>
                                @else
                                    <span class="text-[#64748B] italic">Aucun compte lié</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 2. Contracts Tab -->
            <div x-show="tab === 'contracts'" style="display: none;">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">Référence</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Début</th>
                                <th class="py-3 px-4">Fin</th>
                                <th class="py-3 px-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                            @forelse($employee->contracts as $contract)
                                <tr class="hover:bg-[#F5F7FA]/70 transition">
                                    <td class="py-3.5 px-4 font-mono font-medium">{{ $contract->reference }}</td>
                                    <td class="py-3.5 px-4 font-semibold">{{ $contract->type }}</td>
                                    <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">{{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '—' }}</td>
                                    <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Indéterminée' }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $contract->status == 'actif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-[#64748B] border-[#E2E8F0]' }}">
                                            {{ ucfirst($contract->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-[#64748B]">Aucun contrat enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Amplified Cards -->
                <div class="block md:hidden space-y-3">
                    @forelse($employee->contracts as $contract)
                        <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-sm text-[#0B0F14]">{{ $contract->reference }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $contract->status == 'actif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-[#64748B] border-[#E2E8F0]' }}">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Type</span>
                                    <span class="font-semibold text-[#0B0F14]">{{ $contract->type }}</span>
                                </div>
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Période</span>
                                    <span class="font-mono text-[#0B0F14] text-[11px]">
                                        {{ $contract->start_date ? $contract->start_date->format('d/m/Y') : '—' }} au {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Indét.' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-[#64748B] text-xs">Aucun contrat enregistré.</div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Leaves Tab -->
            <div x-show="tab === 'leaves'" style="display: none;">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Période</th>
                                <th class="py-3 px-4 text-center">Durée</th>
                                <th class="py-3 px-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                            @forelse($employee->leaveRequests as $leave)
                                <tr class="hover:bg-[#F5F7FA]/70 transition">
                                    <td class="py-3.5 px-4 font-medium">{{ str_replace('_', ' ', ucfirst($leave->type)) }}</td>
                                    <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">{{ $leave->start_date->format('d/m/Y') }} au {{ $leave->end_date->format('d/m/Y') }}</td>
                                    <td class="py-3.5 px-4 text-center font-bold">{{ $leave->days }} jours</td>
                                    <td class="py-3.5 px-4">
                                        @php
                                            $stLeave = match($leave->status) {
                                                'approuvé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'refusé' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default => 'bg-amber-50 text-amber-700 border-amber-200'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $stLeave }}">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-[#64748B]">Aucune demande de congé enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Amplified Cards -->
                <div class="block md:hidden space-y-3">
                    @forelse($employee->leaveRequests as $leave)
                        @php
                            $stLeave = match($leave->status) {
                                'approuvé' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'refusé' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-amber-50 text-amber-700 border-amber-200'
                            };
                        @endphp
                        <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#0B0F14] text-sm">{{ str_replace('_', ' ', ucfirst($leave->type)) }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $stLeave }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Durée</span>
                                    <span class="font-bold text-[#0B0F14]">{{ $leave->days }} jours</span>
                                </div>
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Période</span>
                                    <span class="font-mono text-[#0B0F14] text-[11px]">{{ $leave->start_date->format('d/m/Y') }} &ndash; {{ $leave->end_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-[#64748B] text-xs">Aucune demande de congé enregistrée.</div>
                    @endforelse
                </div>
            </div>

            <!-- 4. Attendance Tab -->
            <div x-show="tab === 'attendance'" style="display: none;">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4">Arrivée</th>
                                <th class="py-3 px-4">Départ</th>
                                <th class="py-3 px-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                            @forelse($employee->attendances as $attendance)
                                <tr class="hover:bg-[#F5F7FA]/70 transition">
                                    <td class="py-3.5 px-4 font-mono">{{ $attendance->date->format('d/m/Y') }}</td>
                                    <td class="py-3.5 px-4 font-mono text-[#0066FF] font-semibold">{{ $attendance->check_in ?? '—' }}</td>
                                    <td class="py-3.5 px-4 font-mono text-[#64748B]">{{ $attendance->check_out ?? '—' }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $attendance->status == 'present' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($attendance->status == 'absent' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-[#64748B]">Aucun pointage enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Amplified Cards -->
                <div class="block md:hidden space-y-3">
                    @forelse($employee->attendances as $attendance)
                        <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-sm text-[#0B0F14]">{{ $attendance->date->format('d/m/Y') }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $attendance->status == 'present' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($attendance->status == 'absent' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Arrivée</span>
                                    <span class="font-mono font-semibold text-[#0066FF]">{{ $attendance->check_in ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Départ</span>
                                    <span class="font-mono text-[#64748B]">{{ $attendance->check_out ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-[#64748B] text-xs">Aucun pointage enregistré.</div>
                    @endforelse
                </div>
            </div>

            <!-- 5. Sales Targets Tab -->
            <div x-show="tab === 'targets'" style="display: none;">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 mb-4 border-b border-[#E2E8F0]">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Objectifs Commerciaux & Commissions</h4>
                        <p class="text-[11px] text-[#64748B]">Suivi des quotas attribués et des primes variables de performance</p>
                    </div>
                    <a href="{{ route('rh.sales-targets.create', ['employee_id' => $employee->id]) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#0066FF] text-white text-xs font-semibold hover:bg-blue-600 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Nouvel Objectif</span>
                    </a>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                            <tr>
                                <th class="py-3 px-4">Intitulé</th>
                                <th class="py-3 px-4">Période</th>
                                <th class="py-3 px-4 text-right">Quota Cible</th>
                                <th class="py-3 px-4">Réalisé & Progression</th>
                                <th class="py-3 px-4 text-right">Prime / Comm.</th>
                                <th class="py-3 px-4 text-center">Statut</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                            @forelse($employee->salesTargets as $target)
                                <tr class="hover:bg-[#F5F7FA]/70 transition">
                                    <td class="py-3.5 px-4 font-semibold text-[#0B0F14]">{{ $target->title }}</td>
                                    <td class="py-3.5 px-4 text-[#64748B] font-mono text-[11px]">
                                        {{ $target->start_date->format('d/m/y') }} au {{ $target->end_date->format('d/m/y') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold">{{ number_format($target->target_amount, 0, ',', ' ') }} F</td>
                                    <td class="py-3.5 px-4 min-w-[140px]">
                                        <div class="flex items-center justify-between text-[11px] mb-1">
                                            <span>{{ number_format($target->achieved_amount, 0, ',', ' ') }} F</span>
                                            <span class="font-bold {{ $target->progress_percentage >= 100 ? 'text-emerald-600' : 'text-[#0066FF]' }}">{{ $target->progress_percentage }}%</span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-[#F5F7FA] border border-[#E2E8F0] overflow-hidden">
                                            <div class="h-full rounded-full {{ $target->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-[#0066FF]' }}" style="width: {{ min(100, $target->progress_percentage) }}%"></div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-emerald-600">
                                        {{ number_format($target->estimated_commission, 0, ',', ' ') }} F
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @php $b = $target->status_badge; @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $b['class'] }}">
                                            {{ $b['label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('rh.sales-targets.show', $target) }}" class="inline-flex items-center gap-1 text-[#0066FF] hover:underline font-semibold text-xs">
                                            <span>Détails</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-[#64748B]">Aucun objectif commercial défini pour cet employé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Amplified Cards -->
                <div class="block md:hidden space-y-3">
                    @forelse($employee->salesTargets as $target)
                        @php $b = $target->status_badge; @endphp
                        <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-bold text-sm text-[#0B0F14]">{{ $target->title }}</span>
                                    <div class="text-[11px] text-[#64748B] font-mono mt-0.5">
                                        {{ $target->start_date->format('d/m/y') }} au {{ $target->end_date->format('d/m/y') }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $b['class'] }}">
                                    {{ $b['label'] }}
                                </span>
                            </div>
                            
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-[#64748B]">Progression</span>
                                    <span class="font-bold {{ $target->progress_percentage >= 100 ? 'text-emerald-600' : 'text-[#0066FF]' }}">{{ $target->progress_percentage }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-[#F5F7FA] border border-[#E2E8F0] overflow-hidden">
                                    <div class="h-full rounded-full {{ $target->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-[#0066FF]' }}" style="width: {{ min(100, $target->progress_percentage) }}%"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Cible</span>
                                    <span class="font-bold text-[#0B0F14] text-[11px]">{{ number_format($target->target_amount, 0, ',', ' ') }} F</span>
                                </div>
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Réalisé</span>
                                    <span class="font-bold text-[#0B0F14] text-[11px]">{{ number_format($target->achieved_amount, 0, ',', ' ') }} F</span>
                                </div>
                                <div>
                                    <span class="text-[#64748B] block text-[10px] uppercase font-medium">Prime</span>
                                    <span class="font-bold text-emerald-600 text-[11px]">{{ number_format($target->estimated_commission, 0, ',', ' ') }} F</span>
                                </div>
                            </div>

                            <a href="{{ route('rh.sales-targets.show', $target) }}" class="w-full py-2.5 px-3 rounded-lg bg-[#F5F7FA] hover:bg-[#0066FF] hover:text-white border border-[#E2E8F0] text-center font-semibold text-xs text-[#0B0F14] transition-colors flex items-center justify-center gap-1.5">
                                <span>Consulter les détails</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-[#64748B] text-xs">Aucun objectif commercial défini pour cet employé.</div>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
