{{-- Pied de page des pages d'abonnement (mode jour / nuit) --}}
@php
    $footerEmail = config('company.email', config('mail.from.address'));
    $footerPhone = config('company.phone');
@endphp
<footer class="relative overflow-hidden border-t border-black/[0.08] bg-[#F5F7FA] text-[#0B0F14] night:border-white/10 night:bg-black night:text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Bandeau d'appel --}}
        <div class="flex flex-col gap-6 border-b border-black/[0.08] py-12 sm:flex-row sm:items-center sm:justify-between night:border-white/10">
            <div>
                <p class="text-2xl font-extrabold tracking-tight sm:text-3xl">Votre espace de gestion vous attend.</p>
                <p class="mt-2 text-sm text-black/50 night:text-white/50">Activation immédiate après paiement · Récapitulatif PDF par e-mail</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('offres.index') }}#offres" class="inline-flex h-11 items-center rounded-full bg-[#0B0F14] px-6 text-sm font-semibold text-white transition hover:bg-black night:bg-white night:text-black night:hover:bg-white/90">Voir les offres</a>
                <a href="{{ route('login') }}" class="inline-flex h-11 items-center rounded-full border border-black/15 px-6 text-sm font-semibold transition hover:border-black/40 night:border-white/15 night:hover:border-white/40">Se connecter</a>
            </div>
        </div>

        {{-- Liens --}}
        <div class="grid grid-cols-2 gap-8 py-12 md:grid-cols-12">
            <div class="col-span-2 md:col-span-5">
                <span class="inline-flex rounded-xl bg-white px-2.5 py-1.5 ring-1 ring-black/5">
                    <img src="{{ asset('images/logo-horizontal.png') }}" alt="IVOSPHERE" class="h-8 w-auto">
                </span>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-black/50 night:text-white/50">Vente, facturation, stock et sécurité réunis dans un espace dédié à votre entreprise. Vos données restent séparées et accessibles uniquement par votre équipe.</p>
            </div>

            <nav class="md:col-span-2" aria-label="Abonnements">
                <p class="text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Abonnements</p>
                <ul class="mt-4 space-y-2.5 text-sm text-black/60 night:text-white/60">
                    <li><a href="{{ route('offres.index') }}#offres" class="hover:text-black night:hover:text-white">Offres & tarifs</a></li>
                    <li><a href="{{ route('offres.index') }}#comparatif" class="hover:text-black night:hover:text-white">Comparatif</a></li>
                    <li><a href="{{ route('offres.index') }}#faq" class="hover:text-black night:hover:text-white">Questions fréquentes</a></li>
                </ul>
            </nav>

            <nav class="md:col-span-2" aria-label="IVOSPHERE">
                <p class="text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">IVOSPHERE</p>
                <ul class="mt-4 space-y-2.5 text-sm text-black/60 night:text-white/60">
                    <li><a href="{{ route('home') }}" class="hover:text-black night:hover:text-white">Accueil</a></li>
                    <li><a href="{{ route('home') }}#prestations" class="hover:text-black night:hover:text-white">Nos prestations</a></li>
                    <li><a href="{{ route('home') }}#devis" class="hover:text-black night:hover:text-white">Demander un devis</a></li>
                </ul>
            </nav>

            <div class="col-span-2 md:col-span-3">
                <p class="text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Contact</p>
                <ul class="mt-4 space-y-2.5 text-sm text-black/60 night:text-white/60">
                    @if($footerEmail)<li><a href="mailto:{{ $footerEmail }}" class="break-all hover:text-black night:hover:text-white">{{ $footerEmail }}</a></li>@endif
                    @if($footerPhone)<li><a href="tel:{{ preg_replace('/\s+/', '', $footerPhone) }}" class="hover:text-black night:hover:text-white">{{ $footerPhone }}</a></li>@endif
                    <li><button type="button" @click="$dispatch('open-demo-modal')" class="font-semibold text-[#0B0F14] hover:underline night:text-white">Parler à un conseiller</button></li>
                </ul>
                <div class="mt-5 flex flex-wrap gap-1.5">
                    @foreach(['Paystack', 'Mobile Money', 'Visa', 'Mastercard'] as $method)
                        <span class="rounded-full border border-black/10 px-2.5 py-1 text-[10px] font-medium text-black/50 night:border-white/10 night:text-white/50">{{ $method }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Nom géant en filigrane --}}
    <p class="pointer-events-none select-none text-center text-[19vw] font-extrabold leading-[0.75] tracking-tighter text-black/[0.05] night:text-white/[0.06] lg:text-[250px]" aria-hidden="true">IVOSPHERE</p>

    <div class="relative border-t border-black/[0.08] night:border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-black/45 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8 night:text-white/45">
            <p>© {{ now()->year }} IVOSPHERE. Tous droits réservés.</p>
            <p>Abidjan, Côte d'Ivoire</p>
        </div>
    </div>
</footer>
