<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SaleLifecycleService
{
    /**
     * S'assure que les colonnes d'audit existent sur la table 'orders'.
     */
    protected function ensureAuditColumnsExist(): void
    {
        if (Schema::hasColumn('orders', 'cancelled_at') && Schema::hasColumn('orders', 'validated_at')) {
            return;
        }

        try {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'validated_at')) {
                    $table->timestamp('validated_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('orders', 'validated_by')) {
                    $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
                }
                if (! Schema::hasColumn('orders', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('validated_by');
                }
                if (! Schema::hasColumn('orders', 'cancelled_by')) {
                    $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
                }
                if (! Schema::hasColumn('orders', 'cancellation_reason')) {
                    $table->text('cancellation_reason')->nullable()->after('cancelled_by');
                }
            });
        } catch (\Throwable $e) {
            // En cas d'erreur de permissions DDL, le flux continue avec repli gracieux
        }
    }

    /**
     * Valide une vente en attente : elle passe au statut « livrée » et le stock est déduit.
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

        $this->ensureAuditColumnsExist();

        return DB::transaction(function () use ($order, $validatedBy) {
            $data = ['status' => Order::STATUS_VALIDATED];

            if (Schema::hasColumn('orders', 'validated_at')) {
                $data['validated_at'] = now();
            }
            if (Schema::hasColumn('orders', 'validated_by')) {
                $data['validated_by'] = $validatedBy->id;
            }

            $order->update($data);

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

        $this->ensureAuditColumnsExist();

        return DB::transaction(function () use ($order, $cancelledBy, $reason) {
            $data = ['status' => Order::STATUS_CANCELLED];

            if (Schema::hasColumn('orders', 'cancelled_at')) {
                $data['cancelled_at'] = now();
            }
            if (Schema::hasColumn('orders', 'cancelled_by')) {
                $data['cancelled_by'] = $cancelledBy->id;
            }
            if (Schema::hasColumn('orders', 'cancellation_reason')) {
                $data['cancellation_reason'] = $reason;
            } else {
                $data['notes'] = trim(($order->notes ?? '')."\n[Annulation] Motif: ".$reason);
            }

            $order->update($data);

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
