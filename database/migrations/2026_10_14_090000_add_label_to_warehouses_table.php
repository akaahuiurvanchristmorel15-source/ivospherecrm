<?php

use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->unsignedSmallInteger('label_number')->nullable()->unique()->after('code');
            $table->string('label_color', 7)->nullable()->after('label_number');
        });

        DB::table('warehouses')->orderBy('id')->pluck('id')->each(function (int $id, int $index) {
            DB::table('warehouses')->where('id', $id)->update([
                'label_number' => $index + 1,
                'label_color' => Warehouse::defaultLabelColor($index + 1),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique(['label_number']);
            $table->dropColumn(['label_number', 'label_color']);
        });
    }
};
