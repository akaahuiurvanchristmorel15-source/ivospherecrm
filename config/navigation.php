<?php

/*
|--------------------------------------------------------------------------
| Navigation des pôles
|--------------------------------------------------------------------------
|
| Source unique des sous-pages de chaque pôle. Utilisée par la barre de
| navigation contextuelle (x-module-nav) et par le menu mobile du topbar.
| La clé correspond au préfixe du nom de route (ex : "commercial.*").
| "active" est le motif passé à request()->routeIs().
|
*/

return [
    'modules' => [
        'commercial' => [
            'label' => 'Commercial & CRM',
            'items' => [
                ['route' => 'commercial.index', 'label' => 'Vue d\'ensemble', 'active' => 'commercial.index'],
                ['route' => 'commercial.pos.index', 'label' => 'Caisse', 'active' => 'commercial.pos.*'],
                ['route' => 'commercial.cyber.index', 'label' => 'Cyber', 'active' => 'commercial.cyber.*'],
                ['route' => 'commercial.quotations.index', 'label' => 'Devis', 'active' => 'commercial.quotations.*'],
                ['route' => 'commercial.orders.index', 'label' => 'Commandes', 'active' => 'commercial.orders.*'],
                ['route' => 'commercial.invoices.index', 'label' => 'Factures', 'active' => 'commercial.invoices.*'],
                ['route' => 'commercial.payments.index', 'label' => 'Paiements', 'active' => 'commercial.payments.*'],
                ['route' => 'commercial.customers.index', 'label' => 'Clients', 'active' => 'commercial.customers.*'],
                ['route' => 'commercial.prospects.index', 'label' => 'Prospects', 'active' => 'commercial.prospects.*'],
                ['route' => 'commercial.appointments.index', 'label' => 'Rendez-vous', 'active' => 'commercial.appointments.*'],
                ['route' => 'commercial.suppliers.index', 'label' => 'Fournisseurs', 'active' => 'commercial.suppliers.*'],
            ],
        ],
        'finance' => [
            'label' => 'Finance',
            'items' => [
                ['route' => 'finance.index', 'label' => 'Vue d\'ensemble', 'active' => 'finance.index'],
                ['route' => 'finance.transactions.index', 'label' => 'Transactions', 'active' => 'finance.transactions.*'],
                ['route' => 'finance.revenues.index', 'label' => 'Recettes', 'active' => 'finance.revenues.*'],
                ['route' => 'finance.expenses.index', 'label' => 'Dépenses', 'active' => 'finance.expenses.*'],
                ['route' => 'finance.cash-registers.index', 'label' => 'Caisses', 'active' => 'finance.cash-registers.*'],
                ['route' => 'finance.budgets.index', 'label' => 'Budgets', 'active' => 'finance.budgets.*'],
                ['route' => 'finance.assets.index', 'label' => 'Immobilisations', 'active' => ['finance.assets.*', '!finance.assets.rentals.*']],
                ['route' => 'finance.assets.rentals.index', 'label' => 'Locations', 'active' => 'finance.assets.rentals.*'],
            ],
        ],
        'stock' => [
            'label' => 'Stocks',
            'items' => [
                ['route' => 'stock.index', 'label' => 'Vue d\'ensemble', 'active' => 'stock.index'],
                ['route' => 'stock.movements.index', 'label' => 'Mouvements', 'active' => 'stock.movements.*'],
                ['route' => 'stock.warehouses.index', 'label' => 'Entrepôts', 'active' => 'stock.warehouses.*'],
                ['route' => 'stock.alerts.index', 'label' => 'Alertes', 'active' => 'stock.alerts.*'],
            ],
        ],
        'rh' => [
            'label' => 'Ressources Humaines',
            'items' => [
                ['route' => 'rh.index', 'label' => 'Vue d\'ensemble', 'active' => 'rh.index'],
                ['route' => 'rh.portal.index', 'label' => 'Mon espace', 'active' => 'rh.portal.*'],
                ['route' => 'rh.employees.index', 'label' => 'Employés', 'active' => 'rh.employees.*'],
                ['route' => 'rh.attendance.index', 'label' => 'Pointage', 'active' => 'rh.attendance.*'],
                ['route' => 'rh.schedules.index', 'label' => 'Plannings', 'active' => 'rh.schedules.*'],
                ['route' => 'rh.daily-tasks.index', 'label' => 'Tâches', 'active' => 'rh.daily-tasks.*'],
                ['route' => 'rh.leaves.index', 'label' => 'Congés', 'active' => 'rh.leaves.*'],
                ['route' => 'rh.evaluations.index', 'label' => 'Évaluations', 'active' => 'rh.evaluations.*'],
                ['route' => 'rh.goals.index', 'label' => 'Objectifs', 'active' => 'rh.goals.*'],
                ['route' => 'rh.sales-targets.index', 'label' => 'Objectifs ventes', 'active' => 'rh.sales-targets.*'],
                ['route' => 'rh.pips.index', 'label' => 'PIP', 'active' => 'rh.pips.*'],
                ['route' => 'rh.observations.index', 'label' => 'Observations', 'active' => 'rh.observations.*'],
                ['route' => 'rh.settings.index', 'label' => 'Paramètres', 'active' => 'rh.settings.*'],
            ],
        ],
        'print' => [
            'label' => 'Print',
            'items' => [
                ['route' => 'print.index', 'label' => 'Vue d\'ensemble', 'active' => 'print.index'],
                ['route' => 'print.jobs.index', 'label' => 'Travaux', 'active' => 'print.jobs.*'],
            ],
        ],
        'sport' => [
            'label' => 'Sport',
            'items' => [
                ['route' => 'sport.index', 'label' => 'Vue d\'ensemble', 'active' => 'sport.index'],
                ['route' => 'sport.articles.index', 'label' => 'Articles', 'active' => 'sport.articles.*'],
            ],
        ],
        'tech' => [
            'label' => 'Tech',
            'items' => [
                ['route' => 'tech.index', 'label' => 'Vue d\'ensemble', 'active' => 'tech.index'],
                ['route' => 'tech.projects.index', 'label' => 'Projets', 'active' => 'tech.projects.*'],
                ['route' => 'tech.campaigns.index', 'label' => 'Campagnes', 'active' => 'tech.campaigns.*'],
            ],
        ],
        'media' => [
            'label' => 'Média',
            'items' => [
                ['route' => 'media.index', 'label' => 'Vue d\'ensemble', 'active' => 'media.index'],
                ['route' => 'media.sessions.index', 'label' => 'Séances', 'active' => 'media.sessions.*'],
                ['route' => 'media.events.index', 'label' => 'Événements', 'active' => 'media.events.*'],
                ['route' => 'media.equipment.index', 'label' => 'Matériel', 'active' => 'media.equipment.*'],
                ['route' => 'media.rentals.index', 'label' => 'Locations', 'active' => 'media.rentals.*'],
            ],
        ],
        'assurance' => [
            'label' => 'Assurance',
            'items' => [
                ['route' => 'assurance.index', 'label' => 'Vue d\'ensemble', 'active' => 'assurance.index'],
                ['route' => 'assurance.contracts.index', 'label' => 'Contrats', 'active' => 'assurance.contracts.*'],
                ['route' => 'assurance.products.index', 'label' => 'Produits', 'active' => 'assurance.products.*'],
                ['route' => 'assurance.appointments.index', 'label' => 'Rendez-vous', 'active' => 'assurance.appointments.*'],
                ['route' => 'assurance.commissions.index', 'label' => 'Commissions', 'active' => 'assurance.commissions.*'],
            ],
        ],
        'admin' => [
            'label' => 'Administration',
            'items' => [
                ['route' => 'admin.settings.index', 'label' => 'Paramètres', 'active' => 'admin.settings.*'],
                ['route' => 'admin.users.index', 'label' => 'Utilisateurs', 'active' => 'admin.users.*'],
                ['route' => 'admin.roles.index', 'label' => 'Rôles', 'active' => 'admin.roles.*'],
                ['route' => 'admin.domains.index', 'label' => 'Domaines', 'active' => 'admin.domains.*'],
                ['route' => 'admin.logs.index', 'label' => 'Journal d\'audit', 'active' => 'admin.logs.*'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Connaissance de l'assistant IA général
    |--------------------------------------------------------------------------
    |
    | "keywords" : synonymes des pages du menu ci-dessus (clé = nom de route).
    | "pages" : pages et actions hors barre de pôle (création, outils, compte).
    | L'assistant ne propose que les pages autorisées pour l'utilisateur.
    |
    */

    'assistant' => [
        'keywords' => [
            'commercial.index' => 'tableau de bord commercial ventes chiffre affaires agent ivo',
            'commercial.pos.index' => 'caisse pos point de vente vendre vente comptoir encaisser ticket',
            'commercial.cyber.index' => 'cyber impression photocopie scan saisie internet depot',
            'commercial.quotations.index' => 'devis proforma proposition chiffrage cotation',
            'commercial.orders.index' => 'commandes commande clients bon de commande',
            'commercial.invoices.index' => 'factures facture facturation impayes creances',
            'commercial.payments.index' => 'paiements paiement encaissements reglements recus',
            'commercial.customers.index' => 'clients client fiche client fidelite',
            'commercial.prospects.index' => 'prospects prospect pipeline kanban opportunites leads',
            'commercial.appointments.index' => 'rendez vous rdv agenda commercial visite',
            'commercial.suppliers.index' => 'fournisseurs fournisseur achats',
            'finance.index' => 'finance tresorerie tableau de bord financier',
            'finance.transactions.index' => 'transactions operations mouvements bancaires virements',
            'finance.revenues.index' => 'recettes revenus entrees',
            'finance.expenses.index' => 'depenses charges sorties frais',
            'finance.cash-registers.index' => 'caisses caisse session ouverture fermeture',
            'finance.budgets.index' => 'budgets budget previsions',
            'finance.assets.index' => 'immobilisations actifs amortissement materiel',
            'finance.assets.rentals.index' => 'locations location materiel',
            'stock.index' => 'stock stocks inventaire produits quantites',
            'stock.movements.index' => 'mouvements de stock entrees sorties ajustement',
            'stock.warehouses.index' => 'entrepots entrepot depot magasin',
            'stock.alerts.index' => 'alertes stock rupture seuil minimum reapprovisionnement',
            'rh.index' => 'rh ressources humaines personnel',
            'rh.portal.index' => 'mon espace employe badge mes conges',
            'rh.employees.index' => 'employes employe personnel salaries equipe',
            'rh.attendance.index' => 'pointage presence absences retards arrivee depart',
            'rh.schedules.index' => 'plannings planning horaires emploi du temps',
            'rh.daily-tasks.index' => 'taches fiches de taches journalieres',
            'rh.leaves.index' => 'conges conge absence permission vacances',
            'rh.evaluations.index' => 'evaluations evaluation notes performance',
            'rh.goals.index' => 'objectifs individuels',
            'rh.sales-targets.index' => 'objectifs de ventes quotas commerciaux',
            'rh.pips.index' => 'pip plan amelioration performance',
            'rh.observations.index' => 'observations remarques sanctions',
            'rh.settings.index' => 'parametres rh',
            'print.index' => 'print imprimerie',
            'print.jobs.index' => 'travaux impression print jobs commandes imprimerie',
            'sport.index' => 'sport',
            'sport.articles.index' => 'articles de sport equipements sportifs',
            'tech.index' => 'tech technologie',
            'tech.projects.index' => 'projets developpement informatique',
            'tech.campaigns.index' => 'campagnes marketing publicite',
            'media.index' => 'media audiovisuel',
            'media.sessions.index' => 'seances photo shooting studio',
            'media.events.index' => 'evenements couverture',
            'media.equipment.index' => 'materiel camera equipement',
            'media.rentals.index' => 'locations materiel media',
            'assurance.index' => 'assurance',
            'assurance.contracts.index' => 'contrats assurance polices',
            'assurance.products.index' => 'produits assurance offres',
            'assurance.appointments.index' => 'rendez vous assurance',
            'assurance.commissions.index' => 'commissions assurance',
            'admin.settings.index' => 'parametres entreprise configuration logo identite',
            'admin.users.index' => 'utilisateurs comptes acces invitation lien inscription',
            'admin.roles.index' => 'roles permissions droits acces',
            'admin.domains.index' => 'domaines poles activites',
            'admin.logs.index' => 'journal audit historique logs activite',
        ],

        'pages' => [
            ['route' => 'dashboard', 'label' => 'Accueil', 'module' => 'Général', 'keywords' => 'accueil tableau de bord principal'],
            ['route' => 'menu.index', 'label' => 'Menu des applications', 'module' => 'Général', 'keywords' => 'menu applications modules'],
            ['route' => 'my-day', 'label' => 'Ma journée', 'module' => 'Général', 'keywords' => 'ma journee mon activite aujourd hui bilan journalier'],
            ['route' => 'password.edit', 'label' => 'Mon compte', 'module' => 'Général', 'keywords' => 'mon compte profil photo mot de passe double authentification 2fa'],
            ['route' => 'notifications.index', 'label' => 'Notifications', 'module' => 'Général', 'keywords' => 'notifications alertes'],
            ['route' => 'messages.index', 'label' => 'Messagerie interne', 'module' => 'Général', 'keywords' => 'messages messagerie discussion chat canal'],
            ['route' => 'calendar.index', 'label' => 'Calendrier', 'module' => 'Général', 'keywords' => 'calendrier agenda planning evenements'],
            ['route' => 'ged.index', 'label' => 'Documents (GED)', 'module' => 'Général', 'keywords' => 'documents ged fichiers dossiers archives'],
            ['route' => 'support.index', 'label' => 'Support', 'module' => 'Général', 'keywords' => 'support ticket assistance probleme aide'],
            ['route' => 'contracts.index', 'label' => 'Contrats', 'module' => 'Général', 'keywords' => 'contrats contrat signature'],
            ['route' => 'ai.index', 'label' => 'IA Studio', 'module' => 'Général', 'keywords' => 'ia studio intelligence artificielle generer contenu texte'],
            ['route' => 'analytics.index', 'label' => 'Analytics', 'module' => 'Général', 'keywords' => 'analytics statistiques previsions analyses'],
            ['route' => 'command-center.index', 'label' => 'Direction (Command Center)', 'module' => 'Général', 'keywords' => 'direction command center pilotage consolide'],
            ['route' => 'commercial.quotations.create', 'label' => 'Nouveau devis', 'module' => 'Commercial & CRM', 'keywords' => 'creer nouveau devis faire un devis proforma'],
            ['route' => 'commercial.orders.create', 'label' => 'Nouvelle commande', 'module' => 'Commercial & CRM', 'keywords' => 'creer nouvelle commande'],
            ['route' => 'commercial.invoices.create', 'label' => 'Nouvelle facture', 'module' => 'Commercial & CRM', 'keywords' => 'creer nouvelle facture facturer'],
            ['route' => 'commercial.customers.create', 'label' => 'Nouveau client', 'module' => 'Commercial & CRM', 'keywords' => 'creer ajouter nouveau client'],
            ['route' => 'commercial.prospects.create', 'label' => 'Nouveau prospect', 'module' => 'Commercial & CRM', 'keywords' => 'creer ajouter nouveau prospect'],
            ['route' => 'commercial.appointments.create', 'label' => 'Nouveau rendez-vous', 'module' => 'Commercial & CRM', 'keywords' => 'creer planifier nouveau rendez vous rdv'],
            ['route' => 'commercial.suppliers.create', 'label' => 'Nouveau fournisseur', 'module' => 'Commercial & CRM', 'keywords' => 'creer ajouter nouveau fournisseur'],
            ['route' => 'commercial.cyber.qr', 'label' => 'QR code de dépôt cyber', 'module' => 'Commercial & CRM', 'keywords' => 'qr code depot documents impression cyber client'],
            ['route' => 'stock.products.index', 'label' => 'Catalogue produits', 'module' => 'Stocks', 'keywords' => 'catalogue produits articles references prix'],
            ['route' => 'stock.products.create', 'label' => 'Nouveau produit', 'module' => 'Stocks', 'keywords' => 'creer ajouter nouveau produit article'],
            ['route' => 'stock.products.bulk-create', 'label' => 'Ajout de produits en lot', 'module' => 'Stocks', 'keywords' => 'ajouter plusieurs produits en lot masse import'],
            ['route' => 'stock.products.download-catalog', 'label' => 'Télécharger le catalogue PDF', 'module' => 'Stocks', 'keywords' => 'telecharger catalogue pdf imprimer liste produits'],
            ['route' => 'stock.movements.create', 'label' => 'Nouveau mouvement de stock', 'module' => 'Stocks', 'keywords' => 'entree sortie ajuster stock nouveau mouvement reception'],
            ['route' => 'stock.warehouses.create', 'label' => 'Nouvel entrepôt', 'module' => 'Stocks', 'keywords' => 'creer ajouter nouvel entrepot depot'],
            ['route' => 'stock.scanner', 'label' => 'Scanner de stock', 'module' => 'Stocks', 'keywords' => 'scanner code barre qr scan'],
            ['route' => 'finance.expenses.create', 'label' => 'Nouvelle dépense', 'module' => 'Finance', 'keywords' => 'enregistrer ajouter nouvelle depense charge'],
            ['route' => 'finance.revenues.create', 'label' => 'Nouvelle recette', 'module' => 'Finance', 'keywords' => 'enregistrer ajouter nouvelle recette revenu'],
            ['route' => 'finance.budgets.create', 'label' => 'Nouveau budget', 'module' => 'Finance', 'keywords' => 'creer nouveau budget'],
            ['route' => 'finance.cash-registers.create', 'label' => 'Nouvelle caisse', 'module' => 'Finance', 'keywords' => 'creer ajouter nouvelle caisse'],
            ['route' => 'finance.assets.create', 'label' => 'Nouvelle immobilisation', 'module' => 'Finance', 'keywords' => 'ajouter nouvelle immobilisation actif'],
            ['route' => 'rh.employees.create', 'label' => 'Nouvel employé', 'module' => 'Ressources Humaines', 'keywords' => 'creer ajouter nouvel employe recruter embaucher'],
            ['route' => 'rh.leaves.create', 'label' => 'Demande de congé', 'module' => 'Ressources Humaines', 'keywords' => 'demander conge nouvelle demande absence'],
            ['route' => 'rh.schedules.create', 'label' => 'Nouveau planning', 'module' => 'Ressources Humaines', 'keywords' => 'creer nouveau planning horaires'],
            ['route' => 'rh.daily-tasks.create', 'label' => 'Nouvelle fiche de tâches', 'module' => 'Ressources Humaines', 'keywords' => 'creer nouvelle fiche taches'],
            ['route' => 'rh.sales-targets.create', 'label' => 'Nouvel objectif de ventes', 'module' => 'Ressources Humaines', 'keywords' => 'fixer nouvel objectif ventes quota'],
            ['route' => 'print.jobs.create', 'label' => "Nouveau travail d'impression", 'module' => 'Print', 'keywords' => 'creer nouveau travail impression job'],
            ['route' => 'tech.projects.create', 'label' => 'Nouveau projet', 'module' => 'Tech', 'keywords' => 'creer nouveau projet'],
            ['route' => 'tech.campaigns.create', 'label' => 'Nouvelle campagne', 'module' => 'Tech', 'keywords' => 'creer nouvelle campagne marketing'],
            ['route' => 'media.sessions.create', 'label' => 'Nouvelle séance', 'module' => 'Média', 'keywords' => 'creer nouvelle seance photo shooting'],
            ['route' => 'media.events.create', 'label' => 'Nouvel événement', 'module' => 'Média', 'keywords' => 'creer nouvel evenement'],
            ['route' => 'assurance.contracts.create', 'label' => "Nouveau contrat d'assurance", 'module' => 'Assurance', 'keywords' => 'creer nouveau contrat assurance souscrire'],
            ['route' => 'admin.users.create', 'label' => 'Nouvel utilisateur', 'module' => 'Administration', 'keywords' => 'creer ajouter nouvel utilisateur compte'],
            ['route' => 'support.create', 'label' => 'Nouveau ticket de support', 'module' => 'Général', 'keywords' => 'ouvrir nouveau ticket signaler probleme'],
        ],
    ],
];
