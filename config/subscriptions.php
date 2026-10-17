<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modules vendus dans les plans d'abonnement
    |--------------------------------------------------------------------------
    |
    | Chaque module donne accès à des pages (noms de routes, « * » en fin de motif).
    | Un module « ready » = false n'est pas encore isolé par espace : les plans qui
    | le contiennent restent « bientôt disponibles ».
    |
    */

    'modules' => [
        'pos' => [
            'label' => 'Système de vente POS',
            'description' => 'Caisse tactile, scan des codes-barres, encaissement espèces / mobile money / carte.',
            'routes' => ['commercial.pos.*', 'commercial.customers.*'],
            'ready' => true,
        ],
        'facturation' => [
            'label' => 'Facturation',
            'description' => 'Devis, commandes, factures PDF et suivi des règlements clients.',
            'routes' => ['commercial.quotations.*', 'commercial.orders.*', 'commercial.invoices.*', 'commercial.payments.*', 'commercial.customers.*'],
            'ready' => true,
        ],
        'stock' => [
            'label' => 'Gestion de stock',
            'description' => 'Catalogue, entrepôts, mouvements, inventaires, alertes, photos des articles.',
            'routes' => ['stock.*', 'commercial.products.*', 'commercial.suppliers.*'],
            'ready' => true,
        ],
        'journal_securite' => [
            'label' => 'Journal et sécurité',
            'description' => "Journal d'activité, utilisateurs et rôles de l'espace, mot de passe.",
            'routes' => ['admin.users.*', 'admin.logs.*'],
            'ready' => true,
        ],
        'direction' => [
            'label' => 'Direction',
            'description' => 'Tableaux de bord de pilotage, indicateurs et analyses.',
            'routes' => [],
            'ready' => false,
        ],
        'services_collaboration' => [
            'label' => 'Services et collaboration',
            'description' => 'Messagerie interne, documents partagés (GED), tickets de support.',
            'routes' => [],
            'ready' => false,
        ],
        'administration_securite' => [
            'label' => 'Administration et sécurité',
            'description' => "Utilisateurs, rôles, journal d'activité et paramètres de l'entreprise.",
            'routes' => ['admin.users.*', 'admin.logs.*'],
            'ready' => true,
        ],
    ],

    /*
    | Tables séparées par espace dont les règles de validation « exists » / « unique » sont
    | limitées à l'espace courant (les e-mails des utilisateurs restent uniques partout).
    */
    'tenant_validated_tables' => [
        'domains', 'activity_logs', 'categories', 'brands', 'suppliers', 'products', 'product_variants',
        'product_batches', 'warehouses', 'warehouse_stocks', 'stock_movements', 'stock_alerts',
        'stock_transfers', 'physical_inventories', 'physical_inventory_items', 'purchase_requests',
        'supplier_receptions', 'equipment', 'equipment_maintenances', 'customers', 'quotations',
        'quotation_items', 'orders', 'order_items', 'invoices', 'invoice_items', 'payments',
    ],

    /*
    | Pages accessibles à tout utilisateur d'un espace abonné, quel que soit son plan.
    */
    'always_allowed_routes' => [
        'space.*', 'offres.*', 'password.*', 'logout', 'api.search', 'livewire.*',
    ],

    /*
    | Pages accessibles même quand l'abonnement est expiré ou en attente de paiement.
    */
    'billing_routes' => ['space.*', 'offres.*', 'password.*', 'logout'],

    /*
    |--------------------------------------------------------------------------
    | Plans proposés (valeurs initiales, modifiables dans l'administration)
    |--------------------------------------------------------------------------
    */

    'plans' => [
        [
            'code' => 'commercial_caisse',
            'family' => 'commercial',
            'name' => 'Commercial Caisse',
            'tagline' => 'Tout pour vendre et gérer votre boutique.',
            'modules' => ['pos', 'facturation', 'stock', 'journal_securite'],
            'price_monthly' => 15000,
            'price_yearly' => 150000,
            'max_users' => 3,
            'sort_order' => 1,
        ],
        [
            'code' => 'entreprise_petal',
            'family' => 'entreprise',
            'name' => 'Entreprise Pétal',
            'tagline' => 'Le pilotage de votre entreprise.',
            'modules' => ['direction'],
            'price_monthly' => 25000,
            'price_yearly' => 250000,
            'max_users' => 5,
            'sort_order' => 2,
        ],
        [
            'code' => 'entreprise_plus',
            'family' => 'entreprise',
            'name' => 'Entreprise Plus',
            'tagline' => 'Pilotage et travail en équipe.',
            'modules' => ['direction', 'services_collaboration'],
            'price_monthly' => 45000,
            'price_yearly' => 450000,
            'max_users' => 15,
            'sort_order' => 3,
        ],
        [
            'code' => 'entreprise_business',
            'family' => 'entreprise',
            'name' => 'Entreprise Business',
            'tagline' => 'La suite complète, caisse comprise.',
            'modules' => ['direction', 'services_collaboration', 'administration_securite', 'pos', 'facturation', 'stock', 'journal_securite'],
            'price_monthly' => 75000,
            'price_yearly' => 750000,
            'max_users' => 50,
            'sort_order' => 4,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paiement en ligne
    |--------------------------------------------------------------------------
    |
    | « simulation » : page de paiement factice, pour les tests en local uniquement.
    | « paystack »   : paiement réel (carte bancaire, Mobile Money) via Paystack.
    | « cinetpay »   : paiement réel via CinetPay (alternative).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Adresses e-mail des abonnés
    |--------------------------------------------------------------------------
    |
    | Le reçu Paystack et le PDF d'abonnement sont envoyés par e-mail : on exige
    | un domaine qui reçoit du courrier (vérification DNS) et on refuse les boîtes
    | jetables ou de test.
    |
    */

    'email' => [
        'verify_dns' => (bool) env('SUBSCRIPTION_VERIFY_EMAIL_DNS', true),
        'blocked_domains' => [
            'example.com', 'example.org', 'example.net', 'test.com', 'test.fr', 'email.com', 'domain.com',
            'yopmail.com', 'yopmail.fr', 'yopmail.net', 'mailinator.com', 'guerrillamail.com', 'guerrillamail.info',
            'sharklasers.com', 'grr.la', '10minutemail.com', '10minutemail.net', 'tempmail.com', 'temp-mail.org',
            'temp-mail.io', 'tempmail.net', 'tempmailo.com', 'tempail.com', 'trashmail.com', 'trashmail.fr',
            'getnada.com', 'nada.email', 'maildrop.cc', 'dispostable.com', 'throwawaymail.com', 'fakeinbox.com',
            'mailnesia.com', 'mohmal.com', 'emailondeck.com', 'moakt.com', 'mintemail.com', 'spamgourmet.com',
            'jetable.org', 'mail-temp.com', 'minuteinbox.com', 'discard.email', 'mytemp.email', 'mailcatch.com',
            'spambox.us', 'burnermail.io', 'inboxkitten.com', 'emailfake.com', 'crazymailing.com', 'tmpmail.org',
        ],
    ],

    'payment' => [
        // « simulation » doit être activé explicitement (en local) ; il est toujours refusé en production.
        'provider' => env('SUBSCRIPTION_PAYMENT_PROVIDER', 'paystack'),
        'currency' => env('SUBSCRIPTION_CURRENCY', 'XOF'),
        // Fichier de certificats racine (cacert.pem) si PHP n'en a pas : chemin absolu ou relatif au projet.
        'ca_bundle' => env('HTTP_CA_BUNDLE'),
        'paystack' => [
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
            // Moyens proposés sur la page Paystack (selon ceux activés sur votre compte).
            'channels' => array_values(array_filter(explode(',', (string) env('PAYSTACK_CHANNELS', 'card,mobile_money')))),
            // Paystack exprime les montants en sous-unités : ×100, y compris pour le franc CFA.
            'amount_multiplier' => (int) env('PAYSTACK_AMOUNT_MULTIPLIER', 100),
        ],
        'cinetpay' => [
            'api_key' => env('CINETPAY_API_KEY'),
            'site_id' => env('CINETPAY_SITE_ID'),
            'secret_key' => env('CINETPAY_SECRET_KEY'),
            'base_url' => env('CINETPAY_BASE_URL', 'https://api-checkout.cinetpay.com/v2'),
        ],
    ],
];
