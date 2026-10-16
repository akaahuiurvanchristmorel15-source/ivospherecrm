@php
    $isFeatured = $featured ?? false;
    $subscribable = $plan->canBeSubscribed();
@endphp
<div class="relative flex flex-col rounded-2xl border {{ $isFeatured ? 'border-[#0066FF] ring-2 ring-[#0066FF]/15 shadow-lg' : 'border-[#E2E8F0]' }} bg-white p-5 sm:p-6">
    @if($isFeatured)
        <span class="absolute -top-3 left-5 px-2.5 py-1 rounded-full bg-[#0066FF] text-[10px] font-bold uppercase tracking-wide text-white">Le plus complet</span>
    @endif

    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold tracking-tight text-[#0B0F14]">{{ $plan->name }}</h3>
            @if($plan->tagline)
                <p class="mt-1 text-xs text-[#64748B]">{{ $plan->tagline }}</p>
            @endif
        </div>
        @unless($subscribable)
            <span class="shrink-0 px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-[10px] font-semibold text-amber-700">Bientôt</span>
        @endunless
    </div>

    <div class="mt-5">
        <p x-show="cycle === 'monthly'" class="flex items-baseline gap-1">
            <span class="text-3xl font-extrabold tracking-tight text-[#0B0F14]">{{ number_format($plan->price_monthly, 0, ',', ' ') }}</span>
            <span class="text-xs font-semibold text-[#64748B]">FCFA / mois</span>
        </p>
        <p x-show="cycle === 'yearly'" x-cloak class="flex items-baseline gap-1">
            <span class="text-3xl font-extrabold tracking-tight text-[#0B0F14]">{{ number_format($plan->price_yearly, 0, ',', ' ') }}</span>
            <span class="text-xs font-semibold text-[#64748B]">FCFA / an</span>
        </p>
        <p class="mt-1 text-[11px] text-[#64748B]">
            {{ $plan->max_users ? 'Jusqu\'à '.$plan->max_users.' utilisateurs' : 'Utilisateurs illimités' }}
        </p>
    </div>

    <ul class="mt-5 space-y-3 flex-1">
        @foreach($plan->moduleDetails() as $module)
            <li class="flex items-start gap-2.5">
                <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full {{ $module['ready'] ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span>
                    <span class="block text-xs font-semibold text-[#0B0F14]">{{ $module['label'] }}</span>
                    <span class="block text-[11px] leading-snug text-[#64748B]">{{ $module['description'] }}</span>
                </span>
            </li>
        @endforeach
    </ul>

    @if($subscribable)
        <a :href="'{{ route('offres.signup', $plan) }}?cycle=' + cycle"
           class="mt-6 h-11 rounded-xl {{ $isFeatured ? 'bg-[#0066FF] hover:bg-[#0052CC] text-white' : 'bg-[#0B0F14] hover:bg-black text-white' }} text-sm font-semibold flex items-center justify-center gap-2 transition">
            S'abonner
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    @else
        <button type="button" @click="$dispatch('open-demo-modal', { pole: '{{ $plan->name }}' })"
                class="mt-6 h-11 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0B0F14] hover:border-[#0066FF] hover:text-[#0066FF] transition">
            Être informé du lancement
        </button>
    @endif
</div>
