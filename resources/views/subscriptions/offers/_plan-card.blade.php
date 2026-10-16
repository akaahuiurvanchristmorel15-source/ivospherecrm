{{-- Carte d'offre (style verre dépoli). Variables : $plan, $featured, $includedFrom, $modules --}}
@php
    $subscribable = $plan->canBeSubscribed();
@endphp
<article class="relative flex flex-col rounded-[28px] border p-6 sm:p-7 backdrop-blur-2xl transition duration-300 {{ $featured ? 'border-white/25 bg-white/[0.09] shadow-[0_0_80px_-20px_rgba(255,255,255,0.25)]' : 'border-white/10 bg-white/[0.04] hover:border-white/20 hover:bg-white/[0.06]' }}">
    <header class="flex items-center justify-between gap-3">
        <p class="text-[13px] font-medium text-white/60">{{ $plan->name }}</p>
        @if($featured)
            <span class="px-2.5 py-0.5 rounded-full bg-white text-[10px] font-bold uppercase tracking-wider text-black">Disponible</span>
        @elseif(! $subscribable)
            <span class="px-2.5 py-0.5 rounded-full border border-white/15 text-[10px] font-semibold uppercase tracking-wider text-white/50">Bientôt</span>
        @endif
    </header>

    <p class="mt-4 flex items-baseline gap-1 whitespace-nowrap">
        <span class="text-4xl sm:text-[42px] font-semibold tracking-tight text-white" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }}' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }}'">{{ number_format($plan->price_monthly, 0, ',', ' ') }}</span>
        <span class="text-lg font-light text-white/50" x-text="cycle === 'monthly' ? 'F/mois' : 'F/an'">F/mois</span>
    </p>
    <p class="mt-2 text-xs text-white/40">{{ $plan->tagline }} · {{ $plan->max_users ? $plan->max_users.' utilisateurs' : 'Utilisateurs illimités' }}</p>

    <div class="my-6 h-px bg-gradient-to-r from-transparent via-white/15 to-transparent"></div>

    @if($includedFrom)
        <p class="mb-4 text-[11px] font-medium uppercase tracking-wider text-white/40">Tout {{ $includedFrom }}, plus</p>
    @endif

    <ul class="space-y-3.5 flex-1">
        @foreach($modules as $module)
            <li class="flex items-start gap-3">
                <span class="mt-px w-5 h-5 shrink-0 rounded-full {{ $featured ? 'bg-white text-black' : 'bg-white/10 text-white' }} flex items-center justify-center">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </span>
                <span class="text-[13px] leading-snug text-white/70">
                    <span class="text-white">{{ $module['label'] }}</span>
                    <span class="block text-[11px] text-white/40">{{ $module['description'] }}</span>
                </span>
            </li>
        @endforeach
    </ul>

    @if($subscribable)
        <a :href="'{{ route('offres.signup', $plan) }}?cycle=' + cycle"
           class="mt-8 h-11 rounded-full text-[13px] font-semibold flex items-center justify-center transition {{ $featured ? 'bg-white text-black hover:bg-white/90' : 'bg-black text-white border border-white/15 hover:border-white/40' }}">
            Commencer
        </a>
    @else
        <button type="button" @click="$dispatch('open-demo-modal', { pole: '{{ $plan->name }}' })"
                class="mt-8 h-11 rounded-full bg-black border border-white/15 text-[13px] font-semibold text-white hover:border-white/40 transition">
            Être informé du lancement
        </button>
    @endif
</article>
