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
        Schema::table('monthly_evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_evaluations', 'final_score_20')) {
                $table->decimal('final_score_20', 4, 2)->default(0.00)->after('final_score_30');
            }
            if (! Schema::hasColumn('monthly_evaluations', 'appreciation')) {
                $table->string('appreciation', 50)->nullable()->after('final_score_20');
            }
        });

        Schema::table('monthly_evaluation_criteria_scores', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_evaluation_criteria_scores', 'justification')) {
                $table->text('justification')->nullable()->after('comments');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_evaluation_criteria_scores', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_evaluation_criteria_scores', 'justification')) {
                $table->dropColumn('justification');
            }
        });

        Schema::table('monthly_evaluations', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('monthly_evaluations', 'appreciation')) {
                $columnsToDrop[] = 'appreciation';
            }
            if (Schema::hasColumn('monthly_evaluations', 'final_score_20')) {
                $columnsToDrop[] = 'final_score_20';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
