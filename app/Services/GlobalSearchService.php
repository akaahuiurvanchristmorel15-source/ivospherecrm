<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SupportTicket;
use App\Models\TechProject;

class GlobalSearchService
{
    /**
     * Search across all core ERP entities.
     *
     * @return array<string, array<int, array{id: int|string, title: string, subtitle: string, url: string, badge: string}>>
     */
    public function search(string $query): array
    {
        $term = trim($query);
        if (strlen($term) < 2) {
            return [];
        }

        $results = [];

        // 1. Clients
        $customers = Customer::where('name', 'like', "%{$term}%")
            ->orWhere('company', 'like', "%{$term}%")
            ->orWhere('contact_person', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($customers->isNotEmpty()) {
            $results['Clients'] = $customers->map(function ($c) {
                $name = $c->company ? "{$c->company} ({$c->name})" : $c->name;

                return [
                    'id' => $c->id,
                    'title' => $name,
                    'subtitle' => $c->phone ?: $c->email ?: 'Client actif',
                    'url' => route('commercial.customers.show', $c->id),
                    'badge' => 'Client',
                ];
            })->all();
        }

        // 2. Factures
        $invoices = Invoice::where('reference', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($invoices->isNotEmpty()) {
            $results['Factures'] = $invoices->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'title' => $inv->reference,
                    'subtitle' => number_format($inv->total, 0, ',', ' ')." FCFA — Statut : {$inv->status}",
                    'url' => route('commercial.invoices.show', $inv->id),
                    'badge' => 'Facture',
                ];
            })->all();
        }

        // 3. Devis
        $quotes = Quotation::where('reference', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($quotes->isNotEmpty()) {
            $results['Devis'] = $quotes->map(function ($q) {
                return [
                    'id' => $q->id,
                    'title' => $q->reference,
                    'subtitle' => number_format($q->total, 0, ',', ' ')." FCFA — Statut : {$q->status}",
                    'url' => route('commercial.quotations.show', $q->id),
                    'badge' => 'Devis',
                ];
            })->all();
        }

        // 4. Commandes
        $orders = Order::where('reference', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($orders->isNotEmpty()) {
            $results['Commandes'] = $orders->map(function ($o) {
                return [
                    'id' => $o->id,
                    'title' => $o->reference,
                    'subtitle' => number_format($o->total, 0, ',', ' ')." FCFA — {$o->status}",
                    'url' => route('commercial.orders.show', $o->id),
                    'badge' => 'Commande',
                ];
            })->all();
        }

        // 5. Produits / Articles
        $products = Product::where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($products->isNotEmpty()) {
            $results['Produits'] = $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->name,
                    'subtitle' => "SKU: {$p->sku} — Stock: {$p->stock} — ".number_format($p->selling_price, 0, ',', ' ').' FCFA',
                    'url' => route('commercial.products.show', $p->id),
                    'badge' => 'Produit',
                ];
            })->all();
        }

        // 6. Contrats
        $contracts = Contract::where('reference', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->orWhere('party_name', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($contracts->isNotEmpty()) {
            $results['Contrats'] = $contracts->map(function ($ctr) {
                return [
                    'id' => $ctr->id,
                    'title' => "{$ctr->reference} — {$ctr->name}",
                    'subtitle' => "Tiers : {$ctr->party_name} ({$ctr->type})",
                    'url' => route('contracts.show', $ctr->id),
                    'badge' => 'Contrat',
                ];
            })->all();
        }

        // 7. Support Tickets
        $tickets = SupportTicket::where('ticket_number', 'like', "%{$term}%")
            ->orWhere('subject', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($tickets->isNotEmpty()) {
            $results['Support'] = $tickets->map(function ($tk) {
                return [
                    'id' => $tk->id,
                    'title' => "{$tk->ticket_number} — {$tk->subject}",
                    'subtitle' => "Statut : {$tk->status} — Priorité : {$tk->priority}",
                    'url' => route('support.show', $tk->id),
                    'badge' => 'Ticket',
                ];
            })->all();
        }

        // 8. Projets Tech
        $techProjects = TechProject::where('name', 'like', "%{$term}%")
            ->take(5)
            ->get();

        if ($techProjects->isNotEmpty()) {
            $results['Projets TECH'] = $techProjects->map(function ($tp) {
                return [
                    'id' => $tp->id,
                    'title' => $tp->name,
                    'subtitle' => "Avancement : {$tp->progress}% — {$tp->status}",
                    'url' => route('tech.projects.show', $tp->id),
                    'badge' => 'Projet',
                ];
            })->all();
        }

        return $results;
    }
}
