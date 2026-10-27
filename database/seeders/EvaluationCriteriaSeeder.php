<?php

namespace Database\Seeders;

use App\Models\EvaluationCriterion;
use Illuminate\Database\Seeder;

class EvaluationCriteriaSeeder extends Seeder
{
    /**
     * Les 17 critères du référentiel d'évaluation et de notation des membres d'IVOSPHERE,
     * répartis en 4 blocs (A 40 %, B 25 %, C 25 %, D 10 %) et notés de 1 à 5.
     */
    public function run(): void
    {
        $criteria = [
            // BLOC A — Performance et productivité (40 %)
            ['code' => 'A1', 'block' => 'A', 'calculation_mode' => 'automatic', 'name' => 'Atteinte des objectifs',
                'description' => 'Moyenne du mérite quotidien du mois (objectifs du programme et qualité du bilan).',
                'level_1' => 'Mérite moyen < 40 %', 'level_3' => 'Mérite moyen 55 à 69 %', 'level_5' => 'Mérite moyen ≥ 85 %',
                'evaluator_groups' => ['direction']],
            ['code' => 'A2', 'block' => 'A', 'name' => 'Productivité',
                'description' => 'Clients servis, ventes, recettes, devis, contacts, tâches réalisées par rapport au poste et à l\'objectif de 125 000 F par jour.',
                'level_1' => 'Volume nettement insuffisant ; temps perdu', 'level_3' => 'Volume attendu pour le poste', 'level_5' => 'Volume très supérieur ; occupe chaque temps libre utilement',
                'evaluator_groups' => ['direction', 'finances', 'commercial', 'cyber']],
            ['code' => 'A3', 'block' => 'A', 'name' => 'Qualité et exactitude du travail',
                'description' => 'Travail fini et vérifié, caisse juste, chiffres comptés, pas de reprise.',
                'level_1' => 'Erreurs fréquentes, écarts de caisse inexpliqués', 'level_3' => 'Quelques erreurs, corrigées', 'level_5' => 'Zéro erreur, écart 0 F, travail vérifié',
                'evaluator_groups' => ['direction', 'finances', 'cyber', 'technique']],
            ['code' => 'A4', 'block' => 'A', 'name' => 'Bilans et rapports',
                'description' => 'Bilan quotidien complet (5 rubriques), vrai, chiffré, envoyé à l\'heure.',
                'level_1' => 'Bilans absents, en retard ou avec des « ... »', 'level_3' => 'Bilans complets mais parfois en retard', 'level_5' => 'Bilans exemplaires, toujours à l\'heure',
                'evaluator_groups' => ['direction', 'rh']],

            // BLOC B — Compétences (25 %)
            ['code' => 'B1', 'block' => 'B', 'name' => 'Maîtrise technique du poste',
                'description' => 'Savoir-faire du poste : caisse, monétique, impression, machines, vente, devis, système.',
                'level_1' => 'Ne sait pas faire seul les tâches de base', 'level_3' => 'Fait seul les tâches courantes', 'level_5' => 'Maîtrise complète ; peut former un collègue',
                'evaluator_groups' => ['direction', 'finances', 'technique', 'cyber']],
            ['code' => 'B2', 'block' => 'B', 'name' => 'Polyvalence',
                'description' => 'Nombre de postes tenus seul (caisse, monétique, cyber, librairie, grand magasin).',
                'level_1' => 'Un seul poste, et pas toujours', 'level_3' => 'Deux postes', 'level_5' => 'Trois postes et plus ; relais sûr',
                'evaluator_groups' => ['direction', 'rh']],
            ['code' => 'B3', 'block' => 'B', 'name' => 'Outils numériques',
                'description' => 'Système de gestion, paiements Wave et QR code, ordinateur, Google Drive, téléphone de service.',
                'level_1' => 'N\'utilise pas les outils', 'level_3' => 'Utilise les outils courants', 'level_5' => 'Maîtrise et aide les autres',
                'evaluator_groups' => ['direction', 'technique', 'communication']],
            ['code' => 'B4', 'block' => 'B', 'name' => 'Apprentissage et progression',
                'description' => 'Formations suivies, conseils appliqués, progression mesurée d\'un mois à l\'autre.',
                'level_1' => 'Refuse ou ne retient pas', 'level_3' => 'Progresse lentement', 'level_5' => 'Progresse vite et cherche à apprendre',
                'evaluator_groups' => ['direction', 'rh']],

            // BLOC C — Qualités et valeurs professionnelles (25 %)
            ['code' => 'C1', 'block' => 'C', 'name' => 'Ponctualité et assiduité',
                'description' => 'Heures d\'arrivée et de départ, absences, pauses respectées.',
                'level_1' => 'Retards ou absences répétés', 'level_3' => '1 ou 2 retards dans le mois', 'level_5' => 'Aucun retard, aucune absence non justifiée',
                'evaluator_groups' => ['direction', 'rh']],
            ['code' => 'C2', 'block' => 'C', 'name' => 'Intégrité et honnêteté',
                'description' => 'Argent noté à la réception, écarts expliqués, vérité dans les bilans, respect du matériel.',
                'level_1' => 'Argent non noté, faits cachés', 'level_3' => 'Honnête, quelques oublis d\'enregistrement', 'level_5' => 'Transparence totale ; signale lui-même ses erreurs',
                'evaluator_groups' => ['direction', 'finances']],
            ['code' => 'C3', 'block' => 'C', 'name' => 'Service au client et courtoisie',
                'description' => 'Accueil, rapidité, politesse, réponse aux messages en 15 minutes, gestion des clients difficiles.',
                'level_1' => 'Accueil négligé, conflits avec les clients', 'level_3' => 'Accueil correct', 'level_5' => 'Client satisfait et fidélisé ; zéro plainte',
                'evaluator_groups' => ['direction', 'commercial', 'pair']],
            ['code' => 'C4', 'block' => 'C', 'name' => 'Esprit d\'équipe et entraide',
                'description' => 'Relais, aide aux collègues, partage d\'information, gestion des désaccords.',
                'level_1' => 'Travaille seul, crée des tensions', 'level_3' => 'Coopère quand on le lui demande', 'level_5' => 'Aide spontanément ; apaise les tensions',
                'evaluator_groups' => ['direction', 'rh', 'pair']],
            ['code' => 'C5', 'block' => 'C', 'name' => 'Discipline, règlement et tenue',
                'description' => 'Respect du règlement, des consignes et de la hiérarchie ; tenue du jour ; poste propre.',
                'level_1' => 'Manquements répétés ou abandon de poste', 'level_3' => 'Respecte l\'essentiel', 'level_5' => 'Exemplaire ; modèle pour les autres',
                'evaluator_groups' => ['direction', 'rh']],
            ['code' => 'C6', 'block' => 'C', 'name' => 'Communication et relances',
                'description' => 'Informe son responsable et ses collègues, signale tout problème, relance clients, fournisseurs et partenaires.',
                'level_1' => 'Ne prévient pas ; rien n\'est relancé', 'level_3' => 'Communique quand on le lui demande', 'level_5' => 'Informe en temps réel ; chaque relance faite',
                'evaluator_groups' => ['direction', 'communication', 'pair']],

            // BLOC D — Dynamisme et contribution (10 %)
            ['code' => 'D1', 'block' => 'D', 'name' => 'Dynamisme et initiative',
                'description' => 'Énergie, réactivité, propose son aide, agit sans attendre qu\'on le lui dise.',
                'level_1' => 'Passif, attend les ordres', 'level_3' => 'Actif sur ses tâches', 'level_5' => 'Moteur de l\'équipe ; prend des initiatives utiles',
                'evaluator_groups' => ['direction', 'rh', 'pair']],
            ['code' => 'D2', 'block' => 'D', 'name' => 'Idées, projets et innovation',
                'description' => 'Idées du carnet d\'idées, projets rédigés, améliorations proposées.',
                'level_1' => 'Aucune idée proposée', 'level_3' => 'Une idée ou une contribution dans le mois', 'level_5' => 'Plusieurs idées ou un projet rédigé et utile',
                'evaluator_groups' => ['direction', 'communication']],
            ['code' => 'D3', 'block' => 'D', 'name' => 'Sens des responsabilités',
                'description' => 'Tient ses engagements, assume ses erreurs, protège les intérêts de l\'entreprise.',
                'level_1' => 'Promesses non tenues, rejette la faute', 'level_3' => 'Tient la plupart de ses engagements', 'level_5' => 'Fiable à 100 % ; assume et corrige',
                'evaluator_groups' => ['direction', 'rh']],
        ];

        $blockCounts = collect($criteria)->countBy('block');
        $codes = collect($criteria)->pluck('code')->all();

        // Les anciens critères restent en base pour l'historique, mais ne sont plus utilisés
        EvaluationCriterion::whereNotIn('code', $codes)->update(['is_active' => false]);

        foreach ($criteria as $order => $criterion) {
            EvaluationCriterion::updateOrCreate(
                ['code' => $criterion['code']],
                $criterion + [
                    'calculation_mode' => 'manual',
                    'weight_percentage' => round(EvaluationCriterion::BLOCKS[$criterion['block']]['weight'] * 100 / $blockCounts[$criterion['block']], 2),
                    'department' => null,
                    'is_mandatory' => true,
                    'is_active' => true,
                    'order' => $order + 1,
                ]
            );
        }
    }
}
