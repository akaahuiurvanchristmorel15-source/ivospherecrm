<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dépôts de documents à imprimer envoyés par les clients du cyber (via le QR code du comptoir).
     */
    public function up(): void
    {
        Schema::create('cyber_print_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code', 12);
            $table->string('customer_name', 120);
            $table->string('customer_phone', 30)->nullable();
            $table->boolean('color')->default(false);
            $table->boolean('duplex')->default(false);
            $table->unsignedSmallInteger('copies')->default(1);
            $table->string('notes', 500)->nullable();
            $table->string('status', 20)->default('recu');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'created_at']);
        });

        Schema::create('cyber_print_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cyber_print_request_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('extension', 10);
            $table->unsignedInteger('size');
            $table->unsignedInteger('pages')->nullable();
            $table->boolean('pages_estimated')->default(false);
            $table->string('count_method', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cyber_print_files');
        Schema::dropIfExists('cyber_print_requests');
    }
};
