<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->id();

            // Horaires de travail & pointage
            $table->string('work_start_time', 10)->default('08:00');
            $table->string('work_end_time', 10)->default('20:00');
            $table->boolean('auto_checkout_enabled')->default(true);
            $table->string('auto_checkout_time', 10)->default('20:00');

            // Géofencing & localisation
            $table->boolean('geofence_enabled')->default(true);
            $table->decimal('office_latitude', 10, 7)->default(5.3599520);
            $table->decimal('office_longitude', 10, 7)->default(-4.0082560);
            $table->unsignedInteger('geofence_radius_meters')->default(100);

            // Sécurité QR Code
            $table->string('dynamic_qr_secret')->nullable();
            $table->unsignedInteger('dynamic_qr_refresh_seconds')->default(60);

            // Planning & règles RH
            $table->unsignedTinyInteger('max_work_days_per_week')->default(6);
            $table->boolean('allow_custom_schedules')->default(true);

            // Évaluations mensuelles & barème
            $table->unsignedTinyInteger('evaluation_day_of_month')->default(5);
            $table->decimal('weight_punctuality', 4, 2)->default(0.33);
            $table->decimal('weight_tasks', 4, 2)->default(0.23);
            $table->decimal('weight_teamwork', 4, 2)->default(0.10);
            $table->decimal('max_evaluation_score', 4, 2)->default(30.00);

            // Tolérances et seuils retards
            $table->unsignedInteger('punctuality_grace_minutes')->default(5);
            $table->unsignedInteger('late_threshold_minutes')->default(15);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
};
