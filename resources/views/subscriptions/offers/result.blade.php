<x-layouts.landing>
    <x-slot:title>{{ $subscription->isPaid() ? 'Abonnement activé' : 'Paiement' }} — IVOSPHERE</x-slot>
    <x-slot:navbar>@include('subscriptions.offers._nav')</x-slot:navbar>
    <x-slot:footer>@include('subscriptions.offers._footer')</x-slot:footer>

    @php
        $state = $subscription->isPaid() ? 'paid' : ($subscription->status === 'failed' ? 'failed' : 'pending');
        $tone = [
            'paid' => ['ring' => 'bg-emerald-500/15 text-emerald-600 night:text-emerald-400', 'badge' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 night:text-emerald-300', 'label' => 'Paiement confirmé'],
            'failed' => ['ring' => 'bg-rose-500/15 text-rose-600 night:text-rose-400', 'badge' => 'border-rose-500/30 bg-rose-500/10 text-rose-700 night:text-rose-300', 'label' => 'Paiement non abouti'],
            'pending' => ['ring' => 'bg-amber-500/15 text-amber-600 night:text-amber-400', 'badge' => 'border-amber-500/30 bg-amber-500/10 text-amber-700 night:text-amber-300', 'label' => 'En attente de confirmation'],
        ][$state];
    @endphp

    <div class="relative overflow-hidden bg-[#F5F7FA] text-[#0B0F14] night:bg-black night:text-white transition-colors">
        @include('subscriptions.offers._shell-open')

        <section class="relative mx-auto max-w-xl px-4 pb-24 pt-28 sm:pt-36 text-center">
            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full {{ $tone['ring'] }}">
                @if($state === 'paid')
                    <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @elseif($state === 'failed')
                    <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                @else
                    <svg class="h-9 w-9 animate-spin [animation-duration:2.5s]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                @endif
            </span>

            <span class="mt-6 inline-flex rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wider {{ $tone['badge'] }}">{{ $tone['label'] }}</span>

            <h1 class="mt-4 text-4xl font-extrabold tracking-tighter sm:text-5xl">
                @if($state === 'paid') Bienvenue sur IVOSPHERE @elseif($state === 'failed') Le paiement n'a pas abouti @else Paiement en cours @endif
            </h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-black/55 night:text-white/55">
                @if($state === 'paid')
                    Votre espace <strong class="text-[#0B0F14] night:text-white">{{ $subscription->tenant->name }}</strong> est actif. Le récapitulatif PDF a été envoyé à {{ $subscription->tenant->email }}.
                @elseif($state === 'failed')
                    Aucun montant n'a été débité. Vous pouvez réessayer avec un autre moyen de paiement.
                @else
                    Dès que l'opérateur confirme le paiement, votre espace est activé et vous recevez le récapitulatif PDF par e-mail.
                @endif
            </p>

            @if(session('error'))
                <div class="mx-auto mt-6 max-w-md rounded-2xl border border-rose-500/30 bg-rose-500/10 p-3 text-left text-xs text-rose-700 night:text-rose-200">{{ session('error') }}</div>
            @endif

            @if($state === 'paid' && $ownsSubscription)
                <div class="mt-10 rounded-[28px] border border-black/[0.08] bg-white/60 p-6 text-left backdrop-blur-2xl night:border-white/10 night:bg-white/[0.04]">
                    <dl class="divide-y divide-black/[0.06] text-sm night:divide-white/[0.06]">
                        <div class="flex justify-between gap-3 py-3"><dt class="text-black/50 night:text-white/50">Offre</dt><dd class="font-semibold">{{ $subscription->plan->name }} · {{ $subscription->billingCycleLabel() }}</dd></div>
                        <div class="flex justify-between gap-3 py-3"><dt class="text-black/50 night:text-white/50">Montant payé</dt><dd class="font-semibold">{{ number_format($subscription->amount, 0, ',', ' ') }} FCFA</dd></div>
                        <div class="flex justify-between gap-3 py-3"><dt class="text-black/50 night:text-white/50">Valable jusqu'au</dt><dd class="font-semibold">{{ $subscription->ends_at?->format('d/m/Y') }}</dd></div>
                        @if($subscription->payment_method)
                            <div class="flex justify-between gap-3 py-3"><dt class="text-black/50 night:text-white/50">Moyen de paiement</dt><dd class="font-semibold">{{ $subscription->payment_method }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3 py-3"><dt class="text-black/50 night:text-white/50">Référence</dt><dd class="font-mono text-xs">{{ $subscription->reference }}</dd></div>
                    </dl>
                </div>
            @endif

            <div class="mx-auto mt-8 grid max-w-md gap-3">
                @if($state === 'paid')
                    <a href="{{ route('login') }}" class="flex h-12 items-center justify-center rounded-full bg-[#0B0F14] text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Accéder à mon espace</a>
                    @if($ownsSubscription)
                        <a href="{{ route('offres.payment.pdf', $subscription) }}" class="flex h-12 items-center justify-center gap-2 rounded-full border border-black/15 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Télécharger le récapitulatif PDF
                        </a>
                    @endif
                @else
                    @if($ownsSubscription)
                        <form method="POST" action="{{ route('offres.payment.retry', $subscription) }}">
                            @csrf
                            <button type="submit" class="h-12 w-full rounded-full bg-[#0B0F14] text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">{{ $state === 'failed' ? 'Réessayer le paiement' : 'Payer maintenant' }}</button>
                        </form>
                    @endif
                    @if($state === 'pending')
                        <a href="{{ route('offres.payment.return', $subscription) }}" class="flex h-12 items-center justify-center rounded-full border border-black/15 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">Actualiser</a>
                    @endif
                    <a href="{{ route('offres.index') }}" class="text-xs text-black/45 hover:underline night:text-white/45">Revenir aux offres</a>
                @endif
            </div>

            <p class="mt-8 font-mono text-[11px] text-black/35 night:text-white/35">Réf. {{ $subscription->reference }}</p>
        </section>
    </div>
</x-layouts.landing>
