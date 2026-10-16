<x-layouts.app>
    <x-slot:title>{{ $tenant->name }}</x-slot>

    @php($badge = $tenant->statusBadge())

    <div class="space-y-6">
        <div class="rounded-2xl bg-[#0B0F14] text-white p-5 sm:p-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-white/60">Bienvenue dans votre espace</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight">{{ $tenant->name }}</h1>
                <p class="mt-1 text-xs text-white/70">
                    Offre {{ $tenant->plan?->name }} ·
                    @if($tenant->hasActiveSubscription())
                        valable jusqu'au {{ $tenant->subscription_ends_at?->format('d/m/Y') }} ({{ $tenant->daysRemaining() }} j)
                    @else
                        {{ $badge['label'] }}
                    @endif
                </p>
            </div>
            <a href="{{ route('space.subscription') }}" class="h-10 px-4 rounded-xl bg-white text-[#0B0F14] text-xs font-semibold flex items-center justify-center hover:bg-slate-100 transition">Mon abonnement</a>
        </div>

        @if($tenant->hasActiveSubscription() && $tenant->daysRemaining() <= 7)
            <x-alert type="warning">Votre abonnement arrive à échéance dans {{ $tenant->daysRemaining() }} jour(s). Pensez à le renouveler pour garder l'accès à votre espace.</x-alert>
        @endif

        <div class="grid grid-cols-3 gap-3 sm:gap-4">
            @if(! is_null($stats['products']))
                <x-stat-card title="Articles" :value="number_format($stats['products'], 0, ',', ' ')" />
            @endif
            @if(! is_null($stats['invoicesToday']))
                <x-stat-card title="Factures du jour" :value="$stats['invoicesToday']" />
            @endif
            <x-stat-card title="Utilisateurs" :value="$stats['users'].($tenant->plan?->max_users ? ' / '.$tenant->plan->max_users : '')" />
        </div>

        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#64748B] mb-3">Vos modules</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                @foreach($shortcuts as $shortcut)
                    <a href="{{ route($shortcut['route']) }}" class="group bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-5 flex items-center gap-4 hover:border-[#0066FF] hover:shadow-sm transition">
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-blue-50 text-[#0066FF] flex items-center justify-center">
                            @switch($shortcut['icon'])
                                @case('pos')<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>@break
                                @case('invoice')<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l5.4 5.4a1 1 0 01.3.7V19a2 2 0 01-2 2z"/></svg>@break
                                @case('customers')<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.4-1.9M17 20H7m10 0v-2c0-.7-.1-1.3-.4-1.9M7 20H2v-2a3 3 0 015.4-1.9M7 20v-2c0-.7.1-1.3.4-1.9m0 0a5 5 0 019.2 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>@break
                                @case('stock')<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>@break
                                @case('users')<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.4a4 4 0 110 5.3M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.2M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>@break
                                @default<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-4A12 12 0 0112 2.9 12 12 0 013.4 6 12 12 0 003 9c0 5.6 3.8 10.3 9 11.6 5.2-1.3 9-6 9-11.6 0-1-.1-2-.4-3z"/></svg>
                            @endswitch
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF]">{{ $shortcut['label'] }}</span>
                            <span class="block text-[11px] text-[#64748B] truncate">{{ $shortcut['description'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
