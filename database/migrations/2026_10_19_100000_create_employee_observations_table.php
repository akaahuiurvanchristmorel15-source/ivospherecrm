<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Observations des responsables sur les collaborateurs (constats positifs, points à améliorer, faits neutres).
     */
    public function up(): void
    {
        Schema::create('employee_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('observed_on');
            $table->string('nature', 20);
            $table->string('category', 30);
            $table->string('title');
            $table->text('content');
            $table->boolean('visible_to_employee')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'observed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_observations');
    }
};
