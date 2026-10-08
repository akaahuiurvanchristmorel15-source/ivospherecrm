<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Admin & Sécurité
            ['slug' => 'users.view', 'name' => 'Consulter les utilisateurs', 'group' => 'admin'],
            ['slug' => 'users.create', 'name' => 'Créer des utilisateurs', 'group' => 'admin'],
            ['slug' => 'users.edit', 'name' => 'Modifier les utilisateurs', 'group' => 'admin'],
            ['slug' => 'users.delete', 'name' => 'Supprimer des utilisateurs', 'group' => 'admin'],
            ['slug' => 'roles.manage', 'name' => 'Gérer les rôles et permissions', 'group' => 'admin'],
            ['slug' => 'domains.manage', 'name' => 'Gérer les domaines IVOSPHERE', 'group' => 'admin'],
            ['slug' => 'settings.manage', 'name' => 'Gérer les paramètres système', 'group' => 'admin'],
            ['slug' => 'logs.view', 'name' => 'Consulter le journal d\'activité', 'group' => 'admin'],

            // Pôle RH
            ['slug' => 'employees.view', 'name' => 'Consulter les employés', 'group' => 'rh'],
            ['slug' => 'employees.create', 'name' => 'Créer un employé', 'group' => 'rh'],
            ['slug' => 'employees.edit', 'name' => 'Modifier un employé', 'group' => 'rh'],
            ['slug' => 'employees.delete', 'name' => 'Supprimer un employé', 'group' => 'rh'],
            ['slug' => 'contracts.manage', 'name' => 'Gérer les contrats de travail', 'group' => 'rh'],
            ['slug' => 'leaves.manage', 'name' => 'Gérer les congés et absences', 'group' => 'rh'],
            ['slug' => 'attendance.manage', 'name' => 'Suivre les présences et horaires', 'group' => 'rh'],
            ['slug' => 'trainings.manage', 'name' => 'Gérer les formations et évaluations', 'group' => 'rh'],

            // Pôle Commercial & CRM
            ['slug' => 'customers.view', 'name' => 'Consulter les clients', 'group' => 'commercial'],
            ['slug' => 'customers.create', 'name' => 'Créer des clients', 'group' => 'commercial'],
            ['slug' => 'customers.edit', 'name' => 'Modifier des clients', 'group' => 'commercial'],
            ['slug' => 'customers.delete', 'name' => 'Supprimer des clients', 'group' => 'commercial'],
            ['slug' => 'prospects.manage', 'name' => 'Gérer les prospects et opportunités', 'group' => 'commercial'],
            ['slug' => 'quotations.view', 'name' => 'Consulter les devis', 'group' => 'commercial'],
            ['slug' => 'quotations.create', 'name' => 'Créer des devis', 'group' => 'commercial'],
            ['slug' => 'quotations.edit', 'name' => 'Modifier des devis', 'group' => 'commercial'],
            ['slug' => 'quotations.delete', 'name' => 'Supprimer des devis', 'group' => 'commercial'],
            ['slug' => 'orders.manage', 'name' => 'Gérer les commandes de vente', 'group' => 'commercial'],
            ['slug' => 'sales.reports', 'name' => 'Consulter les statistiques commerciales', 'group' => 'commercial'],
            ['slug' => 'pos.sales', 'name' => 'Effectuer des ventes comptoir et encaissements POS', 'group' => 'commercial'],

            // Pôle Finances
            ['slug' => 'invoices.view', 'name' => 'Consulter les factures', 'group' => 'finance'],
            ['slug' => 'invoices.create', 'name' => 'Créer des factures', 'group' => 'finance'],
            ['slug' => 'invoices.manage', 'name' => 'Gérer les factures clients/fournisseurs', 'group' => 'finance'],
            ['slug' => 'payments.view', 'name' => 'Consulter les paiements', 'group' => 'finance'],
            ['slug' => 'payments.create', 'name' => 'Enregistrer un paiement', 'group' => 'finance'],
            ['slug' => 'payments.manage', 'name' => 'Gérer les transactions de paiement', 'group' => 'finance'],
            ['slug' => 'finance.expenses', 'name' => 'Gérer les dépenses', 'group' => 'finance'],
            ['slug' => 'finance.revenues', 'name' => 'Gérer les revenus', 'group' => 'finance'],
            ['slug' => 'finance.cash', 'name' => 'Gérer les multi-caisses et sessions', 'group' => 'finance'],
            ['slug' => 'finance.bank', 'name' => 'Gérer les comptes bancaires', 'group' => 'finance'],
            ['slug' => 'finance.reports', 'name' => 'Générer les rapports financiers', 'group' => 'finance'],
            ['slug' => 'monetique.transactions', 'name' => 'Gérer les flux et terminaux monétiques (TPE / Mobile)', 'group' => 'finance'],

            // Stocks
            ['slug' => 'stocks.view', 'name' => 'Consulter les stocks', 'group' => 'stock'],
            ['slug' => 'stocks.manage', 'name' => 'Gérer les entrepôts et stocks', 'group' => 'stock'],
            ['slug' => 'stocks.movements', 'name' => 'Effectuer des mouvements de stocks', 'group' => 'stock'],

            // Pôle Communication
            ['slug' => 'communication.campaigns', 'name' => 'Gérer les campagnes de communication', 'group' => 'communication'],
            ['slug' => 'communication.publications', 'name' => 'Gérer les publications réseaux sociaux', 'group' => 'communication'],
            ['slug' => 'communication.calendar', 'name' => 'Gérer le calendrier éditorial', 'group' => 'communication'],

            // Domaines métiers
            ['slug' => 'print.manage', 'name' => 'Gérer les travaux d\'imprimerie et librairie', 'group' => 'domain_print'],
            ['slug' => 'sport.manage', 'name' => 'Gérer les articles de sport et personnalisations', 'group' => 'domain_sport'],
            ['slug' => 'tech.manage', 'name' => 'Gérer les prestations tech et agent IA', 'group' => 'domain_tech'],
            ['slug' => 'tech.interventions', 'name' => 'Réaliser les interventions et maintenances techniques', 'group' => 'domain_tech'],
            ['slug' => 'cyber.services', 'name' => 'Gérer les postes et prestations de l\'espace Cyber', 'group' => 'domain_tech'],
            ['slug' => 'media.manage', 'name' => 'Gérer les shootings, locations et évènements', 'group' => 'domain_media'],
            ['slug' => 'insurance.manage', 'name' => 'Gérer les contrats et commissions assurance', 'group' => 'domain_assurance'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }

        // Define Roles & Their Specific Permissions
        $roles = [
            'administrateur' => [
                'name' => 'Administrateur',
                'description' => 'Contrôle intégral et transversal sur l\'ensemble de la plateforme IVOSPHERE',
                'permissions' => array_keys($permissionModels),
            ],
            'responsable' => [
                'name' => 'Responsable',
                'description' => 'Supervision générale, management d\'équipe, validation opérationnelle, suivi des ventes, caisses et présences',
                'permissions' => [
                    'employees.view', 'leaves.manage', 'attendance.manage', 'trainings.manage',
                    'customers.view', 'customers.create', 'customers.edit', 'prospects.manage',
                    'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete',
                    'orders.manage', 'sales.reports', 'pos.sales',
                    'invoices.view', 'invoices.create', 'invoices.manage',
                    'payments.view', 'payments.create', 'payments.manage',
                    'finance.expenses', 'finance.revenues', 'finance.cash', 'finance.reports',
                    'stocks.view', 'stocks.manage', 'stocks.movements',
                    'communication.campaigns', 'communication.publications',
                    'print.manage', 'sport.manage', 'tech.manage', 'media.manage', 'insurance.manage',
                    'cyber.services', 'monetique.transactions', 'tech.interventions',
                    'logs.view',
                ],
            ],
            'responsable_rh' => [
                'name' => 'Responsable RH',
                'description' => 'Pilotage des ressources humaines, employés, contrats, congés et présences',
                'permissions' => [
                    'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
                    'contracts.manage', 'leaves.manage', 'attendance.manage', 'trainings.manage',
                    'logs.view',
                ],
            ],
            'responsable_commercial' => [
                'name' => 'Responsable Commercial',
                'description' => 'Pilotage de la relation client, prospects, devis, commandes et pipeline CRM',
                'permissions' => [
                    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                    'prospects.manage', 'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete',
                    'orders.manage', 'sales.reports', 'pos.sales', 'invoices.view', 'stocks.view',
                    'print.manage', 'sport.manage', 'tech.manage', 'media.manage', 'insurance.manage',
                    'logs.view',
                ],
            ],
            'responsable_financiere' => [
                'name' => 'Responsable Financière',
                'description' => 'Gestion de la trésorerie, multi-caisses, banques, dépenses, recettes et facturation',
                'permissions' => [
                    'invoices.view', 'invoices.create', 'invoices.manage',
                    'payments.view', 'payments.create', 'payments.manage',
                    'finance.expenses', 'finance.revenues', 'finance.cash', 'finance.bank', 'finance.reports',
                    'monetique.transactions',
                    'quotations.view', 'orders.manage', 'customers.view',
                    'logs.view',
                ],
            ],
            'responsable_communication' => [
                'name' => 'Responsable Communication',
                'description' => 'Gestion des campagnes, publications, calendrier éditorial et réseaux sociaux',
                'permissions' => [
                    'communication.campaigns', 'communication.publications', 'communication.calendar',
                    'tech.manage', 'media.manage',
                    'logs.view',
                ],
            ],
            'agent_commercial_terrain' => [
                'name' => 'Agent commercial terrain',
                'description' => 'Prospection terrain, gestion du portefeuille clients, devis, commandes et suivi des ventes',
                'permissions' => [
                    'customers.view', 'customers.create', 'customers.edit',
                    'prospects.manage',
                    'quotations.view', 'quotations.create', 'quotations.edit',
                    'orders.manage',
                    'sales.reports',
                    'stocks.view',
                    'print.manage', 'sport.manage', 'insurance.manage',
                ],
            ],
            'agent_caissier_vendeur' => [
                'name' => 'Agent caissier et vendeur',
                'description' => 'Tenue de caisse, encaissements, vente au comptoir (POS), facturation directe et paiements',
                'permissions' => [
                    'finance.cash',
                    'pos.sales',
                    'payments.view', 'payments.create', 'payments.manage',
                    'invoices.view', 'invoices.create',
                    'orders.manage',
                    'customers.view', 'customers.create',
                    'stocks.view',
                    'quotations.view',
                    'print.manage', 'sport.manage',
                ],
            ],
            'agent_technique' => [
                'name' => 'Agent technique',
                'description' => 'Réalisation des travaux techniques, infographie, impression (PRINT), maintenance et équipements',
                'permissions' => [
                    'tech.manage',
                    'tech.interventions',
                    'print.manage',
                    'stocks.view', 'stocks.movements',
                    'media.manage',
                    'customers.view',
                ],
            ],
            'agent_monetique' => [
                'name' => 'Agent monétique',
                'description' => 'Gestion des terminaux TPE, transferts d\'argent, recharges, transactions électroniques et réconciliation',
                'permissions' => [
                    'finance.cash',
                    'finance.bank',
                    'monetique.transactions',
                    'payments.view', 'payments.create', 'payments.manage',
                    'invoices.view',
                    'customers.view',
                    'tech.manage',
                ],
            ],
            'agent_cyber' => [
                'name' => 'Agent cyber',
                'description' => 'Gestion de l\'espace cybercafé, postes informatiques, travaux de reprographie, numérisations et encaissements',
                'permissions' => [
                    'cyber.services',
                    'print.manage',
                    'tech.manage',
                    'finance.cash',
                    'pos.sales',
                    'payments.view', 'payments.create',
                    'invoices.view', 'invoices.create',
                    'customers.view', 'customers.create',
                    'stocks.view',
                ],
            ],
        ];

        foreach ($roles as $slug => $data) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]
            );

            $permIds = collect($data['permissions'])
                ->filter(fn ($pSlug) => isset($permissionModels[$pSlug]))
                ->map(fn ($pSlug) => $permissionModels[$pSlug]->id);

            $role->permissions()->sync($permIds);
        }
    }
}
