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
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('title');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('achieved_amount', 15, 2)->default(0);
            $table->integer('target_sales_count')->nullable();
            $table->integer('achieved_sales_count')->default(0);
            $table->string('period', 30)->default('mensuel'); // mensuel, trimestriel, semestriel, annuel, personnalise
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('commission_rate', 5, 2)->nullable()->default(0); // en pourcentage (ex: 2.5%)
            $table->decimal('bonus_amount', 15, 2)->nullable()->default(0); // prime forfaitaire si objectif atteint
            $table->string('status', 30)->default('en_cours'); // en_cours, atteint, partiel, non_atteint, annule
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
