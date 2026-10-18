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
        $groupDots = ['bg-[#0066FF]', 'bg-emerald-500', 'bg-amber-500', 'bg-violet-500', 'bg-rose-500', 'bg-sky-500', 'bg-teal-500', 'bg-orange-500', 'bg-indigo-500', 'bg-pink-500', 'bg-lime-500'];
        $allPermIds = $permissions->flatten()->pluck('id')->values()->all();
        $allPermsFlat = $permissions->flatten()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug, 'group' => $p->group])->values()->all();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6" x-data="{ activeRoleId: '{{ $initialRoleId }}', roleSearch: '' }">

        {{-- En-tête --}}
        <x-page-header
            title="Rôles & permissions"
            description="Choisissez un rôle puis activez ou désactivez ses droits, module par module."
            :breadcrumbs="[
                ['label' => 'Administration', 'url' => route('admin.users.index')],
                ['label' => 'Rôles & Permissions']
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('admin.users.index') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">
                    <svg class="h-4 w-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Utilisateurs
                </a>
                <a href="{{ route('admin.logs.index') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA]">
                    <svg class="h-4 w-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Journal d'audit
                </a>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800" role="status">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Résumé --}}
        <dl class="grid grid-cols-3 divide-x divide-[#E2E8F0] rounded-2xl border border-[#E2E8F0] bg-white">
            <div class="px-4 py-3 sm:px-5">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Rôles</dt>
                <dd class="mt-0.5 text-xl font-bold text-[#0B0F14]">{{ $roles->count() }}</dd>
            </div>
            <div class="px-4 py-3 sm:px-5">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Droits disponibles</dt>
                <dd class="mt-0.5 text-xl font-bold text-[#0B0F14]">{{ $totalPermissionsCount }} <span class="hidden text-xs font-medium text-[#64748B] sm:inline">· {{ $permissions->count() }} modules</span></dd>
            </div>
            <div class="px-4 py-3 sm:px-5">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Comptes rattachés</dt>
                <dd class="mt-0.5 text-xl font-bold text-[#0B0F14]">{{ $roles->sum('users_count') }}</dd>
            </div>
        </dl>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[300px_minmax(0,1fr)] lg:items-start">

            {{-- Liste des rôles --}}
            <aside class="rounded-2xl border border-[#E2E8F0] bg-white lg:sticky lg:top-24">
                <div class="border-b border-[#E2E8F0] p-3">
                    <label for="role-search" class="sr-only">Rechercher un rôle</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input id="role-search" type="search" x-model="roleSearch" placeholder="Rechercher un rôle…"
                               class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-[#F5F7FA] pl-9 pr-3 text-xs text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                    </div>
                </div>
                <nav class="flex gap-2 overflow-x-auto p-2 lg:max-h-[calc(100vh-14rem)] lg:flex-col lg:gap-0.5 lg:overflow-y-auto lg:overflow-x-visible" role="tablist" aria-label="Rôles">
                    @foreach($roles as $r)
                        @php
                            $rPermsCount = $r->permissions->count();
                            $rPercent = $totalPermissionsCount > 0 ? round(($rPermsCount / $totalPermissionsCount) * 100) : 0;
                        @endphp
                        <button type="button" role="tab" id="role-tab-{{ $r->id }}" aria-controls="role-panel-{{ $r->id }}"
                                x-show="roleSearch === '' || {{ Js::from(mb_strtolower($r->name)) }}.includes(roleSearch.toLowerCase())"
                                @click="activeRoleId = '{{ $r->id }}'"
                                :aria-selected="activeRoleId == '{{ $r->id }}'"
                                :class="activeRoleId == '{{ $r->id }}' ? 'bg-[#0066FF]/[0.07] ring-1 ring-[#0066FF]/30' : 'hover:bg-[#F5F7FA]'"
                                class="flex w-60 shrink-0 items-center gap-3 rounded-xl p-2.5 text-left transition lg:w-full">
                            <span :class="activeRoleId == '{{ $r->id }}' ? 'bg-[#0066FF] text-white' : 'bg-[#F5F7FA] text-[#0B0F14]'"
                                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold transition">
                                @if($r->slug === 'administrateur')
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @else
                                    @php
                                        $words = preg_split('/\s+/', trim($r->name));
                                    @endphp
                                    {{ mb_strtoupper(count($words) > 1 ? mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1) : mb_substr($words[0], 0, 2)) }}
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-[#0B0F14]">{{ $r->name }}</span>
                                <span class="mt-1 flex items-center gap-2">
                                    <span class="h-1 flex-1 overflow-hidden rounded-full bg-[#F5F7FA]"><span class="block h-full rounded-full bg-[#0066FF]" style="width: {{ $rPercent }}%"></span></span>
                                    <span class="shrink-0 text-[10px] font-semibold text-[#64748B]">{{ $rPermsCount }}/{{ $totalPermissionsCount }}</span>
                                </span>
                                <span class="mt-0.5 block text-[11px] text-[#64748B]">{{ $r->users_count }} {{ $r->users_count > 1 ? 'utilisateurs' : 'utilisateur' }}</span>
                            </span>
                        </button>
                    @endforeach
                </nav>
            </aside>

            {{-- Droits du rôle sélectionné --}}
            <div class="min-w-0">
                @foreach($roles as $role)
                    @php
                        $rolePermIds = $role->permissions->pluck('id')->values()->all();
                    @endphp
                    <div x-show="activeRoleId == '{{ $role->id }}'" id="role-panel-{{ $role->id }}" role="tabpanel" aria-labelledby="role-tab-{{ $role->id }}" x-cloak
                         x-data="{
                             selected: {{ Js::from($rolePermIds) }},
                             initial: {{ Js::from($rolePermIds) }},
                             allPerms: {{ Js::from($allPermsFlat) }},
                             searchQuery: '',
                             isSaving: false,
                             isSelected(id) { return this.selected.includes(Number(id)); },
                             toggle(id) { id = Number(id); this.selected = this.isSelected(id) ? this.selected.filter(i => i !== id) : [...this.selected, id]; },
                             selectAll(ids) { this.selected = Array.from(new Set([...this.selected, ...ids.map(Number)])); },
                             deselectAll(ids) { const numIds = ids.map(Number); this.selected = this.selected.filter(id => ! numIds.includes(id)); },
                             isGroupFullyChecked(ids) { return ids.length > 0 && ids.every(id => this.selected.includes(Number(id))); },
                             toggleGroup(ids) { this.isGroupFullyChecked(ids) ? this.deselectAll(ids) : this.selectAll(ids); },
                             countSelectedInGroup(ids) { return ids.filter(id => this.selected.includes(Number(id))).length; },
                             resetToInitial() { this.selected = [...this.initial]; },
                             isDirty() { return this.selected.length !== this.initial.length || ! this.initial.every(id => this.selected.includes(id)); },
                             matches(name, slug) { const q = this.searchQuery.trim().toLowerCase(); return q === '' || name.toLowerCase().includes(q) || slug.toLowerCase().includes(q); },
                             groupHasVisible(perms) { return perms.some(p => this.matches(p.name, p.slug)); },
                             totalVisibleCount() { return this.allPerms.filter(p => this.matches(p.name, p.slug)).length; }
                         }">
                        <form method="POST" action="{{ route('admin.roles.update', $role) }}" @submit="isSaving = true" class="space-y-4">
                            @csrf
                            @method('PUT')

                            {{-- Carte du rôle --}}
                            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Rôle sélectionné</p>
                                        <h2 class="mt-1 text-xl font-bold tracking-tight text-[#0B0F14]">{{ $role->name }}</h2>
                                        <p class="mt-1 text-xs leading-relaxed text-[#64748B]">{{ $role->description ?: 'Aucune description.' }}</p>
                                        <p class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                            <span class="rounded-full bg-[#F5F7FA] px-2.5 py-0.5 font-semibold text-[#0B0F14]">{{ $role->users_count }} {{ $role->users_count > 1 ? 'utilisateurs' : 'utilisateur' }}</span>
                                            <span class="font-mono text-[#64748B]">{{ $role->slug }}</span>
                                        </p>
                                    </div>
                                    <div class="shrink-0 sm:w-56">
                                        <p class="flex items-baseline justify-between text-xs text-[#64748B]">
                                            Droits activés
                                            <span><strong class="text-lg font-bold text-[#0B0F14]" x-text="selected.length"></strong> / {{ $totalPermissionsCount }}</span>
                                        </p>
                                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-[#F5F7FA]">
                                            <div class="h-full rounded-full bg-[#0066FF] transition-all duration-300" :style="'width: ' + Math.round(selected.length / {{ max(1, $totalPermissionsCount) }} * 100) + '%'"></div>
                                        </div>
                                    </div>
                                </div>

                                @if($role->slug === 'administrateur')
                                    <p class="mt-4 flex items-start gap-2 rounded-xl bg-blue-50 p-3 text-xs text-blue-800">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        L'administrateur dispose toujours de tous les droits, quelles que soient les cases cochées ici.
                                    </p>
                                @endif
                            </section>

                            {{-- Outils --}}
                            <div class="flex flex-col gap-2 rounded-2xl border border-[#E2E8F0] bg-white p-3 sm:flex-row sm:items-center">
                                <div class="relative flex-1">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="search" x-model="searchQuery" placeholder="Rechercher une permission (devis, stock, employés…)" aria-label="Rechercher une permission"
                                           class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-[#F5F7FA] pl-9 pr-3 text-xs text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="selectAll({{ Js::from($allPermIds) }})" class="h-10 flex-1 rounded-xl border border-[#E2E8F0] px-3 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA] sm:flex-none">Tout activer</button>
                                    <button type="button" @click="deselectAll({{ Js::from($allPermIds) }})" class="h-10 flex-1 rounded-xl border border-[#E2E8F0] px-3 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA] sm:flex-none">Tout désactiver</button>
                                </div>
                            </div>

                            {{-- Modules --}}
                            @foreach($permissions as $groupKey => $perms)
                                @php
                                    $groupPermIds = $perms->pluck('id')->all();
                                    $meta = $groupMeta[$groupKey] ?? ['title' => ucfirst($groupKey), 'desc' => '', 'badge' => ucfirst($groupKey)];
                                    $dot = $groupDots[$loop->index % count($groupDots)];
                                @endphp
                                <fieldset x-data="{ open: true }" x-show="groupHasVisible({{ Js::from($perms->map(fn ($p) => ['name' => $p->name, 'slug' => $p->slug])->values()) }})"
                                          class="min-w-0 overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                                    <div class="flex items-center gap-3 p-4">
                                        <button type="button" @click="open = ! open" class="flex min-w-0 flex-1 items-center gap-3 text-left" :aria-expanded="open">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dot }}"></span>
                                            <span class="min-w-0">
                                                <legend class="block truncate text-sm font-semibold text-[#0B0F14]">{{ $meta['title'] }}</legend>
                                                @if($meta['desc'])
                                                    <span class="block truncate text-[11px] text-[#64748B]">{{ $meta['desc'] }}</span>
                                                @endif
                                            </span>
                                            <svg class="ml-auto h-4 w-4 shrink-0 text-[#64748B] transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <span class="shrink-0 rounded-full bg-[#F5F7FA] px-2.5 py-0.5 text-[11px] font-semibold text-[#0B0F14]">
                                            <span x-text="countSelectedInGroup({{ Js::from($groupPermIds) }})"></span>/{{ count($groupPermIds) }}
                                        </span>
                                        <button type="button" @click="toggleGroup({{ Js::from($groupPermIds) }})"
                                                class="hidden shrink-0 rounded-lg px-2.5 py-1 text-[11px] font-semibold text-[#0066FF] transition hover:bg-blue-50 sm:block"
                                                x-text="isGroupFullyChecked({{ Js::from($groupPermIds) }}) ? 'Tout retirer' : 'Tout activer'"></button>
                                    </div>

                                    <div x-show="open" x-collapse class="grid grid-cols-1 border-t border-[#E2E8F0] xl:grid-cols-2">
                                        @foreach($perms as $perm)
                                            <label x-show="matches({{ Js::from($perm->name) }}, {{ Js::from($perm->slug) }})"
                                                   class="flex cursor-pointer items-center gap-3 border-b border-[#E2E8F0] px-4 py-3 transition hover:bg-[#F5F7FA] xl:odd:border-r">
                                                <span class="min-w-0 flex-1">
                                                    <span class="block text-xs font-semibold text-[#0B0F14]">{{ $perm->name }}</span>
                                                    <span class="block truncate font-mono text-[10px] text-[#64748B]">{{ $perm->slug }}</span>
                                                </span>
                                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="peer sr-only"
                                                       :checked="isSelected({{ $perm->id }})" @change="toggle({{ $perm->id }})">
                                                <span aria-hidden="true" :class="isSelected({{ $perm->id }}) ? 'bg-[#0066FF]' : 'bg-slate-300'"
                                                      class="relative inline-flex h-5 w-9 shrink-0 rounded-full transition peer-focus-visible:ring-2 peer-focus-visible:ring-[#0066FF] peer-focus-visible:ring-offset-2">
                                                    <span :class="isSelected({{ $perm->id }}) ? 'translate-x-4' : 'translate-x-0'"
                                                          class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-[#FFFFFF] shadow transition"></span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach

                            <div x-show="totalVisibleCount() === 0" x-cloak class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-4 py-10 text-center">
                                <p class="text-sm font-semibold text-[#0B0F14]">Aucune permission ne correspond</p>
                                <button type="button" @click="searchQuery = ''" class="mt-2 text-xs font-semibold text-[#0066FF] hover:underline">Effacer la recherche</button>
                            </div>

                            {{-- Barre d'enregistrement --}}
                            <div x-show="isDirty()" x-cloak x-transition.opacity
                                 class="sticky bottom-24 z-20 flex flex-col gap-3 rounded-2xl border border-amber-300 bg-white p-3 pl-4 shadow-lg sm:flex-row sm:items-center lg:bottom-4">
                                <p class="flex flex-1 items-center gap-2 text-xs font-semibold text-amber-700">
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>Modifications non enregistrées pour {{ $role->name }}
                                </p>
                                <div class="flex gap-2">
                                    <button type="button" @click="resetToInitial()" class="h-10 flex-1 rounded-xl border border-[#E2E8F0] px-4 text-xs font-semibold text-[#0B0F14] transition hover:bg-[#F5F7FA] sm:flex-none">Annuler</button>
                                    <button type="submit" :disabled="isSaving"
                                            class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-5 text-xs font-semibold text-white transition hover:bg-[#0052CC] disabled:cursor-not-allowed disabled:opacity-50 sm:flex-none">
                                        <svg x-show="isSaving" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
