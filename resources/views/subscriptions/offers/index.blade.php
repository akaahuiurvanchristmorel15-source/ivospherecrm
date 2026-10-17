<x-layouts.landing>
    <x-slot:title>Offres d'abonnement — IVOSPHERE</x-slot>

    <x-slot:navbar>
        @include('subscriptions.offers._nav')
    </x-slot:navbar>

    <x-slot:footer>
        @include('subscriptions.offers._footer')
    </x-slot:footer>

    @php
        $plans = $commercialPlans->concat($enterprisePlans)->values();
        $featuredPlan = $plans->first(fn ($plan) => $plan->canBeSubscribed());
        $catalogue = config('subscriptions.modules');
        $bestSaving = $plans->filter(fn ($plan) => $plan->price_monthly > 0)
            ->map(fn ($plan) => (int) round(100 - ($plan->price_yearly / ($plan->price_monthly * 12)) * 100))
            ->max() ?? 0;
        $faqs = [
            ['Comment mon espace est-il créé ?', "Dès que votre paiement est confirmé, votre espace est activé automatiquement. Vous recevez par e-mail un récapitulatif PDF de votre abonnement et vous vous connectez avec l'e-mail et le mot de passe choisis à l'inscription."],
            ['Quels moyens de paiement acceptez-vous ?', 'Carte bancaire (Visa, Mastercard) et Mobile Money, via la plateforme de paiement sécurisée Paystack.'],
            ['Mes données sont-elles séparées des autres clients ?', 'Oui. Chaque abonné dispose de son propre espace : articles, clients, factures, stock et utilisateurs ne sont visibles que par votre équipe.'],
            ['Que se passe-t-il à la fin de mon abonnement ?', "Vos données sont conservées. L'accès aux modules est suspendu jusqu'au renouvellement, que vous effectuez en ligne depuis « Mon abonnement »."],
            ["Puis-je changer d'offre ?", "Oui, depuis « Mon abonnement » vous pouvez renouveler ou passer à une offre supérieure. Un renouvellement anticipé s'ajoute à la période en cours."],
            ['Puis-je ajouter des collaborateurs ?', "Oui : l'administrateur crée les comptes de son équipe dans la limite d'utilisateurs de l'offre choisie."],
        ];
    @endphp

    <div x-data="{ cycle: 'monthly' }" class="relative overflow-hidden bg-[#F5F7FA] text-[#0B0F14] night:bg-black night:text-white transition-colors">
        {{-- Halo discret --}}
        <div class="pointer-events-none absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] rounded-full bg-[#0066FF]/[0.07] blur-3xl night:bg-white/[0.06]"></div>

        {{-- ─────────── Titre géant + cartes en verre ─────────── --}}
        <section id="offres" class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-32">
            <h1 data-anim="title" class="select-none text-center font-extrabold tracking-tighter leading-none text-[#0B0F14] night:text-white text-[26vw] sm:text-[22vw] lg:text-[230px]" aria-label="Offres d'abonnement">
                Offres
            </h1>

            @if(session('warning'))
                <div class="relative z-10 mx-auto mt-4 max-w-xl rounded-2xl border border-amber-400/40 bg-amber-50 p-3 text-center text-xs text-amber-800 night:border-amber-400/30 night:bg-amber-400/10 night:text-amber-200">{{ session('warning') }}</div>
            @endif

            <div class="relative z-10 -mt-[11vw] sm:-mt-[9vw] lg:-mt-[96px]"
                 x-data="{ slide: 0, onScroll(el) { const card = el.firstElementChild; if (card) this.slide = Math.round(el.scrollLeft / (card.offsetWidth + 16)); } }">
            <div @scroll.passive="onScroll($el)" data-anim-group
                 class="offer-track -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-6 pt-2 md:mx-0 md:grid md:grid-cols-2 md:overflow-visible md:px-0 md:pb-0 md:pt-0 xl:grid-cols-4 lg:gap-5 items-start md:items-stretch">
                @php
                    $previousEnterprisePlan = null;
                @endphp
                @foreach($plans as $plan)
                    @php
                        $isEnterprise = $plan->family === 'entreprise';
                        $previousModules = $isEnterprise ? ($previousEnterprisePlan?->modules ?? []) : [];
                        $modules = collect($plan->moduleDetails())->reject(fn ($module) => in_array($module['key'], $previousModules, true))->values();
                    @endphp
                    <div data-anim="scale" style="--d:{{ $loop->index * 110 }}ms" class="flex w-[84vw] max-w-sm shrink-0 snap-center md:w-auto md:max-w-none [&>article]:w-full">
                    @include('subscriptions.offers._plan-card', [
                        'plan' => $plan,
                        'featured' => $featuredPlan && $plan->is($featuredPlan),
                        'includedFrom' => $isEnterprise ? $previousEnterprisePlan?->name : null,
                        'modules' => $modules,
                    ])
                    </div>
                    @php
                        if ($isEnterprise) {
                            $previousEnterprisePlan = $plan;
                        }
                    @endphp
                @endforeach
            </div>

            {{-- Points du carrousel (mobile) --}}
            <div class="flex items-center justify-center gap-1.5 md:hidden" aria-hidden="true">
                @foreach($plans as $plan)
                    <span class="h-1.5 rounded-full transition-all duration-300" :class="slide === {{ $loop->index }} ? 'w-6 bg-[#0B0F14] night:bg-white' : 'w-1.5 bg-black/20 night:bg-white/25'"></span>
                @endforeach
            </div>
            </div>

            {{-- Interrupteur annuel --}}
            <div data-anim="up" class="relative z-10 mt-6 md:mt-8 flex flex-col sm:flex-row items-center sm:items-center justify-between gap-3 sm:gap-4 text-center sm:text-left">
                <button type="button" @click="cycle = cycle === 'monthly' ? 'yearly' : 'monthly'" class="flex items-center gap-3 text-[13px] text-black/60 night:text-white/70" role="switch" :aria-checked="cycle === 'yearly'">
                    <span class="relative w-11 h-6 rounded-full transition" :class="cycle === 'yearly' ? 'bg-[#0B0F14] night:bg-white' : 'bg-black/15 night:bg-white/15'">
                        <span class="absolute top-1 left-1 w-4 h-4 rounded-full transition-transform" :class="cycle === 'yearly' ? 'translate-x-5 bg-white night:bg-black' : 'bg-white'"></span>
                    </span>
                    Facturation annuelle
                    @if($bestSaving > 0)
                        <span class="px-2 py-0.5 rounded-full bg-black/[0.06] text-[11px] font-semibold text-[#0B0F14] night:bg-white/10 night:text-white">-{{ $bestSaving }} %</span>
                    @endif
                </button>
                <p class="text-xs text-black/40 night:text-white/40">Prix en francs CFA · Paiement sécurisé Paystack : carte et Mobile Money</p>
            </div>
        </section>

        {{-- ─────────── Étapes ─────────── --}}
        <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-36">
            <div data-anim="up" class="flex justify-center">
                <span class="px-3 py-1 rounded-full border border-black/10 bg-white/60 text-[11px] font-medium text-black/60 night:border-white/15 night:bg-white/5 night:text-white/70">Comment ça marche</span>
            </div>
            <h2 data-anim="up" style="--d:80ms" class="mt-5 text-center text-3xl sm:text-5xl font-semibold tracking-tight">Votre espace en 4 étapes</h2>
            <ol class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['Choisissez votre offre', 'Mensuelle ou annuelle, selon vos besoins.'],
                    ['Créez votre compte', "Votre entreprise et l'administrateur de l'espace."],
                    ['Payez en ligne', 'Mobile Money ou carte, en toute sécurité.'],
                    ['Recevez votre PDF', "Récapitulatif d'abonnement et accès immédiat."],
                ] as [$stepTitle, $stepText])
                    <li data-anim="{{ $loop->odd ? 'right' : 'left' }}" style="--d:{{ $loop->index * 90 }}ms" class="rounded-3xl border border-black/[0.08] bg-white/60 p-6 night:border-white/10 night:bg-white/[0.03]">
                        <span class="text-5xl font-semibold tracking-tighter text-black/10 night:text-white/15">0{{ $loop->iteration }}</span>
                        <h3 class="mt-6 text-sm font-semibold">{{ $stepTitle }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-black/50 night:text-white/50">{{ $stepText }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ─────────── Comparatif ─────────── --}}
        <section id="comparatif" class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 sm:pt-36 scroll-mt-24">
            <div data-anim="up" class="flex justify-center">
                <span class="px-3 py-1 rounded-full border border-black/10 bg-white/60 text-[11px] font-medium text-black/60 night:border-white/15 night:bg-white/5 night:text-white/70">Comparatif</span>
            </div>
            <h2 data-anim="up" style="--d:80ms" class="mt-5 text-center text-3xl sm:text-5xl font-semibold tracking-tight">Ce que comprend chaque offre</h2>

            {{-- Mobile : une offre à la fois --}}
            <div data-anim="up" class="mt-10 md:hidden" x-data="{ tab: {{ max(0, $plans->search(fn ($plan) => $featuredPlan && $plan->is($featuredPlan))) }} }">
                <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none]" role="tablist">
                    @foreach($plans as $plan)
                        <button type="button" role="tab" @click="tab = {{ $loop->index }}" :aria-selected="tab === {{ $loop->index }}"
                                :class="tab === {{ $loop->index }} ? 'bg-[#0B0F14] text-white border-transparent night:bg-white night:text-black' : 'border-black/10 text-black/60 night:border-white/15 night:text-white/60'"
                                class="shrink-0 rounded-full border px-4 py-2 text-xs font-semibold transition">{{ $plan->name }}</button>
                    @endforeach
                </div>
                @foreach($plans as $plan)
                    <div x-show="tab === {{ $loop->index }}" @if(! $loop->first) x-cloak @endif x-transition.opacity.duration.200ms
                         class="mt-4 rounded-3xl border border-black/[0.08] bg-white/60 p-5 backdrop-blur-xl night:border-white/10 night:bg-white/[0.03]">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="font-semibold">{{ $plan->name }}</p>
                            <p class="text-xs text-black/45 night:text-white/45" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }} F/an'">{{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois</p>
                        </div>
                        <ul class="mt-4 divide-y divide-black/[0.06] night:divide-white/[0.06]">
                            @foreach($catalogue as $moduleKey => $module)
                                @php $included = in_array($moduleKey, $plan->modules ?? [], true); @endphp
                                <li class="flex items-center justify-between gap-3 py-3 text-[13px] {{ $included ? '' : 'text-black/35 night:text-white/30' }}">
                                    <span>{{ $module['label'] }}</span>
                                    @if($included)
                                        <span class="w-5 h-5 shrink-0 rounded-full bg-[#0B0F14] text-white night:bg-white night:text-black flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        </span>
                                    @else
                                        <span class="block w-3 h-px bg-black/20 night:bg-white/20"></span>
                                    @endif
                                </li>
                            @endforeach
                            <li class="flex items-center justify-between gap-3 py-3 text-[13px]">
                                <span>Utilisateurs inclus</span>
                                <span class="font-semibold">{{ $plan->max_users ?? '∞' }}</span>
                            </li>
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Ordinateur / tablette : tableau complet --}}
            <div data-anim="up" style="--d:120ms" class="mt-14 hidden md:block overflow-x-auto rounded-3xl border border-black/[0.08] bg-white/60 backdrop-blur-xl night:border-white/10 night:bg-white/[0.03]">
                <table class="w-full min-w-[680px] text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.08] night:border-white/10">
                            <th class="p-5 text-left text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Système</th>
                            @foreach($plans as $plan)
                                <th class="p-5 text-center">
                                    <span class="block font-semibold text-[#0B0F14] night:text-white">{{ $plan->name }}</span>
                                    <span class="block mt-0.5 text-[11px] font-normal text-black/40 night:text-white/40" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }} F/an'">{{ number_format($plan->price_monthly, 0, ',', ' ') }} F/mois</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.06] night:divide-white/[0.06]">
                        @foreach($catalogue as $moduleKey => $module)
                            <tr>
                                <td class="p-5 text-black/75 night:text-white/80">{{ $module['label'] }}</td>
                                @foreach($plans as $plan)
                                    <td class="p-5 text-center">
                                        @if(in_array($moduleKey, $plan->modules ?? [], true))
                                            <span class="mx-auto w-5 h-5 rounded-full bg-[#0B0F14] text-white night:bg-white night:text-black flex items-center justify-center">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                        @else
                                            <span class="block w-3 h-px mx-auto bg-black/20 night:bg-white/20"></span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr>
                            <td class="p-5 text-black/75 night:text-white/80">Utilisateurs inclus</td>
                            @foreach($plans as $plan)
                                <td class="p-5 text-center font-semibold">{{ $plan->max_users ?? '∞' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ─────────── FAQ ─────────── --}}
        <section id="faq" class="relative max-w-3xl mx-auto px-4 sm:px-6 pt-28 sm:pt-36 scroll-mt-24" x-data="{ open: 0 }">
            <div data-anim="up" class="flex justify-center">
                <span class="px-3 py-1 rounded-full border border-black/10 bg-white/60 text-[11px] font-medium text-black/60 night:border-white/15 night:bg-white/5 night:text-white/70">FAQ</span>
            </div>
            <h2 data-anim="up" style="--d:80ms" class="mt-5 text-center text-3xl sm:text-5xl font-semibold tracking-tight">Questions fréquentes</h2>
            <div class="mt-12 space-y-3">
                @foreach($faqs as [$question, $answer])
                    <div data-anim="up" style="--d:{{ $loop->index * 70 }}ms" class="rounded-2xl border border-black/[0.08] bg-white/60 night:border-white/10 night:bg-white/[0.03]">
                        <button type="button" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" class="w-full p-5 flex items-center justify-between gap-4 text-left">
                            <span class="text-sm font-medium">{{ $question }}</span>
                            <svg class="w-4 h-4 shrink-0 text-black/45 night:text-white/50 transition" :class="open === {{ $loop->index }} ? 'rotate-45 text-[#0B0F14] night:text-white' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </button>
                        <div x-show="open === {{ $loop->index }}" x-transition.opacity x-cloak class="px-5 pb-5 -mt-1 text-[13px] leading-relaxed text-black/55 night:text-white/55">{{ $answer }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ─────────── Appel final ─────────── --}}
        <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-28 sm:py-36">
            <div data-anim="scale" class="relative overflow-hidden rounded-[32px] border border-black/[0.08] bg-gradient-to-b from-white to-white/40 night:border-white/10 night:from-white/[0.08] night:to-white/[0.02] px-6 py-14 sm:py-20 text-center">
                <h2 class="text-[1.75rem] leading-tight sm:text-5xl font-semibold tracking-tight">Prêt à lancer votre espace ?</h2>
                <p class="mt-4 text-sm text-black/50 night:text-white/50">Activation immédiate · Données isolées · Récapitulatif PDF par e-mail</p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
                    @if($featuredPlan)
                        <a :href="'{{ route('offres.signup', $featuredPlan) }}?cycle=' + cycle" class="w-full sm:w-auto h-12 px-8 rounded-full bg-[#0B0F14] text-sm font-semibold text-white hover:bg-black night:bg-white night:text-black night:hover:bg-white/90 flex items-center justify-center transition">
                            S'abonner à {{ $featuredPlan->name }}
                        </a>
                    @endif
                    <button type="button" @click="$dispatch('open-demo-modal')" class="w-full sm:w-auto h-12 px-8 rounded-full border border-black/15 text-sm font-semibold hover:border-black/40 night:border-white/15 night:hover:border-white/40 transition">Parler à un conseiller</button>
                </div>
            </div>
        </section>
    </div>

    <style>
        .offer-track{scrollbar-width:none}
        .offer-track::-webkit-scrollbar{display:none}
        /* Apparition au défilement (pages d'offres) */
        .js-anim [data-anim]{opacity:0;transition:opacity .8s cubic-bezier(.22,1,.36,1) var(--d,0ms),transform .8s cubic-bezier(.22,1,.36,1) var(--d,0ms)}
        .js-anim [data-anim="up"]{transform:translateY(32px)}
        .js-anim [data-anim="left"]{transform:translateX(-48px)}
        .js-anim [data-anim="right"]{transform:translateX(48px)}
        .js-anim [data-anim="scale"]{transform:scale(.92)}
        .js-anim [data-anim="title"]{transform:translateY(24px) scale(.96);filter:blur(10px);transition-property:opacity,transform,filter;transition-duration:1.1s}
        .js-anim [data-anim].is-shown{opacity:1;transform:none;filter:none}
        @media (prefers-reduced-motion: reduce){
            .js-anim [data-anim]{opacity:1 !important;transform:none !important;filter:none !important;transition:none !important}
        }
    </style>
    <script>
        (function () {
            var items = document.querySelectorAll('[data-anim]');
            var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (! ('IntersectionObserver' in window) || reduced) { return; }
            document.documentElement.classList.add('js-anim');
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (! entry.isIntersecting) { return; }
                    /* Dans un carrousel, toutes les cartes apparaissent ensemble (même celles hors écran sur le côté) */
                    var group = entry.target.closest('[data-anim-group]');
                    var targets = group ? group.querySelectorAll('[data-anim]') : [entry.target];
                    targets.forEach(function (el) {
                        el.classList.add('is-shown');
                        observer.unobserve(el);
                        setTimeout(function () { el.style.setProperty('--d', '0ms'); }, 1600);
                    });
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
            items.forEach(function (el) { observer.observe(el); });
        })();
    </script>
</x-layouts.landing>
