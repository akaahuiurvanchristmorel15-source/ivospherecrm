<x-layouts.landing>
    <x-slot:title>Offres d'abonnement — IVOSPHERE</x-slot>

    <section class="pt-28 sm:pt-32 pb-16 sm:pb-24" x-data="{ cycle: 'monthly' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="max-w-2xl mx-auto text-center">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-100 text-[11px] font-semibold text-[#0066FF]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0066FF]"></span>
                    Abonnements IVOSPHERE
                </span>
                <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold tracking-tight text-[#0B0F14]">Votre espace de gestion, prêt en quelques minutes</h1>
                <p class="mt-3 text-sm sm:text-base text-[#64748B]">Choisissez votre offre, payez en ligne (Mobile Money ou carte) et recevez immédiatement le récapitulatif PDF de votre abonnement et l'accès administrateur de votre espace.</p>

                <div class="mt-6 inline-flex p-1 rounded-xl bg-white border border-[#E2E8F0]">
                    <button type="button" @click="cycle = 'monthly'" :class="cycle === 'monthly' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B]'" class="h-9 px-4 rounded-lg text-xs font-semibold transition">Mensuel</button>
                    <button type="button" @click="cycle = 'yearly'" :class="cycle === 'yearly' ? 'bg-[#0B0F14] text-white' : 'text-[#64748B]'" class="h-9 px-4 rounded-lg text-xs font-semibold transition">Annuel</button>
                </div>
            </div>

            @if(session('warning'))
                <div class="mt-8 max-w-2xl mx-auto p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">{{ session('warning') }}</div>
            @endif

            @if($commercialPlans->isNotEmpty())
                <div class="mt-12">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Commerçants</h2>
                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-5 lg:items-start">
                        @foreach($commercialPlans as $plan)
                            <div class="lg:col-span-1">
                                @include('subscriptions.offers._plan-card', ['plan' => $plan, 'featured' => false])
                            </div>
                        @endforeach
                        <div class="lg:col-span-2 rounded-2xl border border-dashed border-[#CBD5E1] p-5 sm:p-6 flex flex-col justify-center">
                            <h3 class="text-sm font-bold text-[#0B0F14]">Pensé pour les boutiques et points de vente</h3>
                            <p class="mt-2 text-xs leading-relaxed text-[#64748B]">Encaissez au comptoir, éditez vos factures, suivez vos stocks par magasin et gardez la trace de chaque opération. Vos données sont séparées et accessibles uniquement par votre équipe.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($enterprisePlans->isNotEmpty())
                <div class="mt-14">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Entreprises</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach($enterprisePlans as $plan)
                            @include('subscriptions.offers._plan-card', ['plan' => $plan, 'featured' => $loop->last])
                        @endforeach
                    </div>
                </div>
            @endif

            <p class="mt-10 text-center text-[11px] text-[#64748B]">Prix en francs CFA. Paiement sécurisé : Wave, Orange Money, MTN MoMo, Moov Money et carte bancaire.</p>
        </div>
    </section>
</x-layouts.landing>
