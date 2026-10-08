<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Employés ──
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code', 30)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('national_id', 50)->nullable();
            $table->date('hire_date')->nullable();
            $table->string('contract_type', 30)->default('CDI');
            $table->decimal('salary', 15, 2)->default(0);
            $table->string('status', 20)->default('actif');
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Contrats Employés ──
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 50)->unique();
            $table->string('type', 30);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('actif');
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        // ── Demandes de Congés ──
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days')->default(1);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('en_attente');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // ── Présences ──
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 20)->default('present');
            $table->text('notes')->nullable();
            $table->unique(['employee_id', 'date']);
            $table->timestamps();
        });

        // ── Documents Employés ──
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 50);
            $table->string('file_path');
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employees');
    }
};
