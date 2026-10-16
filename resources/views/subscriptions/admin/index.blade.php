<x-layouts.app>
    <x-slot:title>Abonnements clients</x-slot>

    <div class="space-y-6">
        <x-page-header title="Abonnements clients" description="Espaces des commerçants et entreprises abonnés"
            :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.users.index')], ['label' => 'Abonnements']]">
            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('admin.subscriptions.plans') }}">Offres & tarifs</x-button>
                <x-button variant="primary" href="{{ route('offres.index') }}" target="_blank">Page publique des offres</x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <x-stat-card title="Espaces actifs" :value="$summary['active']" />
            <x-stat-card title="En attente de paiement" :value="$summary['pending']" />
            <x-stat-card title="Encaissé (total)" :value="number_format($summary['revenue'], 0, ',', ' ').' F'" />
        </div>

        <x-card>
            <form method="GET" class="p-4 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs border-b border-[#E2E8F0]">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Nom ou e-mail de l'entreprise" class="sm:col-span-7 h-10 px-3 rounded-xl border border-[#E2E8F0]">
                <select name="status" class="sm:col-span-3 h-10 px-3 rounded-xl border border-[#E2E8F0]">
                    <option value="">Tous les états</option>
                    <option value="active" @selected(request('status') === 'active')>Actifs</option>
                    <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspendus</option>
                </select>
                <x-button type="submit" variant="secondary" class="sm:col-span-2">Filtrer</x-button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-[#64748B]">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Entreprise</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Offre</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Échéance</th>
                            <th class="px-4 py-2.5 text-center font-semibold">Utilisateurs</th>
                            <th class="px-4 py-2.5 text-left font-semibold">État</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($tenants as $tenant)
                            @php($badge = $tenant->statusBadge())
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.subscriptions.show', $tenant) }}" class="font-semibold text-[#0B0F14] hover:text-[#0066FF]">{{ $tenant->name }}</a>
                                    <span class="block text-[11px] text-[#64748B]">{{ $tenant->email }} · {{ $tenant->phone }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $tenant->plan?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $tenant->subscription_ends_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $tenant->users_count }}</td>
                                <td class="px-4 py-3"><x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge></td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.subscriptions.show', $tenant) }}" class="font-semibold text-[#0066FF] hover:underline">Détails</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-[#64748B]">Aucun abonné pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tenants->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">{{ $tenants->links() }}</div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
