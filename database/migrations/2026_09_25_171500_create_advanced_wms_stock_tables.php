<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrichir stock_movements avec motif détaillé et lot
        if (Schema::hasTable('stock_movements') && ! Schema::hasColumn('stock_movements', 'reason_motif')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('reason_motif', 60)->nullable()->after('type'); // achat_fournisseur, retour_client, vente, perte, casse, don, production, utilisation_interne
                $table->string('batch_number', 60)->nullable()->after('reference');
                $table->decimal('unit_cost', 15, 2)->nullable()->after('stock_after');
            });
        }

        // 1. Niveaux de Stock par Entrepôt (Physique, Réservé, Disponible, En commande)
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('physical_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('incoming_quantity')->default(0);
            $table->string('location_aisle', 50)->nullable();
            $table->unique(['warehouse_id', 'product_id']);
            $table->timestamps();
        });

        // 2. Transferts Inter-Entrepôts
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('reason', 150)->nullable();
            $table->string('status', 30)->default('en_attente'); // brouillon, en_attente, valide, expedie, receptionne, annule
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Inventaires Physiques & Écarts
        Schema::create('physical_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 30)->default('en_cours'); // en_cours, valide, annule
            $table->text('notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('physical_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('system_quantity')->default(0);
            $table->integer('real_quantity')->default(0);
            $table->integer('discrepancy')->default(0); // real - system
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 4. Demandes d'Achat & Approvisionnement
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->string('urgency', 30)->default('normale'); // basse, normale, haute, critique
            $table->string('reason')->nullable();
            $table->string('status', 40)->default('en_attente_responsable'); // en_attente_responsable, en_attente_finance, approuve_achat, commande_passee, receptionne, rejete
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Réceptions Fournisseurs & Contrôle Qualité
        Schema::create('supplier_receptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('purchase_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('ordered_quantity');
            $table->integer('received_quantity');
            $table->integer('missing_quantity')->default(0);
            $table->integer('damaged_quantity')->default(0);
            $table->string('batch_number', 60)->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('quality_status', 30)->default('conforme'); // conforme, partiel, non_conforme
            $table->date('received_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Lots, Numéros de Série / IMEI & Dates d'Expiration
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number', 60)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('imei', 60)->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('status', 30)->default('disponible'); // disponible, reserve, vendu, expire, defectueux
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Maintenance des Équipements & Matériel Louable
        Schema::create('equipment_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('title');
            $table->string('type', 30)->default('maintenance'); // maintenance, reparation, controle
            $table->string('technician', 100)->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->date('started_at');
            $table->date('completed_at')->nullable();
            $table->string('status', 30)->default('en_cours'); // en_cours, termine, hors_service
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenances');
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('supplier_receptions');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('physical_inventory_items');
        Schema::dropIfExists('physical_inventories');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('warehouse_stocks');
    }
};
