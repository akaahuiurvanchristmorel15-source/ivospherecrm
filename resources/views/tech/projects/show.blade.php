<x-layouts.app :title="'Projet ' . $project->reference . ' — TECH'">
    @php
        $statusBadge = match($project->status) {
            'termine'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'en_cours'   => 'bg-blue-50 text-[#0066FF] border-blue-200',
            'planifie'   => 'bg-purple-50 text-purple-700 border-purple-200',
            'en_attente' => 'bg-amber-50 text-amber-700 border-amber-200',
            'suspendu'   => 'bg-rose-50 text-rose-700 border-rose-200',
            default      => 'bg-slate-50 text-[#64748B] border-[#E2E8F0]'
        };

        $balance = max(0, $project->budget - $project->spent);
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('tech.projects.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux projets</span>
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        {{ $project->name }}
                    </h1>
                    <span class="text-xs font-mono font-bold text-[#0066FF] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                        {{ $project->reference }}
                    </span>
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $statusBadge }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Créé le {{ $project->created_at->format('d/m/Y') }} • Responsable : {{ $project->user->name ?? 'Équipe TECH' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a 
                    href="{{ route('tech.projects.edit', $project) }}" 
                    class="px-4 py-2 rounded-xl bg-[#0B0F14] text-white hover:bg-[#0066FF] text-xs font-semibold transition-colors flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Modifier</span>
                </a>

                <form action="{{ route('tech.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Confirmer la suppression définitive de ce projet ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- 3 Cartes Synthèse -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Client / Compte</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ $project->customer->name ?? 'Projet Interne' }}
                </span>
                @if($project->customer?->company)
                    <span class="text-xs text-[#64748B] block">{{ $project->customer->company }}</span>
                @endif
                @if($project->customer?->phone)
                    <span class="text-xs text-[#0066FF] font-medium block mt-1">{{ $project->customer->phone }}</span>
                @endif
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Budget & Dépenses</span>
                <span class="text-base font-bold text-[#0B0F14] mt-1 block">
                    {{ number_format($project->budget, 0, ',', ' ') }} FCFA
                </span>
                <div class="flex justify-between items-center text-xs text-[#64748B] mt-1">
                    <span>Engagé : {{ number_format($project->spent, 0, ',', ' ') }} F</span>
                    <span class="font-bold text-emerald-600">Reste : {{ number_format($balance, 0, ',', ' ') }} F</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Avancement</span>
                    <span class="text-xs font-bold text-[#0066FF]">{{ $project->progress }}%</span>
                </div>
                <div class="w-full h-2 rounded-full bg-[#E2E8F0] mt-2 overflow-hidden">
                    <div class="h-full rounded-full bg-[#0066FF] transition-all duration-300" style="width: {{ $project->progress }}%"></div>
                </div>
                <div class="flex justify-between items-center text-xs text-[#64748B] mt-2">
                    <span>Début : {{ $project->start_date ? $project->start_date->format('d/m/Y') : 'Non défini' }}</span>
                    <span>Fin : {{ $project->end_date ? $project->end_date->format('d/m/Y') : 'Non définie' }}</span>
                </div>
            </div>
        </div>

        <!-- Description & Notes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Périmètre & Cahier des charges</h3>
                <p class="text-xs text-[#0B0F14] leading-relaxed whitespace-pre-line">
                    {{ $project->description ?: 'Aucune description ou cahier des charges renseigné.' }}
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Notes Internes & Instructions Techniques</h3>
                <p class="text-xs text-[#0B0F14] leading-relaxed whitespace-pre-line">
                    {{ $project->notes ?: 'Aucune consigne ou note technique enregistrée.' }}
                </p>
            </div>
        </div>

        <!-- Tâches & Livrables -->
        <div class="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <div>
                    <h3 class="text-sm font-bold text-[#0B0F14]">Tâches & Livrables du Projet</h3>
                    <p class="text-xs text-[#64748B]">Suivi opérationnel des jalons de développement et d'intégration.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    {{ $project->tasks->count() }} tâche(s)
                </span>
            </div>

            @if($project->tasks->isNotEmpty())
                <div class="divide-y divide-[#E2E8F0]">
                    @foreach($project->tasks as $task)
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-[#0B0F14] truncate">{{ $task->name }}</p>
                                @if($task->description)
                                    <p class="text-[11px] text-[#64748B] line-clamp-1 mt-0.5">{{ $task->description }}</p>
                                @endif
                                <div class="flex items-center gap-3 text-[11px] text-[#64748B] mt-1">
                                    <span>Assigné à : <strong class="text-[#0B0F14]">{{ $task->assignee->name ?? 'Non assigné' }}</strong></span>
                                    @if($task->due_date)
                                        <span>Échéance : {{ $task->due_date->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @php
                                    $taskBadge = match($task->status) {
                                        'termine' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'en_cours' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                        default => 'bg-slate-50 text-[#64748B] border-[#E2E8F0]',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $taskBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="text-xs text-[#64748B]">Aucune tâche enregistrée pour ce projet.</p>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
