<x-layouts.landing>
    <x-slot:title>Paiement — {{ $subscription->plan->name }} — IVOSPHERE</x-slot>
    <x-slot:navbar>@include('subscriptions.offers._nav')</x-slot:navbar>
    <x-slot:footer>@include('subscriptions.offers._footer')</x-slot:footer>

    <div class="relative overflow-hidden bg-[#F5F7FA] text-[#0B0F14] night:bg-black night:text-white transition-colors">
        @include('subscriptions.offers._shell-open')

        <section class="relative mx-auto max-w-xl px-4 pb-24 pt-28 sm:pt-36">
            <p class="text-center text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Paiement de l'abonnement</p>
            <h1 class="mt-3 text-center text-4xl font-extrabold tracking-tighter sm:text-5xl">Finaliser le paiement</h1>

            <div class="mt-10 rounded-[28px] border border-black/15 bg-white/80 p-6 backdrop-blur-2xl shadow-[0_30px_80px_-30px_rgba(11,15,20,0.35)] sm:p-8 night:border-white/20 night:bg-white/[0.07] night:shadow-[0_0_80px_-20px_rgba(255,255,255,0.2)]">
                <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-800 night:text-amber-200">
                    <strong>Mode test (local)</strong> — aucun paiement réel. En production, le client est redirigé vers la page de paiement sécurisée Paystack.
                </div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $subscription->tenant->name }}</p>
                        <p class="text-xs text-black/45 night:text-white/45">{{ $subscription->plan->name }} · {{ $subscription->billingCycleLabel() }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-black/10 px-3 py-1 font-mono text-[11px] text-black/55 night:border-white/15 night:text-white/55">{{ $subscription->reference }}</span>
                </div>

                <div class="my-6 h-px bg-gradient-to-r from-transparent via-black/10 to-transparent night:via-white/15"></div>

                <div class="flex items-baseline justify-between">
                    <span class="text-sm text-black/50 night:text-white/50">Montant à payer</span>
                    <span class="flex items-baseline gap-1 whitespace-nowrap">
                        <span class="text-4xl font-semibold tracking-tight">{{ number_format($subscription->amount, 0, ',', ' ') }}</span>
                        <span class="text-lg font-light text-black/45 night:text-white/50">FCFA</span>
                    </span>
                </div>

                <form method="POST" action="{{ route('offres.payment.simulate', $subscription) }}" class="mt-8 grid gap-3">
                    @csrf
                    <button type="submit" name="outcome" value="paid" class="h-12 rounded-full bg-[#0B0F14] text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Simuler un paiement réussi</button>
                    <button type="submit" name="outcome" value="failed" class="h-12 rounded-full border border-black/15 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">Simuler un échec</button>
                </form>
            </div>
        </section>
    </div>
</x-layouts.landing>
