<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $domains = [
            [
                'name' => 'IVOSPHERE PRINT',
                'code' => 'PRINT',
                'description' => 'Librairie, Papeterie, Imprimerie et travaux d\'impression',
                'status' => 'actif',
                'color' => 'indigo',
                'icon' => 'printer',
                'is_active' => true,
            ],
            [
                'name' => 'IVOSPHERE SPORT',
                'code' => 'SPORT',
                'description' => 'Vente de matériel sportif, maillots personnalisés et équipements',
                'status' => 'actif',
                'color' => 'emerald',
                'icon' => 'trophy',
                'is_active' => true,
            ],
            [
                'name' => 'IVOSPHERE TECH',
                'code' => 'TECH',
                'description' => 'Prestations digitales, maintenance, agent IA publicitaire et vente matériel IT',
                'status' => 'actif',
                'color' => 'sky',
                'icon' => 'cpu-chip',
                'is_active' => true,
            ],
            [
                'name' => 'IVOSPHERE MEDIA & EVENTS',
                'code' => 'MEDIA',
                'description' => 'Photographie, location de matériel sono/scène et organisation d\'évènements',
                'status' => 'actif',
                'color' => 'purple',
                'icon' => 'camera',
                'is_active' => true,
            ],
            [
                'name' => 'IVOSPHERE ASSURANCE',
                'code' => 'ASSURANCE',
                'description' => 'Partenariat, intermédiation d\'assurance, contrats et commissions',
                'status' => 'actif',
                'color' => 'amber',
                'icon' => 'shield-check',
                'is_active' => true,
            ],
        ];

        foreach ($domains as $domain) {
            Domain::updateOrCreate(['code' => $domain['code']], $domain);
        }
    }
}
