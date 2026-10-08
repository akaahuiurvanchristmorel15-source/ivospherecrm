<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\Prospect;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteRequestController extends Controller
{
    /**
     * Traiter la demande de devis envoyée depuis la page d'accueil vitrine
     * et enregistrer le demandeur en tant que client dans l'application.
     */
    public function store(Request $request): RedirectResponse
    {
        // Protection antispam Honeypot
        if ($request->filled('website')) {
            return redirect()->to(url('/#devis'))->with('quote_sent', true);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'pole' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $domain = null;
            if (! empty($validated['pole'])) {
                $poleKey = strtolower(trim($validated['pole']));
                $domain = Domain::where('code', $poleKey)
                    ->orWhere('name', 'like', '%'.$validated['pole'].'%')
                    ->first();
            }

            $quoteNote = '['.now()->format('d/m/Y H:i').'] Demande de devis site web ('.($validated['pole'] ?: 'Non spécifié').") :\n".$validated['message'];

            // 1. Recherche si le client existe déjà par email ou téléphone
            $customer = null;
            if (! empty($validated['email'])) {
                $customer = Customer::where('email', $validated['email'])->first();
            }
            if (! $customer && ! empty($validated['phone'])) {
                $customer = Customer::where('phone', $validated['phone'])
                    ->orWhere('whatsapp', $validated['phone'])
                    ->first();
            }

            if ($customer) {
                // Client existant : on enrichit son historique de demandes
                $customer->notes = ($customer->notes ? $customer->notes."\n\n" : '').$quoteNote;
                if ($domain && ! $customer->domain_id) {
                    $customer->domain_id = $domain->id;
                }
                $customer->save();
            } else {
                // Nouveau client : génération d'un code client unique (CLI-XXXXXX)
                do {
                    $code = 'CLI-'.Str::upper(Str::random(6));
                } while (Customer::where('code', $code)->exists());

                $customer = Customer::create([
                    'domain_id' => $domain?->id,
                    'code' => $code,
                    'type' => 'particulier',
                    'category' => 'standard',
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'whatsapp' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'status' => 'actif',
                    'loyalty_points' => 0,
                    'loyalty_level' => 'BRONZE',
                    'notes' => "Créé automatiquement depuis la demande de devis sur le site web.\n\n".$quoteNote,
                ]);

                ActivityLogger::log(
                    'created_customer',
                    'Création automatique du client '.$customer->name.' ('.$customer->code.') via demande de devis sur la page d\'accueil',
                    $customer,
                    $domain?->id
                );
            }

            // 2. Enregistrement également dans le pipeline des prospects pour suivi commercial
            Prospect::create([
                'domain_id' => $domain?->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'source' => 'Site Web - Demande de devis',
                'status' => 'nouveau',
                'stage' => 'nouveau',
                'notes' => 'Client enregistré : '.$customer->code.' ('.$customer->name.")\nPôle concerné : ".($validated['pole'] ?: 'Non spécifié')."\n\nDescription du projet :\n".$validated['message'],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->to(url('/#devis'))->with('quote_sent', true);
    }
}
