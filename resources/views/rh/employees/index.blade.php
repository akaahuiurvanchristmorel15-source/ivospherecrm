<x-layouts.app>
    <x-slot:title>Annuaire des Employés — IVOSPHERE RH</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Annuaire des Employés" 
            description="Gestion des dossiers du personnel, affectations aux domaines et statuts contractuels"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Employés']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" href="{{ route('rh.employees.create') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Nouvel Employé</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Filtres -->
        <x-card>
            <form method="GET" action="{{ route('rh.employees.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Recherche (Nom, Email, Code)</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ex: Kouamé, EMP0001..." 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] text-xs transition"
                    >
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine Métier</label>
                    <select name="domain_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les domaines</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" @selected(request('domain_id') == $domain->id)>{{ $domain->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut</label>
                    <select name="status" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        <option value="">Tous les statuts</option>
                        <option value="actif" @selected(request('status') == 'actif')>Actif</option>
                        <option value="inactif" @selected(request('status') == 'inactif')>Inactif</option>
                        <option value="suspendu" @selected(request('status') == 'suspendu')>Suspendu</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-[#0B0F14] hover:bg-[#1E293B] text-white rounded-lg text-xs font-medium transition">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'domain_id', 'status']))
                        <a href="{{ route('rh.employees.index') }}" class="py-2 px-2.5 rounded-lg bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] border border-[#E2E8F0] text-xs">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Table des Employés -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Matricule</th>
                            <th class="py-3 px-4">Employé</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Poste & Département</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#0066FF]">
                                    {{ $employee->employee_code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center font-bold text-xs shrink-0 border border-[#0066FF]/20">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('rh.employees.show', $employee) }}" class="font-semibold text-[#0B0F14] hover:text-[#0066FF] transition-colors">
                                                {{ $employee->full_name }}
                                            </a>
                                            <div class="text-[11px] text-[#64748B] flex items-center gap-1.5 mt-0.5">
                                                <span>{{ $employee->domain->name ?? 'Transversal' }}</span>
                                                @if($employee->user)
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-semibold bg-blue-50 text-[#0066FF] border border-blue-200" title="Compte ERP actif">
                                                        {{ $employee->user->roles->first()?->name ?? 'Accès ERP' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-[#64748B]">
                                    <div>{{ $employee->email }}</div>
                                    <div class="text-[10px]">{{ $employee->phone ?? '—' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-[#0B0F14]">{{ $employee->position ?? '—' }}</div>
                                    <div class="text-[10px] text-[#64748B]">{{ $employee->department ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $stClass = match($employee->status) {
                                            'actif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'suspendu' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-slate-100 text-[#64748B] border-[#E2E8F0]'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $stClass }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('rh.employees.show', $employee) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Fiche complète">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('rh.employees.edit', $employee) }}" class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('rh.employees.destroy', $employee) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmez-vous la désactivation de cet employé ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-[#64748B] hover:text-rose-600 transition" title="Désactiver">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun employé trouvé"
                                        description="Aucun dossier du personnel ne correspond aux critères de filtre sélectionnés."
                                    >
                                        <x-slot:action>
                                            <x-button variant="primary" href="{{ route('rh.employees.create') }}">
                                                Ajouter un employé
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
                @forelse($employees as $employee)
                    @php
                        $stClass = match($employee->status) {
                            'actif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'suspendu' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-slate-100 text-[#64748B] border-[#E2E8F0]'
                        };
                    @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center font-bold text-sm shrink-0 border border-[#0066FF]/20">
                                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('rh.employees.show', $employee) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] transition-colors text-sm">
                                        {{ $employee->full_name }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-[11px] font-bold text-[#0066FF]">{{ $employee->employee_code }}</span>
                                        <span class="text-[10px] text-[#64748B]">&bull;</span>
                                        <span class="text-[11px] text-[#64748B]">{{ $employee->domain->name ?? 'Transversal' }}</span>
                                    </div>
                                    @if($employee->user)
                                        <div class="mt-1">
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-semibold bg-blue-50 text-[#0066FF] border border-blue-200">
                                                {{ $employee->user->roles->first()?->name ?? 'Accès ERP' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $stClass }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>

                        <!-- 2-col info grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block">Poste</span>
                                <span class="font-semibold text-[#0B0F14] truncate block">{{ $employee->position ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block">Département</span>
                                <span class="font-medium text-[#0B0F14] truncate block">{{ $employee->department ?? '—' }}</span>
                            </div>
                        </div>

                        <!-- Contact clickable pills -->
                        <div class="flex flex-wrap gap-2 text-xs text-[#64748B]">
                            @if($employee->phone)
                                <a href="tel:{{ $employee->phone }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] hover:text-[#0066FF] text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>{{ $employee->phone }}</span>
                                </a>
                            @endif
                            <a href="mailto:{{ $employee->email }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] hover:text-[#0066FF] text-[11px] truncate max-w-[200px]">
                                <svg class="w-3.5 h-3.5 text-[#0066FF] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="truncate">{{ $employee->email }}</span>
                            </a>
                        </div>

                        <!-- Actions bar -->
                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                            <a href="{{ route('rh.employees.show', $employee) }}" class="flex-1 py-2 px-3 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Fiche RH</span>
                            </a>
                            <a href="{{ route('rh.employees.edit', $employee) }}" class="p-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('rh.employees.destroy', $employee) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmez-vous la désactivation de cet employé ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition" title="Désactiver">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun employé trouvé"
                        description="Aucun dossier du personnel ne correspond aux critères de filtre sélectionnés."
                    >
                        <x-slot:action>
                            <x-button variant="primary" href="{{ route('rh.employees.create') }}">
                                Ajouter un employé
                            </x-button>
                        </x-slot:action>
                    </x-empty-state>
                @endforelse
            </div>

            @if($employees->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $employees->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
