<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables dont les données sont séparées par espace (abonné).
     *
     * @var list<string>
     */
    private array $scopedTables = [
        'users', 'domains', 'settings', 'activity_logs',
        'categories', 'brands', 'suppliers', 'products', 'product_variants', 'product_batches',
        'warehouses', 'warehouse_stocks', 'stock_movements', 'stock_alerts', 'stock_transfers',
        'physical_inventories', 'physical_inventory_items', 'purchase_requests', 'supplier_receptions',
        'equipment', 'equipment_maintenances',
        'customers', 'quotations', 'quotation_items', 'orders', 'order_items',
        'invoices', 'invoice_items', 'payments',
    ];

    /**
     * Unicités qui deviennent propres à chaque espace : [table => colonnes].
     *
     * @var array<string, list<string>>
     */
    private array $tenantUniques = [
        'domains' => ['code'],
        'settings' => ['key'],
        'categories' => ['slug'],
        'brands' => ['slug'],
        'suppliers' => ['code'],
        'products' => ['sku'],
        'product_variants' => ['sku'],
        'warehouses' => ['code', 'label_number'],
        'customers' => ['code'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->scopedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Par défaut l'espace principal (Ivosphere) : toute donnée insérée sans espace lui appartient.
                $table->unsignedBigInteger('tenant_id')->default(1)->after('id')->index();
            });
        }

        foreach ($this->tenantUniques as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    $table->dropUnique([$column]);
                    $table->unique(['tenant_id', $column]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tenantUniques as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    $table->dropUnique(['tenant_id', $column]);
                    $table->unique([$column]);
                }
            });
        }

        foreach ($this->scopedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
