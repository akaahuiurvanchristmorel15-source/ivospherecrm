<?php

namespace App\Console\Commands;

use App\Models\HrSetting;
use App\Services\EvaluationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('rh:generate-evaluations {--month= : Le mois à évaluer (1-12)} {--year= : L\'année à évaluer}')]
#[Description('Génère et synchronise les évaluations mensuelles normalisées sur 30 pour tous les collaborateurs.')]
class GenerateMonthlyEvaluationsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(EvaluationService $evaluationService): int
    {
        $settings = HrSetting::current();

        // Par défaut : évalue le mois qui vient de se terminer si exécuté au début du mois
        $targetDate = now()->subMonth();
        $month = (int) ($this->option('month') ?: $targetDate->month);
        $year = (int) ($this->option('year') ?: $targetDate->year);

        $this->info("Génération de la campagne d'évaluation pour {$month}/{$year}...");

        $count = $evaluationService->generateAllForMonth($year, $month);

        $this->info("✓ {$count} évaluation(s) mensuelle(s) générée(s) et normalisée(s) sur 30 points.");

        return self::SUCCESS;
    }
}
