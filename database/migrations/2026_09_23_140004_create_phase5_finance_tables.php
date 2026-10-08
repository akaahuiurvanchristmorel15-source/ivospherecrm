<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Caisses ──
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Comptes Bancaires ──
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bank_name');
            $table->string('account_number', 50)->unique();
            $table->string('iban', 50)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Dépenses ──
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 100);
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->string('payment_method', 30)->default('especes');
            $table->string('status', 20)->default('validee');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Recettes ──
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 100);
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->string('payment_method', 30)->default('especes');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Transactions (Mouvement Caisse/Banque) ──
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // depot, retrait, virement, encaissement
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('description');
            $table->string('reference', 100)->nullable();
            $table->date('date');
            $table->timestamps();
        });

        // ── Budgets ──
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 20)->default('actif');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('revenues');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('cash_registers');
    }
};
