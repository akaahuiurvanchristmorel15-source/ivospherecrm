<x-layouts.landing>
    <x-slot:title>Offres d'abonnement — IVOSPHERE</x-slot>

    @php
        $caisse = $commercialPlans->first();
        $allPlans = $commercialPlans->concat($enterprisePlans);
        $catalogue = config('subscriptions.modules');
        $bestSaving = $allPlans->filter(fn ($plan) => $plan->price_monthly > 0)
            ->map(fn ($plan) => (int) round(100 - ($plan->price_yearly / ($plan->price_monthly * 12)) * 100))
            ->max() ?? 0;
        $faqs = [
            ['Comment mon espace est-il créé ?', "Dès que votre paiement est confirmé, votre espace est activé automatiquement. Vous recevez par e-mail un récapitulatif PDF de votre abonnement et vous vous connectez avec l'e-mail et le mot de passe choisis à l'inscription."],
            ['Quels moyens de paiement acceptez-vous ?', 'Wave, Orange Money, MTN MoMo, Moov Money et carte bancaire (Visa, Mastercard), via la plateforme sécurisée CinetPay.'],
            ['Mes données sont-elles séparées des autres clients ?', 'Oui. Chaque abonné dispose de son propre espace : articles, clients, factures, stock et utilisateurs ne sont visibles que par votre équipe.'],
            ["Que se passe-t-il à la fin de mon abonnement ?", "Vos données sont conservées. L'accès aux modules est suspendu jusqu'au renouvellement, que vous effectuez en ligne depuis « Mon abonnement »."],
            ["Puis-je changer d'offre ?", "Oui, depuis « Mon abonnement » vous pouvez renouveler ou passer à une offre supérieure à tout moment. Un renouvellement anticipé s'ajoute à la période en cours."],
            ['Puis-je ajouter des collaborateurs ?', "Oui : l'administrateur crée les comptes de son équipe dans la limite d'utilisateurs de l'offre choisie."],
        ];
    @endphp

    <div x-data="{ cycle: 'monthly' }">

        {{-- ─────────── En-tête ─────────── --}}
        <section class="relative overflow-hidden bg-[#0B0F14] text-white">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[900px] h-[500px] rounded-full bg-[#0066FF]/30 blur-3xl"></div>
                <div class="absolute -bottom-32 -right-24 w-[420px] h-[420px] rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 28px 28px;"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 sm:pt-20 pb-36 sm:pb-44 text-center">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-semibold uppercase tracking-wider text-white/90">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Abonnements IVOSPHERE
                </span>
                <h1 class="mt-5 max-w-3xl mx-auto text-3xl sm:text-5xl font-extrabold tracking-tight leading-[1.1]">
                    Une offre pour chaque étape de <span class="bg-gradient-to-r from-[#4D94FF] to-[#A5B4FC] bg-clip-text text-transparent">votre croissance</span>
                </h1>
                <p class="mt-5 max-w-2xl mx-auto text-sm sm:text-base text-white/70 leading-relaxed">
                    Créez votre espace en quelques minutes, payez par Mobile Money ou carte, et recevez immédiatement votre récapitulatif PDF et votre accès administrateur.
                </p>

                <div class="mt-8 inline-flex items-center p-1 rounded-2xl bg-white/10 border border-white/15 backdrop-blur">
                    <button type="button" @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-white text-[#0B0F14] shadow' : 'text-white/70 hover:text-white'" class="h-10 px-5 rounded-xl text-xs sm:text-sm font-semibold transition">Mensuel</button>
                    <button type="button" @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-white text-[#0B0F14] shadow' : 'text-white/70 hover:text-white'" class="h-10 px-5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2">
                        Annuel
                        @if($bestSaving > 0)
                            <span class="px-1.5 py-0.5 rounded-md bg-emerald-500 text-[10px] font-bold text-white">-{{ $bestSaving }} %</span>
                        @endif
                    </button>
                </div>

                <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-white/70">
                    @foreach(['Activation immédiate', 'Paiement Mobile Money & carte', 'Données isolées et sécurisées', 'Renouvellement libre'] as $argument)
                        <li class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            {{ $argument }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- ─────────── Commercial Caisse ─────────── --}}
        @if($caisse)
            @php $caisseSaving = $caisse->price_monthly > 0 ? (int) round(100 - ($caisse->price_yearly / ($caisse->price_monthly * 12)) * 100) : 0; @endphp
            <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-28 sm:-mt-32">
                @if(session('warning'))
                    <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">{{ session('warning') }}</div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-5 rounded-3xl overflow-hidden bg-white border border-[#E2E8F0] shadow-2xl shadow-slate-900/10">
                    <div class="lg:col-span-2 p-6 sm:p-8 lg:p-10 bg-gradient-to-br from-[#0066FF] to-[#3B5BDB] text-white flex flex-col">
                        <span class="self-start px-2.5 py-1 rounded-full bg-white/15 text-[10px] font-bold uppercase tracking-wider">Commerçants & boutiques</span>
                        <h2 class="mt-4 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $caisse->name }}</h2>
                        <p class="mt-2 text-sm text-white/80">{{ $caisse->tagline }}</p>

                        <p class="mt-8 flex items-baseline gap-1.5">
                            <span class="text-5xl font-extrabold tracking-tight" x-text="cycle === 'monthly' ? '{{ number_format($caisse->price_monthly, 0, ',', ' ') }}' : '{{ number_format($caisse->price_yearly, 0, ',', ' ') }}'">{{ number_format($caisse->price_monthly, 0, ',', ' ') }}</span>
                            <span class="text-sm font-semibold text-white/70" x-text="cycle === 'monthly' ? 'FCFA / mois' : 'FCFA / an'">FCFA / mois</span>
                        </p>
                        <p class="mt-1.5 h-4 text-xs text-white/80">
                            @if($caisseSaving > 0)
                                <span x-show="cycle === 'yearly'" x-cloak>Soit {{ number_format(intdiv($caisse->price_yearly, 12), 0, ',', ' ') }} F/mois · {{ $caisseSaving }} % d'économie</span>
                            @endif
                        </p>

                        <p class="mt-6 text-xs text-white/80">{{ $caisse->max_users ? 'Jusqu\'à '.$caisse->max_users.' utilisateurs inclus' : 'Utilisateurs illimités' }}</p>

                        <div class="mt-auto pt-8">
                            @if($caisse->canBeSubscribed())
                                <a :href="'{{ route('offres.signup', $caisse) }}?cycle=' + cycle" class="h-12 rounded-xl bg-white hover:bg-slate-100 text-[#0B0F14] text-sm font-bold flex items-center justify-center gap-2 shadow-lg transition">
                                    Démarrer avec {{ $caisse->name }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                                <p class="mt-3 text-center text-[11px] text-white/70">Espace prêt en moins de 5 minutes</p>
                            @else
                                <p class="h-12 rounded-xl bg-white/15 text-sm font-semibold flex items-center justify-center">Bientôt disponible</p>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-3 p-6 sm:p-8 lg:p-10">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Tout ce qu'il faut pour vendre et gérer votre boutique</h3>
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($caisse->moduleDetails() as $module)
                                <div class="group p-4 rounded-2xl border border-[#E2E8F0] hover:border-[#0066FF]/40 hover:bg-blue-50/40 transition">
                                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-[#0066FF] flex items-center justify-center group-hover:bg-[#0066FF] group-hover:text-white transition">
                                        @include('subscriptions.offers._module-icon', ['key' => $module['key']])
                                    </span>
                                    <h4 class="mt-3 text-sm font-bold text-[#0B0F14]">{{ $module['label'] }}</h4>
                                    <p class="mt-1 text-xs leading-relaxed text-[#64748B]">{{ $module['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ─────────── Entreprise ─────────── --}}
        @if($enterprisePlans->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
                <div class="max-w-2xl mx-auto text-center">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#0066FF]">Entreprises</p>
                    <h2 class="mt-2 text-2xl sm:text-4xl font-extrabold tracking-tight text-[#0B0F14]">Pilotez, collaborez, sécurisez</h2>
                    <p class="mt-3 text-sm sm:text-base text-[#64748B]">Trois niveaux progressifs : chaque offre reprend la précédente et ajoute de nouveaux systèmes.</p>
                </div>

                <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-5 items-stretch">
                    @php $previousPlan = null; @endphp
                    @foreach($enterprisePlans as $plan)
                        @php
                            $previousModules = $previousPlan?->modules ?? [];
                            $newModules = collect($plan->moduleDetails())->reject(fn ($module) => in_array($module['key'], $previousModules, true))->values();
                        @endphp
                        @include('subscriptions.offers._plan-card', [
                            'plan' => $plan,
                            'featured' => $loop->last,
                            'includedFrom' => $previousPlan?->name,
                            'newModules' => $newModules,
                        ])
                        @php $previousPlan = $plan; @endphp
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ─────────── Comment ça marche ─────────── --}}
        <section class="bg-white border-y border-[#E2E8F0]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
                <div class="text-center">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#0066FF]">Comment ça marche</p>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0B0F14]">Votre espace en 4 étapes</h2>
                </div>
                <ol class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach([
                        ['Choisissez votre offre', 'Mensuelle ou annuelle, selon vos besoins.'],
                        ['Créez votre compte', "Votre entreprise et l'administrateur de l'espace."],
                        ['Payez en ligne', 'Mobile Money ou carte, en toute sécurité.'],
                        ['Recevez votre PDF', "Récapitulatif d'abonnement et accès immédiat."],
                    ] as [$stepTitle, $stepText])
                        <li class="relative p-6 rounded-2xl bg-[#F5F7FA]">
                            <span class="w-10 h-10 rounded-xl bg-[#0B0F14] text-white text-sm font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                            <h3 class="mt-4 text-sm font-bold text-[#0B0F14]">{{ $stepTitle }}</h3>
                            <p class="mt-1 text-xs leading-relaxed text-[#64748B]">{{ $stepText }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ─────────── Comparatif ─────────── --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-[#0066FF]">Comparatif</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0B0F14]">Ce que comprend chaque offre</h2>
            </div>

            <div class="mt-10 overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-white">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-[#E2E8F0]">
                            <th class="p-4 text-left text-xs font-semibold uppercase tracking-wider text-[#64748B]">Système</th>
                            @foreach($allPlans as $plan)
                                <th class="p-4 text-center {{ $loop->last ? 'bg-blue-50/60' : '' }}">
                                    <span class="block text-sm font-bold text-[#0B0F14]">{{ $plan->name }}</span>
                                    <span class="block mt-0.5 text-[11px] font-medium text-[#64748B]" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }} F/an'">{{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach($catalogue as $moduleKey => $module)
                            <tr>
                                <td class="p-4">
                                    <span class="flex items-center gap-2.5 font-medium text-[#0B0F14]">
                                        <span class="text-[#0066FF]">@include('subscriptions.offers._module-icon', ['key' => $moduleKey, 'class' => 'w-4 h-4'])</span>
                                        {{ $module['label'] }}
                                    </span>
                                </td>
                                @foreach($allPlans as $plan)
                                    <td class="p-4 text-center {{ $loop->last ? 'bg-blue-50/60' : '' }}">
                                        @if(in_array($moduleKey, $plan->modules ?? [], true))
                                            <svg class="w-5 h-5 mx-auto text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        @else
                                            <span class="block w-3 h-0.5 mx-auto rounded bg-slate-300"></span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr>
                            <td class="p-4 font-medium text-[#0B0F14]">Utilisateurs inclus</td>
                            @foreach($allPlans as $plan)
                                <td class="p-4 text-center font-semibold text-[#0B0F14] {{ $loop->last ? 'bg-blue-50/60' : '' }}">{{ $plan->max_users ?? '∞' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ─────────── FAQ ─────────── --}}
        <section class="max-w-3xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24" x-data="{ open: 0 }">
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-[#0066FF]">Questions fréquentes</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0B0F14]">Tout savoir sur l'abonnement</h2>
            </div>
            <div class="mt-10 space-y-3">
                @foreach($faqs as [$question, $answer])
                    <div class="rounded-2xl bg-white border border-[#E2E8F0] overflow-hidden">
                        <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" class="w-full p-5 flex items-center justify-between gap-4 text-left">
                            <span class="text-sm font-semibold text-[#0B0F14]">{{ $question }}</span>
                            <svg class="w-4 h-4 shrink-0 text-[#64748B] transition" :class="open === {{ $loop->index }} ? 'rotate-45 text-[#0066FF]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </button>
                        <div x-show="open === {{ $loop->index }}" x-transition.opacity x-cloak class="px-5 pb-5 -mt-1 text-sm leading-relaxed text-[#64748B]">{{ $answer }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ─────────── Appel final ─────────── --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <div class="relative overflow-hidden rounded-3xl bg-[#0B0F14] px-6 py-12 sm:px-12 sm:py-16 text-center text-white">
                <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-[600px] h-[300px] rounded-full bg-[#0066FF]/40 blur-3xl"></div>
                <div class="relative">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Prêt à lancer votre espace ?</h2>
                    <p class="mt-3 text-sm text-white/70">Paiement sécurisé : Wave · Orange Money · MTN MoMo · Moov Money · Visa · Mastercard</p>
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        @if($caisse?->canBeSubscribed())
                            <a :href="'{{ route('offres.signup', $caisse) }}?cycle=' + cycle" class="w-full sm:w-auto h-12 px-7 rounded-xl bg-[#0066FF] hover:bg-[#0052CC] text-sm font-semibold flex items-center justify-center gap-2 transition">
                                S'abonner à {{ $caisse->name }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        @endif
                        <button type="button" @click="$dispatch('open-demo-modal')" class="w-full sm:w-auto h-12 px-7 rounded-xl border border-white/20 hover:bg-white/10 text-sm font-semibold transition">Parler à un conseiller</button>
                    </div>
                    <p class="mt-6 text-[11px] text-white/50">Déjà abonné ? <a href="{{ route('login') }}" class="underline hover:text-white">Se connecter</a></p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.landing>
