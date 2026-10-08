<x-layouts.app title="Tâches du Jour — Ressources Humaines">
    <div class="space-y-6">

        <!-- En-tête de page -->
        <x-page-header 
            title="Tâches du Jour" 
            description="Attribution des missions quotidiennes aux employés, fiches PDF et suivi des notifications WhatsApp & Email"
            :breadcrumbs="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Tâches du Jour']
            ]"
        >
            <x-slot:actions>
                <x-button :href="route('rh.daily-tasks.create')" variant="primary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouvelle Attribution</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- KPI Tâches du Jour -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <x-stat-card 
                title="Fiches Aujourd'hui" 
                :value="$todaySheetsCount ?? 0" 
                change="Émises ce jour" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Total Fiches" 
                :value="$totalAssignedCount ?? 0" 
                change="Historique global" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-[#0B0F14]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="WhatsApp Expédiés" 
                :value="$whatsappDispatchedCount ?? 0" 
                change="Direct aux numéros" 
                changeType="up"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.584 1.961.949 3.013.953h.005c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm0 13.067h-.004c-1.127 0-2.228-.316-3.187-.912l-.229-.144-1.58.414.421-1.54-.15-.238c-.655-1.042-1.001-2.247-1.001-3.483 0-3.621 2.947-6.568 6.569-6.568 3.621 0 6.567 2.947 6.567 6.568 0 3.622-2.946 6.568-6.568 6.568z"/></svg>
                </x-slot:icon>
            </x-stat-card>

            <x-stat-card 
                title="Emails Envoyés" 
                :value="$emailDispatchedCount ?? 0" 
                change="Fiches PDF expédiées" 
                changeType="neutral"
                timeframe=""
            >
                <x-slot:icon>
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Toolbar Filtres Responsive -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#E2E8F0] shadow-2xs space-y-3">
            <form method="GET" action="{{ route('rh.daily-tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3 text-xs">
                <!-- Filtre Employé -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Employé</label>
                    <select name="employee_id" class="w-full px-3 py-2 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF] touch-target">
                        <option value="">Tous les collaborateurs</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtre Date -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Date</label>
                    <input 
                        type="date" 
                        name="date" 
                        value="{{ request('date') }}" 
                        class="w-full px-3 py-2 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF] touch-target"
                    />
                </div>

                <!-- Filtre Statut -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Statut</label>
                    <select name="status" class="w-full px-3 py-2 bg-[#F5F7FA] border border-[#E2E8F0] rounded-xl text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF] touch-target">
                        <option value="">Tous les statuts</option>
                        <option value="assigne" {{ request('status') === 'assigne' ? 'selected' : '' }}>Assigné</option>
                        <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ request('status') === 'termine' ? 'selected' : '' }}>Terminé</option>
                    </select>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 bg-[#0066FF] hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-colors touch-target">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['employee_id', 'date', 'status']))
                        <a href="{{ route('rh.daily-tasks.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors touch-target" title="Réinitialiser">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Desktop & Cartes Mobile -->
        <x-card :noPadding="true">
            <!-- Vue Table Desktop -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Référence</th>
                            <th class="py-3 px-4">Collaborateur & Poste</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-center">Objectifs</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4">Expédition</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @forelse($sheets as $s)
                            @php
                                $tasksCount = is_array($s->tasks) ? count($s->tasks) : 0;
                                $completedCount = is_array($s->tasks) ? collect($s->tasks)->filter(fn($t) => is_array($t) && ($t['status'] ?? '') === 'termine')->count() : 0;
                            @endphp
                            <tr class="hover:bg-[#F5F7FA]/70 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#0066FF]">
                                    <a href="{{ route('rh.daily-tasks.show', $s) }}" class="hover:underline">
                                        {{ $s->reference }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#0B0F14]">{{ $s->employee?->full_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $s->position }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $s->date->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $completedCount === $tasksCount && $tasksCount > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $completedCount }} / {{ $tasksCount }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $stClass = match($s->status) {
                                            'termine' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'en_cours' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-blue-50 text-blue-700 border-blue-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $stClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $s->status)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-xs {{ $s->whatsapp_sent_at ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}" title="WhatsApp : {{ $s->whatsapp_sent_at ? 'Envoyé' : 'En attente' }}">
                                            💬
                                        </span>
                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-xs {{ $s->email_sent_at ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' }}" title="Email : {{ $s->email_sent_at ? 'Envoyé' : 'Non expédié' }}">
                                            ✉️
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a 
                                            href="{{ route('rh.daily-tasks.print', $s) }}" 
                                            target="_blank" 
                                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                                            title="Imprimer / Télécharger le PDF A4"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>

                                        @if($s->whats_app_url)
                                            <a 
                                                href="{{ $s->whats_app_url }}" 
                                                target="_blank" 
                                                class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors"
                                                title="Ouvrir la discussion WhatsApp"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.584 1.961.949 3.013.953h.005c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm0 13.067h-.004c-1.127 0-2.228-.316-3.187-.912l-.229-.144-1.58.414.421-1.54-.15-.238c-.655-1.042-1.001-2.247-1.001-3.483 0-3.621 2.947-6.568 6.569-6.568 3.621 0 6.567 2.947 6.567 6.568 0 3.622-2.946 6.568-6.568 6.568z"/></svg>
                                            </a>
                                        @endif

                                        <a 
                                            href="{{ route('rh.daily-tasks.show', $s) }}" 
                                            class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0066FF] font-bold text-xs"
                                            title="Consulter les détails"
                                        >
                                            Détails →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Aucune fiche de tâches trouvée pour ces critères.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (390 px) -->
            <div class="md:hidden divide-y divide-[#E2E8F0]">
                @forelse($sheets as $s)
                    @php
                        $tasksCount = is_array($s->tasks) ? count($s->tasks) : 0;
                        $completedCount = is_array($s->tasks) ? collect($s->tasks)->filter(fn($t) => is_array($t) && ($t['status'] ?? '') === 'termine')->count() : 0;
                    @endphp
                    <div class="p-3.5 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-[#0066FF]">{{ $s->reference }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">{{ $s->date->format('d/m/Y') }}</span>
                        </div>

                        <div>
                            <h4 class="font-bold text-sm text-[#0B0F14]">{{ $s->employee?->full_name }}</h4>
                            <p class="text-xs text-slate-500 font-medium">{{ $s->position }}</p>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $completedCount }}/{{ $tasksCount }} tâche(s)
                            </span>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('rh.daily-tasks.print', $s) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px] touch-target flex items-center gap-1">
                                    PDF
                                </a>
                                @if($s->whats_app_url)
                                    <a href="{{ $s->whats_app_url }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[11px] touch-target flex items-center gap-1">
                                        WhatsApp
                                    </a>
                                @endif
                                <a href="{{ route('rh.daily-tasks.show', $s) }}" class="px-3 py-1 rounded-lg bg-[#0066FF] text-white font-bold text-[11px] touch-target">
                                    Voir
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400">
                        Aucune fiche de tâches enregistrée.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($sheets->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $sheets->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
