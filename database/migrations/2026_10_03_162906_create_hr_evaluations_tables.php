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
        Schema::create('monthly_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('evaluation_date');

            // Métriques de calcul
            $table->unsignedTinyInteger('total_working_days')->default(0);
            $table->decimal('max_possible_points', 6, 2)->default(0.00); // N * 0.66
            $table->decimal('punctuality_points', 6, 2)->default(0.00);
            $table->decimal('tasks_points', 6, 2)->default(0.00);
            $table->decimal('teamwork_points', 6, 2)->default(0.00);
            $table->decimal('raw_score', 6, 2)->default(0.00); // Somme des 3 critères
            $table->decimal('final_score_30', 4, 2)->default(0.00); // Normalisation exacte sur 30

            $table->string('status', 20)->default('en_cours'); // 'en_cours', 'valide', 'cloture'
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('manager_comment')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month']);
        });

        Schema::create('daily_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('monthly_evaluation_id')->nullable()->constrained('monthly_evaluations')->nullOnDelete();
            $table->date('date');
            $table->boolean('is_working_day')->default(true);

            // 1. Ponctualité (max 0.33/jour)
            $table->decimal('punctuality_score', 4, 2)->default(0.00);
            $table->string('punctuality_notes')->nullable();

            // 2. Tâches validées (max 0.23/jour)
            $table->unsignedSmallInteger('tasks_assigned_count')->default(0);
            $table->unsignedSmallInteger('tasks_validated_count')->default(0);
            $table->decimal('tasks_score', 4, 2)->default(0.00);

            // 3. Travail en équipe (0.00 à 0.10/jour)
            $table->decimal('teamwork_score', 4, 2)->default(0.00);
            $table->text('teamwork_comment')->nullable(); // Obligatoire si note faible <= 0.03

            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_evaluation_scores');
        Schema::dropIfExists('monthly_evaluations');
    }
};
