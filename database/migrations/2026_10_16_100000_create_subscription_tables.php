<?php

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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('family', 30); // commercial, entreprise
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->json('modules');
            $table->unsignedInteger('price_monthly')->default(0);
            $table->unsignedInteger('price_yearly')->default(0);
            $table->unsignedSmallInteger('max_users')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_main')->default(false);
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('address')->nullable();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, active, suspended, expired
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained();
            $table->string('reference', 40)->unique();
            $table->string('billing_cycle', 20); // monthly, yearly
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('XOF');
            $table->string('status', 20)->default('pending'); // pending, paid, failed, cancelled
            $table->string('payment_provider', 30)->nullable();
            $table->string('payment_transaction_id', 100)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->json('payment_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        foreach (config('subscriptions.plans') as $plan) {
            DB::table('subscription_plans')->insert([
                ...$plan,
                'modules' => json_encode($plan['modules']),
                'is_available' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ivosphere est l'espace principal : toutes les données existantes lui appartiennent.
        DB::table('tenants')->insert([
            'id' => 1,
            'name' => 'IVOSPHERE',
            'slug' => 'ivosphere',
            'is_main' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('subscription_plans');
    }
};
