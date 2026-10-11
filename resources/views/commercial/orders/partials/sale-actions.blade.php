@php
    $compact = $compact ?? false;
    $currentUser = auth()->user();
    $canManageSales = $currentUser && ($currentUser->isAdmin() || $currentUser->hasAnyRole(['responsable', 'responsable_commercial']));
@endphp

@if($canManageSales && ($order->canBeValidated() || $order->canBeCancelled()))
    <div x-data="{ showCancelModal: false }" class="{{ $compact ? 'contents' : 'flex flex-wrap items-center gap-2' }}">
        {{-- Valider la vente (si pas encore validée) --}}
        @if($order->canBeValidated())
            <form action="{{ route('commercial.orders.validate', $order) }}" method="POST" class="{{ $compact ? 'inline-flex' : 'inline-block' }}"
                  onsubmit="return confirm('Valider la vente {{ $order->reference }} ? Le stock sera déduit (annulation possible sous 3 jours par un responsable).');">
                @csrf
                <button type="submit"
                        title="Valider la vente"
                        class="{{ $compact
                            ? 'p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 border border-transparent hover:border-emerald-200 transition'
                            : 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @unless($compact)<span>Valider la vente</span>@endunless
                </button>
            </form>
        @endif

        {{-- Annuler la vente (délai de 3 jours, qu'elle soit en cours ou déjà validée) --}}
        @if($order->canBeCancelled())
            <button type="button"
                    @click="showCancelModal = true"
                    title="Annuler la vente {{ $order->isValidated() ? '(déjà validée) ' : '' }}(jusqu'au {{ $order->cancellationDeadline()->format('d/m/Y') }})"
                    class="{{ $compact
                        ? 'p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition'
                        : 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 text-xs font-semibold transition' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                @unless($compact)<span>Annuler la vente</span>@endunless
            </button>

            <template x-teleport="body">
                <div x-show="showCancelModal" x-cloak x-transition.opacity
                     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 p-4"
                     @keydown.escape.window="showCancelModal = false">
                    <div @click.outside="showCancelModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#E2E8F0] p-5 text-left space-y-4">
                        <div>
                            <h3 class="text-sm font-bold text-[#0B0F14]">Annuler la vente {{ $order->reference }}</h3>
                            <p class="text-xs text-[#64748B] mt-1">
                                @if($order->isValidated())
                                    <span class="inline-block px-2 py-0.5 mb-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Vente déjà validée</span><br>
                                    Annulation possible jusqu'au <strong>{{ $order->cancellationDeadline()->format('d/m/Y') }}</strong> (délai de 3 jours). Les stocks déduits seront réintégrés en magasin et les factures/règlements rattachés seront annulés.
                                @else
                                    Annulation possible jusqu'au <strong>{{ $order->cancellationDeadline()->format('d/m/Y') }}</strong> (délai de 3 jours). Les factures et paiements liés seront annulés.
                                @endif
                            </p>
                        </div>
                        <form action="{{ route('commercial.orders.cancel', $order) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label for="cancellation-reason-{{ $order->id }}" class="block text-xs font-semibold text-[#0B0F14] mb-1">
                                    Motif d'annulation <span class="text-rose-600">*</span>
                                </label>
                                <textarea id="cancellation-reason-{{ $order->id }}" name="cancellation_reason" rows="3" required minlength="5" maxlength="500"
                                          class="w-full px-3 py-2 rounded-xl bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500"
                                          placeholder="Ex. : erreur de saisie, client a renoncé à l'achat...">{{ old('cancellation_reason') }}</textarea>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="showCancelModal = false" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    Retour
                                </button>
                                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition">
                                    Confirmer l'annulation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif
    </div>
@endif
