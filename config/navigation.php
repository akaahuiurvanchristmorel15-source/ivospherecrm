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
                ['route' => 'commercial.quotations.index', 'label' => 'Devis', 'active' => 'commercial.quotations.*'],
                ['route' => 'commercial.orders.index', 'label' => 'Commandes', 'active' => 'commercial.orders.*'],
                ['route' => 'commercial.invoices.index', 'label' => 'Factures', 'active' => 'commercial.invoices.*'],
                ['route' => 'commercial.payments.index', 'label' => 'Paiements', 'active' => 'commercial.payments.*'],
                ['route' => 'commercial.customers.index', 'label' => 'Clients', 'active' => 'commercial.customers.*'],
                ['route' => 'commercial.prospects.index', 'label' => 'Prospects', 'active' => 'commercial.prospects.*'],
                ['route' => 'commercial.appointments.index', 'label' => 'Rendez-vous', 'active' => 'commercial.appointments.*'],
                ['route' => 'commercial.promotions.index', 'label' => 'Promotions', 'active' => 'commercial.promotions.*'],
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
];
