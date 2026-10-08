<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('rh:auto-checkout')]
#[Description('Clôture automatiquement la journée de travail à 20h pour tout employé n\'ayant pas pointé son départ manuel.')]
class AutoCheckoutCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $attendanceService): int
    {
        $this->info('Exécution de la clôture automatique des départs RH...');

        $closedCount = $attendanceService->autoCheckoutPending();

        if ($closedCount > 0) {
            $this->info("{$closedCount} présence(s) clôturée(s) automatiquement avec le mode 'automatic'.");
        } else {
            $this->comment('Aucun pointage en attente de clôture pour aujourd\'hui.');
        }

        return self::SUCCESS;
    }
}
