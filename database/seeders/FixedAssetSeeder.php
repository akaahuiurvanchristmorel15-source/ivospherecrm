<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class FixedAssetSeeder extends Seeder
{
    public function run(): void
    {
        // Catégories d'immobilisations par défaut
        AssetCategory::updateOrCreate(['code' => 'CAT-AUD'], [
            'name' => 'Matériel Audiovisuel & Événementiel',
            'description' => 'Caméras, régies, sonos, drones, projecteurs et éclairage de scène',
            'default_useful_life_years' => 3,
            'default_depreciation_method' => 'lineaire',
            'color' => '#8B5CF6',
            'icon' => 'camera',
        ]);

        AssetCategory::updateOrCreate(['code' => 'CAT-PRT'], [
            'name' => 'Matériel d\'Impression & Signalétique',
            'description' => 'Traceurs d\'impression grand format, presses numériques, massicots et laminateurs',
            'default_useful_life_years' => 5,
            'default_depreciation_method' => 'lineaire',
            'color' => '#0EA5E9',
            'icon' => 'printer',
        ]);

        AssetCategory::updateOrCreate(['code' => 'CAT-SPT'], [
            'name' => 'Équipements & Personnalisation Sport',
            'description' => 'Presses pneumatiques, machines de broderie, tables de découpe textile',
            'default_useful_life_years' => 4,
            'default_depreciation_method' => 'lineaire',
            'color' => '#F43F5E',
            'icon' => 'flame',
        ]);

        AssetCategory::updateOrCreate(['code' => 'CAT-INF'], [
            'name' => 'Parc Informatique & Réseau',
            'description' => 'Serveurs de production, stations graphiques Mac/PC, baies de stockage NAS',
            'default_useful_life_years' => 3,
            'default_depreciation_method' => 'lineaire',
            'color' => '#6366F1',
            'icon' => 'server',
        ]);
    }
}
