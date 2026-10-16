{{-- Carte d'offre (verre dépoli, mode jour / nuit). Variables : $plan, $featured, $includedFrom, $modules --}}
@php
    $subscribable = $plan->canBeSubscribed();
@endphp
<article class="relative flex flex-col rounded-[28px] border p-6 sm:p-7 backdrop-blur-2xl transition duration-300 {{ $featured
    ? 'border-black/15 bg-white/80 shadow-[0_30px_80px_-30px_rgba(11,15,20,0.35)] night:border-white/25 night:bg-white/[0.09] night:shadow-[0_0_80px_-20px_rgba(255,255,255,0.25)]'
    : 'border-black/[0.08] bg-white/55 hover:border-black/20 hover:bg-white/70 night:border-white/10 night:bg-white/[0.04] night:hover:border-white/20 night:hover:bg-white/[0.06]' }}">
    <header class="flex items-center justify-between gap-3">
        <p class="text-[13px] font-medium text-black/55 night:text-white/60">{{ $plan->name }}</p>
        @if($featured)
            <span class="px-2.5 py-0.5 rounded-full bg-[#0B0F14] text-[10px] font-bold uppercase tracking-wider text-white night:bg-white night:text-black">Disponible</span>
        @elseif(! $subscribable)
            <span class="px-2.5 py-0.5 rounded-full border border-black/15 text-[10px] font-semibold uppercase tracking-wider text-black/45 night:border-white/15 night:text-white/50">Bientôt</span>
        @endif
    </header>

    <p class="mt-4 flex items-baseline gap-1 whitespace-nowrap">
        <span class="text-4xl sm:text-[42px] font-semibold tracking-tight text-[#0B0F14] night:text-white" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }}' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }}'">{{ number_format($plan->price_monthly, 0, ',', ' ') }}</span>
        <span class="text-lg font-light text-black/45 night:text-white/50" x-text="cycle === 'monthly' ? 'F/mois' : 'F/an'">F/mois</span>
    </p>
    <p class="mt-2 text-xs text-black/45 night:text-white/40">{{ $plan->tagline }} · {{ $plan->max_users ? $plan->max_users.' utilisateurs' : 'Utilisateurs illimités' }}</p>

    <div class="my-6 h-px bg-gradient-to-r from-transparent via-black/10 to-transparent night:via-white/15"></div>

    @if($includedFrom)
        <p class="mb-4 text-[11px] font-medium uppercase tracking-wider text-black/40 night:text-white/40">Tout {{ $includedFrom }}, plus</p>
    @endif

    <ul class="space-y-3.5 flex-1">
        @foreach($modules as $module)
            <li class="flex items-start gap-3">
                <span class="mt-px w-5 h-5 shrink-0 rounded-full flex items-center justify-center {{ $featured ? 'bg-[#0B0F14] text-white night:bg-white night:text-black' : 'bg-black/[0.06] text-[#0B0F14] night:bg-white/10 night:text-white' }}">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </span>
                <span class="text-[13px] leading-snug">
                    <span class="text-[#0B0F14] night:text-white">{{ $module['label'] }}</span>
                    <span class="block text-[11px] text-black/45 night:text-white/40">{{ $module['description'] }}</span>
                </span>
            </li>
        @endforeach
    </ul>

    @if($subscribable)
        <a :href="'{{ route('offres.signup', $plan) }}?cycle=' + cycle"
           class="mt-8 h-11 rounded-full text-[13px] font-semibold flex items-center justify-center transition {{ $featured
               ? 'bg-[#0B0F14] text-white hover:bg-black night:bg-white night:text-black night:hover:bg-white/90'
               : 'bg-white text-[#0B0F14] border border-black/15 hover:border-black/40 night:bg-black night:text-white night:border-white/15 night:hover:border-white/40' }}">
            Commencer
        </a>
    @else
        <button type="button" @click="$dispatch('open-demo-modal', { pole: '{{ $plan->name }}' })"
                class="mt-8 h-11 rounded-full border text-[13px] font-semibold transition bg-white text-[#0B0F14] border-black/15 hover:border-black/40 night:bg-black night:text-white night:border-white/15 night:hover:border-white/40">
            Être informé du lancement
        </button>
    @endif
</article>
