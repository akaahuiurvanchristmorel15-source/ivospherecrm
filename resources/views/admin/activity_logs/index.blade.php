<x-layouts.app>
    <x-slot:title>Journal d'activité & Piste d'audit</x-slot>

    <div class="space-y-6">
        <x-page-header 
            title="Journal d'Activité (Audit Trail)"
            description="Traçabilité complète : qui a fait quoi, quand et sur quel domaine (Section 13 du Cahier des charges)"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Administration', 'url' => route('admin.users.index')],
                    ['label' => 'Audit & Logs']
                ]" />
            </x-slot:breadcrumbs>
        </x-page-header>

        <!-- Filtres -->
        <x-card>
            <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Recherche mot-clé</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Mot-clé (devis, client...)" 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] placeholder-[#64748B] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                    >
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Domaine</label>
                    <select 
                        name="domain_id" 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                    >
                        <option value="">Tous les domaines</option>
                        @foreach($domains as $d)
                            <option value="{{ $d->id }}" {{ request('domain_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Utilisateur</label>
                    <select 
                        name="user_id" 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                    >
                        <option value="">Tous les utilisateurs</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->primary_role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Date</label>
                    <input 
                        type="date" 
                        name="date" 
                        value="{{ request('date') }}" 
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] focus:ring-1 focus:ring-[#0066FF] text-xs transition"
                    >
                </div>

                <div class="sm:col-span-1 flex items-end gap-1">
                    <button 
                        type="submit" 
                        class="w-full py-2 px-3 rounded-lg bg-[#0B0F14] hover:bg-[#1E293B] text-white font-medium transition flex items-center justify-center cursor-pointer text-xs"
                        title="Filtrer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    @if(request()->hasAny(['search', 'domain_id', 'user_id', 'date']))
                        <a href="{{ route('admin.logs.index') }}" class="py-2 px-2.5 rounded-lg bg-[#F5F7FA] text-[#64748B] hover:text-[#0B0F14] border border-[#E2E8F0] flex items-center justify-center text-xs">
                            ✕
                        </a>
                    @endif
                </div>

            </form>
        </x-card>

        <!-- Table du Journal d'Activité -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Date & Heure</th>
                            <th class="py-3 px-4">Utilisateur / Rôle</th>
                            <th class="py-3 px-4">Domaine</th>
                            <th class="py-3 px-4">Action</th>
                            <th class="py-3 px-4">Détails de l'opération</th>
                            <th class="py-3 px-4 text-right">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($logs as $log)
                            <tr class="hover:bg-[#F5F7FA]/70 transition">
                                <td class="py-3.5 px-4 font-mono text-[11px] text-[#64748B] whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-[#0B0F14]">{{ $log->user?->name ?? 'Système' }}</div>
                                    <div class="text-[10px] text-[#0066FF] font-semibold">{{ $log->user?->primary_role ?? 'Automate' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($log->domain)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14]">
                                            {{ $log->domain->code }}
                                        </span>
                                    @else
                                        <span class="text-[#64748B] text-[10px]">Transversal</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-[#0066FF]/10 text-[#0066FF] border border-[#0066FF]/20">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="text-xs text-[#0B0F14] leading-relaxed">{{ $log->description }}</p>
                                    @if($log->properties)
                                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                                            @foreach($log->properties as $k => $v)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#F5F7FA] border border-[#E2E8F0] text-[10px] text-[#64748B]">
                                                    <strong class="text-[#0B0F14]">{{ $k }}:</strong>
                                                    <span class="text-[#0066FF] font-mono">{{ is_array($v) ? implode(', ', $v) : $v }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-[11px] text-[#64748B] whitespace-nowrap">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12">
                                    <x-empty-state 
                                        title="Aucun événement trouvé"
                                        description="Aucun événement ne correspond aux filtres appliqués dans le journal d'activité."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($logs as $log)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-[#0B0F14] text-xs">{{ $log->user?->name ?? 'Système' }}</h4>
                                <span class="text-[10px] text-[#0066FF] font-semibold">{{ $log->user?->primary_role ?? 'Automate' }}</span>
                            </div>
                            <span class="font-mono text-[10px] text-[#64748B]">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-[#0066FF]/10 text-[#0066FF] border border-[#0066FF]/20">
                                {{ $log->action }}
                            </span>
                            @if($log->domain)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14]">
                                    {{ $log->domain->code }}
                                </span>
                            @else
                                <span class="text-[#64748B] text-[10px]">Transversal</span>
                            @endif
                        </div>

                        <p class="text-xs text-[#0B0F14] leading-relaxed bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            {{ $log->description }}
                        </p>

                        @if($log->properties)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($log->properties as $k => $v)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#F5F7FA] border border-[#E2E8F0] text-[10px] text-[#64748B]">
                                        <strong class="text-[#0B0F14]">{{ $k }}:</strong>
                                        <span class="text-[#0066FF] font-mono">{{ is_array($v) ? implode(', ', $v) : $v }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between text-[10px] text-[#64748B] font-mono">
                            <span>IP: {{ $log->ip_address ?? '—' }}</span>
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <x-empty-state 
                        title="Aucun événement trouvé"
                        description="Aucun événement ne correspond aux filtres appliqués dans le journal d'activité."
                    />
                @endforelse
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $logs->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
