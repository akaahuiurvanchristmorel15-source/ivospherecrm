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
            $table->dateTime('check_in_at')->nullable()->after('date');
            $table->dateTime('check_out_at')->nullable()->after('check_in_at');
            $table->string('check_out_type', 20)->default('manual')->after('check_out'); // 'manual' | 'automatic'
            $table->decimal('latitude', 10, 7)->nullable()->after('check_out_type');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('accuracy_meters', 8, 2)->nullable()->after('longitude');
            $table->decimal('distance_meters', 8, 2)->nullable()->after('accuracy_meters');
            $table->boolean('location_verified')->default(false)->after('distance_meters');
            $table->string('qr_type', 30)->nullable()->after('location_verified'); // 'premises_dynamic' | 'personal_badge'
            $table->string('device_fingerprint')->nullable()->after('qr_type');
            $table->string('device_info')->nullable()->after('device_fingerprint');
            $table->string('ip_address', 45)->nullable()->after('device_info');
            $table->unsignedInteger('delay_minutes')->default(0)->after('ip_address');
            $table->decimal('punctuality_score', 4, 2)->default(0.00)->after('delay_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_at',
                'check_out_at',
                'check_out_type',
                'latitude',
                'longitude',
                'accuracy_meters',
                'distance_meters',
                'location_verified',
                'qr_type',
                'device_fingerprint',
                'device_info',
                'ip_address',
                'delay_minutes',
                'punctuality_score',
            ]);
        });
    }
};
