<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // Création des entrepôts officiels
        $warehousesData = [
            ['name' => 'Entrepôt Général', 'code' => 'ENT-GEN', 'address' => 'Siège Principal Abidjan'],
            ['name' => 'Boutique PRINT', 'code' => 'ENT-PRI', 'address' => 'Atelier Imprimerie Plateau'],
            ['name' => 'Dépôt SPORT', 'code' => 'ENT-SPO', 'address' => 'Boutique Équipement Sportif'],
            ['name' => 'Labo TECH', 'code' => 'ENT-TEC', 'address' => 'Centre R&D Tech & IA'],
            ['name' => 'Studio MEDIA', 'code' => 'ENT-MED', 'address' => 'Parc Événementiel & Studio'],
        ];

        foreach ($warehousesData as $data) {
            Warehouse::firstOrCreate(['code' => $data['code']], $data);
        }

        // Le DatabaseSeeder désactive les événements de modèle : attribuer ici les étiquettes (n° + couleur).
        Warehouse::whereNull('label_number')->orderBy('id')->get()->each(function (Warehouse $warehouse) {
            $labelNumber = Warehouse::nextLabelNumber();
            $warehouse->update([
                'label_number' => $labelNumber,
                'label_color' => $warehouse->label_color ?: Warehouse::defaultLabelColor($labelNumber),
            ]);
        });
    }
}
