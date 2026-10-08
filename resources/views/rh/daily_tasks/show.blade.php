<x-layouts.app title="Fiche de Tâches {{ $sheet->reference }} — RH">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- En-tête de page -->
        <x-page-header 
            title="Fiche de Tâches : {{ $sheet->reference }}" 
            description="Suivi en direct des objectifs assignés, émargement et contrôle des notifications"
            :breadcrumbs="[
                ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                ['label' => 'Tâches du Jour', 'url' => route('rh.daily-tasks.index')],
                ['label' => $sheet->reference]
            ]"
        >
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <x-button :href="route('rh.daily-tasks.index')" variant="secondary" size="md">
                        ← Liste
                    </x-button>

                    <a 
                        href="{{ route('rh.daily-tasks.print', $sheet) }}" 
                        target="_blank" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-[#0066FF] text-white text-xs font-bold transition-all shadow-xs touch-target"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Document PDF (A4)</span>
                    </a>

                    @if($sheet->whats_app_url)
                        <a 
                            href="{{ $sheet->whats_app_url }}" 
                            target="_blank" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#25D366] hover:bg-[#1eb956] text-white text-xs font-bold transition-all shadow-xs touch-target"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.584 1.961.949 3.013.953h.005c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm0 13.067h-.004c-1.127 0-2.228-.316-3.187-.912l-.229-.144-1.58.414.421-1.54-.15-.238c-.655-1.042-1.001-2.247-1.001-3.483 0-3.621 2.947-6.568 6.569-6.568 3.621 0 6.567 2.947 6.567 6.568 0 3.622-2.946 6.568-6.568 6.568z"/></svg>
                            <span>WhatsApp</span>
                        </a>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Notification de Redirection WhatsApp immédiate si présente en session -->
        @if(session('whatsapp_redirect_url'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shrink-0">
                        💬
                    </div>
                    <div>
                        <strong class="text-sm block text-emerald-950 font-extrabold">La fiche PDF et les tâches sont prêtes pour WhatsApp !</strong>
                        <p class="text-xs text-emerald-700">Cliquez sur le bouton ci-contre pour ouvrir la discussion avec {{ $sheet->employee?->full_name }}.</p>
                    </div>
                </div>
                <a 
                    href="{{ session('whatsapp_redirect_url') }}" 
                    target="_blank" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center shadow-xs transition-all touch-target shrink-0"
                >
                    Ouvrir WhatsApp Web / Mobile →
                </a>
            </div>
        @endif

        <!-- Carte Récapitulative Employé & Statut -->
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Collaborateur -->
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Collaborateur</span>
                    <h3 class="text-base font-extrabold text-[#0B0F14]">{{ $sheet->employee?->full_name }}</h3>
                    <p class="text-xs font-bold text-[#0066FF]">{{ $sheet->position }}</p>
                    <p class="text-[11px] text-slate-500">Matricule : {{ $sheet->employee?->employee_code ?? '—' }}</p>
                </div>

                <!-- Mission & Date -->
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Mission & Superviseur</span>
                    <p class="text-xs text-slate-700 font-medium">Date : <strong class="text-[#0B0F14]">{{ $sheet->date->translatedFormat('d F Y') }}</strong></p>
                    <p class="text-xs text-slate-700 font-medium">Assigné par : <strong class="text-[#0B0F14]">{{ $sheet->assignedBy?->name ?? 'Direction RH' }}</strong></p>
                    <p class="text-xs text-slate-700 font-medium">Créée le : {{ $sheet->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <!-- État des expéditions -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Notifications Automatiques</span>
                    
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-emerald-50 border border-emerald-200">
                        <span class="flex items-center gap-1.5 font-semibold text-emerald-800">
                            💬 WhatsApp :
                        </span>
                        <span class="font-bold text-emerald-700">
                            {{ $sheet->whatsapp_sent_at ? 'Envoyé ('.$sheet->whatsapp_sent_at->format('H:i').')' : 'En attente' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-blue-50 border border-blue-200">
                        <span class="flex items-center gap-1.5 font-semibold text-blue-800">
                            ✉️ Email :
                        </span>
                        <span class="font-bold text-blue-700">
                            {{ $sheet->email_sent_at ? 'Envoyé ('.$sheet->email_sent_at->format('H:i').')' : 'Non expédié' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Barre de progression des tâches -->
            @php
                $tasksList = is_array($sheet->tasks) ? $sheet->tasks : [];
                $totalTasks = count($tasksList);
                $doneTasks = collect($tasksList)->filter(fn($t) => is_array($t) && ($t['status'] ?? '') === 'termine')->count();
                $pct = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
            @endphp
            <div class="mt-6 pt-5 border-t border-[#E2E8F0] space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-600">Progression d'exécution des objectifs</span>
                    <span class="text-[#0B0F14] font-bold">{{ $doneTasks }} / {{ $totalTasks }} achevée(s) ({{ $pct }}%)</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-[#0066FF] rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </x-card>

        <!-- Checklist Interactive des Tâches & Circuit de Validation Managériale -->
        <x-card title="Checklist des Objectifs & Validation Managériale" subtitle="Circuit à double validation : Déclaration collaborateur &rarr; Contrôle manager (Règle 0.23 pt)">
            <div class="divide-y divide-[#E2E8F0]">
                @foreach($tasksList as $idx => $t)
                    @php
                        $title = is_array($t) ? ($t['title'] ?? '') : (string) $t;
                        $status = is_array($t) ? ($t['status'] ?? 'a_faire') : 'a_faire';
                        $validationStatus = is_array($t) ? ($t['validation_status'] ?? 'non_soumis') : 'non_soumis';
                        $refusalReason = is_array($t) ? ($t['refusal_reason'] ?? null) : null;
                        $isCompleted = ($status === 'termine' || $validationStatus === 'valide');
                        $isPendingValidation = ($validationStatus === 'en_attente');
                        $isValidated = ($validationStatus === 'valide');
                        $isRefused = ($validationStatus === 'refuse');
                        $canValidate = auth()->user()->hasRole('administrateur', 'responsable', 'responsable_rh');
                    @endphp
                    <div class="py-4 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 font-extrabold text-xs {{ $isValidated ? 'bg-emerald-100 text-emerald-700' : ($isPendingValidation ? 'bg-amber-100 text-amber-700' : ($isRefused ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $idx + 1 }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <span class="text-sm font-medium {{ $isValidated ? 'line-through text-slate-400' : 'text-[#0B0F14]' }} block">
                                        {{ $title }}
                                    </span>
                                    
                                    @if($isRefused && $refusalReason)
                                        <p class="text-xs text-rose-600 font-semibold mt-1 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Motif de refus : {{ $refusalReason }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @if($isValidated)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Validé (+0.23 pt)
                                    </span>
                                @elseif($isPendingValidation)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                        En attente validation
                                    </span>
                                @elseif($isRefused)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Refusé / À refaire
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        À faire
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Ligne d'actions selon profil (Employé ou Manager) -->
                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                            @if(!$isValidated)
                                <!-- Option 1 : Déclarer terminée par l'employé -->
                                @if(!$isPendingValidation)
                                    <form action="{{ route('rh.daily-tasks.submit-task', $sheet) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="task_index" value="{{ $idx }}" />
                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                            Déclarer terminée
                                        </button>
                                    </form>
                                @endif

                                <!-- Option 2 : Décision Managériale (Valider ou Refuser) -->
                                @if($canValidate)
                                    <div class="flex items-center gap-1.5" x-data="{ showRefuse: false }">
                                        <form action="{{ route('rh.daily-tasks.validate-task', $sheet) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="task_index" value="{{ $idx }}" />
                                            <input type="hidden" name="action" value="valide" />
                                            <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-colors flex items-center gap-1">
                                                <span>✓ Valider</span>
                                            </button>
                                        </form>

                                        <button 
                                            type="button" 
                                            @click="showRefuse = !showRefuse"
                                            class="px-2 py-1 text-xs font-semibold rounded bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors"
                                        >
                                            ✕ Refuser
                                        </button>

                                        <!-- Mini formulaire de motif de refus -->
                                        <div x-show="showRefuse" @click.away="showRefuse = false" class="absolute z-20 mt-1 right-0 w-72 p-3 bg-white rounded-xl shadow-xl border border-slate-200 text-left">
                                            <form action="{{ route('rh.daily-tasks.validate-task', $sheet) }}" method="POST" class="space-y-2">
                                                @csrf
                                                <input type="hidden" name="task_index" value="{{ $idx }}" />
                                                <input type="hidden" name="action" value="refuse" />
                                                <label class="block text-[11px] font-bold text-slate-700">Motif du refus :</label>
                                                <input type="text" name="reason" placeholder="Ex: Incomplet, document non joint..." required class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-rose-500" />
                                                <div class="flex items-center justify-end gap-1.5 pt-1">
                                                    <button type="button" @click="showRefuse = false" class="px-2 py-1 text-[11px] text-slate-500 hover:text-slate-700">Annuler</button>
                                                    <button type="submit" class="px-2.5 py-1 text-[11px] font-bold bg-rose-600 hover:bg-rose-700 text-white rounded">Confirmer le refus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($sheet->notes)
                <div class="mt-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                    <strong class="font-bold block mb-1">💡 Instructions complémentaires :</strong>
                    <p class="whitespace-pre-line text-amber-800">{{ $sheet->notes }}</p>
                </div>
            @endif
        </x-card>

        <!-- Actions de renvoi -->
        <div class="flex items-center justify-between text-xs text-slate-500 pt-2">
            <span>Réf Fiche : {{ $sheet->reference }}</span>

            <div class="flex items-center gap-2">
                <form action="{{ route('rh.daily-tasks.resend', [$sheet, 'email']) }}" method="POST">
                    @csrf
                    <button type="submit" class="hover:text-[#0066FF] underline font-medium">
                        Renvoyer l'Email
                    </button>
                </form>
                <span>&bull;</span>
                <form action="{{ route('rh.daily-tasks.resend', [$sheet, 'whatsapp']) }}" method="POST">
                    @csrf
                    <button type="submit" class="hover:text-[#0066FF] underline font-medium">
                        Renvoyer WhatsApp
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
