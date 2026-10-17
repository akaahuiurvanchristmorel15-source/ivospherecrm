<?php

namespace App\Services\Subscriptions;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Client HTTP des prestataires de paiement.
 *
 * Sur certains PHP (Windows notamment), aucune liste de certificats racine n'est configurée :
 * HTTP_CA_BUNDLE permet d'indiquer le fichier cacert.pem à utiliser. La vérification SSL reste active.
 */
class PaymentHttp
{
    public static function request(): PendingRequest
    {
        $request = Http::acceptJson()->timeout(20);
        $caBundle = self::caBundlePath();

        return $caBundle ? $request->withOptions(['verify' => $caBundle]) : $request;
    }

    public static function caBundlePath(): ?string
    {
        $configured = config('subscriptions.payment.ca_bundle');
        if (blank($configured)) {
            return null;
        }

        foreach ([$configured, base_path($configured)] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
