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
        // 1. Table de configuration des critères d'évaluation
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->index();
            $table->text('description')->nullable();
            $table->decimal('weight_percentage', 5, 2)->default(10.00); // Ex: 15.00 pour 15%
            $table->string('calculation_mode', 20)->default('manual'); // 'automatic' | 'manual'
            $table->string('department', 50)->nullable(); // null = Tous, ou 'commercial', 'tech', 'print', 'media', 'rh'
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        // 2. Table des scores détaillés par critère par évaluation mensuelle
        Schema::create('monthly_evaluation_criteria_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_evaluation_id')->constrained('monthly_evaluations', indexName: 'eval_crit_scores_eval_id_fk')->cascadeOnDelete();
            $table->foreignId('evaluation_criterion_id')->constrained('evaluation_criteria', indexName: 'eval_crit_scores_crit_id_fk')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0.00); // Note attribuée (ex: 8.5 / 10)
            $table->decimal('max_score', 5, 2)->default(10.00); // Plafond (ex: 10.00)
            $table->decimal('weighted_score', 5, 2)->default(0.00); // Score pondéré calculé
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['monthly_evaluation_id', 'evaluation_criterion_id'], 'monthly_eval_criterion_unique');
        });

        // 3. Extension de la table monthly_evaluations (Workflow, Verrouillage, Signatures, Appréciations)
        Schema::table('monthly_evaluations', function (Blueprint $table) {
            $table->text('strengths')->nullable()->after('manager_comment'); // Points forts
            $table->text('weaknesses')->nullable()->after('strengths'); // Points à améliorer
            $table->text('recommendations')->nullable()->after('weaknesses'); // Recommandations RH
            $table->text('future_goals')->nullable()->after('recommendations'); // Objectifs mois suivant
            $table->string('workflow_step', 30)->default('en_evaluation')->after('status'); // 'brouillon', 'en_evaluation', 'validation_manager', 'valide_rh', 'cloture'
            $table->boolean('is_locked')->default(false)->after('workflow_step');
            $table->dateTime('locked_at')->nullable()->after('is_locked');
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete()->after('locked_at');
            $table->string('manager_signature')->nullable()->after('locked_by');
            $table->dateTime('manager_signed_at')->nullable()->after('manager_signature');
            $table->string('employee_signature')->nullable()->after('manager_signed_at');
            $table->dateTime('employee_signed_at')->nullable()->after('employee_signature');
            $table->decimal('score_progress', 4, 2)->default(0.00)->after('final_score_30'); // Différence vs mois précédent (ex: +1.40)
        });

        // 4. Objectifs individuels des collaborateurs
        Schema::create('employee_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('monthly_evaluation_id')->nullable()->constrained('monthly_evaluations')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('due_date');
            $table->unsignedTinyInteger('progress_pct')->default(0); // 0 à 100%
            $table->string('status', 30)->default('a_faire'); // 'a_faire', 'en_cours', 'atteint', 'non_atteint'
            $table->text('result_notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        // 5. Plans d'Amélioration de la Performance (PIP)
        Schema::create('performance_improvement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('monthly_evaluation_id')->nullable()->constrained('monthly_evaluations')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('problem_identified');
            $table->text('target_objective');
            $table->text('corrective_actions');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('progress_pct')->default(0); // 0 à 100%
            $table->string('status', 30)->default('en_cours'); // 'en_cours', 'satisfaisant', 'non_satisfaisant', 'annule'
            $table->text('final_assessment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_improvement_plans');
        Schema::dropIfExists('employee_goals');

        Schema::table('monthly_evaluations', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn([
                'strengths',
                'weaknesses',
                'recommendations',
                'future_goals',
                'workflow_step',
                'is_locked',
                'locked_at',
                'locked_by',
                'manager_signature',
                'manager_signed_at',
                'employee_signature',
                'employee_signed_at',
                'score_progress',
            ]);
        });

        Schema::dropIfExists('monthly_evaluation_criteria_scores');
        Schema::dropIfExists('evaluation_criteria');
    }
};
