<x-layouts.app>
    <x-slot:title>{{ $isEdit ? 'Modifier l\'Employé' : 'Ajouter un Employé' }} — IVOSPHERE RH</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header 
            title="{{ $isEdit ? 'Modifier l\'Employé : ' . $employee->full_name : 'Créer un Dossier Employé' }}"
            description="Enregistrement des données administratives, professionnelles et contractuelles"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Employés', 'url' => route('rh.employees.index')],
                    ['label' => $isEdit ? 'Modification' : 'Nouveau']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        <form action="{{ $isEdit ? route('rh.employees.update', $employee) : route('rh.employees.store') }}" method="POST" class="space-y-6 text-xs">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- 1. Informations Personnelles -->
            <x-card title="1. État Civil & Coordonnées Personnelles">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-[#0B0F14]">Matricule / Code Employé</label>
                            <span class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded font-semibold">Non modifiable</span>
                        </div>
                        <input 
                            type="text" 
                            name="employee_code" 
                            value="{{ old('employee_code', $employee->employee_code ?: \App\Models\Employee::generateEmployeeCode()) }}" 
                            readonly 
                            class="w-full px-3.5 py-2 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] text-[#0B0F14] font-mono cursor-not-allowed select-none text-xs transition"
                            placeholder="Ex: EMP-0001"
                            tabindex="-1"
                        >
                        <span class="text-[10px] text-[#64748B] mt-1 block">Attribué automatiquement par le système et immuable.</span>
                        @error('employee_code') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Prénom *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @error('first_name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nom *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @error('last_name') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Email Professionnel *</label>
                        <input type="email" name="email" value="{{ old('email', $employee->email) }}" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @error('email') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Numéro de Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @error('phone') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date de naissance</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @error('date_of_birth') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Genre</label>
                        <select name="gender" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="">Sélectionner</option>
                            <option value="M" @selected(old('gender', $employee->gender) == 'M')>Homme</option>
                            <option value="F" @selected(old('gender', $employee->gender) == 'F')>Femme</option>
                            <option value="Autre" @selected(old('gender', $employee->gender) == 'Autre')>Autre</option>
                        </select>
                        @error('gender') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </x-card>

            <!-- 2. Informations Professionnelles -->
            <x-card title="2. Affectation Professionnelle & Contrat">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine d'activité *</label>
                        <select name="domain_id" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="">Sélectionner un domaine</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" @selected(old('domain_id', $employee->domain_id) == $domain->id)>{{ $domain->name }}</option>
                            @endforeach
                        </select>
                        @error('domain_id') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Poste / Intitulé *</label>
                        <input type="text" name="position" value="{{ old('position', $employee->position) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="Ex: Comptable Principal">
                        @error('position') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Département</label>
                        <input type="text" name="department" value="{{ old('department', $employee->department) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition" placeholder="Ex: Direction Financière">
                        @error('department') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date d'embauche</label>
                        <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                        @error('hire_date') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Type de contrat initial</label>
                        <select name="contract_type" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="">Sélectionner</option>
                            <option value="CDI" @selected(old('contract_type', $employee->contract_type) == 'CDI')>CDI</option>
                            <option value="CDD" @selected(old('contract_type', $employee->contract_type) == 'CDD')>CDD</option>
                            <option value="Stage" @selected(old('contract_type', $employee->contract_type) == 'Stage')>Stage</option>
                        </select>
                        @error('contract_type') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut du dossier *</label>
                        <select name="status" required class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="actif" @selected(old('status', $employee->status ?? 'actif') == 'actif')>Actif</option>
                            <option value="inactif" @selected(old('status', $employee->status) == 'inactif')>Inactif</option>
                            <option value="suspendu" @selected(old('status', $employee->status) == 'suspendu')>Suspendu</option>
                        </select>
                        @error('status') <span class="text-rose-600 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </x-card>

            <!-- 3. Compte d'Accès ERP & Rôles (Agents, Responsables, Collaborateurs) -->
            <x-card title="3. Compte d'Accès ERP & Espace Collaborateur" x-data="{ enableUser: {{ ($employee->user_id || old('create_user_account')) ? 'true' : 'false' }} }">
                <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-lg text-xs text-[#0066FF] flex items-start gap-2.5 mb-4">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <p class="font-semibold text-[#0B0F14]">Unification Métier : Agents et Responsables sont des Employés</p>
                        <p class="text-[11px] text-[#64748B] mt-0.5">L'activation d'un compte utilisateur permet au collaborateur de s'authentifier dans l'ERP, d'accéder au module commercial (POS, ventes, caisse), de pointer ses présences et de consulter son Espace Collaborateur.</p>
                    </div>
                </div>

                @if($employee->user)
                    <div class="bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#64748B] block">Compte Utilisateur Associé</span>
                            <span class="font-bold text-[#0B0F14] text-xs">{{ $employee->user->name }} ({{ $employee->user->email }})</span>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $employee->user->is_active ? 'Compte Actif' : 'Compte Inactif' }}
                                </span>
                                @foreach($employee->user->roles as $role)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-[#0066FF] border border-blue-200">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="user_id" value="{{ $employee->user->id }}">
                    </div>
                @else
                    <div class="mb-4">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="create_user_account" 
                                value="1" 
                                x-model="enableUser" 
                                class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]"
                            >
                            <span class="font-semibold text-[#0B0F14]">Créer un compte d'accès ERP & Espace Collaborateur</span>
                        </label>
                    </div>
                @endif

                <div x-show="enableUser" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-[#E2E8F0]">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Rôle Opérationnel dans l'ERP</label>
                        <select name="role_id" class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                            <option value="">Sélectionner un rôle</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $employee->user?->roles?->first()?->id) == $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-[#64748B] mt-1 block">Détermine les autorisations (ex: Agent commercial terrain, Agent caissier, Responsable...)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Mot de passe {{ $employee->user ? '(laisser vide pour conserver)' : '(par défaut : Ivosphere2026@)' }}
                        </label>
                        <input 
                            type="password" 
                            name="user_password" 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] text-xs transition" 
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <div class="pt-6 border-t border-[#E2E8F0] mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('rh.employees.index') }}">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ $isEdit ? 'Mettre à jour le dossier' : 'Créer le dossier employé' }}
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
