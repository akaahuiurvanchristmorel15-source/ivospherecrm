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
        // Idempotente : une exécution précédente interrompue a pu créer une partie des colonnes
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('orders', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('validated_at');
            }
            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('validated_by');
            }
            if (! Schema::hasColumn('orders', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            }
        });

        $existingForeignKeyColumns = collect(Schema::getForeignKeys('orders'))->pluck('columns')->flatten()->all();

        Schema::table('orders', function (Blueprint $table) use ($existingForeignKeyColumns) {
            foreach (['validated_by', 'cancelled_by'] as $column) {
                if (! in_array($column, $existingForeignKeyColumns, true)) {
                    $table->foreign($column)->references('id')->on('users')->nullOnDelete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['validated_at', 'cancelled_at', 'cancellation_reason']);
        });
    }
};
