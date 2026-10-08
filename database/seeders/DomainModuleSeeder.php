<?php

namespace Database\Seeders;

use App\Models\InsuranceProduct;
use App\Models\PrintFinishing;
use App\Models\PrintFormat;
use App\Models\PrintSupport;
use Illuminate\Database\Seeder;

class DomainModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Paramétrages initiaux pour le pôle PRINT
        PrintFormat::firstOrCreate(
            ['name' => 'A4'],
            ['width' => 210, 'height' => 297, 'unit' => 'mm', 'is_active' => true]
        );

        PrintSupport::firstOrCreate(
            ['name' => 'Papier couché 300g'],
            ['description' => 'Papier couché de haute qualité', 'price_modifier' => 1.5, 'is_active' => true]
        );

        PrintFinishing::firstOrCreate(
            ['name' => 'Pelliculage mat'],
            ['description' => 'Finition mate', 'price_modifier' => 0.5, 'is_active' => true]
        );

        // 2. Produits de base pour le pôle ASSURANCE
        InsuranceProduct::firstOrCreate(
            ['name' => 'Assurance Auto'],
            [
                'partner' => 'NSIA Assurances',
                'type' => 'auto',
                'status' => 'actif',
                'commission_rate' => 10,
            ]
        );
    }
}
