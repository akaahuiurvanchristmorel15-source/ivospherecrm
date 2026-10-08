<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\LeaveRequest;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $domains = Domain::all();
        $adminUser = User::whereHas('roles', function ($q) {
            $q->where('slug', 'administrateur');
        })->first();

        $departments = ['IT', 'Ressources Humaines', 'Comptabilité', 'Marketing', 'Commercial'];
        $positions = ['Développeur', 'Manager RH', 'Comptable', 'Spécialiste Marketing', 'Commercial Terrain'];

        // Create 10 Employees
        for ($i = 0; $i < 10; $i++) {
            $domain = $domains->random();
            $gender = $faker->randomElement(['M', 'F']);

            $employee = Employee::create([
                'domain_id' => $domain->id,
                'employee_code' => 'EMP'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $faker->firstName($gender == 'M' ? 'male' : 'female'),
                'last_name' => $faker->lastName,
                'email' => "emp{$i}@ivosphere.test",
                'phone' => $faker->phoneNumber,
                'position' => $faker->randomElement($positions),
                'department' => $faker->randomElement($departments),
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
                'gender' => $gender,
                'address' => $faker->address,
                'national_id' => $faker->ean13,
                'hire_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'contract_type' => $faker->randomElement(['CDI', 'CDD', 'Stage']),
                'salary' => $faker->numberBetween(150000, 1500000),
                'status' => $faker->randomElement(['actif', 'actif', 'actif', 'inactif', 'suspendu']),
            ]);

            // Create 2-3 contracts per employee
            $numContracts = rand(2, 3);
            $startDate = Carbon::parse($employee->hire_date);

            for ($c = 0; $c < $numContracts; $c++) {
                $isLast = ($c == $numContracts - 1);
                $endDate = $isLast ? ($employee->status == 'actif' ? null : $startDate->copy()->addMonths(rand(3, 12))) : $startDate->copy()->addMonths(rand(6, 12));

                EmployeeContract::create([
                    'employee_id' => $employee->id,
                    'reference' => 'CONT'.strtoupper($faker->bothify('?????###')),
                    'type' => $c == 0 ? 'CDD' : 'CDI',
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                    'salary' => $employee->salary * (1 - ($numContracts - $c - 1) * 0.1),
                    'description' => 'Contrat de travail standard',
                    'status' => $isLast && $employee->status == 'actif' ? 'actif' : 'terminé',
                ]);

                if ($endDate) {
                    $startDate = $endDate->copy()->addDays(1);
                }
            }

            // Create 5 leave requests
            for ($l = 0; $l < 5; $l++) {
                $startLeave = Carbon::instance($faker->dateTimeBetween('-1 year', '+1 month'));
                $days = rand(1, 15);
                $endLeave = $startLeave->copy()->addDays($days - 1);
                $status = $faker->randomElement(['en_attente', 'approuvé', 'approuvé', 'refusé']);

                LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'type' => $faker->randomElement(['congé_annuel', 'congé_maladie', 'congé_maternité', 'sans_solde', 'autre']),
                    'start_date' => $startLeave->format('Y-m-d'),
                    'end_date' => $endLeave->format('Y-m-d'),
                    'days' => $days,
                    'reason' => $faker->sentence,
                    'status' => $status,
                    'approved_by' => $status == 'approuvé' ? $adminUser->id : null,
                    'approved_at' => $status == 'approuvé' ? $faker->dateTimeBetween('-1 year', 'now') : null,
                    'rejection_reason' => $status == 'refusé' ? $faker->sentence : null,
                ]);
            }

            // Create attendance records for the last 5 working days
            if ($employee->status == 'actif') {
                for ($d = 0; $d < 5; $d++) {
                    $date = Carbon::now()->subDays($d);
                    if ($date->isWeekday()) {
                        Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $date->format('Y-m-d'),
                            'check_in' => $faker->dateTimeBetween($date->format('Y-m-d 07:30:00'), $date->format('Y-m-d 09:30:00'))->format('H:i'),
                            'check_out' => $faker->dateTimeBetween($date->format('Y-m-d 16:30:00'), $date->format('Y-m-d 18:30:00'))->format('H:i'),
                            'status' => $faker->randomElement(['present', 'present', 'present', 'retard', 'absent']),
                            'notes' => $faker->optional(0.2)->sentence,
                        ]);
                    }
                }
            }

            // Seed sample sales target for active employees / commercials
            if ($employee->status === 'actif' && ($employee->department === 'Commercial' || $i < 3)) {
                SalesTarget::create([
                    'employee_id' => $employee->id,
                    'domain_id' => $employee->domain_id,
                    'title' => 'Objectif Commercial '.Carbon::now()->translatedFormat('F Y'),
                    'target_amount' => 5000000,
                    'achieved_amount' => ($i === 0 ? 5200000 : 3850000),
                    'target_sales_count' => 20,
                    'achieved_sales_count' => ($i === 0 ? 21 : 14),
                    'period' => 'mensuel',
                    'start_date' => Carbon::now()->startOfMonth()->toDateString(),
                    'end_date' => Carbon::now()->endOfMonth()->toDateString(),
                    'commission_rate' => 3.5,
                    'bonus_amount' => 100000,
                    'status' => ($i === 0 ? 'atteint' : 'en_cours'),
                    'notes' => 'Quota mensuel d\'activité commerciale avec prime d\'atteinte forfaitaire.',
                ]);
            }
        }
    }
}
