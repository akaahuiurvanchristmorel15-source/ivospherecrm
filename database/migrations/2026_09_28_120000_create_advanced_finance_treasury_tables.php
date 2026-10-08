<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Comptes de Trésorerie Unifiés (Caisses, Banques, Mobile Money: Wave, Orange Money)
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('type', 30); // caisse, banque, mobile_money, autre
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('institution_name', 100)->nullable(); // SGBCI, ECOBANK, Wave, Orange Money
            $table->string('account_number', 100)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->string('currency', 10)->default('FCFA');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Transferts entre Comptes de Trésorerie
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('from_account_id')->references('id')->on('financial_accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->references('id')->on('financial_accounts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 30)->default('valide'); // valide, annule
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Sessions Journalières de Caisse (Ouverture, Mouvements, Clôture avec écarts)
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('financial_account_id')->references('id')->on('financial_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_inflow', 15, 2)->default(0);
            $table->decimal('total_outflow', 15, 2)->default(0);
            $table->decimal('theoretical_balance', 15, 2)->default(0);
            $table->decimal('real_balance', 15, 2)->nullable();
            $table->decimal('discrepancy', 15, 2)->nullable()->default(0);
            $table->text('discrepancy_reason')->nullable();
            $table->string('status', 30)->default('ouverte'); // ouverte, fermee
            $table->foreignId('validated_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Dettes Fournisseurs (Factures d'Achats & Suivi des Dettes)
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->date('due_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('non_payee'); // non_payee, partielle, payee
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Rapprochement Bancaire
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('financial_account_id')->references('id')->on('financial_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('statement_date');
            $table->decimal('statement_balance', 15, 2);
            $table->decimal('system_balance', 15, 2);
            $table->decimal('discrepancy', 15, 2)->default(0);
            $table->string('status', 30)->default('en_cours'); // en_cours, rapproche, ecart
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->references('id')->on('bank_reconciliations')->cascadeOnDelete();
            $table->date('date');
            $table->string('description');
            $table->string('reference', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('type', 20)->default('credit'); // credit, debit
            $table->string('status', 30)->default('matched'); // matched, to_verify, unmatched
            $table->unsignedBigInteger('matched_transaction_id')->nullable();
            $table->timestamps();
        });

        // 6. Journal d'Audit Financier Immuable
        Schema::create('financial_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50); // creation, modification, suppression, validation, paiement, cloture, transfert
            $table->string('auditable_type', 100);
            $table->unsignedBigInteger('auditable_id');
            $table->string('auditable_reference', 100)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->decimal('amount_before', 15, 2)->nullable();
            $table->decimal('amount_after', 15, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Clôtures Financières & Verrouillage Mensuel
        Schema::create('financial_period_closings', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->string('period_label', 50);
            $table->foreignId('closed_by')->references('id')->on('users')->cascadeOnDelete();
            $table->dateTime('closed_at');
            $table->boolean('is_locked')->default(true);
            $table->decimal('total_revenues', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0);
            $table->decimal('net_result', 15, 2)->default(0);
            $table->decimal('closing_cash_balance', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unique(['year', 'month']);
            $table->timestamps();
        });

        // 8. Enrichissement des tables existantes avec colonnes nécessaires
        if (Schema::hasTable('expenses') && ! Schema::hasColumn('expenses', 'financial_account_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('financial_account_id')->nullable()->after('cash_register_id')->constrained('financial_accounts')->nullOnDelete();
                $table->string('required_approval_level', 30)->default('responsable')->after('status'); // responsable (<50k), finance (50k-250k), direction (>250k)
                $table->text('rejection_reason')->nullable()->after('notes');
            });
        }

        if (Schema::hasTable('revenues') && ! Schema::hasColumn('revenues', 'financial_account_id')) {
            Schema::table('revenues', function (Blueprint $table) {
                $table->foreignId('financial_account_id')->nullable()->after('cash_register_id')->constrained('financial_accounts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('payments') && ! Schema::hasColumn('payments', 'financial_account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('financial_account_id')->nullable()->after('domain_id')->constrained('financial_accounts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('transactions') && ! Schema::hasColumn('transactions', 'financial_account_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('financial_account_id')->nullable()->after('bank_account_id')->constrained('financial_accounts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'financial_account_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('financial_account_id');
            });
        }
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'financial_account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('financial_account_id');
            });
        }
        if (Schema::hasTable('revenues') && Schema::hasColumn('revenues', 'financial_account_id')) {
            Schema::table('revenues', function (Blueprint $table) {
                $table->dropConstrainedForeignId('financial_account_id');
            });
        }
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'financial_account_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn(['required_approval_level', 'rejection_reason']);
                $table->dropConstrainedForeignId('financial_account_id');
            });
        }

        Schema::dropIfExists('financial_period_closings');
        Schema::dropIfExists('financial_audit_logs');
        Schema::dropIfExists('bank_reconciliation_items');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('cash_register_sessions');
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('financial_accounts');
    }
};
