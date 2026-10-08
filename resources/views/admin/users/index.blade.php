<x-layouts.app>
    <x-slot:title>Gestion des Utilisateurs & Rôles</x-slot>

    <div class="space-y-6">
        <!-- En-tête standardisé -->
        <x-page-header 
            title="Utilisateurs de la Plateforme"
            description="Gestion des accès, affectations aux domaines et rôles de sécurité"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Utilisateurs']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button variant="primary" href="{{ route('admin.users.create') }}" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Créer un utilisateur</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- Filtres & Recherche -->
        <x-card>
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 text-xs">
                <div class="md:col-span-6">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Recherche textuelle</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Rechercher par nom, email, téléphone..." 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                    >
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Filtrer par rôle</label>
                    <select 
                        name="role" 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                    >
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}" {{ request('role') === $r->slug ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 py-2 px-3 rounded-lg bg-[#0B0F14] hover:bg-[#1E293B] text-white font-medium transition text-xs"
                    >
                        Filtrer
                    </button>
                    @if(request('search') || request('role'))
                        <a href="{{ route('admin.users.index') }}" class="py-2 px-2.5 rounded-lg bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] border border-[#E2E8F0] text-xs">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Table des Utilisateurs -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Utilisateur</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Rôles Attribués</th>
                            <th class="py-3 px-4">Accès Domaines</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($users as $user)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-[#0066FF]/10 border border-[#0066FF]/20 text-[#0066FF] flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-[#0B0F14]">{{ $user->name }}</p>
                                                @if($user->employee)
                                                    <span class="font-mono text-[10px] text-[#0066FF] bg-[#0066FF]/5 border border-[#0066FF]/20 px-1.5 py-0.5 rounded font-semibold" title="Collaborateur RH : {{ $user->employee->position }}">
                                                        {{ $user->employee->employee_code }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-[#64748B] font-mono">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-[#64748B]">
                                    {{ $user->phone ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            @php
                                                $badgeStyle = match($role->slug) {
                                                    'administrateur' => 'bg-slate-900 text-white border-slate-900',
                                                    'responsable', 'responsable_rh', 'responsable_commercial', 'responsable_financiere', 'responsable_communication' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                                    'agent_commercial_terrain' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                    'agent_caissier_vendeur' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                    'agent_technique' => 'bg-sky-50 text-sky-800 border-sky-200',
                                                    'agent_monetique' => 'bg-purple-50 text-purple-800 border-purple-200',
                                                    'agent_cyber' => 'bg-teal-50 text-teal-800 border-teal-200',
                                                    default => 'bg-[#F5F7FA] text-[#0B0F14] border-[#E2E8F0]',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold border {{ $badgeStyle }}">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-[#64748B] text-[11px]">Aucun rôle</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($user->all_domains || $user->isAdmin())
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium bg-[#0066FF]/10 text-[#0066FF] border border-[#0066FF]/20">
                                            <svg class="w-3 h-3 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                            <span>Tous les 5 domaines</span>
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($user->domains as $domain)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-[#F5F7FA] text-[#64748B] border border-[#E2E8F0]">
                                                    {{ $domain->code }}
                                                </span>
                                            @empty
                                                <span class="text-[#64748B] text-[11px]">Aucun</span>
                                            @endforelse
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium transition cursor-pointer {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }}"
                                            title="Cliquer pour changer le statut"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            <span>{{ $user->is_active ? 'Actif' : 'Désactivé' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a 
                                            href="{{ route('admin.users.edit', $user) }}" 
                                            class="p-1.5 rounded-lg text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] transition border border-transparent hover:border-[#E2E8F0]"
                                            title="Modifier"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Confirmez-vous la suppression de cet utilisateur ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="p-1.5 rounded-lg text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer border border-transparent hover:border-rose-100"
                                                    title="Supprimer"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun utilisateur trouvé"
                                        description="Aucun utilisateur ne correspond à vos critères de recherche actuels."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden sm: space-y-2">
                @forelse($users as $user)
                    <div class="bg-white rounded-xl p-2 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#0066FF]/10 border border-[#0066FF]/20 text-[#0066FF] flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-[#0B0F14] text-sm">{{ $user->name }}</h4>
                                        @if($user->employee)
                                            <span class="font-mono text-[10px] text-[#0066FF] bg-[#0066FF]/5 border border-[#0066FF]/20 px-1.5 py-0.5 rounded font-semibold">
                                                {{ $user->employee->employee_code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold transition cursor-pointer {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>{{ $user->is_active ? 'Actif' : 'Inactif' }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- 2-col roles & domain access grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-1">Rôles</span>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        @php
                                            $mBadgeStyle = match($role->slug) {
                                                'administrateur' => 'bg-slate-900 text-white border-slate-900',
                                                'responsable', 'responsable_rh', 'responsable_commercial', 'responsable_financiere', 'responsable_communication' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                                'agent_commercial_terrain' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                'agent_caissier_vendeur' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                'agent_technique' => 'bg-sky-50 text-sky-800 border-sky-200',
                                                'agent_monetique' => 'bg-purple-50 text-purple-800 border-purple-200',
                                                'agent_cyber' => 'bg-teal-50 text-teal-800 border-teal-200',
                                                default => 'bg-white text-[#0B0F14] border-[#E2E8F0]',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $mBadgeStyle }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-[#64748B] text-[10px]">Aucun rôle</span>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block mb-1">Domaines</span>
                                @if($user->all_domains || $user->isAdmin())
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#0066FF]/10 text-[#0066FF]">
                                        <svg class="w-2.5 h-2.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                        <span>Tous les 5</span>
                                    </span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->domains as $domain)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-white text-[#64748B] border border-[#E2E8F0]">
                                                {{ $domain->code }}
                                            </span>
                                        @empty
                                            <span class="text-[#64748B] text-[10px]">Aucun</span>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Contact info & actions -->
                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                            @if($user->phone)
                                <a href="tel:{{ $user->phone }}" class="inline-flex items-center gap-1 text-[11px] font-mono text-[#0066FF]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>{{ $user->phone }}</span>
                                </a>
                            @else
                                <span class="text-[11px] text-[#64748B]">Sans téléphone</span>
                            @endif

                            <div class="flex items-center gap-1.5">
                                <a 
                                    href="{{ route('admin.users.edit', $user) }}" 
                                    class="py-1.5 px-3 rounded-lg border border-[#E2E8F0] text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold flex items-center gap-1 transition"
                                >
                                    <svg class="w-3.5 h-3.5 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Modifier</span>
                                </a>

                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Confirmez-vous la suppression de cet utilisateur ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1.5 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition"
                                            title="Supprimer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun utilisateur trouvé"
                        description="Aucun utilisateur ne correspond à vos critères de recherche actuels."
                    />
                @endforelse
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $users->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
