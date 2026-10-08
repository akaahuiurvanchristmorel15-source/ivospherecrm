<x-layouts.app title="Prévisions & Business Intelligence">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Modélisation Prédictive</span>
                    <span class="text-xs text-slate-500">Marge d'incertitude ±{{ $marginOfError }}%</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">Prévisions Commerciales & BI</h1>
                <p class="text-xs text-slate-500">Projections algorithmiques basées sur la vélocité des ventes et les flux d'exploitation.</p>
            </div>
            
            <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Estimation statistique établie sur l'historique récent (marge d'incertitude indicative ±{{ $marginOfError }}%).</span>
            </div>
        </div>

        <!-- Section 1: Projections Ventes à 3 mois -->
        <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-sm font-bold text-[#0B0F14] uppercase tracking-wider">Projection du Chiffre d'Affaires à 3 Mois</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Historique réel (6 derniers mois) et projection linéaire pondérée.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#F5F7FA] text-[#0B0F14] border border-[#E2E8F0]">
                    Tendance Globale
                </span>
            </div>

            <!-- Visualization Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-6 lg:grid-cols-9 gap-3 text-center">
                <!-- Historical 6 months -->
                @foreach($history as $item)
                    <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $item['label'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-200 text-slate-700 font-medium inline-block my-1">Réel</span>
                        <p class="text-sm font-bold text-[#0B0F14] mt-1">{{ number_format($item['revenue'], 0, ',', ' ') }}</p>
                        <span class="text-[10px] text-slate-400">FCFA</span>
                        <span class="block text-[10px] text-slate-500 mt-1">{{ $item['count'] }} commande(s)</span>
                    </div>
                @endforeach

                <!-- Forecast 3 months -->
                @foreach($forecast as $item)
                    <div class="p-3 rounded-xl bg-[#0066FF]/5 border-2 border-[#0066FF]/30 relative overflow-hidden">
                        <span class="text-[10px] font-bold text-[#0066FF] uppercase tracking-wider block">{{ $item['label'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-[#0066FF] text-white font-bold inline-block my-1">Prévision</span>
                        <p class="text-sm font-extrabold text-[#0066FF] mt-1">{{ number_format($item['projected'], 0, ',', ' ') }}</p>
                        <span class="text-[10px] text-[#0066FF]/80">FCFA</span>
                        <span class="block text-[10px] text-slate-500 mt-1">± {{ number_format($item['min'], 0, ',', ' ') }} - {{ number_format($item['max'], 0, ',', ' ') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section 2: Anticipation Ruptures de Stocks (Vélocité) & Dépenses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Rupture prévisionnelle stocks -->
            <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Anticipation des Ruptures de Stock</h3>
                        <p class="text-[11px] text-slate-500">Calcul basé sur la vélocité moyenne de vente sur 30 jours.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Seuil 30 jours</span>
                </div>

                <div class="space-y-3">
                    @forelse($stockAlerts as $alert)
                        <div class="p-3 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#0B0F14]">{{ $alert['name'] }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $alert['days_until_stockout'] <= 7 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                    Rupture estimée : {{ $alert['days_until_stockout'] }} jour(s)
                                </span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Stock actuel : <strong>{{ $alert['current_stock'] }}</strong> ({{ $alert['domain'] }})</span>
                                <span>Vélocité : {{ $alert['daily_velocity'] }}/jour</span>
                                <span class="text-[#0066FF] font-semibold">Réassort conseillé : +{{ $alert['suggested_reorder'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-slate-400 text-xs">
                            <svg class="w-8 h-8 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p class="font-medium text-[#0B0F14]">Aucun risque de rupture sous 30 jours.</p>
                            <p class="text-slate-400 text-[11px]">La vélocité actuelle est couverte par les stocks disponibles.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Projection Dépenses & Performance sans parti-pris -->
            <div class="space-y-6">

                <!-- Projection dépenses -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                    <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-3">Projection des Dépenses Récurrentes</h3>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0]">
                            <span class="text-slate-500 text-[11px] block">Moyenne mensuelle constatée :</span>
                            <span class="text-base font-bold text-[#0B0F14] mt-1 block">{{ number_format($expenseTrends['historical_avg'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#0066FF]/5 border border-[#0066FF]/30">
                            <span class="text-[#0066FF] text-[11px] font-semibold block">Budget prévisionnel M+1 :</span>
                            <span class="text-base font-bold text-[#0066FF] mt-1 block">{{ number_format($expenseTrends['projected_next_month'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Scores Opérationnels Objectifs -->
                <div class="bg-white rounded-2xl p-6 border border-[#E2E8F0] shadow-xs">
                    <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-1">Score Opérationnel par Pôle</h3>
                    <p class="text-[11px] text-slate-400 mb-4">Mesure objective de l'efficacité d'exécution des commandes sans jugement individuel.</p>

                    <div class="space-y-2.5">
                        @foreach($operationalScores as $score)
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-xs">
                                <div>
                                    <span class="font-bold text-[#0B0F14]">{{ $score['name'] }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ $score['metric'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-[#0B0F14]">{{ $score['value'] }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $score['rating'] === 'Excellent' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $score['rating'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
