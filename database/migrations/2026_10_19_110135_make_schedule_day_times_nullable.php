<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les jours de repos n'ont pas d'horaires : start_time et end_time doivent accepter NULL.
     */
    public function up(): void
    {
        Schema::table('schedule_days', function (Blueprint $table) {
            $table->string('start_time', 10)->nullable()->default(null)->change();
            $table->string('end_time', 10)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('schedule_days')->whereNull('start_time')->update(['start_time' => '08:00']);
        DB::table('schedule_days')->whereNull('end_time')->update(['end_time' => '20:00']);

        Schema::table('schedule_days', function (Blueprint $table) {
            $table->string('start_time', 10)->nullable(false)->default('08:00')->change();
            $table->string('end_time', 10)->nullable(false)->default('20:00')->change();
        });
    }
};
