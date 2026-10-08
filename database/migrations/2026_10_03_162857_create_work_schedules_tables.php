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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('week_start_date');
            $table->string('status', 20)->default('valide'); // 'brouillon', 'valide', 'archive'
            $table->unsignedTinyInteger('total_working_days')->default(6);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'week_start_date']);
        });

        Schema::create('schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_schedule_id')->constrained('work_schedules')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('day_of_week'); // 1 = Lundi, ..., 7 = Dimanche
            $table->boolean('is_working_day')->default(true);
            $table->string('start_time', 10)->default('08:00');
            $table->string('end_time', 10)->default('20:00');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['work_schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_days');
        Schema::dropIfExists('work_schedules');
    }
};
