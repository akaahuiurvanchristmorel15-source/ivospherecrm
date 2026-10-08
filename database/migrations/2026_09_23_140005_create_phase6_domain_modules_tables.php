<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════
        // ═══ IVOSPHERE PRINT ═══
        // ══════════════════════════════════════════════════

        Schema::create('print_formats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('unit', 10)->default('cm');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('print_supports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_modifier', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('print_finishings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_modifier', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->foreignId('format_id')->nullable()->constrained('print_formats')->nullOnDelete();
            $table->foreignId('support_id')->nullable()->constrained('print_supports')->nullOnDelete();
            $table->foreignId('finishing_id')->nullable()->constrained('print_finishings')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('file_path')->nullable();
            $table->text('specifications')->nullable();
            $table->string('status', 30)->default('en_attente');
            $table->date('deadline')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════
        // ═══ IVOSPHERE SPORT ═══
        // ══════════════════════════════════════════════════

        Schema::create('sport_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('size', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('number', 10)->nullable();
            $table->string('custom_name', 100)->nullable();
            $table->string('team', 100)->nullable();
            $table->string('logo_path')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 30)->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════
        // ═══ IVOSPHERE TECH ═══
        // ══════════════════════════════════════════════════

        Schema::create('tech_projects', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('en_cours');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->integer('progress')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tech_project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('tech_projects')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('a_faire');
            $table->string('priority', 20)->default('normal');
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('brief')->nullable();
            $table->string('status', 30)->default('brouillon');
            $table->json('channels')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->text('results')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_campaign_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('ai_campaigns')->cascadeOnDelete();
            $table->string('type', 30);
            $table->text('content')->nullable();
            $table->string('media_path')->nullable();
            $table->string('platform', 50)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('status', 30)->default('brouillon');
            $table->json('metrics')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════
        // ═══ IVOSPHERE MEDIA & ÉVÉNEMENTS ═══
        // ══════════════════════════════════════════════════

        Schema::create('photo_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('photographer', 100)->nullable();
            $table->date('date')->nullable();
            $table->string('location')->nullable();
            $table->string('package', 50)->nullable();
            $table->string('status', 30)->default('planifié');
            $table->decimal('price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('photo_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('photo_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->integer('photos_count')->default(0);
            $table->string('folder_path')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('status', 30)->default('en_traitement');
            $table->timestamps();
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category', 50);
            $table->string('serial_number', 50)->nullable()->unique();
            $table->string('condition', 30)->default('neuf');
            $table->decimal('daily_rate', 15, 2)->default(0);
            $table->decimal('value', 15, 2)->default(0);
            $table->string('status', 30)->default('disponible');
            $table->boolean('is_available')->default(true);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('equipment_rentals', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('returned_at')->nullable();
            $table->decimal('daily_rate', 15, 2)->default(0);
            $table->decimal('deposit', 15, 2)->default(0);
            $table->decimal('penalty', 15, 2)->default(0);
            $table->string('condition_before', 30)->nullable();
            $table->string('condition_after', 30)->nullable();
            $table->string('status', 30)->default('en_cours');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 50);
            $table->date('date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->integer('guests_count')->default(0);
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('status', 30)->default('prospect');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('event_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('provider')->nullable();
            $table->string('status', 30)->default('planifié');
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════
        // ═══ IVOSPHERE ASSURANCE ═══
        // ══════════════════════════════════════════════════

        Schema::create('insurance_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('partner');
            $table->string('type', 50);
            $table->text('description')->nullable();
            $table->string('premium_range', 100)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->string('status', 20)->default('actif');
            $table->text('conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained('insurance_products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('partner');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('premium', 15, 2)->default(0);
            $table->string('frequency', 20)->default('annuel');
            $table->string('status', 20)->default('actif');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('insurance_contracts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('rate', 5, 2)->default(0);
            $table->date('date');
            $table->string('status', 20)->default('en_attente');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('status', 30)->default('planifié');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Assurance
        Schema::dropIfExists('insurance_appointments');
        Schema::dropIfExists('insurance_commissions');
        Schema::dropIfExists('insurance_contracts');
        Schema::dropIfExists('insurance_products');
        // Media & Events
        Schema::dropIfExists('event_services');
        Schema::dropIfExists('events');
        Schema::dropIfExists('equipment_rentals');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('photo_galleries');
        Schema::dropIfExists('photo_sessions');
        // Tech
        Schema::dropIfExists('ai_campaign_contents');
        Schema::dropIfExists('ai_campaigns');
        Schema::dropIfExists('tech_project_tasks');
        Schema::dropIfExists('tech_projects');
        // Sport
        Schema::dropIfExists('sport_articles');
        // Print
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('print_finishings');
        Schema::dropIfExists('print_supports');
        Schema::dropIfExists('print_formats');
    }
};
