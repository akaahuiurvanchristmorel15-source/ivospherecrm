<x-layouts.landing>
    <x-slot:title>Inscription — {{ $plan->name }} — IVOSPHERE</x-slot>

    <x-slot:navbar>
        @include('subscriptions.offers._nav')
    </x-slot:navbar>

    <x-slot:footer>
        @include('subscriptions.offers._footer')
    </x-slot:footer>

    <div x-data="{ cycle: '{{ old('billing_cycle', $billingCycle) }}' }" class="relative overflow-hidden bg-[#F5F7FA] text-[#0B0F14] night:bg-black night:text-white transition-colors">
        <div class="pointer-events-none absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] rounded-full bg-[#0066FF]/[0.07] blur-3xl night:bg-white/[0.06]"></div>

        <section class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-32 pb-24">
            <a href="{{ route('offres.index') }}" class="inline-flex items-center gap-1.5 text-xs text-black/50 hover:text-black night:text-white/50 night:hover:text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Toutes les offres
            </a>
            <h1 class="mt-4 text-4xl sm:text-6xl font-extrabold tracking-tighter leading-none">Créez votre espace</h1>
            <p class="mt-4 max-w-xl text-sm text-black/50 night:text-white/50">Après le paiement, votre espace est activé immédiatement et vous recevez le récapitulatif PDF de votre abonnement par e-mail.</p>

            <div class="mt-10 grid grid-cols-1 lg:grid-cols-5 gap-5 items-start">
                <form method="POST" action="{{ route('offres.register', $plan) }}"
                      class="lg:col-span-3 rounded-[28px] border border-black/[0.08] bg-white/60 p-6 sm:p-8 backdrop-blur-2xl space-y-8 night:border-white/10 night:bg-white/[0.04]">
                    @csrf
                    <input type="hidden" name="billing_cycle" :value="cycle">

                    @foreach(['success' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-800 night:text-emerald-200', 'warning' => 'border-amber-400/40 bg-amber-400/10 text-amber-800 night:text-amber-200', 'error' => 'border-rose-500/30 bg-rose-500/10 text-rose-700 night:text-rose-200'] as $flash => $flashClasses)
                        @if(session($flash))
                            <div class="rounded-2xl border p-3 text-xs {{ $flashClasses }}">{{ session($flash) }}</div>
                        @endif
                    @endforeach

                    @if($googleIdentity)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/[0.07] p-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white ring-1 ring-black/5">
                                    @include('subscriptions.offers._google-icon')
                                </span>
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-emerald-700 night:text-emerald-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        E-mail vérifié par Google
                                    </p>
                                    <p class="truncate text-sm font-semibold">{{ $googleIdentity['email'] }}</p>
                                </div>
                            </div>
                            <button type="submit" form="google-forget" class="h-9 rounded-full border border-black/15 px-3.5 text-xs font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">Changer d'adresse</button>
                        </div>
                    @elseif($googleEnabled)
                        <div class="space-y-3">
                            <a href="{{ route('offres.google.redirect', ['plan' => $plan, 'cycle' => $billingCycle]) }}" :href="'{{ route('offres.google.redirect', $plan) }}?cycle=' + cycle"
                               class="flex h-12 w-full items-center justify-center gap-3 rounded-full border border-black/15 bg-white text-sm font-semibold text-[#0B0F14] shadow-sm transition hover:border-black/40 night:border-white/20 night:hover:border-white/50">
                                @include('subscriptions.offers._google-icon')
                                Continuer avec Google
                            </a>
                            <p class="text-center text-[11px] text-black/45 night:text-white/45">Recommandé : Google confirme votre adresse e-mail, vos reçus de paiement y seront envoyés.</p>
                            <div class="flex items-center gap-3 text-[11px] uppercase tracking-wider text-black/35 night:text-white/35">
                                <span class="h-px flex-1 bg-black/10 night:bg-white/10"></span>
                                ou remplissez le formulaire
                                <span class="h-px flex-1 bg-black/10 night:bg-white/10"></span>
                            </div>
                        </div>
                    @endif

                    <fieldset class="space-y-4">
                        <legend class="flex items-center gap-3 text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">
                            <span class="w-6 h-6 rounded-full bg-[#0B0F14] text-white night:bg-white night:text-black text-[11px] font-bold flex items-center justify-center">1</span>
                            Votre entreprise
                        </legend>
                        @include('subscriptions.offers._field', ['name' => 'company_name', 'label' => "Nom de l'entreprise / boutique", 'required' => true, 'placeholder' => 'Ex : Boutique Grâce'])
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @include('subscriptions.offers._field', ['name' => 'email', 'label' => "E-mail de l'entreprise", 'type' => 'email', 'required' => true, 'placeholder' => 'contact@entreprise.ci', 'value' => $googleIdentity['email'] ?? null])
                            @include('subscriptions.offers._field', ['name' => 'phone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true, 'placeholder' => '07 00 00 00 00'])
                            @include('subscriptions.offers._field', ['name' => 'city', 'label' => 'Ville', 'placeholder' => 'Abidjan'])
                            @include('subscriptions.offers._field', ['name' => 'address', 'label' => 'Adresse', 'placeholder' => 'Cocody, Angré'])
                        </div>
                    </fieldset>

                    <div class="h-px bg-gradient-to-r from-transparent via-black/10 to-transparent night:via-white/15"></div>

                    <fieldset class="space-y-4">
                        <legend class="flex items-center gap-3 text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">
                            <span class="w-6 h-6 rounded-full bg-[#0B0F14] text-white night:bg-white night:text-black text-[11px] font-bold flex items-center justify-center">2</span>
                            Compte administrateur de l'espace
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @include('subscriptions.offers._field', ['name' => 'admin_name', 'label' => 'Nom complet', 'required' => true, 'autocomplete' => 'name', 'value' => $googleIdentity['name'] ?? null])
                            @include('subscriptions.offers._field', ['name' => 'admin_email', 'label' => $googleIdentity ? 'E-mail de connexion (vérifié par Google)' : 'E-mail de connexion', 'type' => 'email', 'required' => true, 'autocomplete' => 'username', 'value' => $googleIdentity['email'] ?? null, 'readonly' => (bool) $googleIdentity])
                            @include('subscriptions.offers._field', ['name' => 'password', 'label' => 'Mot de passe', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                            @include('subscriptions.offers._field', ['name' => 'password_confirmation', 'label' => 'Confirmer le mot de passe', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                        </div>
                        <p class="text-[11px] text-black/40 night:text-white/40">Ce compte gère votre espace : il pourra créer les accès de votre équipe.</p>
                    </fieldset>

                    <div class="h-px bg-gradient-to-r from-transparent via-black/10 to-transparent night:via-white/15"></div>

                    <div>
                        <label class="flex items-start gap-3 text-[13px] text-black/70 night:text-white/70 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-0.5 w-4 h-4 rounded border-black/20 text-[#0B0F14] focus:ring-black/20 night:border-white/30 night:bg-transparent">
                            <span>J'accepte les conditions d'abonnement IVOSPHERE et le renouvellement manuel à chaque échéance.</span>
                        </label>
                        @error('terms')
                            <p class="mt-1.5 text-[11px] text-rose-600 night:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full h-12 rounded-full bg-[#0B0F14] text-sm font-semibold text-white hover:bg-black night:bg-white night:text-black night:hover:bg-white/90 transition flex items-center justify-center gap-2">
                        Continuer vers le paiement
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </form>

                @if($googleIdentity)
                    <form id="google-forget" method="POST" action="{{ route('offres.google.forget') }}" class="hidden">@csrf</form>
                @endif

                <aside class="lg:col-span-2 lg:sticky lg:top-28 rounded-[28px] border border-black/15 bg-white/80 p-6 sm:p-7 backdrop-blur-2xl shadow-[0_30px_80px_-30px_rgba(11,15,20,0.35)] night:border-white/25 night:bg-white/[0.09] night:shadow-[0_0_80px_-20px_rgba(255,255,255,0.25)]">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[13px] font-medium text-black/55 night:text-white/60">Votre offre</p>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#0B0F14] text-[10px] font-bold uppercase tracking-wider text-white night:bg-white night:text-black">{{ $plan->name }}</span>
                    </div>

                    <p class="mt-5 flex items-baseline gap-1 whitespace-nowrap">
                        <span class="text-[42px] font-semibold tracking-tight" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }}' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }}'">{{ number_format($plan->price_monthly, 0, ',', ' ') }}</span>
                        <span class="text-lg font-light text-black/45 night:text-white/50" x-text="cycle === 'monthly' ? 'F/mois' : 'F/an'">F/mois</span>
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-1 p-1 rounded-full bg-black/[0.05] night:bg-white/[0.06]">
                        <button type="button" @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-[#0B0F14] text-white night:bg-white night:text-black' : 'text-black/50 night:text-white/50'" class="h-9 rounded-full text-xs font-semibold transition">Mensuel</button>
                        <button type="button" @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-[#0B0F14] text-white night:bg-white night:text-black' : 'text-black/50 night:text-white/50'" class="h-9 rounded-full text-xs font-semibold transition">Annuel</button>
                    </div>

                    <div class="my-6 h-px bg-gradient-to-r from-transparent via-black/10 to-transparent night:via-white/15"></div>

                    <ul class="space-y-3">
                        @foreach($plan->moduleDetails() as $module)
                            <li class="flex items-center gap-3 text-[13px]">
                                <span class="w-5 h-5 shrink-0 rounded-full bg-[#0B0F14] text-white night:bg-white night:text-black flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                {{ $module['label'] }}
                            </li>
                        @endforeach
                        <li class="flex items-center gap-3 text-[13px] text-black/55 night:text-white/55">
                            <span class="w-5 h-5 shrink-0 rounded-full bg-black/[0.06] night:bg-white/10 flex items-center justify-center">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </span>
                            {{ $plan->max_users ? 'Jusqu\'à '.$plan->max_users.' utilisateurs' : 'Utilisateurs illimités' }}
                        </li>
                    </ul>

                    <div class="mt-6 rounded-2xl bg-black/[0.04] p-4 text-[11px] leading-relaxed text-black/50 night:bg-white/[0.04] night:text-white/50">
                        Paiement sécurisé par Paystack : carte bancaire ou Mobile Money. Vous recevez le récapitulatif PDF et l'accès administrateur dès la confirmation.
                    </div>
                </aside>
            </div>
        </section>
    </div>
</x-layouts.landing>
