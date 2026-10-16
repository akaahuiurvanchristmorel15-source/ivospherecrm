<x-layouts.guest>
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 space-y-5 text-center">
        @if(session('error'))
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 text-left">{{ session('error') }}</div>
        @endif

        @if($subscription->isPaid())
            <div class="mx-auto w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-[#0B0F14]">Abonnement activé</h1>
                <p class="mt-1 text-xs text-[#64748B]">Votre espace <strong>{{ $subscription->tenant->name }}</strong> est prêt. Le récapitulatif PDF a été envoyé à {{ $subscription->tenant->email }}.</p>
            </div>

            @if($ownsSubscription)
                <dl class="text-left text-xs divide-y divide-[#E2E8F0] border border-[#E2E8F0] rounded-xl">
                    <div class="flex justify-between gap-3 p-3"><dt class="text-[#64748B]">Offre</dt><dd class="font-semibold">{{ $subscription->plan->name }} ({{ $subscription->billingCycleLabel() }})</dd></div>
                    <div class="flex justify-between gap-3 p-3"><dt class="text-[#64748B]">Montant payé</dt><dd class="font-semibold">{{ number_format($subscription->amount, 0, ',', ' ') }} FCFA</dd></div>
                    <div class="flex justify-between gap-3 p-3"><dt class="text-[#64748B]">Valable jusqu'au</dt><dd class="font-semibold">{{ $subscription->ends_at?->format('d/m/Y') }}</dd></div>
                    <div class="flex justify-between gap-3 p-3"><dt class="text-[#64748B]">Référence</dt><dd class="font-mono">{{ $subscription->reference }}</dd></div>
                </dl>

                <a href="{{ route('offres.payment.pdf', $subscription) }}" class="h-11 rounded-xl border border-[#E2E8F0] text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Télécharger le récapitulatif PDF
                </a>
            @endif

            <a href="{{ route('login') }}" class="h-11 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-semibold transition flex items-center justify-center">Accéder à mon espace</a>
        @elseif($subscription->status === 'failed')
            <div class="mx-auto w-14 h-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-[#0B0F14]">Paiement non abouti</h1>
                <p class="mt-1 text-xs text-[#64748B]">Aucun montant n'a été validé. Vous pouvez réessayer.</p>
            </div>
            @if($ownsSubscription)
                <form method="POST" action="{{ route('offres.payment.retry', $subscription) }}">
                    @csrf
                    <button type="submit" class="w-full h-11 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-semibold transition">Réessayer le paiement</button>
                </form>
            @endif
        @else
            <div class="mx-auto w-14 h-14 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-[#0B0F14]">Paiement en cours de confirmation</h1>
                <p class="mt-1 text-xs text-[#64748B]">Dès que l'opérateur confirme le paiement, votre espace est activé et vous recevez le récapitulatif PDF par e-mail.</p>
            </div>
            <a href="{{ route('offres.payment.return', $subscription) }}" class="h-11 rounded-xl border border-[#E2E8F0] text-sm font-semibold text-[#0B0F14] hover:bg-slate-50 transition flex items-center justify-center">Actualiser</a>
            @if($ownsSubscription)
                <form method="POST" action="{{ route('offres.payment.retry', $subscription) }}">
                    @csrf
                    <button type="submit" class="w-full h-11 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-semibold transition">Payer maintenant</button>
                </form>
            @endif
        @endif

        <p class="text-[11px] text-[#64748B]">Réf. {{ $subscription->reference }}</p>
    </div>
</x-layouts.guest>
