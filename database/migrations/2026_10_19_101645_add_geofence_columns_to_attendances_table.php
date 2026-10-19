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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('check_in_location_id')->nullable()->after('location_verified')->constrained('attendance_locations')->nullOnDelete();
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_in_location_id');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->decimal('check_out_accuracy_meters', 8, 2)->nullable()->after('check_out_longitude');
            $table->decimal('check_out_distance_meters', 8, 2)->nullable()->after('check_out_accuracy_meters');
            $table->foreignId('check_out_location_id')->nullable()->after('check_out_distance_meters')->constrained('attendance_locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('check_in_location_id');
            $table->dropConstrainedForeignId('check_out_location_id');
            $table->dropColumn([
                'check_out_latitude',
                'check_out_longitude',
                'check_out_accuracy_meters',
                'check_out_distance_meters',
            ]);
        });
    }
};
