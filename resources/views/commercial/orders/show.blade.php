<x-layouts.app>
    <x-slot:title>Commande {{ $order->reference }} — IVOSPHERE ERP</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header 
            title="Commande : {{ $order->reference }}" 
            description="Bon de commande client émis le {{ $order->date->format('d/m/Y') }}"
        >
            <x-slot:breadcrumbs>
                <x-breadcrumb :items="[
                    ['label' => 'Gestion Commerciale', 'url' => route('commercial.index')],
                    ['label' => 'Commandes', 'url' => route('commercial.orders.index')],
                    ['label' => $order->reference]
                ]" />
            </x-slot:breadcrumbs>

            <x-slot:actions>
                @if(! $order->isCancelled())
                    <form action="{{ route('commercial.orders.invoice', $order) }}" method="POST" class="inline-block">
                        @csrf
                        <x-button variant="primary" type="submit" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Générer la Facture</span>
                        </x-button>
                    </form>
                @endif

                @unless($order->isFinal())
                    <x-button variant="secondary" href="{{ route('commercial.orders.edit', $order) }}">
                        Modifier
                    </x-button>
                @endunless

                @include('commercial.orders.partials.sale-actions', ['order' => $order, 'compact' => false])
            </x-slot:actions>
        </x-page-header>

        <!-- Statut de la commande (Card) -->
        <x-card title="Cycle de Vie & Validation Commerciale">
            <div class="space-y-4">
                @if($order->isValidated())
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="text-xs space-y-1.5 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-bold text-emerald-900 text-sm">Vente Validée</h4>
                                @if($order->canBeCancelled())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Annulable sous 3j
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Définitif
                                    </span>
                                @endif
                            </div>
                            <p class="text-emerald-800">
                                Cette vente a été validée le <strong>{{ $order->validated_at?->format('d/m/Y à H:i') ?? $order->updated_at->format('d/m/Y à H:i') }}</strong>
                                @if($order->validator) par <strong>{{ $order->validator->name }}</strong>@endif.
                            </p>
                            <p class="text-emerald-700">Le stock physique a été déduit.</p>
                            @if($order->canBeCancelled())
                                <p class="text-amber-800 font-semibold bg-amber-50 p-2 rounded-lg border border-amber-200">
                                    ⏱️ Annulation autorisée pour les responsables jusqu'au <strong>{{ $order->cancellationDeadline()->format('d/m/Y') }}</strong> (délai de 3 jours).
                                </p>
                            @else
                                <p class="text-slate-600 font-medium">
                                    🔒 Le délai d'annulation de 3 jours est expiré : cette vente est désormais définitive.
                                </p>
                            @endif
                        </div>
                    </div>
                @elseif($order->isCancelled())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-rose-100 text-rose-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div class="text-xs space-y-1">
                            <h4 class="font-bold text-rose-900 text-sm">Vente Annulée</h4>
                            <p class="text-rose-800">
                                Annulée le <strong>{{ $order->cancelled_at?->format('d/m/Y à H:i') ?? $order->updated_at->format('d/m/Y à H:i') }}</strong>
                                @if($order->canceller) par <strong>{{ $order->canceller->name }}</strong>@endif.
                            </p>
                            @if($order->cancellation_reason)
                                <p class="text-rose-900 font-semibold bg-white/70 p-2 rounded-lg border border-rose-200">
                                    Motif : <span class="font-normal">{{ $order->cancellation_reason }}</span>
                                </p>
                            @endif
                            <p class="text-rose-700">Les stocks ont été réintégrés et les factures/règlements rattachés ont été annulés.</p>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $order->canBeCancelled() ? 'bg-amber-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            <div>
                                @if($order->canBeCancelled())
                                    <p class="text-slate-800 font-medium">
                                        Délai d'annulation de 3 jours actif — Annulation possible jusqu'au
                                        <strong class="text-[#0B0F14] font-bold">{{ $order->cancellationDeadline()->format('d/m/Y') }}</strong>.
                                    </p>
                                @else
                                    <p class="text-slate-700">
                                        Le délai d'annulation de 3 jours est dépassé (limite atteinte le {{ $order->cancellationDeadline()->format('d/m/Y') }}).
                                        Cette vente peut désormais uniquement être validée.
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0 text-slate-500 font-mono text-[11px]">
                            Émise le {{ $order->date?->format('d/m/Y') }}
                        </div>
                    </div>

                    <form action="{{ route('commercial.orders.status', $order) }}" method="POST" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs pt-2">
                        @csrf 
                        @method('PATCH')
                        
                        <div class="flex items-center gap-3">
                            <label class="font-semibold text-[#0B0F14]">Étape préparatoire :</label>
                            <select name="status" class="px-3.5 py-2 rounded-lg bg-white border border-[#E2E8F0] text-[#0B0F14] focus:outline-none focus:border-[#0066FF] text-xs transition">
                                @foreach(\App\Models\Order::PENDING_STATUSES as $st)
                                    <option value="{{ $st }}" {{ $order->status == $st ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucfirst($st)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <x-button variant="secondary" type="submit">
                            Mettre à jour l'étape
                        </x-button>
                    </form>
                @endif
            </div>
        </x-card>

        <!-- Détail Commande -->
        <x-card>
            <div class="flex flex-col sm:flex-row justify-between items-start border-b border-[#E2E8F0] pb-6 mb-6 gap-4">
                <div>
                    <span class="text-xs font-bold text-[#0066FF] uppercase tracking-wider">Bon de Commande</span>
                    <h2 class="text-2xl font-bold text-[#0B0F14] mt-0.5">COMMANDE CLIENT</h2>
                    <p class="text-xs font-mono text-[#64748B] mt-1">{{ $order->reference }}</p>
                </div>
                <div class="sm:text-right text-xs">
                    <p class="font-bold text-[#0B0F14] text-sm">Client : {{ $order->customer->name ?? 'Client Particulier' }}</p>
                    <p class="text-[#64748B] mt-0.5">Date : {{ $order->date->format('d/m/Y') }}</p>
                    <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#F5F7FA] border border-[#E2E8F0] text-[#0B0F14]">
                        Statut : {{ str_replace('_', ' ', ucfirst($order->status)) }}
                    </span>
                </div>
            </div>

            <!-- Table articles Desktop (>= md) -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-[#E2E8F0] mb-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-[#64748B] uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4 text-center">Quantité</th>
                            <th class="py-3 px-4 text-right">Prix Unitaire</th>
                            <th class="py-3 px-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-[#0B0F14]">
                        @foreach($order->items as $item)
                            <tr class="hover:bg-[#F5F7FA]/50 transition">
                                <td class="py-3 px-4 font-medium">{{ $item->description }}</td>
                                <td class="py-3 px-4 text-center text-[#64748B]">{{ $item->quantity }}</td>
                                <td class="py-3 px-4 text-right text-[#64748B]">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                <td class="py-3 px-4 text-right font-bold">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cartes Articles Mobile (< md) -->
            <div class="block md:hidden space-y-2.5 mb-6">
                @foreach($order->items as $item)
                    <div class="p-3 bg-[#F5F7FA] rounded-xl border border-[#E2E8F0] space-y-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <h5 class="font-bold text-[#0B0F14] text-xs">{{ $item->description }}</h5>
                            <span class="font-bold text-[#0066FF] text-xs shrink-0">{{ number_format($item->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-[#64748B]">
                            <span>Quantité : <strong class="text-[#0B0F14]">{{ $item->quantity }}</strong></span>
                            <span>P.U : <strong class="text-[#0B0F14]">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <div class="w-72 space-y-2 text-xs bg-[#F5F7FA] p-4 rounded-xl border border-[#E2E8F0]">
                    <div class="flex justify-between text-[#64748B]">
                        <span>Sous-total</span>
                        <span class="font-semibold text-[#0B0F14]">{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($order->tax_amount > 0)
                        <div class="flex justify-between text-[#64748B]">
                            <span>Taxes</span>
                            <span class="font-semibold text-[#0B0F14]">{{ number_format($order->tax_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm font-bold text-[#0B0F14] pt-2 border-t border-[#E2E8F0]">
                        <span>Total TTC</span>
                        <span class="text-[#0066FF]">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
