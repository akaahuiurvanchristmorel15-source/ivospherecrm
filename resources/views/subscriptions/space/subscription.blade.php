<x-layouts.app>
    <x-slot:title>Mon abonnement</x-slot>

    @php($badge = $tenant->statusBadge())

    <div class="space-y-6">
        <x-page-header title="Mon abonnement" description="Offre en cours, renouvellement et récapitulatifs PDF" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <x-card class="lg:col-span-2">
                <div class="p-5 sm:p-6 space-y-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Offre actuelle</p>
                            <h2 class="mt-1 text-xl font-bold text-[#0B0F14]">{{ $tenant->plan?->name ?? '—' }}</h2>
                        </div>
                        <x-badge :variant="$badge['variant']" size="md" dot>{{ $badge['label'] }}</x-badge>
                    </div>

                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <dt class="text-[#64748B]">Échéance</dt>
                            <dd class="mt-0.5 font-semibold text-[#0B0F14]">{{ $tenant->subscription_ends_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#64748B]">Jours restants</dt>
                            <dd class="mt-0.5 font-semibold text-[#0B0F14]">{{ $tenant->daysRemaining() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#64748B]">Utilisateurs</dt>
                            <dd class="mt-0.5 font-semibold text-[#0B0F14]">{{ $usersCount }}{{ $tenant->plan?->max_users ? ' / '.$tenant->plan->max_users : '' }}</dd>
                        </div>
                    </dl>

                    @if($tenant->plan)
                        <div class="flex flex-wrap gap-2">
                            @foreach($tenant->plan->moduleDetails() as $module)
                                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-[#E2E8F0] text-[11px] font-medium text-[#0B0F14]">{{ $module['label'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card>

            <x-card title="Renouveler / changer d'offre">
                @if(auth()->user()->isAdmin() && $plans->isNotEmpty())
                    <form method="POST" action="{{ route('space.subscription.renew') }}" class="p-5 space-y-3" x-data="{ cycle: 'monthly' }">
                        @csrf
                        <div>
                            <label for="plan_id" class="block text-xs font-semibold text-[#0B0F14] mb-1">Offre</label>
                            <select id="plan_id" name="plan_id" class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0] text-sm">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected($plan->id === $tenant->subscription_plan_id)>
                                        {{ $plan->name }} — {{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois · {{ number_format($plan->price_yearly, 0, ',', ' ') }} F/an
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-slate-100">
                            <label class="h-9 rounded-lg text-xs font-semibold flex items-center justify-center cursor-pointer" :class="cycle === 'monthly' ? 'bg-white shadow-sm text-[#0B0F14]' : 'text-[#64748B]'">
                                <input type="radio" name="billing_cycle" value="monthly" x-model="cycle" class="sr-only"> Mensuel
                            </label>
                            <label class="h-9 rounded-lg text-xs font-semibold flex items-center justify-center cursor-pointer" :class="cycle === 'yearly' ? 'bg-white shadow-sm text-[#0B0F14]' : 'text-[#64748B]'">
                                <input type="radio" name="billing_cycle" value="yearly" x-model="cycle" class="sr-only"> Annuel
                            </label>
                        </div>
                        <x-button type="submit" variant="primary" class="w-full">Payer en ligne</x-button>
                        <p class="text-[11px] text-[#64748B]">Un renouvellement anticipé s'ajoute à la période en cours.</p>
                    </form>
                @else
                    <p class="p-5 text-xs text-[#64748B]">Seul l'administrateur de l'espace peut renouveler l'abonnement.</p>
                @endif
            </x-card>
        </div>

        <x-card title="Historique des paiements">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-[#64748B]">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Référence</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Offre</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Période</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Montant</th>
                            <th class="px-4 py-2.5 text-left font-semibold">État</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td class="px-4 py-3 font-mono">{{ $subscription->reference }}</td>
                                <td class="px-4 py-3">{{ $subscription->plan?->name }} · {{ $subscription->billingCycleLabel() }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $subscription->starts_at?->format('d/m/Y') ?? '—' }} → {{ $subscription->ends_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">{{ number_format($subscription->amount, 0, ',', ' ') }} F</td>
                                <td class="px-4 py-3">
                                    @if($subscription->isPaid())
                                        <x-badge variant="success">Payé</x-badge>
                                    @elseif($subscription->status === 'failed')
                                        <x-badge variant="danger">Échec</x-badge>
                                    @else
                                        <x-badge variant="warning">En attente</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($subscription->isPaid())
                                        <a href="{{ route('space.subscription.pdf', $subscription) }}" class="font-semibold text-[#0066FF] hover:underline whitespace-nowrap">PDF</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-[#64748B]">Aucun paiement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
