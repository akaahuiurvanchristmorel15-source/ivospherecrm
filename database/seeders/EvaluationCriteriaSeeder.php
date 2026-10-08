<?php

namespace Database\Seeders;

use App\Models\EvaluationCriterion;
use Illuminate\Database\Seeder;

class EvaluationCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criteria = [
            // 1. Ponctualité — /20 (Automatique : pointage QR)
            [
                'name' => 'Ponctualité',
                'code' => 'punctuality',
                'description' => 'Calculée à partir du pointage QR et des arrivées à l\'heure sans retard.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'automatic',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 1,
            ],
            // 2. Assiduité — /20 (Automatique : présences)
            [
                'name' => 'Assiduité',
                'code' => 'attendance',
                'description' => 'Calculée à partir des présences effectives par rapport aux jours ouvrés programmés.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'automatic',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 2,
            ],
            // 3. Travail réalisé — /20 (Automatique : tâches)
            [
                'name' => 'Travail réalisé',
                'code' => 'tasks',
                'description' => 'Basé sur le volume et le taux de réalisation des tâches quotidiennes validées.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'automatic',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 3,
            ],
            // 4. Qualité du travail — /20 (Manuel RH)
            [
                'name' => 'Qualité du travail',
                'code' => 'quality',
                'description' => 'Évaluée par le Responsable RH : rigueur, excellence du travail rendu et respect des standards.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'manual',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 4,
            ],
            // 5. Travail en équipe — /20 (Manuel RH)
            [
                'name' => 'Travail en équipe',
                'code' => 'teamwork',
                'description' => 'Évalué par le Responsable RH : collaboration harmonieuse, esprit d\'entraide et synergie.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'manual',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 5,
            ],
            // 6. Communication — /20 (Manuel RH)
            [
                'name' => 'Communication',
                'code' => 'communication',
                'description' => 'Évaluée par le Responsable RH : clarté, fluidité des échanges professionnels et transmission des informations.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'manual',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 6,
            ],
            // 7. Discipline — /20 (Manuel RH)
            [
                'name' => 'Discipline',
                'code' => 'discipline',
                'description' => 'Évaluée par le Responsable RH : respect du règlement intérieur, de la hiérarchie et de l\'éthique professionnelle.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'manual',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 7,
            ],
            // 8. Respect des délais — /20 (Automatique : dates de réalisation)
            [
                'name' => 'Respect des délais',
                'code' => 'deadline_respect',
                'description' => 'Basé sur les dates de réalisation et la livraison ponctuelle des tâches et livrables dans les temps.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'automatic',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 8,
            ],
            // 9. Atteinte des objectifs — /20 (Automatique : objectifs enregistrés)
            [
                'name' => 'Atteinte des objectifs',
                'code' => 'objectives',
                'description' => 'Basée sur les objectifs individuels enregistrés et le niveau d\'accomplissement des cibles.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'automatic',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 9,
            ],
            // 10. Compétences acquises — /20 (Manuel RH)
            [
                'name' => 'Compétences acquises',
                'code' => 'skills_acquired',
                'description' => 'Évaluées par le Responsable RH : progression technique, apprentissage de nouveaux outils et autonomie.',
                'weight_percentage' => 10.00,
                'calculation_mode' => 'manual',
                'department' => null,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 10,
            ],
        ];

        $codes = collect($criteria)->pluck('code')->all();

        // Désactiver les anciens critères non inclus dans la nouvelle grille des 10 matières
        EvaluationCriterion::whereNotIn('code', $codes)->update(['is_active' => false]);

        foreach ($criteria as $criterion) {
            EvaluationCriterion::updateOrCreate(
                ['code' => $criterion['code']],
                $criterion
            );
        }
    }
}
