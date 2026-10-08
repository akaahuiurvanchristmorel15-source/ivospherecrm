<x-layouts.app title="Objectifs des Ventes — IVOSPHERE RH">
    <div class="space-y-6">

        <!-- En-tête RH -->
        <x-page-header 
            title="Objectifs des Ventes & Performance Commerciale" 
            description="Supervision RH des quotas de vente, suivi du réalisé, primes d'atteinte et commissions par collaborateur"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Ressources Humaines', 'url' => route('rh.index')],
                    ['label' => 'Objectifs des Ventes']
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                <x-button :href="route('rh.sales-targets.create')" variant="primary" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nouvel Objectif</span>
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <!-- 4 Stat Cards Consolidées -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <span class="text-xs font-medium text-[#64748B]">Objectifs Totaux Assignés</span>
                <div class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">
                    {{ number_format($totalTargetAmount, 0, ',', ' ') }} <span class="text-xs font-normal text-[#64748B]">FCFA</span>
                </div>
                <div class="pt-3 mt-3 border-t border-[#E2E8F0] flex items-center justify-between text-[11px] text-[#64748B]">
                    <span>Quotas actifs : <strong class="text-[#0B0F14]">{{ $activeCount }}</strong></span>
                    <span>Atteints : <strong class="text-emerald-600">{{ $achievedCount }}</strong></span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <span class="text-xs font-medium text-[#64748B]">Chiffre d'Affaires Réalisé</span>
                <div class="text-2xl font-bold text-[#0066FF] tracking-tight mt-1">
                    {{ number_format($totalAchievedAmount, 0, ',', ' ') }} <span class="text-xs font-normal text-[#64748B]">FCFA</span>
                </div>
                <div class="pt-3 mt-3 border-t border-[#E2E8F0] text-[11px] text-[#64748B]">
                    Calculé en temps réel sur les flux
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <span class="text-xs font-medium text-[#64748B]">Taux Moyen d'Atteinte</span>
                <div class="text-2xl font-bold {{ $averageAchievementRate >= 100 ? 'text-emerald-600' : ($averageAchievementRate >= 60 ? 'text-[#0B0F14]' : 'text-amber-600') }} tracking-tight mt-1">
                    {{ $averageAchievementRate }} %
                </div>
                <div class="pt-3 mt-3 border-t border-[#E2E8F0]">
                    <div class="w-full h-1.5 rounded-full bg-[#F5F7FA] border border-[#E2E8F0] overflow-hidden">
                        <div class="h-full rounded-full bg-[#0066FF]" style="width: {{ min(100, $averageAchievementRate) }}%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs flex flex-col justify-between">
                <span class="text-xs font-medium text-[#64748B]">Commissions & Primes Estimées</span>
                <div class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">
                    {{ number_format($totalCommissions, 0, ',', ' ') }} <span class="text-xs font-normal text-[#64748B]">FCFA</span>
                </div>
                <div class="pt-3 mt-3 border-t border-[#E2E8F0] text-[11px] text-[#64748B]">
                    Part variable sur performance
                </div>
            </div>

        </div>

        <!-- Filtres Avancés Épurés -->
        <div class="bg-white rounded-2xl p-4 border border-[#E2E8F0] shadow-xs">
            <form method="GET" action="{{ route('rh.sales-targets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                
                <div>
                    <label class="block text-[#64748B] font-medium mb-1">Commercial / Employé</label>
                    <select name="employee_id" class="w-full px-3 py-1.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Tous les employés</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[#64748B] font-medium mb-1">Pôle d'Activité</label>
                    <select name="domain_id" class="w-full px-3 py-1.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Tous les pôles</option>
                        @foreach($domains as $dom)
                            <option value="{{ $dom->id }}" {{ request('domain_id') == $dom->id ? 'selected' : '' }}>
                                {{ $dom->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[#64748B] font-medium mb-1">Statut</label>
                    <select name="status" class="w-full px-3 py-1.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Tous les statuts</option>
                        <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="atteint" {{ request('status') === 'atteint' ? 'selected' : '' }}>Atteint</option>
                        <option value="partiel" {{ request('status') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                        <option value="non_atteint" {{ request('status') === 'non_atteint' ? 'selected' : '' }}>Non atteint</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[#64748B] font-medium mb-1">Périodicité</label>
                    <select name="period" class="w-full px-3 py-1.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-[#0066FF]">
                        <option value="">Toutes périodicités</option>
                        <option value="mensuel" {{ request('period') === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                        <option value="trimestriel" {{ request('period') === 'trimestriel' ? 'selected' : '' }}>Trimestriel</option>
                        <option value="semestriel" {{ request('period') === 'semestriel' ? 'selected' : '' }}>Semestriel</option>
                        <option value="annuel" {{ request('period') === 'annuel' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-1.5 rounded-lg bg-[#0066FF] text-white font-medium hover:bg-blue-600 transition-colors">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['employee_id', 'domain_id', 'status', 'period']))
                        <a href="{{ route('rh.sales-targets.index') }}" class="px-3 py-1.5 rounded-lg bg-[#F5F7FA] hover:bg-slate-200 text-[#64748B] transition-colors" title="Réinitialiser">
                            ✕
                        </a>
                    @endif
                </div>

            </form>
        </div>

        <!-- Tableau des Objectifs Commerciaux -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <!-- Vue Table Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
                        <tr>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B]">Collaborateur</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B]">Intitulé & Pôle</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B]">Période</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B] text-right">Cible (FCFA)</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B]">Progression Réalisée</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B] text-right">Commission / Prime</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B] text-center">Statut</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[#64748B] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($targets as $target)
                            <tr class="hover:bg-[#F5F7FA]/60 transition-colors">
                                
                                <!-- Employé -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-[#0066FF]/10 text-[#0066FF] font-bold flex items-center justify-center shrink-0 text-xs">
                                            {{ substr($target->employee->first_name ?? 'E', 0, 1) }}{{ substr($target->employee->last_name ?? 'M', 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('rh.employees.show', $target->employee) }}" class="font-semibold text-[#0B0F14] hover:text-[#0066FF] transition-colors block">
                                                {{ $target->employee->full_name ?? 'Employé' }}
                                            </a>
                                            <span class="text-[11px] text-[#64748B]">{{ $target->employee->position ?? 'Commercial' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Titre & Pôle -->
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-[#0B0F14] block">{{ $target->title }}</span>
                                    <span class="text-[11px] text-[#64748B]">
                                        {{ $target->domain->name ?? 'Tous les pôles' }}
                                    </span>
                                </td>

                                <!-- Dates -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 capitalize">
                                        {{ $target->period }}
                                    </span>
                                    <span class="text-[11px] text-[#64748B] flex items-center gap-1 mt-0.5">
                                        <span>{{ $target->start_date->format('d/m/y') }}</span>
                                        <svg class="w-3 h-3 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        <span>{{ $target->end_date->format('d/m/y') }}</span>
                                    </span>
                                </td>

                                <!-- Cible -->
                                <td class="py-3.5 px-4 text-right font-bold text-[#0B0F14]">
                                    {{ number_format($target->target_amount, 0, ',', ' ') }}
                                </td>

                                <!-- Réalisé & Barre -->
                                <td class="py-3.5 px-4 min-w-[180px]">
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-[#0B0F14]">{{ number_format($target->achieved_amount, 0, ',', ' ') }} FCFA</span>
                                        <span class="font-bold {{ $target->progress_percentage >= 100 ? 'text-emerald-600' : 'text-[#0066FF]' }}">
                                            {{ $target->progress_percentage }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-[#F5F7FA] border border-[#E2E8F0] overflow-hidden">
                                        <div 
                                            class="h-full rounded-full {{ $target->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-[#0066FF]' }} transition-all" 
                                            style="width: {{ min(100, $target->progress_percentage) }}%"
                                        ></div>
                                    </div>
                                    @if($target->remaining_amount > 0)
                                        <span class="text-[10px] text-[#64748B] block mt-0.5">Reste : {{ number_format($target->remaining_amount, 0, ',', ' ') }} FCFA</span>
                                    @else
                                        <span class="text-[10px] text-emerald-600 font-semibold block mt-0.5">Dépassé de {{ number_format(abs($target->target_amount - $target->achieved_amount), 0, ',', ' ') }} FCFA</span>
                                    @endif
                                </td>

                                <!-- Commission & Bonus -->
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-bold text-[#0B0F14] block">
                                        {{ number_format($target->estimated_commission, 0, ',', ' ') }} FCFA
                                    </span>
                                    <span class="text-[10px] text-[#64748B]">
                                        {{ $target->commission_rate }}% 
                                        @if($target->bonus_amount > 0)
                                            + {{ number_format($target->bonus_amount, 0, ',', ' ') }} prime
                                        @endif
                                    </span>
                                </td>

                                <!-- Statut -->
                                <td class="py-3.5 px-4 text-center">
                                    @php $badge = $target->status_badge; @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold border {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('rh.sales-targets.show', $target) }}" class="text-[#0066FF] hover:underline font-semibold text-xs">
                                            Détails
                                        </a>
                                        <a href="{{ route('rh.sales-targets.edit', $target) }}" class="text-[#64748B] hover:text-[#0B0F14] text-xs">
                                            Modifier
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-[#64748B]">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-[#64748B]">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        </div>
                                        <p class="font-semibold text-[#0B0F14]">Aucun objectif commercial défini</p>
                                        <p class="text-xs">Attribuez des quotas de ventes aux collaborateurs pour suivre leur performance et calculer leurs commissions.</p>
                                        <div class="pt-2">
                                            <a href="{{ route('rh.sales-targets.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0066FF] text-white text-xs font-semibold hover:bg-blue-600 transition-colors">
                                                <span>Fixer un Objectif</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Vue Cartes Mobile (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($targets as $target)
                    @php $badge = $target->status_badge; @endphp
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-lg bg-[#0066FF]/10 text-[#0066FF] font-bold flex items-center justify-center shrink-0 text-xs">
                                    {{ substr($target->employee->first_name ?? 'E', 0, 1) }}{{ substr($target->employee->last_name ?? 'M', 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('rh.sales-targets.show', $target) }}" class="font-bold text-[#0B0F14] hover:text-[#0066FF] text-sm block">
                                        {{ $target->title }}
                                    </a>
                                    <div class="text-[11px] text-[#64748B]">
                                        {{ $target->employee->full_name ?? 'Employé' }} &bull; {{ $target->domain->name ?? 'Tous les pôles' }}
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border shrink-0 {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </div>

                        <!-- Progress Bar Container -->
                        <div class="bg-[#F5F7FA] p-3 rounded-lg border border-[#E2E8F0] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#64748B] font-medium">Réalisé / Cible</span>
                                <span class="font-bold {{ $target->progress_percentage >= 100 ? 'text-emerald-600' : 'text-[#0066FF]' }}">
                                    {{ $target->progress_percentage }}%
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                <div 
                                    class="h-full rounded-full {{ $target->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-[#0066FF]' }} transition-all" 
                                    style="width: {{ min(100, $target->progress_percentage) }}%"
                                ></div>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1">
                                <span class="font-bold text-[#0B0F14]">{{ number_format($target->achieved_amount, 0, ',', ' ') }} FCFA</span>
                                <span class="text-[#64748B] font-mono text-[11px]">Objectif: {{ number_format($target->target_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>

                        <!-- 2-col info grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs bg-[#F5F7FA]/70 p-2.5 rounded-lg border border-[#E2E8F0]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block">Commission Est.</span>
                                <span class="font-bold text-[#0B0F14] text-xs">{{ number_format($target->estimated_commission, 0, ',', ' ') }} FCFA</span>
                                <span class="text-[10px] text-[#64748B]">Taux : {{ $target->commission_rate }}%</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#64748B] block">Période</span>
                                <span class="font-semibold text-[#0B0F14] capitalize text-xs block">{{ $target->period }}</span>
                                <span class="text-[10px] text-[#64748B] block">{{ $target->start_date->format('d/m/y') }} au {{ $target->end_date->format('d/m/y') }}</span>
                            </div>
                        </div>

                        <!-- Actions bar -->
                        <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2">
                            <a href="{{ route('rh.sales-targets.show', $target) }}" class="flex-1 py-2 px-3 rounded-lg bg-[#0066FF] hover:bg-blue-600 text-white text-xs font-semibold text-center transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Suivi détaillé</span>
                            </a>
                            <a href="{{ route('rh.sales-targets.edit', $target) }}" class="py-2 px-3 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:text-[#0B0F14] hover:bg-[#F5F7FA] text-xs font-semibold transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Modifier</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-[#64748B]">
                        <p class="font-semibold text-[#0B0F14] text-sm">Aucun objectif commercial défini</p>
                        <a href="{{ route('rh.sales-targets.create') }}" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0066FF] text-white text-xs font-semibold hover:bg-blue-600 transition-colors">
                            <span>Fixer un Objectif</span>
                        </a>
                    </div>
                @endforelse
            </div>

            @if($targets->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $targets->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
