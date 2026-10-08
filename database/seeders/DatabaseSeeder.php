<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DomainSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            ActivityLogSeeder::class,
            EmployeeSeeder::class,
            CommercialSeeder::class,
            StockSeeder::class,
            FinanceSeeder::class,
            DomainModuleSeeder::class,
            HrSettingSeeder::class,
            EvaluationCriteriaSeeder::class,
            FixedAssetSeeder::class,
        ]);
    }
}
