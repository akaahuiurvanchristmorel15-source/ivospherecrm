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
        // Enrichir la table customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->string('nif', 50)->nullable()->after('company');
            $table->string('contact_person', 100)->nullable()->after('name');
            $table->string('category', 50)->default('particulier')->after('type'); // particulier, pme, grand_compte, vip
            $table->integer('loyalty_points')->default(0)->after('status');
            $table->string('loyalty_level', 20)->default('BRONZE')->after('loyalty_points'); // BRONZE, SILVER, GOLD, PREMIUM
            $table->foreignId('commercial_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        // Enrichir la table prospects
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('stage', 50)->default('nouveau')->after('status'); // nouveau, contacte, interesse, devis_envoye, negociation, gagne, perdu
            $table->integer('probability')->default(10)->after('estimated_value'); // 0 à 100%
            $table->foreignId('commercial_id')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
        });

        // Enrichir la table products
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode', 100)->nullable()->after('sku');
            $table->integer('max_stock')->nullable()->after('min_stock');
        });

        // Table des rendez-vous commerciaux
        Schema::create('commercial_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prospect_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->string('time', 10)->nullable();
            $table->string('location')->nullable();
            $table->string('status', 30)->default('planifié'); // planifié, effectué, reporté, annulé
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table des codes promo & remises
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('type', 20)->default('percent'); // percent, fixed
            $table->decimal('value', 15, 2);
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('commercial_appointments');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['barcode', 'max_stock']);
        });

        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn(['stage', 'probability', 'commercial_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn([
                'whatsapp',
                'nif',
                'contact_person',
                'category',
                'loyalty_points',
                'loyalty_level',
                'commercial_id',
            ]);
        });
    }
};
