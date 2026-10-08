<x-layouts.app :title="'Objectif : ' . $salesTarget->title . ' — IVOSPHERE RH'">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- En-tête -->
        <div class="pb-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('rh.sales-targets.index') }}" class="text-xs text-[#0066FF] hover:underline inline-flex items-center gap-1.5 mb-1 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour aux objectifs des ventes</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight">
                        {{ $salesTarget->title }}
                    </h1>
                    @php $badge = $salesTarget->status_badge; @endphp
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold border {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                </div>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Collaborateur : <strong class="text-[#0B0F14]">{{ $salesTarget->employee->full_name }}</strong> 
                    ({{ $salesTarget->employee->position ?? 'Commercial' }}) &bull; 
                    Pôle : <strong class="text-[#0B0F14]">{{ $salesTarget->domain->name ?? 'Consolidé' }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('rh.sales-targets.recalculate', $salesTarget) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:bg-[#F5F7FA] text-xs font-semibold text-[#0B0F14] transition-colors shadow-xs flex items-center gap-1.5" title="Recalculer les ventes réelles enregistrées">
                        <svg class="w-3.5 h-3.5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Recalculer Réalisé</span>
                    </button>
                </form>

                <a 
                    href="{{ route('rh.sales-targets.edit', $salesTarget) }}" 
                    class="px-4 py-2 rounded-xl bg-[#0B0F14] text-white hover:bg-[#0066FF] text-xs font-semibold transition-colors"
                >
                    Modifier
                </a>

                <form action="{{ route('rh.sales-targets.destroy', $salesTarget) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de cet objectif ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- 3 Cartes de Jauge & Progression -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Objectif Fixé (Quota)</span>
                <span class="text-2xl font-bold text-[#0B0F14] mt-1 block">
                    {{ number_format($salesTarget->target_amount, 0, ',', ' ') }} FCFA
                </span>
                <span class="text-xs text-[#64748B] block mt-1">
                    Période : <strong class="capitalize text-[#0B0F14]">{{ $salesTarget->period }}</strong>
                </span>
                <span class="text-[11px] text-[#64748B] block">
                    Du {{ $salesTarget->start_date->format('d/m/Y') }} au {{ $salesTarget->end_date->format('d/m/Y') }}
                </span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Montant Actuellement Réalisé</span>
                <span class="text-2xl font-bold text-[#0066FF] mt-1 block">
                    {{ number_format($salesTarget->achieved_amount, 0, ',', ' ') }} FCFA
                </span>
                <div class="mt-2">
                    <div class="w-full h-2 rounded-full bg-[#F5F7FA] border border-[#E2E8F0] overflow-hidden">
                        <div 
                            class="h-full rounded-full {{ $salesTarget->progress_percentage >= 100 ? 'bg-emerald-500' : 'bg-[#0066FF]' }} transition-all" 
                            style="width: {{ min(100, $salesTarget->progress_percentage) }}%"
                        ></div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs mt-1.5 font-semibold">
                    <span class="{{ $salesTarget->progress_percentage >= 100 ? 'text-emerald-600' : 'text-[#0066FF]' }}">
                        {{ $salesTarget->progress_percentage }}% accompli
                    </span>
                    <span class="text-[#64748B] text-[11px]">
                        @if($salesTarget->remaining_amount > 0)
                            Reste {{ number_format($salesTarget->remaining_amount, 0, ',', ' ') }} F
                        @else
                            Objectif Dépassé !
                        @endif
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E8F0] shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Rémunération Variable & Prime</span>
                <span class="text-2xl font-bold text-emerald-600 mt-1 block">
                    {{ number_format($salesTarget->estimated_commission, 0, ',', ' ') }} FCFA
                </span>
                <div class="text-[11px] text-[#64748B] space-y-0.5 mt-1">
                    <div>Commission ({{ $salesTarget->commission_rate }}%) : <strong>{{ number_format(($salesTarget->achieved_amount * $salesTarget->commission_rate) / 100, 0, ',', ' ') }} FCFA</strong></div>
                    @if($salesTarget->bonus_amount > 0)
                        <div>Prime d'atteinte : <strong>{{ number_format($salesTarget->bonus_amount, 0, ',', ' ') }} FCFA</strong> {{ $salesTarget->achieved_amount >= $salesTarget->target_amount ? '(Débloquée)' : '(Si 100% atteint)' }}</div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Détails & Notes de l'Entretien RH -->
        <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs space-y-4 text-xs">
            <h3 class="text-sm font-bold text-[#0B0F14] pb-2 border-b border-[#E2E8F0]">Dossier d'Évaluation Commerciale</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block">Nombre de ventes / Contrats</span>
                    <span class="text-lg font-bold text-[#0B0F14] mt-1 block">
                        {{ $salesTarget->achieved_sales_count }}
                        @if($salesTarget->target_sales_count)
                            / {{ $salesTarget->target_sales_count }} cible
                        @endif
                    </span>
                </div>

                <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block">Compte Utilisateur Lié</span>
                    <span class="text-xs font-semibold text-[#0B0F14] mt-1 block">
                        {{ $salesTarget->employee->user ? $salesTarget->employee->user->email : 'Aucun compte login associé' }}
                    </span>
                    <span class="text-[10px] text-[#64748B]">Permet la synchronisation automatique des factures</span>
                </div>

                <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block">Date de Clôture Prévue</span>
                    <span class="text-xs font-semibold text-[#0B0F14] mt-1 block">
                        {{ $salesTarget->end_date->format('d/m/Y') }}
                    </span>
                    <span class="text-[10px] {{ $salesTarget->end_date->isPast() ? 'text-rose-600 font-bold' : 'text-[#64748B]' }}">
                        {{ $salesTarget->end_date->isPast() ? 'Période échue' : 'En cours jusqu\'à échéance' }}
                    </span>
                </div>
            </div>

            @if($salesTarget->notes)
                <div class="pt-3">
                    <span class="text-[10px] text-[#64748B] uppercase font-bold block mb-1">Accords, Remarques & Contexte RH</span>
                    <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14] whitespace-pre-line leading-relaxed">
                        {{ $salesTarget->notes }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Commandes Récentes Associées -->
        @if($relatedOrders->count() > 0)
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs p-6 space-y-3">
                <h3 class="text-sm font-bold text-[#0B0F14]">Dernières Ventes Réalisées sur la Période</h3>
                
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[#64748B] border-b border-[#E2E8F0] pb-2">
                                <th class="pb-2 font-semibold">Référence</th>
                                <th class="pb-2 font-semibold">Client</th>
                                <th class="pb-2 font-semibold">Date</th>
                                <th class="pb-2 font-semibold text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @foreach($relatedOrders as $order)
                                <tr>
                                    <td class="py-2.5 font-mono font-semibold text-[#0066FF]">{{ $order->reference }}</td>
                                    <td class="py-2.5 font-medium text-[#0B0F14]">{{ $order->customer->name ?? 'Client' }}</td>
                                    <td class="py-2.5 text-[#64748B]">{{ $order->date?->format('d/m/Y') }}</td>
                                    <td class="py-2.5 text-right font-bold text-[#0B0F14]">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Amplified Cards -->
                <div class="block md:hidden space-y-2.5">
                    @foreach($relatedOrders as $order)
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs text-[#0066FF]">{{ $order->reference }}</span>
                                <span class="font-bold text-xs text-[#0B0F14]">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-[#64748B]">
                                <span>{{ $order->customer->name ?? 'Client' }}</span>
                                <span>{{ $order->date?->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-layouts.app>
