<x-layouts.landing>
    <x-slot:title>Inscription — {{ $plan->name }} — IVOSPHERE</x-slot>

    <section class="pt-28 sm:pt-32 pb-16" x-data="{ cycle: '{{ old('billing_cycle', $billingCycle) }}' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
            <form method="POST" action="{{ route('offres.register', $plan) }}" class="lg:col-span-3 bg-white border border-[#E2E8F0] rounded-2xl p-5 sm:p-7 space-y-6">
                @csrf
                <input type="hidden" name="billing_cycle" :value="cycle">

                <div>
                    <a href="{{ route('offres.index') }}" class="text-[11px] font-semibold text-[#64748B] hover:text-[#0066FF]">← Toutes les offres</a>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-[#0B0F14]">Créer votre espace</h1>
                    <p class="mt-1 text-xs text-[#64748B]">Après le paiement, votre espace est activé immédiatement et vous recevez le récapitulatif PDF par e-mail.</p>
                </div>

                <fieldset class="space-y-3">
                    <legend class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Votre entreprise</legend>
                    @include('subscriptions.offers._field', ['name' => 'company_name', 'label' => "Nom de l'entreprise / boutique", 'required' => true, 'placeholder' => 'Ex : Boutique Grâce'])
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @include('subscriptions.offers._field', ['name' => 'email', 'label' => 'E-mail de l\'entreprise', 'type' => 'email', 'required' => true, 'placeholder' => 'contact@entreprise.ci'])
                        @include('subscriptions.offers._field', ['name' => 'phone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true, 'placeholder' => '07 00 00 00 00'])
                        @include('subscriptions.offers._field', ['name' => 'city', 'label' => 'Ville', 'placeholder' => 'Abidjan'])
                        @include('subscriptions.offers._field', ['name' => 'address', 'label' => 'Adresse', 'placeholder' => 'Cocody, Angré'])
                    </div>
                </fieldset>

                <fieldset class="space-y-3">
                    <legend class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Compte administrateur de l'espace</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @include('subscriptions.offers._field', ['name' => 'admin_name', 'label' => 'Nom complet', 'required' => true, 'autocomplete' => 'name'])
                        @include('subscriptions.offers._field', ['name' => 'admin_email', 'label' => 'E-mail de connexion', 'type' => 'email', 'required' => true, 'autocomplete' => 'username'])
                        @include('subscriptions.offers._field', ['name' => 'password', 'label' => 'Mot de passe', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                        @include('subscriptions.offers._field', ['name' => 'password_confirmation', 'label' => 'Confirmer le mot de passe', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                    </div>
                    <p class="text-[11px] text-[#64748B]">Ce compte gère votre espace : il pourra créer les accès de votre équipe.</p>
                </fieldset>

                <label class="flex items-start gap-2.5 text-xs text-[#0B0F14]">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-0.5 w-4 h-4 rounded border-[#CBD5E1] text-[#0066FF] focus:ring-[#0066FF]">
                    <span>J'accepte les conditions d'abonnement IVOSPHERE et le renouvellement manuel à chaque échéance.</span>
                </label>
                @error('terms')
                    <p class="-mt-4 text-[11px] text-rose-600">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full h-12 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-white text-sm font-semibold transition flex items-center justify-center gap-2">
                    Continuer vers le paiement
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            <aside class="lg:col-span-2 space-y-4">
                <div class="bg-[#0B0F14] text-white rounded-2xl p-5 sm:p-6 lg:sticky lg:top-28">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-white/60">Votre offre</p>
                    <h2 class="mt-1 text-xl font-bold">{{ $plan->name }}</h2>

                    <div class="mt-4 grid grid-cols-2 gap-1 p-1 rounded-xl bg-white/10">
                        <button type="button" @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-white text-[#0B0F14]' : 'text-white/70'" class="h-9 rounded-lg text-xs font-semibold transition">Mensuel</button>
                        <button type="button" @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-white text-[#0B0F14]' : 'text-white/70'" class="h-9 rounded-lg text-xs font-semibold transition">Annuel</button>
                    </div>

                    <div class="mt-5 flex items-baseline justify-between border-b border-white/10 pb-4">
                        <span class="text-xs text-white/60">Total à payer</span>
                        <span class="text-2xl font-extrabold">
                            <span x-show="cycle === 'monthly'">{{ number_format($plan->price_monthly, 0, ',', ' ') }}</span>
                            <span x-show="cycle === 'yearly'" x-cloak>{{ number_format($plan->price_yearly, 0, ',', ' ') }}</span>
                            <span class="text-xs font-semibold text-white/60">FCFA</span>
                        </span>
                    </div>

                    <ul class="mt-4 space-y-2">
                        @foreach($plan->moduleDetails() as $module)
                            <li class="flex items-center gap-2 text-xs">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $module['label'] }}
                            </li>
                        @endforeach
                        <li class="flex items-center gap-2 text-xs text-white/70">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ $plan->max_users ? 'Jusqu\'à '.$plan->max_users.' utilisateurs' : 'Utilisateurs illimités' }}
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.landing>
