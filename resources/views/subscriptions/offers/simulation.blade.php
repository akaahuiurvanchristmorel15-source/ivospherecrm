<x-layouts.guest>
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 space-y-5">
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800">
            <strong>Mode test (local)</strong> — aucun paiement réel. En production, le client est redirigé vers la page de paiement CinetPay.
        </div>

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Paiement de l'abonnement</p>
            <h1 class="mt-1 text-xl font-bold text-[#0B0F14]">{{ $subscription->plan->name }} — {{ $subscription->billingCycleLabel() }}</h1>
            <p class="mt-1 text-xs text-[#64748B]">{{ $subscription->tenant->name }} · Réf. {{ $subscription->reference }}</p>
        </div>

        <div class="flex items-baseline justify-between p-4 rounded-xl bg-slate-50">
            <span class="text-xs text-[#64748B]">Montant</span>
            <span class="text-2xl font-extrabold text-[#0B0F14]">{{ number_format($subscription->amount, 0, ',', ' ') }} <span class="text-xs">FCFA</span></span>
        </div>

        <form method="POST" action="{{ route('offres.payment.simulate', $subscription) }}" class="grid grid-cols-1 gap-2">
            @csrf
            <button type="submit" name="outcome" value="paid" class="h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">Simuler un paiement réussi</button>
            <button type="submit" name="outcome" value="failed" class="h-11 rounded-xl border border-[#E2E8F0] text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 transition">Simuler un échec</button>
        </form>
    </div>
</x-layouts.guest>
