<?php

use Database\Seeders\EvaluationCriteriaSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rôles qui reçoivent par défaut la permission de noter en tant que responsable de fonction.
     *
     * @var list<string>
     */
    private array $functionRoleSlugs = [
        'administrateur',
        'responsable',
        'responsable_rh',
        'responsable_commercial',
        'responsable_financiere',
        'responsable_communication',
    ];

    /**
     * Référentiel d'évaluation des membres : 17 critères en 4 blocs notés de 1 à 5,
     * fiches par évaluateur (Direction, RH, responsables de fonction, pairs, auto-évaluation),
     * notes de bloc, mois d'essai et contestation sous 48 heures.
     */
    public function up(): void
    {
        Schema::table('evaluation_criteria', function (Blueprint $table) {
            $table->string('block', 1)->nullable()->after('code');
            $table->text('level_1')->nullable()->after('description');
            $table->text('level_3')->nullable()->after('level_1');
            $table->text('level_5')->nullable()->after('level_3');
            $table->json('evaluator_groups')->nullable()->after('level_5');
        });

        Schema::create('evaluation_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('evaluator_group', 20);
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['monthly_evaluation_id', 'evaluator_id', 'evaluator_group'], 'evaluation_sheets_unique');
        });

        Schema::create('evaluation_sheet_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_sheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_criterion_id')->constrained('evaluation_criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_sheet_id', 'evaluation_criterion_id'], 'evaluation_sheet_scores_unique');
        });

        Schema::table('monthly_evaluations', function (Blueprint $table) {
            $table->decimal('block_a_score', 3, 2)->nullable()->after('final_score_20');
            $table->decimal('block_b_score', 3, 2)->nullable()->after('block_a_score');
            $table->decimal('block_c_score', 3, 2)->nullable()->after('block_b_score');
            $table->decimal('block_d_score', 3, 2)->nullable()->after('block_c_score');
            $table->boolean('is_trial')->default(false)->after('month');
            $table->text('contestation')->nullable()->after('employee_signed_at');
            $table->dateTime('contested_at')->nullable()->after('contestation');
            $table->text('contestation_response')->nullable()->after('contested_at');
            $table->dateTime('contestation_answered_at')->nullable()->after('contestation_response');
            $table->foreignId('contestation_answered_by')->nullable()->after('contestation_answered_at')->constrained('users')->nullOnDelete();
        });

        DB::table('monthly_evaluations')->where('year', 2026)->where('month', 10)->update(['is_trial' => true]);

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'evaluations.function'],
            ['name' => 'Noter les membres en tant que responsable de fonction', 'group' => 'rh', 'updated_at' => now(), 'created_at' => now()],
        );

        $permissionId = DB::table('permissions')->where('slug', 'evaluations.function')->value('id');

        foreach (DB::table('roles')->whereIn('slug', $this->functionRoleSlugs)->pluck('id') as $roleId) {
            DB::table('permission_role')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }

        (new EvaluationCriteriaSeeder)->run();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'evaluations.function')->value('id');

        DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();

        Schema::table('monthly_evaluations', function (Blueprint $table) {
            $table->dropForeign(['contestation_answered_by']);
            $table->dropColumn([
                'block_a_score',
                'block_b_score',
                'block_c_score',
                'block_d_score',
                'is_trial',
                'contestation',
                'contested_at',
                'contestation_response',
                'contestation_answered_at',
                'contestation_answered_by',
            ]);
        });

        Schema::dropIfExists('evaluation_sheet_scores');
        Schema::dropIfExists('evaluation_sheets');

        Schema::table('evaluation_criteria', function (Blueprint $table) {
            $table->dropColumn(['block', 'level_1', 'level_3', 'level_5', 'evaluator_groups']);
        });
    }
};
