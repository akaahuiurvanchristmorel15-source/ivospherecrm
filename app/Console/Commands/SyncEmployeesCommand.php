<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeSyncService;
use Illuminate\Console\Command;

class SyncEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:sync-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Harmonise les comptes utilisateurs (agents, responsables, direction) avec les fiches employés RH';

    /**
     * Execute the console command.
     */
    public function handle(EmployeeSyncService $syncService): int
    {
        $this->info('Début de la synchronisation Utilisateurs <-> Employés...');

        $users = User::with(['roles', 'domains'])->get();
        $syncedCount = 0;

        foreach ($users as $user) {
            $employee = $syncService->syncUserToEmployee($user);
            $syncedCount++;
            $this->line("✓ Utilisateur {$user->name} ({$user->email}) synchronisé -> Employé [{$employee->employee_code}] {$employee->full_name} ({$employee->position})");
        }

        // Vérification des employés sans user_id mais ayant un email utilisateur existant
        $employeesWithoutUser = Employee::whereNull('user_id')->get();
        foreach ($employeesWithoutUser as $emp) {
            $matchingUser = User::where('email', $emp->email)->first();
            if ($matchingUser) {
                $emp->update(['user_id' => $matchingUser->id]);
                $this->line("✓ Liaison automatique de l'employé [{$emp->employee_code}] avec l'utilisateur ID {$matchingUser->id}");
            }
        }

        $this->info("Synchronisation terminée avec succès ! {$syncedCount} collaborateur(s) vérifié(s).");

        return self::SUCCESS;
    }
}
