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
        Schema::create('smart_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // unpaid_invoice, low_stock, contract_expiry, quote_followup, custom
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('moyenne'); // urgente, haute, moyenne, basse
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active'); // active, resolved, dismissed
            $table->string('action_url')->nullable();
            $table->date('due_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event'); // invoice_overdue_7d, stock_below_minimum, quote_unanswered_3d, contract_expiring_30d
            $table->json('conditions')->nullable();
            $table->json('actions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('execution_count')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module'); // expenses, quotations, purchases, leaves
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('custom_workflows')->cascadeOnDelete();
            $table->unsignedInteger('step_order')->default(1);
            $table->string('name');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->unsignedInteger('time_limit_hours')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('custom_workflows')->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->json('history')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('custom_workflows');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('smart_alerts');
    }
};
