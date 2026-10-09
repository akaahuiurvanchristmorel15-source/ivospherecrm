<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SmartAlert;
use Carbon\Carbon;

class AutomationEngineService
{
    /**
     * Run all registered automation rules and built-in condition checks.
     *
     * @return array{alerts_generated: int, rules_evaluated: int}
     */
    public function runChecks(): array
    {
        $alertsGenerated = 0;

        // 1. Check Overdue Invoices (> 7 days late)
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $overdueInvoices = Invoice::whereIn('status', ['impayée', 'partiellement_payée'])
            ->where('due_date', '<=', $sevenDaysAgo)
            ->get();

        foreach ($overdueInvoices as $inv) {
            $alreadyAlerted = SmartAlert::where('type', 'unpaid_invoice')
                ->where('action_url', route('commercial.invoices.show', $inv->id))
                ->where('status', 'active')
                ->exists();

            if (! $alreadyAlerted) {
                SmartAlert::create([
                    'type' => 'unpaid_invoice',
                    'title' => "Facture impayée depuis plus de 7 jours : {$inv->reference}",
                    'description' => 'Solde restant : '.number_format($inv->balance, 0, ',', ' ').' FCFA. Échéance dépassée.',
                    'priority' => 'haute',
                    'domain_id' => $inv->domain_id,
                    'action_url' => route('commercial.invoices.show', $inv->id),
                    'due_date' => $inv->due_date,
                    'metadata' => ['invoice_id' => $inv->id, 'balance' => $inv->balance],
                ]);
                $alertsGenerated++;
            }
        }

        // 2. Check Low Stock Products
        $lowStockProducts = Product::with(['warehouseStocks', 'movements'])
            ->where('is_active', true)
            ->get()
            ->filter(function ($prod) {
                $threshold = $prod->min_stock > 0 ? (int) $prod->min_stock : 5;

                return (int) $prod->current_stock <= $threshold;
            });

        foreach ($lowStockProducts as $prod) {
            $alreadyAlerted = SmartAlert::where('type', 'low_stock')
                ->where('action_url', route('commercial.products.show', $prod->id))
                ->where('status', 'active')
                ->exists();

            if (! $alreadyAlerted) {
                $threshold = $prod->min_stock > 0 ? (int) $prod->min_stock : 5;
                SmartAlert::create([
                    'type' => 'low_stock',
                    'title' => "Rupture imminente de stock : {$prod->name}",
                    'description' => "Stock actuel ({$prod->current_stock}) sous le seuil d'alerte ({$threshold}).",
                    'priority' => 'urgente',
                    'domain_id' => $prod->domain_id,
                    'action_url' => route('commercial.products.show', $prod->id),
                    'metadata' => ['product_id' => $prod->id, 'current_stock' => $prod->current_stock, 'min_stock' => $threshold],
                ]);
                $alertsGenerated++;
            }
        }

        // 3. Check Contracts Expiring within 30 days
        $thirtyDaysAhead = Carbon::now()->addDays(30)->toDateString();
        $expiringContracts = Contract::where('status', 'actif')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $thirtyDaysAhead)
            ->where('end_date', '>=', Carbon::now()->toDateString())
            ->get();

        foreach ($expiringContracts as $contract) {
            $alreadyAlerted = SmartAlert::where('type', 'contract_expiry')
                ->where('action_url', route('contracts.show', $contract->id))
                ->where('status', 'active')
                ->exists();

            if (! $alreadyAlerted) {
                SmartAlert::create([
                    'type' => 'contract_expiry',
                    'title' => "Échéance contrat imminente : {$contract->reference}",
                    'description' => "Le contrat '{$contract->name}' avec {$contract->party_name} expire le {$contract->end_date->format('d/m/Y')}.",
                    'priority' => 'haute',
                    'domain_id' => $contract->domain_id,
                    'action_url' => route('contracts.show', $contract->id),
                    'due_date' => $contract->end_date,
                    'metadata' => ['contract_id' => $contract->id],
                ]);
                $alertsGenerated++;
            }
        }

        // 4. Check Quotes unanswered > 3 days
        $threeDaysAgo = Carbon::now()->subDays(3);
        $pendingQuotes = Quotation::where('status', 'brouillon')
            ->where('created_at', '<=', $threeDaysAgo)
            ->get();

        foreach ($pendingQuotes as $quote) {
            $alreadyAlerted = SmartAlert::where('type', 'quote_followup')
                ->where('action_url', route('commercial.quotations.show', $quote->id))
                ->where('status', 'active')
                ->exists();

            if (! $alreadyAlerted) {
                SmartAlert::create([
                    'type' => 'quote_followup',
                    'title' => "Devis en attente de relance : {$quote->reference}",
                    'description' => 'Devis transmis il y a plus de 3 jours sans réponse client.',
                    'priority' => 'moyenne',
                    'domain_id' => $quote->domain_id,
                    'action_url' => route('commercial.quotations.show', $quote->id),
                    'metadata' => ['quotation_id' => $quote->id],
                ]);
                $alertsGenerated++;
            }
        }

        // 5. Evaluate custom automation rules
        $rules = AutomationRule::where('is_active', true)->get();
        foreach ($rules as $rule) {
            $rule->update([
                'last_triggered_at' => Carbon::now(),
                'execution_count' => $rule->execution_count + 1,
            ]);
        }

        return [
            'alerts_generated' => $alertsGenerated,
            'rules_evaluated' => $rules->count(),
        ];
    }
}
