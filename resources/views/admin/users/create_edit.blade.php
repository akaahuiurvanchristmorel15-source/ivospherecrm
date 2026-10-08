<x-layouts.app>
    <x-slot:title>{{ $isEdit ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header 
            title="{{ $isEdit ? 'Modifier l\'utilisateur : ' . $user->name : 'Créer un nouvel utilisateur' }}"
            description="Définissez les informations personnelles, les rôles de sécurité (Administrateur, Responsable, Agents) et les accès aux domaines métiers"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Utilisateurs', 'url' => route('admin.users.index')],
                    ['label' => $isEdit ? 'Modification' : 'Nouveau']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        @if ($errors->any())
            <x-alert type="danger" title="Veuillez corriger les erreurs suivantes :">
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form 
            method="POST" 
            action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" 
            class="space-y-6 text-xs"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- 1. Informations Générales -->
            <x-card title="1. Informations Personnelles & Identifiants">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Nom & Prénoms *</label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $user->name) }}" 
                            required 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                            placeholder="Ex: Kouamé Marc"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Adresse Email professionnelle *</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email', $user->email) }}" 
                            required 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                            placeholder="nom@ivosphere.com"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Numéro de Téléphone</label>
                        <input 
                            type="text" 
                            name="phone" 
                            value="{{ old('phone', $user->phone) }}" 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                            placeholder="+225 07 00 00 00 00"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">
                            Mot de passe {{ $isEdit ? '(laisser vide pour conserver)' : '*' }}
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            {{ $isEdit ? '' : 'required' }} 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                            placeholder="••••••••"
                        >
                    </div>
                </div>
            </x-card>

            <!-- 2. Affectation des Rôles & Permissions Dédiées -->
            @php
                $userRoleIds = old('roles', $isEdit ? $user->roles->pluck('id')->toArray() : []);
                
                $adminRoles = $roles->where('slug', 'administrateur');
                $primaryResponsable = $roles->where('slug', 'responsable');
                $specializedResponsables = $roles->filter(fn($r) => str_starts_with($r->slug, 'responsable_'));
                $agentRoles = $roles->filter(fn($r) => str_starts_with($r->slug, 'agent_'));
                $otherRoles = $roles->reject(fn($r) => $r->slug === 'administrateur' || str_starts_with($r->slug, 'responsable') || str_starts_with($r->slug, 'agent_'));

                $roleIcons = [
                    'administrateur' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                    'responsable' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    'agent_commercial_terrain' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'agent_caissier_vendeur' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    'agent_technique' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'agent_monetique' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                    'agent_cyber' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                ];

                $roleBadges = [
                    'administrateur' => ['label' => 'Accès Total Transversal', 'color' => 'bg-slate-900 text-white'],
                    'responsable' => ['label' => 'Supervision & Management', 'color' => 'bg-blue-100 text-[#0066FF] border border-blue-200'],
                    'agent_commercial_terrain' => ['label' => 'Prospection & Vente B2B', 'color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200'],
                    'agent_caissier_vendeur' => ['label' => 'Caisse & Vente Comptoir', 'color' => 'bg-amber-100 text-amber-800 border border-amber-200'],
                    'agent_technique' => ['label' => 'Technique & Ateliers PRINT', 'color' => 'bg-sky-100 text-sky-800 border border-sky-200'],
                    'agent_monetique' => ['label' => 'Monétique, TPE & Flux', 'color' => 'bg-purple-100 text-purple-800 border border-purple-200'],
                    'agent_cyber' => ['label' => 'Cybercafé & Reprographie', 'color' => 'bg-teal-100 text-teal-800 border border-teal-200'],
                ];
            @endphp

            <x-card title="2. Rôles de Sécurité & Permissions Associées (RBAC)">
                <p class="text-xs text-[#64748B] mb-4 leading-relaxed">
                    Sélectionnez le ou les rôles à assigner à cet utilisateur. Chaque rôle confère automatiquement son ensemble de permissions opérationnelles. Vous pouvez déplier chaque rôle pour inspecter ses droits.
                </p>

                <div class="space-y-6">

                    {{-- GROUPE 1 : ADMINISTRATEUR --}}
                    @if($adminRoles->isNotEmpty())
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1.5 border-b border-slate-200">
                                <span class="w-2 h-2 rounded-full bg-slate-900"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#0B0F14]">
                                    Niveau 1 : Direction & Administration
                                </h4>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                @foreach($adminRoles as $role)
                                    @php
                                        $checked = in_array($role->id, $userRoleIds);
                                        $meta = $roleBadges[$role->slug] ?? ['label' => 'Administration', 'color' => 'bg-slate-100 text-slate-800'];
                                    @endphp
                                    <div x-data="{ showPerms: false, isChecked: {{ $checked ? 'true' : 'false' }} }" 
                                         :class="isChecked ? 'border-[#0066FF] bg-blue-50/20 ring-1 ring-[#0066FF]/30' : 'border-[#E2E8F0] bg-white hover:border-slate-300'"
                                         class="p-4 rounded-xl border transition-all">
                                        <div class="flex items-start gap-3">
                                            <input 
                                                type="checkbox" 
                                                name="roles[]" 
                                                value="{{ $role->id }}" 
                                                x-model="isChecked"
                                                id="role_{{ $role->id }}"
                                                class="mt-1 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF] cursor-pointer"
                                            >
                                            <div class="flex-1">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <label for="role_{{ $role->id }}" class="font-bold text-sm text-[#0B0F14] cursor-pointer hover:text-[#0066FF] flex items-center gap-2">
                                                        <span>{{ $role->name }}</span>
                                                    </label>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $meta['color'] }}">
                                                        {{ $meta['label'] }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-[#64748B] mt-1">{{ $role->description }}</p>

                                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                        ✓ 100% des privilèges (Toutes les permissions de l'ERP)
                                                    </span>
                                                    <button type="button" @click="showPerms = !showPerms" class="text-[11px] font-semibold text-[#0066FF] hover:underline flex items-center gap-1">
                                                        <span x-text="showPerms ? 'Masquer détails' : 'Voir les permissions'"></span>
                                                        <svg class="w-3 h-3 transition-transform" :class="showPerms ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                </div>

                                                <div x-show="showPerms" x-cloak class="flex flex-wrap gap-1 mt-2.5 max-h-36 overflow-y-auto p-2 rounded-lg bg-[#F8FAFC] border border-slate-200">
                                                    @foreach($role->permissions as $p)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-[#E2E8F0] text-slate-700">
                                                            {{ $p->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- GROUPE 2 : RESPONSABLE --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2 pb-1.5 border-b border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#0B0F14]">
                                Niveau 2 : Responsable & Encadrement
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            {{-- Responsable Général / Opérationnel --}}
                            @foreach($primaryResponsable as $role)
                                @php
                                    $checked = in_array($role->id, $userRoleIds);
                                    $meta = $roleBadges[$role->slug] ?? ['label' => 'Supervision', 'color' => 'bg-blue-100 text-[#0066FF]'];
                                @endphp
                                <div x-data="{ showPerms: false, isChecked: {{ $checked ? 'true' : 'false' }} }" 
                                     :class="isChecked ? 'border-[#0066FF] bg-blue-50/20 ring-1 ring-[#0066FF]/30' : 'border-[#E2E8F0] bg-white hover:border-slate-300'"
                                     class="p-4 rounded-xl border transition-all">
                                    <div class="flex items-start gap-3">
                                        <input 
                                            type="checkbox" 
                                            name="roles[]" 
                                            value="{{ $role->id }}" 
                                            x-model="isChecked"
                                            id="role_{{ $role->id }}"
                                            class="mt-1 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF] cursor-pointer"
                                        >
                                        <div class="flex-1">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <label for="role_{{ $role->id }}" class="font-bold text-sm text-[#0B0F14] cursor-pointer hover:text-[#0066FF] flex items-center gap-2">
                                                    <span>{{ $role->name }}</span>
                                                </label>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $meta['color'] }}">
                                                    {{ $meta['label'] }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-[#64748B] mt-1">{{ $role->description }}</p>

                                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                                <span class="text-[11px] font-semibold text-[#0066FF] bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                                                    {{ $role->permissions->count() }} permissions opérationnelles
                                                </span>
                                                <button type="button" @click="showPerms = !showPerms" class="text-[11px] font-semibold text-[#0066FF] hover:underline flex items-center gap-1">
                                                    <span x-text="showPerms ? 'Masquer détails' : 'Voir les {{ $role->permissions->count() }} permissions'"></span>
                                                    <svg class="w-3 h-3 transition-transform" :class="showPerms ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            </div>

                                            <div x-show="showPerms" x-cloak class="flex flex-wrap gap-1 mt-2.5 max-h-36 overflow-y-auto p-2 rounded-lg bg-[#F8FAFC] border border-slate-200">
                                                @foreach($role->permissions as $p)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-[#E2E8F0] text-slate-700">
                                                        {{ $p->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Responsables Spécialisés (RH, Commercial, Finance, Com) --}}
                            @if($specializedResponsables->isNotEmpty())
                                <div x-data="{ showSpecialized: false }" class="mt-1">
                                    <button type="button" @click="showSpecialized = !showSpecialized" 
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#64748B] hover:text-[#0066FF] py-1 transition">
                                        <svg class="w-3.5 h-3.5 transition-transform" :class="showSpecialized ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        <span x-text="showSpecialized ? 'Masquer les responsables spécialisés par pôle' : 'Afficher d\'autres profils responsables (RH, Commercial, Finance, Com)'"></span>
                                    </button>

                                    <div x-show="showSpecialized" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                        @foreach($specializedResponsables as $role)
                                            @php
                                                $checked = in_array($role->id, $userRoleIds);
                                            @endphp
                                            <div x-data="{ showPerms: false, isChecked: {{ $checked ? 'true' : 'false' }} }" 
                                                 :class="isChecked ? 'border-[#0066FF] bg-blue-50/20' : 'border-[#E2E8F0] bg-white hover:border-slate-300'"
                                                 class="p-3 rounded-xl border transition-all">
                                                <div class="flex items-start gap-2.5">
                                                    <input 
                                                        type="checkbox" 
                                                        name="roles[]" 
                                                        value="{{ $role->id }}" 
                                                        x-model="isChecked"
                                                        id="role_{{ $role->id }}"
                                                        class="mt-1 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF] cursor-pointer"
                                                    >
                                                    <div class="flex-1">
                                                        <label for="role_{{ $role->id }}" class="font-bold text-xs text-[#0B0F14] cursor-pointer hover:text-[#0066FF] block">
                                                            {{ $role->name }}
                                                        </label>
                                                        <p class="text-[11px] text-[#64748B] mt-0.5 line-clamp-1">{{ $role->description }}</p>
                                                        <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-100 text-[10px]">
                                                            <span class="text-[#0066FF] font-medium">{{ $role->permissions->count() }} permissions</span>
                                                            <button type="button" @click="showPerms = !showPerms" class="text-[#0066FF] hover:underline">
                                                                <span x-text="showPerms ? 'Fermer' : 'Détails'"></span>
                                                            </button>
                                                        </div>
                                                        <div x-show="showPerms" x-cloak class="flex flex-wrap gap-1 mt-1.5 max-h-28 overflow-y-auto p-1.5 rounded bg-slate-50 border border-slate-200">
                                                            @foreach($role->permissions as $p)
                                                                <span class="px-1 py-0.5 rounded text-[9px] bg-white border border-slate-200 text-slate-700">{{ $p->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- GROUPE 3 : CORPS DES AGENTS OPÉRATIONNELS --}}
                    @if($agentRoles->isNotEmpty())
                        <div>
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-200 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#0B0F14]">
                                        Niveau 3 : Corps des Agents (Agents Opérationnels)
                                    </h4>
                                </div>
                                <span class="text-[11px] text-[#64748B] font-medium">5 profils agents métier</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                @foreach($agentRoles as $role)
                                    @php
                                        $checked = in_array($role->id, $userRoleIds);
                                        $meta = $roleBadges[$role->slug] ?? ['label' => 'Agent Métier', 'color' => 'bg-slate-100 text-slate-800'];
                                        $iconSvg = $roleIcons[$role->slug] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>';
                                    @endphp
                                    <div x-data="{ showPerms: false, isChecked: {{ $checked ? 'true' : 'false' }} }" 
                                         :class="isChecked ? 'border-[#0066FF] bg-blue-50/20 ring-1 ring-[#0066FF]/30' : 'border-[#E2E8F0] bg-white hover:border-slate-300'"
                                         class="p-4 rounded-xl border transition-all flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-start gap-3">
                                                <input 
                                                    type="checkbox" 
                                                    name="roles[]" 
                                                    value="{{ $role->id }}" 
                                                    x-model="isChecked"
                                                    id="role_{{ $role->id }}"
                                                    class="mt-1 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF] cursor-pointer"
                                                >
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 text-[#0066FF]">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                {!! $iconSvg !!}
                                                            </svg>
                                                        </div>
                                                        <label for="role_{{ $role->id }}" class="font-bold text-xs text-[#0B0F14] cursor-pointer hover:text-[#0066FF]">
                                                            {{ $role->name }}
                                                        </label>
                                                    </div>

                                                    <div class="mt-1.5">
                                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $meta['color'] }}">
                                                            {{ $meta['label'] }}
                                                        </span>
                                                    </div>

                                                    <p class="text-[11px] text-[#64748B] mt-1.5 leading-relaxed">
                                                        {{ $role->description }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 pt-2.5 border-t border-slate-100">
                                            <div class="flex items-center justify-between text-[11px]">
                                                <span class="font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                                    {{ $role->permissions->count() }} permissions
                                                </span>
                                                <button type="button" @click="showPerms = !showPerms" class="text-[11px] font-semibold text-[#0066FF] hover:underline flex items-center gap-1">
                                                    <span x-text="showPerms ? 'Masquer' : 'Voir les droits'"></span>
                                                    <svg class="w-3 h-3 transition-transform" :class="showPerms ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                            </div>

                                            <div x-show="showPerms" x-cloak class="flex flex-wrap gap-1 mt-2 max-h-32 overflow-y-auto p-2 rounded-lg bg-[#F8FAFC] border border-slate-200">
                                                @foreach($role->permissions as $p)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-[#E2E8F0] text-slate-700">
                                                        {{ $p->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Autres rôles éventuels --}}
                    @if($otherRoles->isNotEmpty())
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1.5 border-b border-slate-200">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">
                                    Autres Rôles
                                </h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($otherRoles as $role)
                                    @php
                                        $checked = in_array($role->id, $userRoleIds);
                                    @endphp
                                    <label class="flex items-start gap-3 p-3 rounded-lg border border-[#E2E8F0] hover:border-[#0066FF] bg-[#F5F7FA]/40 hover:bg-white cursor-pointer transition">
                                        <input 
                                            type="checkbox" 
                                            name="roles[]" 
                                            value="{{ $role->id }}" 
                                            {{ $checked ? 'checked' : '' }}
                                            class="mt-0.5 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]"
                                        >
                                        <div>
                                            <span class="font-semibold text-[#0B0F14] block">{{ $role->name }}</span>
                                            <span class="text-[11px] text-[#64748B] block mt-0.5">{{ $role->description }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </x-card>

            <!-- 3. Affectation des Domaines d'Activité -->
            <x-card 
                title="3. Périmètre & Domaines Métiers Autorisés" 
                x-data="{ allDomains: {{ old('all_domains', $user->all_domains ?? false) ? 'true' : 'false' }} }"
            >
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0] mb-3">
                    <span class="text-xs text-[#64748B]">Définissez les domaines accessibles par cet utilisateur</span>
                    <label class="flex items-center gap-2 cursor-pointer text-[#0066FF] font-semibold">
                        <input 
                            type="checkbox" 
                            name="all_domains" 
                            value="1" 
                            x-model="allDomains" 
                            class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]"
                        >
                        <span>Accès à tous les domaines (Transversal)</span>
                    </label>
                </div>

                <div x-show="!allDomains" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($domains as $domain)
                        @php
                            $dChecked = in_array($domain->id, old('domains', $isEdit ? $user->domains->pluck('id')->toArray() : []));
                        @endphp
                        <label class="flex items-center gap-2.5 p-3 rounded-lg border border-[#E2E8F0] hover:border-[#0066FF] bg-[#F5F7FA]/40 hover:bg-white cursor-pointer transition">
                            <input 
                                type="checkbox" 
                                name="domains[]" 
                                value="{{ $domain->id }}" 
                                {{ $dChecked ? 'checked' : '' }}
                                class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]"
                            >
                            <span class="font-medium text-[#0B0F14]">{{ $domain->name }}</span>
                        </label>
                    @endforeach
                </div>
            </x-card>

            <!-- 4. Profil Collaborateur & RH (Fiche Employé Associée) -->
            <x-card title="4. Profil Collaborateur & RH (Fiche Employé Associée)">
                <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-lg text-xs text-[#0066FF] flex items-start gap-2.5 mb-4">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold text-[#0B0F14]">Règle Métier IVOSPHERE : Agents et Responsables sont tous des collaborateurs</p>
                        <p class="text-[11px] text-[#64748B] mt-0.5">Une fiche employé RH est automatiquement créée ou synchronisée pour le pointage mobile, les fiches de tâches journalières, les plannings et les évaluations /30. Vous pouvez personnaliser les champs ci-dessous ou laisser le système les déduire automatiquement des rôles sélectionnés.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-[#0B0F14]">Matricule Employé</label>
                            <span class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded font-semibold">Non modifiable</span>
                        </div>
                        <input 
                            type="text" 
                            name="employee_code" 
                            value="{{ old('employee_code', $user->employee->employee_code ?? '') }}" 
                            readonly
                            class="w-full px-3.5 py-2 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] text-[#0B0F14] font-mono cursor-not-allowed select-none text-xs transition"
                            placeholder="Généré automatiquement par le système"
                            tabindex="-1"
                        >
                        <span class="text-[10px] text-[#64748B] mt-1 block">Attribué automatiquement par le système et immuable.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Intitulé du Poste</label>
                        <input 
                            type="text" 
                            name="position" 
                            value="{{ old('position', $user->employee->position ?? '') }}" 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] text-xs transition"
                            placeholder="Déduit du rôle principal"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Département / Pôle</label>
                        <input 
                            type="text" 
                            name="department" 
                            value="{{ old('department', $user->employee->department ?? '') }}" 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] text-xs transition"
                            placeholder="Déduit du domaine/rôle"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date d'embauche</label>
                        <input 
                            type="date" 
                            name="hire_date" 
                            value="{{ old('hire_date', isset($user->employee->hire_date) ? $user->employee->hire_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" 
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition"
                        >
                    </div>
                </div>
            </x-card>

            <!-- 5. Statut du Compte & Actions -->
            <x-card>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <label class="font-semibold text-[#0B0F14] block">Statut du compte</label>
                        <span class="text-[#64748B] text-[11px]">Un compte désactivé ne peut pas s'authentifier</span>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="hidden" 
                            name="is_active" 
                            value="0"
                        >
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1" 
                            {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}
                            class="rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF]"
                        >
                        <span class="text-[#0B0F14] font-medium">Compte Actif</span>
                    </label>
                </div>

                <div class="pt-5 border-t border-[#E2E8F0] mt-5 flex items-center justify-end gap-3">
                    <x-button variant="secondary" href="{{ route('admin.users.index') }}">
                        Annuler
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ $isEdit ? 'Mettre à jour l\'utilisateur' : 'Créer l\'utilisateur' }}
                    </x-button>
                </div>
            </x-card>

        </form>
    </div>
</x-layouts.app>
