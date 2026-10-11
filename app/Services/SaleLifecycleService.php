<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleLifecycleService
{
    /**
     * Valide une vente en attente : elle passe au statut « livrée » et le stock est déduit.
     * Une vente validée devient définitive.
     *
     * @throws ValidationException
     */
    public function validate(Order $order, User $validatedBy): Order
    {
        if (! $order->canBeValidated()) {
            throw ValidationException::withMessages([
                'order' => 'Cette vente est déjà '.($order->isCancelled() ? 'annulée' : 'validée').' et ne peut plus être validée.',
            ]);
        }

        return DB::transaction(function () use ($order, $validatedBy) {
            $order->update([
                'status' => Order::STATUS_VALIDATED,
                'validated_at' => now(),
                'validated_by' => $validatedBy->id,
            ]);

            StockService::deductOrderStock($order, 'Sortie vente validée ('.$order->reference.')');

            ActivityLogger::log('validated_order', 'Validation de la vente '.$order->reference, $order);

            return $order;
        });
    }

    /**
     * Annule une vente (en cours ou déjà validée) dans le délai autorisé de 3 jours après sa date.
     * Remet le stock en magasin, annule les factures et paiements liés et trace le motif d'annulation.
     *
     * @throws ValidationException
     */
    public function cancel(Order $order, User $cancelledBy, string $reason): Order
    {
        if ($order->isCancelled()) {
            throw ValidationException::withMessages(['order' => 'Cette vente est déjà annulée.']);
        }

        if (! $order->isWithinCancellationWindow()) {
            throw ValidationException::withMessages([
                'order' => 'Le délai d\'annulation de '.Order::CANCELLATION_WINDOW_DAYS.' jours est dépassé (limite : '.$order->cancellationDeadline()->format('d/m/Y').').',
            ]);
        }

        return DB::transaction(function () use ($order, $cancelledBy, $reason) {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy->id,
                'cancellation_reason' => $reason,
            ]);

            StockService::restoreOrderStock($order);

            $order->loadMissing('invoices.payments');

            foreach ($order->invoices as $invoice) {
                $invoice->payments()->update(['status' => 'annule']);
                $invoice->update(['status' => 'annulee']);
            }

            ActivityLogger::log('cancelled_order', 'Annulation de la vente '.$order->reference.' — Motif : '.$reason, $order);

            return $order;
        });
    }
}
