<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commercial = User::where('email', 'commercial@ivosphere.com')->first();
        $finance = User::where('email', 'finance@ivosphere.com')->first();
        $rh = User::where('email', 'rh@ivosphere.com')->first();
        $comm = User::where('email', 'communication@ivosphere.com')->first();
        $admin = User::where('email', 'admin@ivosphere.com')->first();

        $printDomain = Domain::where('code', 'PRINT')->first();
        $sportDomain = Domain::where('code', 'SPORT')->first();
        $techDomain = Domain::where('code', 'TECH')->first();
        $mediaDomain = Domain::where('code', 'MEDIA')->first();
        $assuranceDomain = Domain::where('code', 'ASSURANCE')->first();

        $baseDate = Carbon::create(2026, 9, 23, 9, 0, 0);

        $logs = [
            [
                'user_id' => $commercial?->id,
                'domain_id' => $printDomain?->id,
                'action' => 'creation_devis',
                'description' => 'A créé le devis DEV-2026-0045 pour le client Société ABC',
                'properties' => [
                    'reference' => 'DEV-2026-0045',
                    'client' => 'Société ABC',
                    'montant' => '850 000 FCFA',
                    'domaine' => 'IVOSPHERE PRINT',
                ],
                'ip_address' => '192.168.1.45',
                'created_at' => $baseDate->copy()->setHour(9)->setMinute(42),
            ],
            [
                'user_id' => $finance?->id,
                'domain_id' => $printDomain?->id,
                'action' => 'enregistrement_paiement',
                'description' => 'A enregistré un paiement pour la facture FAC-2026-0089',
                'properties' => [
                    'facture' => 'FAC-2026-0089',
                    'montant' => '250 000 FCFA',
                    'mode' => 'Virement bancaire',
                ],
                'ip_address' => '192.168.1.18',
                'created_at' => $baseDate->copy()->setHour(10)->setMinute(15),
            ],
            [
                'user_id' => $techDomain ? $commercial?->id : null,
                'domain_id' => $techDomain?->id,
                'action' => 'lancement_campagne_ia',
                'description' => 'A configuré le brief de campagne IA publicitaire pour "Pack Promo Rentrée Tech"',
                'properties' => [
                    'brief' => 'Pack Promo Rentrée Tech',
                    'canaux' => ['Facebook', 'LinkedIn', 'Instagram'],
                ],
                'ip_address' => '192.168.1.33',
                'created_at' => $baseDate->copy()->setHour(10)->setMinute(45),
            ],
            [
                'user_id' => $sportDomain ? $commercial?->id : null,
                'domain_id' => $sportDomain?->id,
                'action' => 'commande_maillots',
                'description' => 'A validé la commande CMD-2026-0112 (50 maillots personnalisés - Club ASEC)',
                'properties' => [
                    'commande' => 'CMD-2026-0112',
                    'quantite' => 50,
                    'montant' => '750 000 FCFA',
                ],
                'ip_address' => '192.168.1.45',
                'created_at' => $baseDate->copy()->setHour(11)->setMinute(05),
            ],
            [
                'user_id' => $mediaDomain ? $commercial?->id : null,
                'domain_id' => $mediaDomain?->id,
                'action' => 'reservation_materiel',
                'description' => 'A enregistré la réservation de matériel sono et projecteurs pour Gala VIP',
                'properties' => [
                    'caution' => '300 000 FCFA',
                    'evenement' => 'Gala VIP',
                ],
                'ip_address' => '192.168.1.22',
                'created_at' => $baseDate->copy()->setHour(11)->setMinute(30),
            ],
            [
                'user_id' => $rh?->id,
                'domain_id' => null,
                'action' => 'approbation_conge',
                'description' => 'A validé la demande de congé annuel de M. Bamba Yannick (5 jours)',
                'properties' => [
                    'employe' => 'Bamba Yannick',
                    'jours' => 5,
                    'type' => 'Congé payé',
                ],
                'ip_address' => '192.168.1.12',
                'created_at' => $baseDate->copy()->setHour(11)->setMinute(50),
            ],
            [
                'user_id' => $comm?->id,
                'domain_id' => $assuranceDomain?->id,
                'action' => 'publication_planifiee',
                'description' => 'A planifié la publication hebdomadaire du vendredi pour IVOSPHERE ASSURANCE',
                'properties' => [
                    'theme' => 'Protection Santé Entreprise',
                    'jour' => 'Vendredi',
                ],
                'ip_address' => '192.168.1.55',
                'created_at' => $baseDate->copy()->setHour(12)->setMinute(10),
            ],
            [
                'user_id' => $admin?->id,
                'domain_id' => null,
                'action' => 'connexion_admin',
                'description' => 'Connexion réussie au panneau d\'administration globale',
                'properties' => [
                    'navigateur' => 'Chrome',
                ],
                'ip_address' => '127.0.0.1',
                'created_at' => $baseDate->copy()->setHour(8)->setMinute(30),
            ],
        ];

        foreach ($logs as $log) {
            ActivityLog::create($log);
        }
    }
}
