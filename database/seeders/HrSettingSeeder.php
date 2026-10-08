<?php

namespace Database\Seeders;

use App\Models\HrSetting;
use Illuminate\Database\Seeder;

class HrSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HrSetting::firstOrCreate([], [
            'work_start_time' => '08:00',
            'work_end_time' => '20:00',
            'auto_checkout_enabled' => true,
            'auto_checkout_time' => '20:00',
            'geofence_enabled' => true,
            'office_latitude' => 5.3599520, // Siège IVOSPHERE Abidjan
            'office_longitude' => -4.0082560,
            'geofence_radius_meters' => 100, // Rayon autorisé de 100 mètres
            'dynamic_qr_secret' => bin2hex(random_bytes(16)),
            'dynamic_qr_refresh_seconds' => 60,
            'max_work_days_per_week' => 6,
            'allow_custom_schedules' => true,
            'evaluation_day_of_month' => 5,
            'weight_punctuality' => 0.33,
            'weight_tasks' => 0.23,
            'weight_teamwork' => 0.10,
            'max_evaluation_score' => 30.00,
            'punctuality_grace_minutes' => 5,
            'late_threshold_minutes' => 15,
        ]);
    }
}
