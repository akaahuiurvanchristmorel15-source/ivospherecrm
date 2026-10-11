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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('status');
            $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('validated_by');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
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
