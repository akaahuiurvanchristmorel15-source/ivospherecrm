@php
    $roleBadge = fn (string $slug) => match ($slug) {
        'administrateur' => 'bg-[#0B0F14] text-white',
        'responsable', 'responsable_rh', 'responsable_commercial', 'responsable_financiere', 'responsable_communication' => 'bg-blue-50 text-[#0066FF]',
        'agent_commercial_terrain' => 'bg-emerald-50 text-emerald-700',
        'agent_caissier_vendeur' => 'bg-amber-50 text-amber-700',
        'agent_technique' => 'bg-sky-50 text-sky-700',
        'agent_monetique' => 'bg-purple-50 text-purple-700',
        'agent_cyber' => 'bg-teal-50 text-teal-700',
        default => 'bg-[#F5F7FA] text-[#0B0F14]',
    };
    $hasFilters = request()->filled('search') || request()->filled('role') || request()->filled('status');
    $statusFilters = ['' => 'Tous', 'active' => 'Actifs', 'inactive' => 'Désactivés'];
@endphp

<x-layouts.app>
    <x-slot:title>Utilisateurs</x-slot>

    <div class="space-y-5">
        {{-- En-tête --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Utilisateurs</h1>
                <p class="mt-1 text-sm text-[#64748B]">Comptes, rôles, accès et journée de chaque collaborateur.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-button variant="secondary" type="button" x-data x-on:click="$dispatch('open-invitation-modal')" class="flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    <span>Générer un lien d'inscription</span>
                </x-button>
                <x-button variant="primary" href="{{ route('admin.users.create') }}" class="flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Créer un utilisateur</span>
                </x-button>
            </div>
        </header>

        @include('admin.users._invitations')

        {{-- Résumé --}}
        <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Utilisateurs</dt>
                <dd class="mt-1 text-2xl font-bold text-[#0B0F14]">{{ $userCounts['total'] }}</dd>
            </div>
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Actifs</dt>
                <dd class="mt-1 text-2xl font-bold text-emerald-600">{{ $userCounts['active'] }}</dd>
            </div>
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Désactivés</dt>
                <dd class="mt-1 text-2xl font-bold text-rose-600">{{ $userCounts['total'] - $userCounts['active'] }}</dd>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="group rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-[#0066FF]/40">
                <dt class="text-[11px] font-medium uppercase tracking-wider text-[#64748B]">Rôles</dt>
                <dd class="mt-1 flex items-baseline justify-between text-2xl font-bold text-[#0B0F14]">{{ $roles->count() }}<span class="text-xs font-semibold text-[#0066FF] group-hover:underline">Gérer →</span></dd>
            </a>
        </dl>

        {{-- Recherche et filtres --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-3">
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#64748B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <label for="user-search" class="sr-only">Rechercher</label>
                    <input id="user-search" type="search" name="search" value="{{ request('search') }}" placeholder="Nom, e-mail ou téléphone…"
                           class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-3.5 text-sm text-[#0B0F14] placeholder-[#64748B] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                </div>
                <label for="user-role" class="sr-only">Rôle</label>
                <select id="user-role" name="role" onchange="this.form.submit()" class="h-11 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm text-[#0B0F14] sm:w-56">
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}" @selected(request('role') === $r->slug)>{{ $r->name }} ({{ $r->users_count }})</option>
                    @endforeach
                </select>
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="submit" class="h-11 rounded-xl bg-[#0B0F14] px-5 text-sm font-semibold text-white transition hover:bg-[#1E293B]">Rechercher</button>
            </div>
            <div class="flex items-center gap-2">
                @foreach($statusFilters as $value => $label)
                    <a href="{{ route('admin.users.index', array_merge(request()->except('page', 'status'), $value !== '' ? ['status' => $value] : [])) }}"
                       class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ (string) request('status', '') === $value ? 'bg-[#0B0F14] text-white' : 'border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#0B0F14]' }}">{{ $label }}</a>
                @endforeach
                @if($hasFilters)
                    <a href="{{ route('admin.users.index') }}" class="ml-auto text-xs font-medium text-[#0066FF] hover:underline">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- Fiches utilisateurs --}}
        @if($users->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] bg-white px-6 py-14 text-center">
                <p class="text-sm font-semibold text-[#0B0F14]">Aucun utilisateur trouvé</p>
                <p class="mt-1 text-xs text-[#64748B]">Modifiez la recherche ou ajoutez un utilisateur.</p>
            </div>
        @else
            <ul class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($users as $user)
                    <li class="flex flex-col rounded-2xl border border-[#E2E8F0] bg-white transition hover:border-[#0066FF]/40 hover:shadow-sm {{ $user->is_active ? '' : 'opacity-75' }}">
                        <div class="flex items-start gap-3.5 p-4">
                            <div class="relative">
                                <x-user-avatar :user="$user" size="lg" />
                                <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}" title="{{ $user->is_active ? 'Actif' : 'Désactivé' }}"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-[#0B0F14]">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                        <span class="shrink-0 rounded-full bg-[#F5F7FA] px-2 py-0.5 text-[10px] font-semibold text-[#64748B]">Vous</span>
                                    @endif
                                </div>
                                <p class="truncate text-xs text-[#64748B]">{{ $user->email }}</p>
                                @if($user->phone)
                                    <p class="truncate text-xs text-[#64748B]">{{ $user->phone }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $roleBadge($role->slug) }}">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-[11px] text-[#64748B]">Aucun rôle</span>
                                    @endforelse
                                    @if($user->employee)
                                        <span class="rounded-full bg-[#F5F7FA] px-2 py-0.5 font-mono text-[10px] text-[#0B0F14]" title="Fiche collaborateur">{{ $user->employee->employee_code }}</span>
                                    @endif
                                </div>
                                <p class="mt-1.5 truncate text-[11px] text-[#64748B]">
                                    @if($user->all_domains || $user->isAdmin())
                                        Tous les domaines
                                    @else
                                        {{ $user->domains->pluck('code')->join(' · ') ?: 'Aucun domaine' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center gap-1 border-t border-[#E2E8F0] px-2 py-1.5">
                            <a href="{{ route('admin.users.day', $user) }}" class="inline-flex h-8 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold text-[#0066FF] transition hover:bg-blue-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                Journée
                            </a>
                            <span class="flex-1"></span>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="h-8 rounded-lg px-2.5 text-xs font-medium transition {{ $user->is_active ? 'text-[#64748B] hover:bg-[#F5F7FA] hover:text-[#0B0F14]' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                        {{ $user->is_active ? 'Désactiver' : 'Réactiver' }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg p-2 text-[#64748B] transition hover:bg-[#F5F7FA] hover:text-[#0B0F14]" title="Modifier">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer {{ addslashes($user->name) }} ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-[#64748B] transition hover:bg-rose-50 hover:text-rose-600" title="Supprimer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($users->hasPages())
                <div>{{ $users->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
