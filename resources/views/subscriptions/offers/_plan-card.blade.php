{{-- Carte d'une offre Entreprise. Variables : $plan, $featured, $includedFrom (nom de l'offre précédente), $newModules --}}
@php
    $subscribable = $plan->canBeSubscribed();
    $yearlySaving = $plan->price_monthly > 0 ? (int) round(100 - ($plan->price_yearly / ($plan->price_monthly * 12)) * 100) : 0;
@endphp
<article class="relative flex flex-col rounded-3xl p-6 sm:p-7 transition duration-300 {{ $featured ? 'bg-[#0B0F14] text-white shadow-2xl shadow-[#0066FF]/20 lg:-my-4 lg:py-10' : 'bg-white border border-[#E2E8F0] hover:border-[#0066FF]/40 hover:shadow-xl hover:shadow-slate-200/60' }}">
    @if($featured)
        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gradient-to-r from-[#0066FF] to-[#4F46E5] text-[10px] font-bold uppercase tracking-wider text-white shadow-lg">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69l1.07-3.29z"/></svg>
            Suite complète
        </span>
    @endif

    <header class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold tracking-tight">{{ $plan->name }}</h3>
            <p class="mt-1 text-xs {{ $featured ? 'text-white/60' : 'text-[#64748B]' }}">{{ $plan->tagline }}</p>
        </div>
        @unless($subscribable)
            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $featured ? 'bg-white/10 text-amber-300' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">Bientôt</span>
        @endunless
    </header>

    <div class="mt-6">
        <p class="flex items-baseline gap-1.5">
            <span class="text-4xl font-extrabold tracking-tight" x-text="cycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }}' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }}'">{{ number_format($plan->price_monthly, 0, ',', ' ') }}</span>
            <span class="text-xs font-semibold {{ $featured ? 'text-white/60' : 'text-[#64748B]' }}" x-text="cycle === 'monthly' ? 'FCFA / mois' : 'FCFA / an'">FCFA / mois</span>
        </p>
        <p class="mt-1.5 h-4 text-[11px] {{ $featured ? 'text-emerald-300' : 'text-emerald-600' }}">
            @if($yearlySaving > 0)
                <span x-show="cycle === 'yearly'" x-cloak>Soit {{ number_format(intdiv($plan->price_yearly, 12), 0, ',', ' ') }} F/mois · {{ $yearlySaving }} % d'économie</span>
            @endif
        </p>
    </div>

    <p class="mt-4 flex items-center gap-2 text-xs {{ $featured ? 'text-white/80' : 'text-[#0B0F14]' }}">
        <svg class="w-4 h-4 {{ $featured ? 'text-white/50' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        {{ $plan->max_users ? 'Jusqu\'à '.$plan->max_users.' utilisateurs' : 'Utilisateurs illimités' }}
    </p>

    <div class="my-6 h-px {{ $featured ? 'bg-white/10' : 'bg-[#E2E8F0]' }}"></div>

    @if($includedFrom)
        <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider {{ $featured ? 'text-[#7AB0FF]' : 'text-[#0066FF]' }}">Tout {{ $includedFrom }}, plus :</p>
    @endif

    <ul class="space-y-3.5 flex-1">
        @foreach($newModules as $module)
            <li class="flex items-start gap-3">
                <span class="mt-0.5 w-8 h-8 shrink-0 rounded-xl flex items-center justify-center {{ $featured ? 'bg-white/10 text-[#7AB0FF]' : 'bg-blue-50 text-[#0066FF]' }}">
                    @include('subscriptions.offers._module-icon', ['key' => $module['key'], 'class' => 'w-4 h-4'])
                </span>
                <span>
                    <span class="block text-sm font-semibold">{{ $module['label'] }}</span>
                    <span class="block text-[11px] leading-snug {{ $featured ? 'text-white/55' : 'text-[#64748B]' }}">{{ $module['description'] }}</span>
                </span>
            </li>
        @endforeach
    </ul>

    @if($subscribable)
        <a :href="'{{ route('offres.signup', $plan) }}?cycle=' + cycle"
           class="mt-8 h-12 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition {{ $featured ? 'bg-[#0066FF] hover:bg-[#0052CC] text-white' : 'bg-[#0B0F14] hover:bg-black text-white' }}">
            Choisir {{ $plan->name }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    @else
        <button type="button" @click="$dispatch('open-demo-modal', { pole: '{{ $plan->name }}' })"
                class="mt-8 h-12 rounded-xl text-sm font-semibold transition {{ $featured ? 'bg-white text-[#0B0F14] hover:bg-slate-100' : 'border border-[#E2E8F0] text-[#0B0F14] hover:border-[#0066FF] hover:text-[#0066FF]' }}">
            Être informé du lancement
        </button>
    @endif
</article>
