<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CommercialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categoriesData = ['Papeterie & Impression', 'Textiles Sportifs', 'Équipements Studio', 'Solutions Digitales & IA', 'Accessoires'];
        foreach ($categoriesData as $idx => $catName) {
            Category::firstOrCreate(
                ['slug' => 'cat-'.($idx + 1)],
                ['name' => $catName, 'is_active' => true]
            );
        }

        // 2. Brands
        $brandsData = ['IVOSPHERE Original', 'Canon Pro', 'Nike Pro Côte d\'Ivoire', 'Sony Alpha Series'];
        foreach ($brandsData as $idx => $brandName) {
            Brand::firstOrCreate(
                ['slug' => 'brand-'.($idx + 1)],
                ['name' => $brandName, 'is_active' => true]
            );
        }
    }
}
