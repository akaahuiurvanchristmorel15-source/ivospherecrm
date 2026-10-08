<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetRental;
use App\Models\AssetUsage;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FixedAssetSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $employee = Employee::first();
        $supplier = Supplier::first();
        $customer = Customer::first();

        $domainPrint = Domain::where('name', 'like', '%PRINT%')->first() ?? Domain::first();
        $domainSport = Domain::where('name', 'like', '%SPORT%')->first() ?? Domain::first();
        $domainTech = Domain::where('name', 'like', '%TECH%')->first() ?? Domain::first();
        $domainMedia = Domain::where('name', 'like', '%MEDIA%')->orWhere('name', 'like', '%ÉVÈNEMENT%')->orWhere('name', 'like', '%EVENEMENT%')->first() ?? Domain::first();

        // 1. Catégories d'immobilisations
        $catAudio = AssetCategory::updateOrCreate(['code' => 'CAT-AUD'], [
            'name' => 'Matériel Audiovisuel & Événementiel',
            'description' => 'Caméras, régies, sonos, drones, projecteurs et éclairage de scène',
            'default_useful_life_years' => 3,
            'default_depreciation_method' => 'lineaire',
            'color' => '#8B5CF6',
            'icon' => 'camera',
        ]);

        $catPrint = AssetCategory::updateOrCreate(['code' => 'CAT-PRT'], [
            'name' => 'Matériel d\'Impression & Signalétique',
            'description' => 'Traceurs d\'impression grand format, presses numériques, massicots et laminateurs',
            'default_useful_life_years' => 5,
            'default_depreciation_method' => 'lineaire',
            'color' => '#0EA5E9',
            'icon' => 'printer',
        ]);

        $catSport = AssetCategory::updateOrCreate(['code' => 'CAT-SPT'], [
            'name' => 'Équipements & Personnalisation Sport',
            'description' => 'Presses pneumatiques, machines de broderie, tables de découpe textile',
            'default_useful_life_years' => 4,
            'default_depreciation_method' => 'lineaire',
            'color' => '#F43F5E',
            'icon' => 'flame',
        ]);

        $catTech = AssetCategory::updateOrCreate(['code' => 'CAT-INF'], [
            'name' => 'Parc Informatique & Réseau',
            'description' => 'Serveurs de production, stations graphiques Mac/PC, baies de stockage NAS',
            'default_useful_life_years' => 3,
            'default_depreciation_method' => 'lineaire',
            'color' => '#6366F1',
            'icon' => 'server',
        ]);

        // 2. Actif 1 : Appareil photo pro Sony A7 IV (MEDIA & ÉVÈNEMENTS)
        $camera = FixedAsset::updateOrCreate(
            ['code' => 'IMM-MED-001'],
            [
                'name' => 'Boîtier Hybride Sony Alpha 7 IV + Objectif 24-70mm f/2.8 GM',
                'asset_category_id' => $catAudio->id,
                'domain_id' => $domainMedia?->id,
                'description' => 'Appareil photo hybride plein format 33MP pour reportages VIP, shootings et tournages vidéos 4K',
                'brand' => 'Sony',
                'model' => 'ILCE-7M4 + SEL2470GM2',
                'serial_number' => 'SN-SNY-2026-9812A',
                'acquisition_date' => Carbon::parse('2026-01-01'),
                'supplier_id' => $supplier?->id,
                'purchase_price' => 1500000,
                'additional_fees' => 0,
                'acquisition_value' => 1500000,
                'residual_value' => 150000,
                'useful_life_years' => 3,
                'depreciation_method' => 'lineaire',
                'depreciation_start_date' => Carbon::parse('2026-01-01'),
                'location' => 'Studio Médias & Événements - Armoire Sécurisée A1',
                'responsible_employee_id' => $employee?->id,
                'responsible_department' => 'Production Audiovisuelle',
                'status' => 'disponible',
                'condition' => 'tres_bon',
                'is_rental_eligible' => true,
                'rental_price_per_day' => 35000,
                'rental_deposit_amount' => 150000,
            ]
        );

        // Prestations / Usages directs de la caméra (CA généré)
        AssetUsage::updateOrCreate(
            ['fixed_asset_id' => $camera->id, 'title' => 'Prestation Mariage VIP - Captation & Shooting Konan'],
            [
                'customer_id' => $customer?->id,
                'user_id' => $admin?->id,
                'date' => Carbon::parse('2026-02-14'),
                'duration_hours' => 10,
                'revenue_generated' => 500000,
                'notes' => 'Captation photo et teaser vidéo 4K pour mariage prestige.',
            ]
        );

        AssetUsage::updateOrCreate(
            ['fixed_asset_id' => $camera->id, 'title' => 'Couverture Photo & Vidéo Gala BNI Abidjan'],
            [
                'customer_id' => $customer?->id,
                'user_id' => $admin?->id,
                'date' => Carbon::parse('2026-03-05'),
                'duration_hours' => 8,
                'revenue_generated' => 750000,
                'notes' => 'Couverture intégrale soirée d\'affaires et remise des prix.',
            ]
        );

        // Location client de la caméra
        AssetRental::updateOrCreate(
            ['reference' => 'LOC-2026-0001'],
            [
                'fixed_asset_id' => $camera->id,
                'customer_id' => $customer?->id,
                'user_id' => $admin?->id,
                'start_date' => Carbon::parse('2026-03-20'),
                'end_date' => Carbon::parse('2026-03-22'),
                'actual_return_date' => Carbon::parse('2026-03-22'),
                'daily_rate' => 35000,
                'total_days' => 2,
                'total_amount' => 70000,
                'deposit_amount' => 150000,
                'deposit_returned' => true,
                'status' => 'cloture',
                'condition_at_departure' => 'tres_bon',
                'condition_at_return' => 'tres_bon',
                'notes' => 'Location avec flash cobra et 2 cartes SD 128Go.',
            ]
        );

        // 3. Actif 2 : Traceur Grand Format Roland TrueVIS (PRINT)
        $printer = FixedAsset::updateOrCreate(
            ['code' => 'IMM-PRT-001'],
            [
                'name' => 'Traceur d\'Impression & Découpe Roland TrueVIS VG3-540',
                'asset_category_id' => $catPrint->id,
                'domain_id' => $domainPrint?->id,
                'description' => 'Traceur d\'impression éco-solvant laize 137cm avec découpe intégrée haute précision',
                'brand' => 'Roland DG',
                'model' => 'TrueVIS VG3-540',
                'serial_number' => 'RLD-VG3-88203',
                'acquisition_date' => Carbon::parse('2025-01-15'),
                'supplier_id' => $supplier?->id,
                'purchase_price' => 8500000,
                'additional_fees' => 300000,
                'acquisition_value' => 8800000,
                'residual_value' => 500000,
                'useful_life_years' => 5,
                'depreciation_method' => 'lineaire',
                'depreciation_start_date' => Carbon::parse('2025-02-01'),
                'location' => 'Atelier Imprimerie - Zone Numérique',
                'responsible_employee_id' => $employee?->id,
                'responsible_department' => 'Atelier Print',
                'status' => 'en_service',
                'condition' => 'tres_bon',
                'is_rental_eligible' => false,
            ]
        );

        AssetUsage::updateOrCreate(
            ['fixed_asset_id' => $printer->id, 'title' => 'Campagne Nationale Bâches Électorales & Affichage'],
            [
                'customer_id' => $customer?->id,
                'user_id' => $admin?->id,
                'date' => Carbon::parse('2025-06-10'),
                'duration_hours' => 48,
                'revenue_generated' => 4200000,
                'notes' => 'Impression de 350 m² de bâches frontlit 510g.',
            ]
        );

        AssetUsage::updateOrCreate(
            ['fixed_asset_id' => $printer->id, 'title' => 'Habillage Flotte Véhicules Commerciaux Orange'],
            [
                'customer_id' => $customer?->id,
                'user_id' => $admin?->id,
                'date' => Carbon::parse('2025-11-20'),
                'duration_hours' => 24,
                'revenue_generated' => 3100000,
                'notes' => 'Adhésif polymère micro-perforé et covering complet.',
            ]
        );

        AssetMaintenance::updateOrCreate(
            ['reference' => 'MAINT-2025-0001'],
            [
                'fixed_asset_id' => $printer->id,
                'type' => 'preventive',
                'provider_name' => 'Roland Support West Africa',
                'cost' => 350000,
                'maintenance_date' => Carbon::parse('2025-12-10'),
                'description' => 'Remplacement kit dampers & têtes piézoélectriques. Entretien périodique annuel et calibration des buses.',
                'parts_replaced' => 'Kit dampers & Wiper',
                'status' => 'terminee',
            ]
        );

        // 4. Actif 3 : Presse à Chaud Pneumatique (SPORT)
        FixedAsset::updateOrCreate(
            ['code' => 'IMM-SPT-001'],
            [
                'name' => 'Presse Pneumatique Double Plateau Secabo TPD7',
                'asset_category_id' => $catSport->id,
                'domain_id' => $domainSport?->id,
                'description' => 'Presse à chaud 40x50cm pneumatique pour flocage de maillots sportifs et textile haute cadence',
                'brand' => 'Secabo',
                'model' => 'TPD7 Pneumatic',
                'serial_number' => 'SCB-TPD7-4401',
                'acquisition_date' => Carbon::parse('2025-05-10'),
                'supplier_id' => $supplier?->id,
                'purchase_price' => 2200000,
                'additional_fees' => 100000,
                'acquisition_value' => 2300000,
                'residual_value' => 100000,
                'useful_life_years' => 4,
                'depreciation_method' => 'lineaire',
                'depreciation_start_date' => Carbon::parse('2025-06-01'),
                'location' => 'Atelier Sport & Flocage',
                'responsible_employee_id' => $employee?->id,
                'responsible_department' => 'Production Sport',
                'status' => 'en_service',
                'condition' => 'bon_etat',
                'is_rental_eligible' => false,
            ]
        );

        // 5. Actif 4 : Sono Yamaha & Lumières Événementielles (MEDIA) - Éligible Location
        $soundSystem = FixedAsset::updateOrCreate(
            ['code' => 'IMM-MED-002'],
            [
                'name' => 'Système de Sonorisation & Éclairage Concert Yamaha DXR15 + Caissons DXS18XLF',
                'asset_category_id' => $catAudio->id,
                'domain_id' => $domainMedia?->id,
                'description' => 'Pack sonorisation 6000W avec 4 têtes actives, 2 caissons et console numérique TF1',
                'brand' => 'Yamaha Pro Audio',
                'model' => 'DXR15mkII + TF1',
                'serial_number' => 'YMH-DXR-9921B',
                'acquisition_date' => Carbon::parse('2025-08-01'),
                'supplier_id' => $supplier?->id,
                'purchase_price' => 4500000,
                'additional_fees' => 200000,
                'acquisition_value' => 4700000,
                'residual_value' => 300000,
                'useful_life_years' => 4,
                'depreciation_method' => 'lineaire',
                'depreciation_start_date' => Carbon::parse('2025-08-01'),
                'location' => 'Entrepôt Logistique Scène & Son',
                'responsible_employee_id' => $employee?->id,
                'responsible_department' => 'Régie Technique',
                'status' => 'disponible',
                'condition' => 'tres_bon',
                'is_rental_eligible' => true,
                'rental_price_per_day' => 85000,
                'rental_deposit_amount' => 400000,
            ]
        );

        AssetRental::updateOrCreate(
            ['reference' => 'LOC-2026-0002'],
            [
                'fixed_asset_id' => $soundSystem->id,
                'customer_id' => $customer?->id,
                'user_id' => $admin?->id,
                'start_date' => Carbon::parse('2026-04-10'),
                'end_date' => Carbon::parse('2026-04-12'),
                'daily_rate' => 85000,
                'total_days' => 3,
                'total_amount' => 255000,
                'deposit_amount' => 400000,
                'deposit_returned' => false,
                'status' => 'loue',
                'condition_at_departure' => 'tres_bon',
                'notes' => 'Festival des musiques urbaines - Matériel vérifié au départ.',
            ]
        );
    }
}
