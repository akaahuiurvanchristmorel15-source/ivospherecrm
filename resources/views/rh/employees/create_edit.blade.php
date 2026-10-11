@php
    $input = 'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-[#0B0F14] placeholder-slate-400 transition focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15';
    $field = fn (string $name): string => $input.' '.($errors->has($name) ? 'border-rose-400' : 'border-[#E2E8F0]');
    $label = 'mb-1.5 block text-xs font-medium text-[#0B0F14]';
    $hint = 'mt-1.5 block text-[11px] text-[#64748B]';
    $error = 'mt-1.5 block text-[11px] font-medium text-rose-600';
    $card = 'rounded-2xl border border-[#E2E8F0] bg-white';

    $employeeCode = old('employee_code', $employee->employee_code ?: \App\Models\Employee::generateEmployeeCode());
    $statuses = [
        'actif' => ['Actif', 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700', 'bg-emerald-500'],
        'suspendu' => ['Suspendu', 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700', 'bg-amber-500'],
        'inactif' => ['Inactif', 'peer-checked:border-slate-500 peer-checked:bg-slate-100 peer-checked:text-slate-700', 'bg-slate-400'],
    ];
    $currentStatus = old('status', $employee->status ?? 'actif');
@endphp

<x-layouts.app>
    <x-slot:title>{{ $isEdit ? 'Modifier '.$employee->full_name : 'Nouvel employé' }} — IVOSPHERE RH</x-slot>

    <div class="mx-auto max-w-5xl space-y-5 sm:space-y-6"
         x-data="{
            firstName: @js(old('first_name', $employee->first_name ?? '')),
            lastName: @js(old('last_name', $employee->last_name ?? '')),
            position: @js(old('position', $employee->position ?? '')),
            status: @js($currentStatus),
            enableUser: {{ ($employee->user_id || old('create_user_account')) ? 'true' : 'false' }},
            get initials() { return ((this.firstName[0] || '') + (this.lastName[0] || '')).toUpperCase() || '?'; },
            get fullName() { return (this.firstName + ' ' + this.lastName).trim() || 'Nouvel employé'; },
         }">

        {{-- ───────── En-tête ───────── --}}
        <header class="space-y-4">
            <x-breadcrumb :items="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Employés', 'url' => route('rh.employees.index')],
                ['label' => $isEdit ? 'Modifier' : 'Nouveau'],
            ]" />

            <div class="relative overflow-hidden rounded-3xl bg-[#0B0F14] p-5 text-white sm:p-6">
                <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-[#0066FF]/30 blur-3xl"></div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="relative flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#0066FF] text-xl font-semibold" x-text="initials"></span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-400">{{ $isEdit ? 'Modifier le dossier' : 'Nouveau dossier employé' }}</p>
                            <h1 class="truncate text-xl font-semibold tracking-tight sm:text-2xl" x-text="fullName"></h1>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px]">
                                <span class="rounded-full bg-white/10 px-2 py-0.5 font-mono">{{ $employeeCode }}</span>
                                <span class="rounded-full bg-white/10 px-2 py-0.5" x-show="position" x-text="position"></span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5 capitalize">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="status === 'actif' ? 'bg-emerald-400' : (status === 'suspendu' ? 'bg-amber-400' : 'bg-slate-400')"></span>
                                    <span x-text="status"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($isEdit)
                        <a href="{{ route('rh.employees.show', $employee) }}" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-white/10 px-4 text-sm font-medium transition hover:bg-white/20">
                            Voir la fiche
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </header>

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
                <p class="font-semibold">{{ $errors->count() }} champ(s) à corriger :</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $isEdit ? route('rh.employees.update', $employee) : route('rh.employees.store') }}" method="POST" class="space-y-5 md:pb-24">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="employee_code" value="{{ $employeeCode }}">

            {{-- ───────── Identité ───────── --}}
            <section class="{{ $card }}">
                <div class="flex items-center gap-3 border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-[#0066FF]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Identité et coordonnées</h2>
                        <p class="text-xs text-[#64748B]">Informations personnelles du collaborateur.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <label for="first_name" class="{{ $label }}">Prénom <span class="text-rose-500">*</span></label>
                        <input id="first_name" type="text" name="first_name" x-model="firstName" required autocomplete="given-name" class="{{ $field('first_name') }}">
                        @error('first_name') <span class="{{ $error }}">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="{{ $label }}">Nom <span class="text-rose-500">*</span></label>
                        <input id="last_name" type="text" name="last_name" x-model="lastName" required autocomplete="family-name" class="{{ $field('last_name') }}">
                        @error('last_name') <span class="{{ $error }}">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="email" class="{{ $label }}">E-mail professionnel <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <input id="email" type="email" name="email" value="{{ old('email', $employee->email) }}" required autocomplete="email" placeholder="prenom.nom@ivosphere.com" class="{{ $field('email') }} pl-10">
                        </div>
                        @error('email') <span class="{{ $error }}">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="phone" class="{{ $label }}">Téléphone</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <input id="phone" type="tel" name="phone" inputmode="tel" autocomplete="tel" value="{{ old('phone', $employee->phone) }}" placeholder="+225 07 08 09 10 11" class="{{ $field('phone') }} pl-10">
                        </div>
                        @error('phone')
                            <span class="{{ $error }}">{{ $message }}</span>
                        @else
                            <span class="{{ $hint }}">Avec l'indicatif pays pour l'étranger (ex. +33 6 12 34 56 78). Sans indicatif : Côte d'Ivoire.</span>
                        @enderror
                    </div>
                    <div>
                        <label for="date_of_birth" class="{{ $label }}">Date de naissance</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" class="{{ $field('date_of_birth') }}">
                        @error('date_of_birth') <span class="{{ $error }}">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <span class="{{ $label }}">Genre</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['M' => 'Homme', 'F' => 'Femme', 'Autre' => 'Autre'] as $value => $text)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gender" value="{{ $value }}" class="peer sr-only" @checked(old('gender', $employee->gender) === $value)>
                                    <span class="flex h-[42px] items-center justify-center rounded-xl border border-[#E2E8F0] text-sm text-[#64748B] transition peer-checked:border-[#0066FF] peer-checked:bg-blue-50 peer-checked:font-medium peer-checked:text-[#0066FF] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]/30">{{ $text }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('gender') <span class="{{ $error }}">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            {{-- ───────── Poste ───────── --}}
            <section class="{{ $card }}">
                <div class="flex items-center gap-3 border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-[#0B0F14]">Poste et contrat</h2>
                        <p class="text-xs text-[#64748B]">Affectation et situation contractuelle.</p>
                    </div>
                </div>
                <div class="space-y-5 p-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="position" class="{{ $label }}">Poste</label>
                            <input id="position" type="text" name="position" x-model="position" placeholder="Ex. Comptable principal" class="{{ $field('position') }}">
                            @error('position') <span class="{{ $error }}">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="department" class="{{ $label }}">Département</label>
                            <input id="department" type="text" name="department" value="{{ old('department', $employee->department) }}" placeholder="Ex. Direction financière" class="{{ $field('department') }}">
                            @error('department') <span class="{{ $error }}">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="domain_id" class="{{ $label }}">Domaine d'activité <span class="text-rose-500">*</span></label>
                            <select id="domain_id" name="domain_id" required class="{{ $field('domain_id') }}">
                                <option value="">Sélectionner un domaine</option>
                                @foreach($domains as $domain)
                                    <option value="{{ $domain->id }}" @selected(old('domain_id', $employee->domain_id) == $domain->id)>{{ $domain->name }}</option>
                                @endforeach
                            </select>
                            @error('domain_id') <span class="{{ $error }}">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="hire_date" class="{{ $label }}">Date d'embauche</label>
                            <input id="hire_date" type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" class="{{ $field('hire_date') }}">
                            @error('hire_date') <span class="{{ $error }}">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <span class="{{ $label }}">Type de contrat</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['CDI', 'CDD', 'Stage'] as $contract)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="contract_type" value="{{ $contract }}" class="peer sr-only" @checked(old('contract_type', $employee->contract_type) === $contract)>
                                        <span class="flex h-[42px] items-center justify-center rounded-xl border border-[#E2E8F0] text-sm text-[#64748B] transition peer-checked:border-[#0066FF] peer-checked:bg-blue-50 peer-checked:font-medium peer-checked:text-[#0066FF] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]/30">{{ $contract }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('contract_type') <span class="{{ $error }}">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <span class="{{ $label }}">Statut du dossier <span class="text-rose-500">*</span></span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($statuses as $value => [$text, $checkedClass, $dot])
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status" value="{{ $value }}" x-model="status" required class="peer sr-only" @checked($currentStatus === $value)>
                                        <span class="flex h-[42px] items-center justify-center gap-1.5 rounded-xl border border-[#E2E8F0] text-sm text-[#64748B] transition peer-checked:font-medium peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]/30 {{ $checkedClass }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $text }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('status') <span class="{{ $error }}">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- ───────── Accès ERP ───────── --}}
            <section class="{{ $card }}">
                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6" :class="enableUser ? 'border-b border-[#E2E8F0]' : ''">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-[#0B0F14]">Accès à l'ERP</h2>
                            <p class="text-xs text-[#64748B]">Connexion, pointage, caisse et espace collaborateur.</p>
                        </div>
                    </div>
                    @unless($employee->user)
                        <label class="cursor-pointer">
                            <span class="sr-only">Créer un compte d'accès</span>
                            <input type="checkbox" name="create_user_account" value="1" x-model="enableUser" class="peer sr-only">
                            <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-[#0066FF] peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF]/40 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                        </label>
                    @endunless
                </div>

                <div x-show="enableUser" x-cloak class="space-y-4 p-5 sm:p-6">
                    @if($employee->user)
                        <input type="hidden" name="user_id" value="{{ $employee->user->id }}">
                        <div class="flex flex-wrap items-center gap-2 rounded-xl bg-[#F5F7FA] p-3.5 text-xs">
                            <span class="font-medium text-[#0B0F14]">{{ $employee->user->email }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $employee->user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $employee->user->is_active ? 'Compte actif' : 'Compte inactif' }}</span>
                            @foreach($employee->user->roles as $role)
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-[#0066FF]">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="role_id" class="{{ $label }}">Rôle dans l'ERP</label>
                            <select id="role_id" name="role_id" class="{{ $field('role_id') }}">
                                <option value="">Sélectionner un rôle</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id', $employee->user?->roles?->first()?->id) == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <span class="{{ $hint }}">Détermine les autorisations (commercial, caissier, responsable…).</span>
                        </div>
                        <div x-data="{ show: false }">
                            <label for="user_password" class="{{ $label }}">Mot de passe</label>
                            <div class="relative">
                                <input id="user_password" :type="show ? 'text' : 'password'" name="user_password" autocomplete="new-password" placeholder="••••••••" class="{{ $field('user_password') }} pr-11">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-[#0B0F14]" :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <span class="{{ $hint }}">{{ $employee->user ? 'Laisser vide pour conserver le mot de passe actuel.' : 'Laisser vide pour utiliser le mot de passe par défaut.' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ───────── Enregistrement ───────── --}}
            <div class="z-10 flex flex-col-reverse gap-2 rounded-2xl border border-[#E2E8F0] bg-white/95 p-3 shadow-[0_12px_32px_-12px_rgba(15,23,42,0.25)] backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:pl-5 md:sticky md:bottom-4">
                <p class="hidden text-xs text-[#64748B] sm:block">Les champs marqués <span class="text-rose-500">*</span> sont obligatoires.</p>
                <div class="flex gap-2">
                    <a href="{{ $isEdit ? route('rh.employees.show', $employee) : route('rh.employees.index') }}" class="inline-flex h-11 flex-1 items-center justify-center rounded-xl px-5 text-sm font-medium text-[#64748B] transition hover:bg-slate-100 hover:text-[#0B0F14] sm:flex-none">Annuler</a>
                    <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#0066FF] px-5 text-sm font-semibold text-white transition hover:bg-[#0052cc] sm:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $isEdit ? 'Enregistrer' : 'Créer le dossier' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
