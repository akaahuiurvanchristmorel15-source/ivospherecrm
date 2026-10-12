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
        Schema::create('cyber_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 50)->default('autre');
            $table->string('unit', 30)->default('unité');
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Prestations de base du cyber (prix modifiables depuis la page Cyber)
        $now = now();
        $defaults = [
            ['Impression A4 noir & blanc', 'impression', 'page', 50],
            ['Impression A4 couleur', 'impression', 'page', 200],
            ['Impression A3 noir & blanc', 'impression', 'page', 100],
            ['Impression A3 couleur', 'impression', 'page', 400],
            ['Photocopie A4 noir & blanc', 'photocopie', 'page', 50],
            ['Photocopie A4 couleur', 'photocopie', 'page', 200],
            ['Photo minute (identité, 4 photos)', 'photo', 'planche', 1000],
            ['Tirage photo 10x15', 'photo', 'tirage', 500],
            ['Scan de document', 'bureautique', 'page', 200],
            ['Saisie de texte', 'bureautique', 'page', 500],
            ['Plastification A4', 'finition', 'unité', 500],
            ['Reliure', 'finition', 'unité', 1000],
            ['Internet', 'internet', '30 min', 250],
        ];

        DB::table('cyber_services')->insert(array_map(fn (array $service, int $index) => [
            'name' => $service[0],
            'category' => $service[1],
            'unit' => $service[2],
            'price' => $service[3],
            'is_active' => true,
            'sort_order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $defaults, array_keys($defaults)));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cyber_services');
    }
};
