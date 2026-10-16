<x-layouts.app>
    <x-slot:title>{{ $tenant->name }} — Abonnement</x-slot>

    @php($badge = $tenant->statusBadge())

    <div class="space-y-6">
        <x-page-header :title="$tenant->name" :description="$tenant->email.' · '.$tenant->phone"
            :breadcrumbs="[['label' => 'Abonnements', 'url' => route('admin.subscriptions.index')], ['label' => $tenant->name]]">
            <x-slot:actions>
                @if($tenant->status !== 'pending')
                    <form method="POST" action="{{ route('admin.subscriptions.toggle', $tenant) }}">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" :variant="$tenant->status === 'suspended' ? 'primary' : 'danger'">
                            {{ $tenant->status === 'suspended' ? "Réactiver l'espace" : "Suspendre l'espace" }}
                        </x-button>
                    </form>
                @endif
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <x-stat-card title="Offre" :value="$tenant->plan?->name ?? '—'" />
            <x-stat-card title="État" :value="$badge['label']" />
            <x-stat-card title="Échéance" :value="$tenant->subscription_ends_at?->format('d/m/Y') ?? '—'" />
            <x-stat-card title="Utilisateurs" :value="$users->count().($tenant->plan?->max_users ? ' / '.$tenant->plan->max_users : '')" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <x-card title="Paiements">
                <div class="divide-y divide-[#E2E8F0] text-xs">
                    @forelse($subscriptions as $subscription)
                        <div class="px-4 py-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-mono font-semibold">{{ $subscription->reference }}</p>
                                <p class="text-[11px] text-[#64748B]">{{ $subscription->plan?->name }} · {{ $subscription->billingCycleLabel() }} · {{ $subscription->payment_provider }}{{ $subscription->payment_method ? ' / '.$subscription->payment_method : '' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-semibold">{{ number_format($subscription->amount, 0, ',', ' ') }} F</p>
                                @if($subscription->isPaid())
                                    <a href="{{ route('admin.subscriptions.pdf', [$tenant, $subscription->id]) }}" class="text-[11px] font-semibold text-[#0066FF] hover:underline">PDF</a>
                                @else
                                    <span class="text-[11px] text-[#64748B]">{{ $subscription->status === 'failed' ? 'Échec' : 'En attente' }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-[#64748B]">Aucun paiement.</p>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Comptes de l'espace">
                <div class="divide-y divide-[#E2E8F0] text-xs">
                    @foreach($users as $user)
                        <div class="px-4 py-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $user->name }}</p>
                                <p class="text-[11px] text-[#64748B]">{{ $user->email }}</p>
                            </div>
                            <x-badge :variant="$user->is_active ? 'success' : 'neutral'">{{ $user->primary_role }}</x-badge>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
