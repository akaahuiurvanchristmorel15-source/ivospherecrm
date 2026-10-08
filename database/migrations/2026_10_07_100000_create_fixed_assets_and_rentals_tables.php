<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Catégories d'immobilisations (Matériel de production, Informatique, Audiovisuel, Mobilier, etc.)
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('default_useful_life_years')->default(5);
            $table->string('default_depreciation_method', 30)->default('lineaire'); // lineaire, degressif, non_amortissable
            $table->string('icon', 50)->nullable()->default('cube');
            $table->string('color', 30)->nullable()->default('#0066FF');
            $table->timestamps();
        });

        // 2. Fiche d'Immobilisation / Actif
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // Ex: IMM-PRT-001, IMM-MED-002
            $table->string('name');
            $table->foreignId('asset_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('acquisition_date');
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('additional_fees', 15, 2)->default(0);
            $table->decimal('acquisition_value', 15, 2)->default(0);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->unsignedInteger('useful_life_years')->default(5);
            $table->string('depreciation_method', 30)->default('lineaire'); // lineaire, degressif, non_amortissable
            $table->date('depreciation_start_date')->nullable();
            $table->string('location')->nullable(); // Ex: Studio Cocody, Atelier Angré 8e, Siège Plateau
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('responsible_employee_id')->nullable()->references('id')->on('employees')->nullOnDelete();
            $table->string('responsible_department')->nullable();
            $table->string('status', 30)->default('disponible'); // disponible, reserve, en_location, en_utilisation, en_maintenance, a_reparer, hors_service, vendu, cede
            $table->string('condition', 30)->default('bon_etat'); // neuf, tres_bon_etat, bon_etat, a_reparer, en_maintenance, hors_service
            $table->boolean('is_rental_eligible')->default(false);
            $table->decimal('rental_price_per_day', 15, 2)->default(0);
            $table->decimal('rental_deposit_amount', 15, 2)->default(0);
            $table->string('purchase_document_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->date('disposed_at')->nullable();
            $table->decimal('disposal_price', 15, 2)->nullable();
            $table->string('disposal_reason')->nullable();
            $table->timestamps();
        });

        // 3. Tableau et Historique des Amortissements
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('fiscal_period', 50); // Ex: Année 1 (2026)
            $table->decimal('base_value', 15, 2)->default(0);
            $table->decimal('depreciation_amount', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('book_value', 15, 2)->default(0); // Valeur Nette Comptable (VNC)
            $table->boolean('is_posted')->default(false);
            $table->dateTime('posted_at')->nullable();
            $table->timestamps();
        });

        // 4. Utilisations pour Prestations / Activités Commerciales (Traçabilité CA généré)
        Schema::create('asset_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Ex: Prestation Mariage VIP, Impression Bâches Grand Format
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('duration_hours', 6, 2)->default(1);
            $table->decimal('revenue_generated', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Locations de Matériel (Cycle : Disponible -> Réservé -> Loué -> Retour -> Contrôle -> Disponible)
        Schema::create('asset_rentals', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique(); // Ex: LOC-2026-0001
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_return_date')->nullable();
            $table->decimal('daily_rate', 15, 2)->default(0);
            $table->unsignedInteger('total_days')->default(1);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->boolean('deposit_returned')->default(false);
            $table->string('status', 30)->default('reserve'); // reserve, en_cours, retourne, controle_valide, litige, annule
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('condition_at_departure')->nullable();
            $table->string('condition_at_return')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Maintenance & Réparations du Matériel
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique(); // Ex: MAINT-2026-0001
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('preventive'); // preventive, curative, revision, etalonnage
            $table->date('maintenance_date');
            $table->string('provider_name')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('description');
            $table->string('parts_replaced')->nullable();
            $table->string('status', 30)->default('terminee'); // planifiee, en_cours, terminee
            $table->date('next_maintenance_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_rentals');
        Schema::dropIfExists('asset_usages');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('asset_categories');
    }
};
