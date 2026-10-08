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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('type'); // employe, client, fournisseur, partenaire, location, prestation, assurance
            $table->string('party_name');
            $table->string('party_type')->nullable(); // customer, supplier, employee, partner
            $table->unsignedBigInteger('party_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('FCFA');
            $table->string('status')->default('actif'); // brouillon, actif, expire, resilie
            $table->string('document_path')->nullable();
            $table->json('alert_days')->nullable(); // e.g. [90, 60, 30, 7]
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->cascadeOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->boolean('is_vault')->default(false);
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('version')->default('1.0');
            $table->json('tags')->nullable();
            $table->boolean('is_vault')->default(false);
            $table->string('status')->default('active');
            $table->unsignedInteger('downloads_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_folders');
        Schema::dropIfExists('contracts');
    }
};
