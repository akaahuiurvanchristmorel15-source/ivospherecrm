<x-layouts.app>
    <x-slot:title>Fiche d'Évaluation — {{ $evaluation->employee->full_name }} — IVOSPHERE RH</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- En-tête de la Fiche -->
        <x-page-header 
            :title="'Fiche d\'Évaluation — ' . $evaluation->month_name . ' ' . $evaluation->year" 
            :description="$evaluation->employee->full_name . ' • ' . ($evaluation->employee->position ?? 'Collaborateur') . ' • IVOSPHERE RH'"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Évaluations', 'url' => route('rh.evaluations.index', ['year' => $evaluation->year, 'month' => $evaluation->month])],
                    ['label' => $evaluation->employee->full_name]
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('rh.evaluations.print', $evaluation) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] hover:text-[#0066FF] text-xs font-semibold text-[#0B0F14] shadow-xs transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Imprimer Rapport A4</span>
                    </a>

                    <a href="{{ route('rh.evaluations.history', $evaluation->employee) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-xs font-semibold text-[#64748B] hover:text-[#0066FF] shadow-xs transition">
                        <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Historique</span>
                    </a>

                    @if($evaluation->is_locked)
                        @if(auth()->user()->hasRole('administrateur', 'responsable_rh'))
                            <form action="{{ route('rh.evaluations.unlock', $evaluation) }}" method="POST" onsubmit="return confirm('Déverrouiller cette évaluation pour correction ?');">
                                @csrf
                                <x-button variant="secondary" type="submit" size="md">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                    <span>Déverrouiller</span>
                                </x-button>
                            </form>
                        @endif
                    @else
                        <form action="{{ route('rh.evaluations.lock', $evaluation) }}" method="POST" onsubmit="return confirm('Verrouiller définitivement cette évaluation ? Les modifications ne seront plus autorisées.');">
                            @csrf
                            <x-button variant="secondary" type="submit" size="md">
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Verrouiller</span>
                            </x-button>
                        </form>
                    @endif

                    <x-button :href="route('rh.evaluations.index', ['year' => $evaluation->year, 'month' => $evaluation->month])" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Retour</span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Bannière d'Avertissement si Dossier Verrouillé -->
        @if($evaluation->is_locked)
            <div class="p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white uppercase tracking-wider">Évaluation Formellement Verrouillée</div>
                        <div class="text-[11px] text-slate-300">
                            Verrouillée le {{ $evaluation->locked_at?->format('d/m/Y à H:i') }} par {{ $evaluation->lockedByUser?->name ?? 'Direction RH' }}. Ce dossier est en lecture seule protégée.
                        </div>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-mono font-bold">
                    Certifié Conforme
                </span>
            </div>
        @endif

        <!-- Stepper Workflow de Validation (Point 11) -->
        <div class="p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-xs">
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="font-bold text-[#0B0F14] uppercase tracking-wider text-[11px]">Workflow d'approbation</span>
                <span class="text-[11px] font-semibold text-[#64748B]">
                    Statut actuel : <strong class="text-[#0066FF]">{{ ucfirst(str_replace('_', ' ', $evaluation->workflow_step ?? 'brouillon')) }}</strong>
                </span>
            </div>
            @php
                $steps = [
                    'brouillon' => '1. Brouillon',
                    'en_evaluation' => '2. En évaluation',
                    'validation_manager' => '3. Validation Manager',
                    'valide_rh' => '4. Validé RH',
                    'verrouille' => '5. Verrouillé 🔒',
                ];
                $stepKeys = array_keys($steps);
                $currentIndex = array_search($evaluation->workflow_step ?? 'brouillon', $stepKeys);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                @foreach($steps as $k => $label)
                    @php
                        $idx = array_search($k, $stepKeys);
                        $isActive = ($idx <= $currentIndex);
                    @endphp
                    <div class="p-2.5 rounded-xl border text-center transition {{ $isActive ? 'bg-blue-50/70 border-blue-200 text-[#0066FF]' : 'bg-[#F8FAFC] border-[#E2E8F0] text-[#94A3B8]' }}">
                        <span class="text-[11px] font-bold block">{{ $label }}</span>
                        <span class="text-[9px] mt-0.5 block {{ $isActive ? 'text-blue-600' : 'text-slate-400' }}">
                            {{ $isActive ? 'Validé' : 'En attente' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- CARTE CENTRALE : Synthèse Collaborateur & Note /30 -->
        <div class="rounded-3xl bg-white border border-[#E2E8F0] p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#E2E8F0] gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-bold text-xl shadow-md">
                        {{ strtoupper(substr($evaluation->employee->first_name ?? 'E', 0, 1) . substr($evaluation->employee->last_name ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#64748B]">Dossier Collaborateur certifié</span>
                        <h2 class="text-xl font-extrabold text-[#0B0F14] tracking-tight mt-0.5">{{ $evaluation->employee->full_name }}</h2>
                        <p class="text-xs text-[#64748B] mt-0.5">
                            Matricule : <strong class="text-[#0B0F14] font-mono">{{ $evaluation->employee->employee_code }}</strong> • 
                            Poste : <strong class="text-[#0B0F14]">{{ $evaluation->employee->position ?? 'Collaborateur' }}</strong> • 
                            Service : <strong class="text-[#0B0F14]">{{ $evaluation->employee->department ?? 'Général' }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Moyenne Mensuelle / 20 & Badge Appréciation -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 text-white p-5 rounded-2xl text-center sm:min-w-[220px] shadow-lg shadow-slate-900/10">
                    <span class="text-[9px] uppercase tracking-widest font-extrabold text-[#0066FF] block">Moyenne Mensuelle / 20</span>
                    <div class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-0.5">
                        {{ number_format($evaluation->final_score_20, 2, ',', ' ') }}
                        <span class="text-sm font-semibold text-slate-400">/ 20</span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-300 mt-1">
                        Total : <strong class="text-white">{{ (float)$evaluation->total_score_200 == (int)$evaluation->total_score_200 ? number_format($evaluation->total_score_200, 0) : number_format($evaluation->total_score_200, 1) }}</strong> / 200 pts
                    </div>
                    <div class="mt-2 flex items-center justify-center gap-1.5 text-[11px] font-mono">
                        @if($evaluation->score_progress > 0)
                            <span class="text-emerald-400 font-bold">↗ +{{ number_format($evaluation->score_progress, 2) }} pts</span>
                        @elseif($evaluation->score_progress < 0)
                            <span class="text-rose-400 font-bold">↘ {{ number_format($evaluation->score_progress, 2) }} pts</span>
                        @else
                            <span class="text-slate-400">Stable vs M-1</span>
                        @endif
                        <span class="text-slate-500">•</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $evaluation->appreciation_badge_class }}">
                            {{ $evaluation->appreciation }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 4 Indicateurs Clés de Performance (Gauges KPI) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <span class="text-[11px] text-[#64748B] font-semibold block">Taux de Présence</span>
                    <span class="text-lg font-black text-[#0B0F14] font-mono mt-0.5 block">{{ $attendanceRate }} %</span>
                    <span class="text-[10px] text-[#64748B]">{{ $evaluation->total_working_days }} j prévus au planning</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <span class="text-[11px] text-[#64748B] font-semibold block">Ponctualité</span>
                    <span class="text-lg font-black text-[#0B0F14] font-mono mt-0.5 block">{{ $punctualityRate }} %</span>
                    <span class="text-[10px] text-[#64748B]">Arrivées à l'heure</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <span class="text-[11px] text-[#64748B] font-semibold block">Tâches Réalisées</span>
                    <span class="text-lg font-black text-[#0B0F14] font-mono mt-0.5 block">{{ $tasksRate }} %</span>
                    <span class="text-[10px] text-[#64748B]">Validées par le manager</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <span class="text-[11px] text-[#64748B] font-semibold block">Appréciation Automatique</span>
                    <span class="text-base font-black mt-1 block {{ str_contains($evaluation->appreciation_badge_class, 'emerald') ? 'text-emerald-600' : (str_contains($evaluation->appreciation_badge_class, 'rose') ? 'text-rose-600' : 'text-[#0066FF]') }}">{{ $evaluation->appreciation }}</span>
                    <span class="text-[10px] text-[#64748B]">Barème automatique IVOSPHERE</span>
                </div>
            </div>
        </div>

        <!-- FORMULAIRE D'ÉVALUATION DES CRITÈRES ET APPRÉCIATIONS QUALITATIVES -->
        <form action="{{ route('rh.evaluations.update', $evaluation) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- SECTION SYSTÈME DE NOTATION MENSUEL (10 Matières /20) -->
            <x-card title="Système de Notation Mensuel (10 Matières sur 20)" subtitle="Chaque matière est évaluée sur 20 points — Moyenne mensuelle = Somme des 10 notes ÷ 10">
                <div class="space-y-4">
                    @forelse($evaluation->criteriaScores as $idx => $scoreItem)
                        @php
                            $crit = $scoreItem->criterion;
                            $isAuto = $crit?->isAutomatic() ?? false;
                            $scoreOn20 = (float) $scoreItem->score;
                        @endphp
                        <div class="p-4 rounded-2xl border border-[#E2E8F0] bg-white transition hover:border-[#CBD5E1]">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 font-mono font-bold text-xs text-slate-700 flex items-center justify-center shrink-0">
                                        {{ $idx + 1 }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $isAuto ? 'bg-blue-50 text-[#0066FF] border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                        {{ $isAuto ? 'Automatique' : 'Évaluation RH' }}
                                    </span>
                                    <span class="font-bold text-sm text-[#0B0F14]">{{ $crit->name ?? 'Matière' }}</span>
                                    <span class="text-xs text-[#64748B] font-mono">({{ $crit->weight_percentage ?? 10 }}% — Poids 1)</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="px-3.5 py-1.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-center min-w-[90px]">
                                        <span class="text-base font-black text-[#0B0F14] font-mono">
                                            {{ number_format($scoreOn20, 2, ',', ' ') }}
                                        </span>
                                        <span class="text-xs font-semibold text-[#64748B]">/ 20</span>
                                    </div>
                                </div>
                            </div>

                            @if($crit?->description)
                                <p class="text-xs text-[#64748B] mb-2.5">{{ $crit->description }}</p>
                            @endif

                            <!-- Justificatif factuel détaillé pour contrôle objectif -->
                            @if($scoreItem->justification)
                                <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100 text-xs flex items-center gap-2 mb-2.5 text-[#0B0F14]">
                                    <span class="text-sm shrink-0">📊</span>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#0066FF] block">Détail & Justificatif factuel :</span>
                                        <span class="font-medium text-slate-800">{{ $scoreItem->justification }}</span>
                                    </div>
                                </div>
                            @endif

                            @if(!$isAuto && !$evaluation->is_locked)
                                <!-- Saisie manuelle réservée au RH / Manager (/20) -->
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-[#F1F5F9] items-center">
                                    <div class="sm:col-span-4">
                                        <label class="block text-[11px] font-semibold text-[#0B0F14] mb-1">Note managériale RH (/20)</label>
                                        <div class="relative">
                                            <input type="number" step="0.5" min="0" max="20" 
                                                   name="criteria[{{ $crit->id }}][score]" 
                                                   value="{{ old('criteria.' . $crit->id . '.score', $scoreItem->score) }}"
                                                   class="w-full px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs font-mono font-bold text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                            <span class="absolute right-3 top-1.5 text-xs text-[#64748B] font-mono">/20</span>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-8">
                                        <label class="block text-[11px] font-semibold text-[#0B0F14] mb-1">Observation ou commentaire du responsable</label>
                                        <input type="text" 
                                               name="criteria[{{ $crit->id }}][comments]" 
                                               value="{{ old('criteria.' . $crit->id . '.comments', $scoreItem->comments) }}"
                                               placeholder="Observation ou appréciation managériale..."
                                               class="w-full px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                    </div>
                                </div>
                            @elseif(!$isAuto && $scoreItem->comments)
                                <div class="mt-2 text-xs text-[#64748B] bg-[#F8FAFC] p-2.5 rounded-xl border border-[#E2E8F0]">
                                    <strong class="text-[#0B0F14]">Commentaire RH :</strong> {{ $scoreItem->comments }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-[#64748B] py-4 text-center">Aucun critère associé. Cliquez sur « Générer Campagne » pour synchroniser les critères de base.</p>
                    @endforelse

                    <!-- Bandeau Récapitulatif Total & Moyenne sur 20 -->
                    <div class="p-4 rounded-2xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-extrabold text-[#0066FF] block">Récapitulatif du Calcul</span>
                            <div class="text-xs text-slate-300 mt-0.5">
                                Total des 10 matières : <strong class="text-white font-mono">{{ (float)$evaluation->total_score_200 == (int)$evaluation->total_score_200 ? number_format($evaluation->total_score_200, 0) : number_format($evaluation->total_score_200, 2) }} / 200</strong> 
                                ÷ 10 = <strong class="text-[#38bdf8] font-mono text-sm">{{ number_format($evaluation->final_score_20, 2) }} / 20</strong>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-300 font-semibold">Appréciation officielle :</span>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-white text-slate-900">
                                {{ $evaluation->appreciation }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- SECTION ANALYSE QUALITATIVE RH (Points 4 & 7) -->
            <x-card title="Évaluation Qualitative & Recommandations RH" subtitle="Points forts, axes d'amélioration et plan de développement">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-emerald-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Points forts constatés</span>
                        </label>
                        <textarea name="strengths" rows="3" {{ $evaluation->is_locked ? 'disabled' : '' }}
                                  placeholder="Réalisations remarquables, esprit d'initiative, qualités techniques ou humaines..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] transition disabled:bg-[#F8FAFC] disabled:text-[#94A3B8]">{{ old('strengths', $evaluation->strengths) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-amber-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Axes d'amélioration & points faibles</span>
                        </label>
                        <textarea name="weaknesses" rows="3" {{ $evaluation->is_locked ? 'disabled' : '' }}
                                  placeholder="Retards, lenteur d'exécution, manque de rigueur, communication à parfaire..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] transition disabled:bg-[#F8FAFC] disabled:text-[#94A3B8]">{{ old('weaknesses', $evaluation->weaknesses) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0B0F14] mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Recommandations & Actions RH</span>
                        </label>
                        <textarea name="recommendations" rows="3" {{ $evaluation->is_locked ? 'disabled' : '' }}
                                  placeholder="Formation continue, mentorat, recadrage horaire, réattribution de missions..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] transition disabled:bg-[#F8FAFC] disabled:text-[#94A3B8]">{{ old('recommendations', $evaluation->recommendations) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0B0F14] mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Objectifs prioritaires du mois prochain</span>
                        </label>
                        <textarea name="future_goals" rows="3" {{ $evaluation->is_locked ? 'disabled' : '' }}
                                  placeholder="Cap à atteindre pour la prochaine période d'évaluation..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] transition disabled:bg-[#F8FAFC] disabled:text-[#94A3B8]">{{ old('future_goals', $evaluation->future_goals) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-[#0B0F14] mb-1.5">Commentaire général de synthèse</label>
                        <textarea name="manager_comment" rows="2" {{ $evaluation->is_locked ? 'disabled' : '' }}
                                  placeholder="Synthèse officielle validée par le Responsable RH..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF] transition disabled:bg-[#F8FAFC] disabled:text-[#94A3B8]">{{ old('manager_comment', $evaluation->manager_comment) }}</textarea>
                    </div>
                </div>

                <!-- Sélecteurs de Statut & Workflow -->
                @if(!$evaluation->is_locked)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-[#E2E8F0]">
                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Statut du dossier</label>
                            <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                <option value="en_cours" @selected($evaluation->status === 'en_cours')>En cours d'évaluation</option>
                                <option value="valide" @selected($evaluation->status === 'valide')>Validé par le Responsable</option>
                                <option value="cloture" @selected($evaluation->status === 'cloture')>Clôturé définitivement</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#0B0F14] mb-1.5">Étape du Workflow</label>
                            <select name="workflow_step" class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                                <option value="brouillon" @selected($evaluation->workflow_step === 'brouillon')>1. Brouillon</option>
                                <option value="en_evaluation" @selected($evaluation->workflow_step === 'en_evaluation')>2. En évaluation RH</option>
                                <option value="validation_manager" @selected($evaluation->workflow_step === 'validation_manager')>3. Validation Manager</option>
                                <option value="valide_rh" @selected($evaluation->workflow_step === 'valide_rh')>4. Validé par la Direction RH</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <x-button variant="primary" type="submit" size="md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Enregistrer & Recalculer la Moyenne /20</span>
                        </x-button>
                    </div>
                @endif
            </x-card>
        </form>

        <!-- SECTION OBJECTIFS INDIVIDUELS DU MOIS (Point 9) -->
        <x-card title="Objectifs Individuels Assignés" subtitle="Suivi des 1 à 3 objectifs ciblés du collaborateur">
            <x-slot:actions>
                <button type="button" onclick="document.getElementById('modal-add-goal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-50 text-[#0066FF] hover:bg-blue-100 text-xs font-bold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Assigner un objectif</span>
                </button>
            </x-slot:actions>

            <div class="space-y-3">
                @forelse($evaluation->employee->goals as $goal)
                    <div class="p-3.5 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC]/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-[#0B0F14]">{{ $goal->title }}</span>
                                @php
                                    $stBadge = match($goal->status) {
                                        'atteint' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'en_cours' => 'bg-blue-50 text-[#0066FF] border-blue-200',
                                        'non_atteint' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $stBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                                </span>
                            </div>
                            @if($goal->description)
                                <p class="text-[11px] text-[#64748B]">{{ $goal->description }}</p>
                            @endif
                            <div class="text-[10px] text-[#94A3B8] font-mono">
                                Échéance : {{ $goal->due_date?->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3 sm:min-w-[180px]">
                            <div class="w-full">
                                <div class="flex justify-between text-[11px] font-mono mb-1">
                                    <span class="text-[#64748B]">Avancement</span>
                                    <span class="font-bold text-[#0B0F14]">{{ $goal->progress_pct }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-[#0066FF] transition-all" style="width: {{ $goal->progress_pct }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#64748B] py-4 text-center">Aucun objectif individuel enregistré pour ce collaborateur.</p>
                @endforelse
            </div>
        </x-card>

        <!-- SECTION PLAN D'AMÉLIORATION DE LA PERFORMANCE (Point 10 - PIP) -->
        @if($evaluation->final_score_30 < 18 || $evaluation->employee->improvementPlans->isNotEmpty())
            <div class="p-5 rounded-2xl border border-rose-200 bg-rose-50/40 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                            ⚠️
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-rose-900 uppercase tracking-wider">Plan d'Amélioration de la Performance (PIP)</h4>
                            <p class="text-[11px] text-rose-700">Mesures d'accompagnement requises pour les scores inférieurs à 18/30</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('modal-add-pip').classList.remove('hidden')" 
                            class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs">
                        + Ouvrir un PIP
                    </button>
                </div>

                @forelse($evaluation->employee->improvementPlans as $pip)
                    <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#0B0F14] text-sm">{{ $pip->title }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                {{ ucfirst(str_replace('_', ' ', $pip->status)) }} ({{ $pip->progress_pct }}%)
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-[#64748B] pt-1">
                            <div><strong class="text-[#0B0F14]">Difficulté identifiée :</strong> {{ $pip->problem_identified }}</div>
                            <div><strong class="text-[#0B0F14]">Objectif visé :</strong> {{ $pip->target_objective }}</div>
                        </div>
                        <div class="pt-2 border-t border-[#F1F5F9] text-[11px] text-[#94A3B8] flex items-center justify-between">
                            <span>Superviseur : {{ $pip->supervisor?->name ?? 'Direction RH' }}</span>
                            <span>Période : {{ $pip->start_date?->format('d/m/Y') }} au {{ $pip->end_date?->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-rose-800">Aucun PIP actif. Vous pouvez ouvrir un plan d'action personnalisé ci-dessus.</p>
                @endforelse
            </div>
        @endif

        <!-- SECTION SIGNATURE ÉLECTRONIQUE INTERNE (Point 11 & 16) -->
        <x-card title="Émargement & Signatures Électroniques" subtitle="Validation formelle interne du Manager et du Collaborateur">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Signature Manager -->
                <div class="p-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC]/50 space-y-3">
                    <span class="text-xs font-bold text-[#0B0F14] block uppercase tracking-wider">Visa du Manager RH</span>
                    @if($evaluation->manager_signature)
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 space-y-1">
                            <div class="font-bold text-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Signé par : {{ $evaluation->manager_signature }}</span>
                            </div>
                            <div class="text-[10px] text-emerald-700 font-mono">
                                Le {{ $evaluation->manager_signed_at?->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    @else
                        <form action="{{ route('rh.evaluations.sign', $evaluation) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="signer_type" value="manager">
                            <input type="text" name="signature_name" value="{{ auth()->user()?->name ?? '' }}" required
                                   placeholder="Nom et qualité du signataire..."
                                   class="w-full px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                            <button type="submit" class="w-full py-1.5 px-3 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white text-xs font-bold transition">
                                Apposer la signature Manager
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Signature Collaborateur -->
                <div class="p-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC]/50 space-y-3">
                    <span class="text-xs font-bold text-[#0B0F14] block uppercase tracking-wider">Émargement du Collaborateur</span>
                    @if($evaluation->employee_signature)
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 space-y-1">
                            <div class="font-bold text-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pris connaissance : {{ $evaluation->employee_signature }}</span>
                            </div>
                            <div class="text-[10px] text-emerald-700 font-mono">
                                Le {{ $evaluation->employee_signed_at?->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    @else
                        <form action="{{ route('rh.evaluations.sign', $evaluation) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="signer_type" value="employee">
                            <input type="text" name="signature_name" value="{{ $evaluation->employee->full_name }}" required
                                   placeholder="Nom du collaborateur pour visa..."
                                   class="w-full px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:border-[#0066FF]">
                            <button type="submit" class="w-full py-1.5 px-3 rounded-xl bg-[#0B0F14] hover:bg-slate-800 text-white text-xs font-bold transition">
                                Émarger la fiche collaborateur
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </x-card>
    </div>

    <!-- MODAL ASSIGNATION OBJECTIF (Point 9) -->
    <div id="modal-add-goal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-[#0B0F14]">Nouvel Objectif Individuel</h3>
                <button type="button" onclick="document.getElementById('modal-add-goal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('rh.goals.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $evaluation->employee_id }}">
                <input type="hidden" name="monthly_evaluation_id" value="{{ $evaluation->id }}">
                <div>
                    <label class="block font-semibold mb-1">Intitulé de l'objectif *</label>
                    <input type="text" name="title" required placeholder="Ex : Clôturer 15 devis grands comptes" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Description / Modalités de réussite</label>
                    <textarea name="description" rows="2" placeholder="Critères mesurables..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Date de début *</label>
                        <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Date d'échéance *</label>
                        <input type="date" name="due_date" value="{{ now()->addMonth()->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                </div>
                <input type="hidden" name="status" value="a_faire">
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-goal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-blue-700 text-white font-bold">Créer l'objectif</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CRÉATION PIP (Point 10) -->
    <div id="modal-add-pip" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                <h3 class="text-sm font-bold text-rose-700">Ouvrir un Plan d'Amélioration (PIP)</h3>
                <button type="button" onclick="document.getElementById('modal-add-pip').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('rh.pips.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $evaluation->employee_id }}">
                <input type="hidden" name="monthly_evaluation_id" value="{{ $evaluation->id }}">
                <div>
                    <label class="block font-semibold mb-1">Titre du plan d'amélioration *</label>
                    <input type="text" name="title" required placeholder="Ex : Rétablissement de la ponctualité & rigueur" class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Problème ou lacune identifiée *</label>
                    <textarea name="problem_identified" rows="2" required placeholder="Description factuelle des manquements..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Objectif ciblé mesurable *</label>
                    <textarea name="target_objective" rows="2" required placeholder="Zéro retard sur 30 jours, 95% des tâches complétées..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Actions correctives à mettre en place *</label>
                    <textarea name="corrective_actions" rows="2" required placeholder="Point hebdomadaire, formation, binôme..." class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Date de début *</label>
                        <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Date de fin prévisionnelle *</label>
                        <input type="date" name="end_date" value="{{ now()->addMonths(2)->format('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E2E8F0] focus:outline-none focus:border-[#0066FF]">
                    </div>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-pip').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-[#E2E8F0] font-bold text-slate-700">Annuler</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold">Lancer le PIP</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
