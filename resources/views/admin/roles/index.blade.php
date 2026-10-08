<x-layouts.app>
    <x-slot:title>Gestion des Rôles & Permissions (RBAC) — IVOSPHERE ERP</x-slot>

    @php
        $groupMeta = [
            'admin' => [
                'title' => 'Administration & Sécurité',
                'desc' => 'Contrôle des accès utilisateurs, audit trail, configuration système et sécurité.',
                'badge' => 'Système',
            ],
            'rh' => [
                'title' => 'Pôle Ressources Humaines',
                'desc' => 'Dossiers employés, contrats de travail, congés, présences et évaluations.',
                'badge' => 'RH',
            ],
            'commercial' => [
                'title' => 'Pôle Commercial & CRM',
                'desc' => 'Fichier clients, prospection, devis, commandes de vente et statistiques.',
                'badge' => 'Ventes',
            ],
            'finance' => [
                'title' => 'Pôle Finances & Multi-Caisses',
                'desc' => 'Factures clients & fournisseurs, règlements, trésorerie et dépenses.',
                'badge' => 'Finances',
            ],
            'stock' => [
                'title' => 'Gestion des Stocks & Entrepôts',
                'desc' => 'Catalogue articles, mouvements de stocks, seuils d\'alerte et inventaires.',
                'badge' => 'Stocks',
            ],
            'communication' => [
                'title' => 'Pôle Communication & Réseaux',
                'desc' => 'Campagnes marketing, calendrier éditorial et publications digitales.',
                'badge' => 'Médias',
            ],
            'domain_print' => [
                'title' => 'Domaine IVOSPHERE PRINT',
                'desc' => 'Atelier d\'impression numérique, validation des BAT et production papeterie.',
                'badge' => 'PRINT',
            ],
            'domain_sport' => [
                'title' => 'Domaine IVOSPHERE SPORT',
                'desc' => 'Articles de sport, flocage personnalisé, confection et équipements clubs.',
                'badge' => 'SPORT',
            ],
            'domain_tech' => [
                'title' => 'Domaine IVOSPHERE TECH',
                'desc' => 'Développement applicatif, déploiement d\'agents IA et maintenance logicielle.',
                'badge' => 'TECH',
            ],
            'domain_media' => [
                'title' => 'Domaine IVOSPHERE MEDIA & EVENTS',
                'desc' => 'Studio photo & vidéo, sonorisation, reportages et régie événementielle.',
                'badge' => 'MEDIA',
            ],
            'domain_assurance' => [
                'title' => 'Domaine IVOSPHERE ASSURANCE',
                'desc' => 'Courtage d\'assurances, contrats prévoyance, santé et commissions courtiers.',
                'badge' => 'ASSURANCE',
            ],
        ];

        $initialRole = request('role') ? $roles->firstWhere('id', request('role')) : $roles->first();
        $initialRoleId = $initialRole?->id ?? $roles->first()?->id;
    @endphp

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ activeRoleId: '{{ $initialRoleId }}' }">

        {{-- 1. En-tête de page & Fil d'Ariane --}}
        <x-page-header 
            title="Matrice des Rôles & Permissions"
            description="Contrôlez finement les autorisations d'accès fonctionnelles et les privilèges RBAC pour chaque profil de la plateforme."
            :breadcrumbs="[
                ['label' => 'Administration', 'url' => route('admin.users.index')],
                ['label' => 'Rôles & Permissions']
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('admin.users.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] transition shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Comptes Utilisateurs</span>
                </a>
                <a href="{{ route('admin.logs.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] transition shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Journal d'Audit</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- 2. Message de succès / retour opérationnel --}}
        @if(session('success'))
            <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs shadow-xs" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-emerald-900">Mise à jour enregistrée</p>
                        <p class="text-emerald-700 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. Métriques Exécutives RBAC --}}
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" aria-label="Statistiques RBAC">
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider">Rôles Définis</p>
                    <p class="text-2xl font-bold tracking-tight text-[#0B0F14] mt-1">{{ $roles->count() }}</p>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tous actifs
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-center text-[#0B0F14]">
                    <svg class="w-5 h-5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider">Droits Disponibles</p>
                    <p class="text-2xl font-bold tracking-tight text-[#0B0F14] mt-1">{{ $totalPermissionsCount }}</p>
                    <span class="text-[11px] font-medium text-[#64748B] mt-1 block">
                        {{ $permissions->count() }} pôles fonctionnels
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-center text-[#0B0F14]">
                    <svg class="w-5 h-5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider">Affectations Utilisateurs</p>
                    <p class="text-2xl font-bold tracking-tight text-[#0B0F14] mt-1">{{ $roles->sum('users_count') }}</p>
                    <span class="text-[11px] font-medium text-[#64748B] mt-1 block">
                        Comptes opérationnels
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-center text-[#0B0F14]">
                    <svg class="w-5 h-5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-medium text-[#64748B] uppercase tracking-wider">Modèle de Sécurité</p>
                    <p class="text-lg font-bold tracking-tight text-[#0B0F14] mt-1">RBAC Strict</p>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#0066FF] mt-1">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0066FF] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#0066FF]"></span>
                        </span>
                        Contrôle actif
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex items-center justify-center text-[#0B0F14]">
                    <svg class="w-5 h-5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
        </section>

        {{-- 4. Grille de sélection des profils / Rôles --}}
        <section aria-labelledby="roles-list-title">
            <div class="flex items-center justify-between mb-3 px-1">
                <div>
                    <h2 id="roles-list-title" class="text-xs font-bold uppercase tracking-wider text-[#64748B]">
                        Sélectionner un Rôle Fonctionnel
                    </h2>
                    <p class="text-xs text-[#64748B]">Cliquez sur un profil pour inspecter et ajuster ses habilitations.</p>
                </div>
                <span class="text-xs font-semibold text-[#0066FF]">
                    {{ $roles->count() }} profils configurés
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5" role="tablist">
                @foreach($roles as $r)
                    @php
                        $rPermsCount = $r->permissions->count();
                        $rPercent = $totalPermissionsCount > 0 ? round(($rPermsCount / $totalPermissionsCount) * 100) : 0;
                        $isAdmin = $r->slug === 'administrateur';
                    @endphp
                    <button 
                        type="button" 
                        role="tab"
                        id="role-tab-{{ $r->id }}"
                        @click="activeRoleId = '{{ $r->id }}'"
                        :aria-selected="activeRoleId == '{{ $r->id }}'"
                        aria-controls="role-panel-{{ $r->id }}"
                        :class="activeRoleId == '{{ $r->id }}' 
                            ? 'bg-white border-[#0066FF] shadow-sm ring-2 ring-[#0066FF]/20 -translate-y-0.5' 
                            : 'bg-white border-[#E2E8F0] hover:border-slate-300 hover:bg-[#F5F7FA]/40'"
                        class="p-4 rounded-2xl border text-left transition-all duration-150 cursor-pointer flex flex-col justify-between group relative overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]"
                    >
                        {{-- Indicateur de sélection active --}}
                        <div 
                            x-show="activeRoleId == '{{ $r->id }}'"
                            class="absolute top-0 left-0 right-0 h-1 bg-[#0066FF]"
                            x-cloak
                        ></div>

                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <div :class="activeRoleId == '{{ $r->id }}' ? 'bg-[#0066FF] text-white' : 'bg-[#F5F7FA] text-[#0B0F14] group-hover:bg-[#0066FF]/10 group-hover:text-[#0066FF]'"
                                         class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold transition-colors shrink-0">
                                        @if($isAdmin)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-[#0B0F14] leading-snug line-clamp-1">{{ $r->name }}</h3>
                                        <span class="font-mono text-[10px] text-[#64748B] block">slug: {{ $r->slug }}</span>
                                    </div>
                                </div>
                                <span :class="activeRoleId == '{{ $r->id }}' ? 'bg-[#0066FF]/10 text-[#0066FF]' : 'bg-[#F5F7FA] text-[#64748B]'"
                                      class="text-[10px] px-2 py-0.5 rounded-full font-bold transition-colors shrink-0">
                                    {{ $rPermsCount }} / {{ $totalPermissionsCount }}
                                </span>
                            </div>

                            <p class="text-xs text-[#64748B] line-clamp-2 leading-relaxed mt-1">
                                {{ $r->description ?? 'Aucune description spécifique renseignée.' }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-[#E2E8F0]">
                            <div class="flex items-center justify-between text-[11px] mb-1.5">
                                <span class="text-[#64748B] flex items-center gap-1">
                                    <svg class="w-3 h-3 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    {{ $r->users_count }} utilisateur(s)
                                </span>
                                <span class="font-semibold" :class="activeRoleId == '{{ $r->id }}' ? 'text-[#0066FF]' : 'text-[#0B0F14]'">
                                    {{ $rPercent }}%
                                </span>
                            </div>
                            <div class="w-full bg-[#F5F7FA] h-1.5 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $rPercent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="bg-[#0066FF] h-full rounded-full transition-all duration-300" style="width: {{ $rPercent }}%"></div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- 5. Espaces de gestion des permissions par Rôle --}}
        @foreach($roles as $role)
            @php
                $rolePermIds = $role->permissions->pluck('id')->values()->all();
                $allPermIds = $permissions->flatten()->pluck('id')->values()->all();
                $allPermsFlat = $permissions->flatten()->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'group' => $p->group,
                ])->values()->all();
            @endphp

            <div 
                x-show="activeRoleId == '{{ $role->id }}'" 
                id="role-panel-{{ $role->id }}"
                role="tabpanel"
                aria-labelledby="role-tab-{{ $role->id }}"
                x-cloak
                x-data="{
                    selected: {{ json_encode($rolePermIds) }},
                    initial: {{ json_encode($rolePermIds) }},
                    allPerms: {{ json_encode($allPermsFlat) }},
                    searchQuery: '',
                    selectedGroup: 'all',
                    isSaving: false,

                    isSelected(id) {
                        return this.selected.includes(Number(id));
                    },
                    toggle(id) {
                        id = Number(id);
                        if (this.selected.includes(id)) {
                            this.selected = this.selected.filter(i => i !== id);
                        } else {
                            this.selected.push(id);
                        }
                    },
                    selectAll(ids) {
                        const numIds = ids.map(Number);
                        const s = new Set([...this.selected, ...numIds]);
                        this.selected = Array.from(s);
                    },
                    deselectAll(ids) {
                        const numIds = ids.map(Number);
                        this.selected = this.selected.filter(id => !numIds.includes(id));
                    },
                    toggleGroup(ids) {
                        const numIds = ids.map(Number);
                        const allChecked = numIds.every(id => this.selected.includes(id));
                        if (allChecked) {
                            this.deselectAll(ids);
                        } else {
                            this.selectAll(ids);
                        }
                    },
                    isGroupFullyChecked(ids) {
                        const numIds = ids.map(Number);
                        return numIds.length > 0 && numIds.every(id => this.selected.includes(id));
                    },
                    countSelectedInGroup(ids) {
                        const numIds = ids.map(Number);
                        return numIds.filter(id => this.selected.includes(id)).length;
                    },
                    resetToInitial() {
                        this.selected = [...this.initial];
                    },
                    isDirty() {
                        if (this.selected.length !== this.initial.length) return true;
                        return !this.initial.every(id => this.selected.includes(id));
                    },
                    matchesSearch(name, slug, group) {
                        if (this.selectedGroup !== 'all' && this.selectedGroup !== group) {
                            return false;
                        }
                        if (!this.searchQuery || this.searchQuery.trim() === '') {
                            return true;
                        }
                        const q = this.searchQuery.toLowerCase().trim();
                        return name.toLowerCase().includes(q) || slug.toLowerCase().includes(q);
                    },
                    groupHasVisible(perms, group) {
                        if (this.selectedGroup !== 'all' && this.selectedGroup !== group) {
                            return false;
                        }
                        if (!this.searchQuery || this.searchQuery.trim() === '') {
                            return true;
                        }
                        const q = this.searchQuery.toLowerCase().trim();
                        return perms.some(p => p.name.toLowerCase().includes(q) || p.slug.toLowerCase().includes(q));
                    },
                    totalVisibleCount() {
                        return this.allPerms.filter(p => this.matchesSearch(p.name, p.slug, p.group)).length;
                    }
                }"
                class="space-y-6"
            >
                {{-- Formulaire de mise à jour des permissions du rôle actif --}}
                <form method="POST" action="{{ route('admin.roles.update', $role) }}" @submit="isSaving = true">
                    @csrf
                    @method('PUT')

                    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
                        {{-- En-tête de l'espace de configuration --}}
                        <div class="p-5 sm:p-6 border-b border-[#E2E8F0] bg-white">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex items-start sm:items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center shrink-0">
                                        @if($role->slug === 'administrateur')
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="text-lg font-bold text-[#0B0F14]">Droits du Rôle : {{ $role->name }}</h2>
                                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-lg bg-[#F5F7FA] text-[#64748B] border border-[#E2E8F0]">
                                                slug: {{ $role->slug }}
                                            </span>
                                            <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-lg bg-[#0066FF]/10 text-[#0066FF]">
                                                {{ $role->users_count }} utilisateur(s) rattaché(s)
                                            </span>
                                        </div>
                                        <p class="text-xs text-[#64748B] mt-1">{{ $role->description }}</p>
                                    </div>
                                </div>

                                {{-- Compteur en temps réel et indicateur de modification --}}
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-xs font-semibold text-[#0B0F14]">
                                            <span x-text="selected.length" class="text-sm font-bold text-[#0066FF]"></span>
                                            <span class="text-[#64748B]">/ {{ $totalPermissionsCount }} autorisations</span>
                                        </p>
                                        <p class="text-[11px] text-[#64748B]" x-show="isDirty()" x-cloak>
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span>Modifications non enregistrées
                                        </p>
                                    </div>

                                    <button 
                                        type="submit" 
                                        :disabled="isSaving"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white transition shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2"
                                    >
                                        <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <svg x-show="isSaving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Sauvegarder</span>
                                    </button>
                                </div>
                            </div>

                            @if($role->slug === 'administrateur')
                                <div class="mt-4 p-3 rounded-xl bg-blue-50/70 border border-blue-200/80 text-blue-900 text-xs flex items-start sm:items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 text-[#0066FF]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-blue-950">Profil Super-Administrateur</p>
                                        <p class="text-blue-800 text-[11px] mt-0.5">
                                            L'administrateur possède par défaut l'intégralité des privilèges de la plateforme via la police de sécurité RBAC. Vous pouvez toutefois ajuster les droits cochés ci-dessous pour standardiser les profils secondaires.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Barre d'outils interactive (Recherche dynamique + Filtres de pôles + Actions globales) --}}
                        <div class="p-4 sm:p-5 bg-[#F5F7FA]/70 border-b border-[#E2E8F0] space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                {{-- Recherche instantanée --}}
                                <div class="relative flex-1 max-w-lg">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        x-model="searchQuery" 
                                        placeholder="Filtrer une permission par mot-clé (ex: devis, factures, utilisateurs...)"
                                        class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-[#E2E8F0] bg-white text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:ring-2 focus:ring-[#0066FF] transition shadow-2xs"
                                    >
                                    <button 
                                        type="button" 
                                        x-show="searchQuery.length > 0" 
                                        @click="searchQuery = ''"
                                        x-cloak
                                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#64748B] hover:text-[#0B0F14] cursor-pointer"
                                        aria-label="Effacer la recherche"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                {{-- Boutons d'action globale (Tout cocher / Tout décocher / Réinitialiser) --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <button 
                                        type="button" 
                                        @click="selectAll({{ json_encode($allPermIds) }})"
                                        class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] bg-white text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]"
                                    >
                                        <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Tout cocher</span>
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="deselectAll({{ json_encode($allPermIds) }})"
                                        class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] bg-white text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]"
                                    >
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Tout décocher</span>
                                    </button>

                                    <button 
                                        type="button" 
                                        x-show="isDirty()"
                                        @click="resetToInitial()"
                                        x-cloak
                                        class="px-3 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-900 hover:bg-amber-100 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                                        title="Rétablir les permissions d'origine avant modification"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Rétablir</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Filtres par Pôle fonctionnel --}}
                            <div class="flex items-center gap-1.5 overflow-x-auto pt-1 pb-0.5 [scrollbar-width:none]">
                                <button 
                                    type="button" 
                                    @click="selectedGroup = 'all'"
                                    :class="selectedGroup === 'all' ? 'bg-[#0B0F14] text-white border-[#0B0F14]' : 'bg-white text-[#64748B] hover:text-[#0B0F14] border-[#E2E8F0]'"
                                    class="shrink-0 px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition-colors cursor-pointer"
                                >
                                    Tous les pôles ({{ $totalPermissionsCount }})
                                </button>
                                @foreach($permissions as $groupKey => $permsInGroup)
                                    <button 
                                        type="button" 
                                        @click="selectedGroup = '{{ $groupKey }}'"
                                        :class="selectedGroup === '{{ $groupKey }}' ? 'bg-[#0066FF] text-white border-[#0066FF]' : 'bg-white text-[#64748B] hover:text-[#0B0F14] border-[#E2E8F0]'"
                                        class="shrink-0 px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition-colors cursor-pointer"
                                    >
                                        {{ $groupMeta[$groupKey]['badge'] ?? ucfirst($groupKey) }} ({{ $permsInGroup->count() }})
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Grille des Modules & Permissions --}}
                        <div class="p-5 sm:p-6 space-y-6">
                            @foreach($permissions as $groupKey => $perms)
                                @php
                                    $groupPermIds = $perms->pluck('id')->all();
                                    $meta = $groupMeta[$groupKey] ?? [
                                        'title' => ucfirst($groupKey),
                                        'desc' => 'Permissions du module ' . $groupKey,
                                        'badge' => ucfirst($groupKey),
                                    ];
                                @endphp

                                <fieldset 
                                    x-show="groupHasVisible({{ json_encode($perms->map(fn($p) => ['name' => $p->name, 'slug' => $p->slug])) }}, '{{ $groupKey }}')"
                                    class="rounded-2xl border border-[#E2E8F0] bg-[#F5F7FA]/30 overflow-hidden"
                                >
                                    {{-- En-tête du groupe de permissions --}}
                                    <div class="p-4 bg-white border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                                                <legend class="font-bold text-sm text-[#0B0F14]">
                                                    {{ $meta['title'] }}
                                                </legend>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#F5F7FA] text-[#64748B] border border-[#E2E8F0]">
                                                    {{ $meta['badge'] }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-[#64748B] mt-0.5 ml-4">
                                                {{ $meta['desc'] }}
                                            </p>
                                        </div>

                                        {{-- Contrôle du pôle : bouton tout cocher / décocher le pôle + compteur dynamique --}}
                                        <div class="flex items-center gap-2.5 ml-4 sm:ml-0 shrink-0">
                                            <span class="text-xs font-semibold text-[#0B0F14] bg-[#F5F7FA] px-2.5 py-1 rounded-lg border border-[#E2E8F0]">
                                                <span x-text="countSelectedInGroup({{ json_encode($groupPermIds) }})" class="text-[#0066FF] font-bold"></span>
                                                <span class="text-[#64748B]">/ {{ count($groupPermIds) }}</span>
                                            </span>

                                            <button 
                                                type="button" 
                                                @click="toggleGroup({{ json_encode($groupPermIds) }})"
                                                :class="isGroupFullyChecked({{ json_encode($groupPermIds) }}) ? 'border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100' : 'border-[#E2E8F0] text-[#0066FF] bg-white hover:bg-[#F5F7FA]'"
                                                class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition cursor-pointer flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF]"
                                            >
                                                <span x-text="isGroupFullyChecked({{ json_encode($groupPermIds) }}) ? 'Tout décocher ce pôle' : 'Tout cocher ce pôle'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Cartes de permissions individuelles --}}
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2.5">
                                        @foreach($perms as $perm)
                                            <label 
                                                x-show="matchesSearch('{{ addslashes($perm->name) }}', '{{ addslashes($perm->slug) }}', '{{ $groupKey }}')"
                                                :class="isSelected({{ $perm->id }}) 
                                                    ? 'bg-[#0066FF]/5 border-[#0066FF]/40 text-[#0B0F14] shadow-2xs' 
                                                    : 'bg-white border-[#E2E8F0] hover:border-slate-300 text-[#0B0F14]'"
                                                class="relative flex items-start gap-3 p-3 rounded-xl border transition-all duration-150 cursor-pointer select-none group"
                                            >
                                                <div class="pt-0.5">
                                                    <input 
                                                        type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $perm->id }}" 
                                                        :checked="isSelected({{ $perm->id }})"
                                                        @change="toggle({{ $perm->id }})"
                                                        class="h-4 w-4 rounded border-[#E2E8F0] text-[#0066FF] focus:ring-[#0066FF] transition cursor-pointer"
                                                    >
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <span class="font-semibold text-xs leading-snug block group-hover:text-[#0066FF] transition-colors">
                                                        {{ $perm->name }}
                                                    </span>
                                                    <span class="font-mono text-[10px] text-[#64748B] block mt-0.5 truncate" title="{{ $perm->slug }}">
                                                        {{ $perm->slug }}
                                                    </span>
                                                </div>
                                                <span 
                                                    x-show="isSelected({{ $perm->id }})"
                                                    class="w-1.5 h-1.5 rounded-full bg-[#0066FF] shrink-0 mt-1.5"
                                                    title="Permission active"
                                                ></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach

                            {{-- État vide en cas de recherche infructueuse --}}
                            <div 
                                x-show="totalVisibleCount() === 0" 
                                x-cloak
                                class="py-12 px-4 text-center rounded-2xl border border-dashed border-[#E2E8F0] bg-[#F5F7FA]/50"
                            >
                                <div class="w-12 h-12 rounded-2xl bg-white border border-[#E2E8F0] flex items-center justify-center mx-auto text-[#64748B] mb-3 shadow-2xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-[#0B0F14]">Aucune permission correspondante</h3>
                                <p class="text-xs text-[#64748B] max-w-sm mx-auto mt-1">
                                    Aucun droit ne correspond à « <strong class="text-[#0B0F14]" x-text="searchQuery"></strong> ». Essayez un autre terme ou réinitialisez les filtres.
                                </p>
                                <button 
                                    type="button" 
                                    @click="searchQuery = ''; selectedGroup = 'all'"
                                    class="mt-4 px-3.5 py-1.5 rounded-xl bg-white border border-[#E2E8F0] text-xs font-semibold text-[#0066FF] hover:bg-[#F5F7FA] transition cursor-pointer"
                                >
                                    Effacer les filtres
                                </button>
                            </div>
                        </div>

                        {{-- Barre d'action inférieure (Enregistrement sticky / permanent) --}}
                        <div class="p-4 sm:p-5 bg-[#F5F7FA] border-t border-[#E2E8F0] flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-[#64748B] text-center sm:text-left">
                                <span class="font-medium text-[#0B0F14]">Configuration du rôle :</span>
                                <strong class="text-[#0066FF]">{{ $role->name }}</strong> ·
                                <span x-text="selected.length" class="font-bold text-[#0B0F14]"></span> permissions cochées sur {{ $totalPermissionsCount }}
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                                <button 
                                    type="button" 
                                    x-show="isDirty()"
                                    @click="resetToInitial()"
                                    x-cloak
                                    class="px-4 py-2 text-xs font-semibold rounded-xl bg-white border border-[#E2E8F0] text-[#0B0F14] hover:bg-slate-100 transition cursor-pointer"
                                >
                                    Annuler
                                </button>

                                <button 
                                    type="submit" 
                                    :disabled="isSaving"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-semibold rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white transition shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0066FF] focus-visible:ring-offset-2"
                                >
                                    <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <svg x-show="isSaving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Enregistrer les permissions pour {{ $role->name }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach

    </div>
</x-layouts.app>
