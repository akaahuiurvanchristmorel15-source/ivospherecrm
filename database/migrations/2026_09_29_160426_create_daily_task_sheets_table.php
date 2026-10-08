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
        Schema::create('daily_task_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('position')->nullable();
            $table->json('tasks');
            $table->text('notes')->nullable();
            $table->string('status')->default('assigne');
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->string('whatsapp_status')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->string('email_status')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_sheets');
    }
};
