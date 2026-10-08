<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Entrepôts ──
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('manager', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Mouvements de Stock ──
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // entree, sortie, transfert, retour, ajustement
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->string('reference', 100)->nullable();
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();
        });

        // ── Alertes de Stock ──
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('min_quantity')->default(0);
            $table->integer('current_quantity')->default(0);
            $table->string('status', 20)->default('active');
            $table->boolean('is_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->unique(['product_id', 'warehouse_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouses');
    }
};
