<?php

namespace Database\Seeders;

use App\Models\AiCampaign;
use App\Models\AiCampaignContent;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Models\Event;
use App\Models\EventService;
use App\Models\InsuranceAppointment;
use App\Models\InsuranceCommission;
use App\Models\InsuranceContract;
use App\Models\InsuranceProduct;
use App\Models\PhotoGallery;
use App\Models\PhotoSession;
use App\Models\PrintFinishing;
use App\Models\PrintFormat;
use App\Models\PrintJob;
use App\Models\PrintSupport;
use App\Models\Product;
use App\Models\SportArticle;
use App\Models\TechProject;
use App\Models\TechProjectTask;
use Illuminate\Database\Seeder;

class DomainModuleSeeder extends Seeder
{
    public function run(): void
    {
        $sportProduct = Product::first();

        // Add sample data for PRINT
        $format = PrintFormat::create(['name' => 'A4', 'width' => 210, 'height' => 297, 'unit' => 'mm', 'is_active' => true]);
        $support = PrintSupport::create(['name' => 'Papier couché 300g', 'description' => 'Papier couché de haute qualité', 'price_modifier' => 1.5, 'is_active' => true]);
        $finishing = PrintFinishing::create(['name' => 'Pelliculage mat', 'description' => 'Finition mate', 'price_modifier' => 0.5, 'is_active' => true]);

        PrintJob::create([
            'reference' => 'PJ-2026-001',
            'customer_id' => 1,
            'domain_id' => 1,
            'user_id' => 1,
            'type' => 'impression',
            'format_id' => $format->id,
            'support_id' => $support->id,
            'finishing_id' => $finishing->id,
            'quantity' => 1000,
            'unit_price' => 50,
            'total' => 50000,
            'status' => 'en_attente',
            'deadline' => now()->addDays(7),
        ]);

        // Add sample data for SPORT
        SportArticle::create([
            'product_id' => $sportProduct ? $sportProduct->id : 1,
            'customer_id' => 1,
            'size' => 'L',
            'color' => 'Rouge',
            'quantity' => 10,
            'unit_price' => 15000,
            'total' => 150000,
            'status' => 'en_attente',
        ]);

        // Add sample data for TECH
        $project = TechProject::create([
            'reference' => 'TP-2026-001',
            'customer_id' => 1,
            'domain_id' => 1,
            'user_id' => 1,
            'name' => 'Site Web Corporate',
            'type' => 'site_web',
            'status' => 'en_cours',
            'budget' => 500000,
            'start_date' => now(),
        ]);

        TechProjectTask::create([
            'project_id' => $project->id,
            'name' => 'Design UI/UX',
            'status' => 'en_cours',
        ]);

        $campaign = AiCampaign::create([
            'reference' => 'AC-2026-001',
            'customer_id' => 1,
            'domain_id' => 1,
            'user_id' => 1,
            'name' => 'Campagne Marketing AI',
            'status' => 'actif',
            'budget' => 200000,
        ]);

        AiCampaignContent::create([
            'campaign_id' => $campaign->id,
            'type' => 'post',
            'status' => 'planifié',
            'platform' => 'facebook',
        ]);

        // Add sample data for MEDIA
        $session = PhotoSession::create([
            'reference' => 'PS-2026-001',
            'customer_id' => 1,
            'domain_id' => 1,
            'user_id' => 1,
            'status' => 'planifié',
            'price' => 100000,
        ]);

        PhotoGallery::create([
            'session_id' => $session->id,
            'name' => 'Galerie Mariage',
            'status' => 'en_traitement',
        ]);

        $equipment = Equipment::create([
            'domain_id' => 1,
            'name' => 'Camera Sony A7III',
            'category' => 'camera',
            'status' => 'disponible',
            'daily_rate' => 25000,
            'is_available' => true,
        ]);

        EquipmentRental::create([
            'reference' => 'ER-2026-001',
            'equipment_id' => $equipment->id,
            'customer_id' => 1,
            'user_id' => 1,
            'status' => 'en_cours',
            'start_date' => now(),
            'end_date' => now()->addDays(3),
            'daily_rate' => 25000,
            'total' => 75000,
        ]);

        $event = Event::create([
            'reference' => 'EV-2026-001',
            'customer_id' => 1,
            'domain_id' => 1,
            'user_id' => 1,
            'name' => 'Conférence Tech',
            'type' => 'conference',
            'status' => 'planification',
            'budget' => 1000000,
        ]);

        EventService::create([
            'event_id' => $event->id,
            'name' => 'Sonorisation',
            'price' => 200000,
        ]);

        // Add sample data for ASSURANCE
        $product = InsuranceProduct::create([
            'name' => 'Assurance Auto',
            'partner' => 'NSIA Assurances',
            'type' => 'auto',
            'status' => 'actif',
            'commission_rate' => 10,
        ]);

        $contract = InsuranceContract::create([
            'reference' => 'IC-2026-001',
            'customer_id' => 1,
            'product_id' => $product->id,
            'user_id' => 1,
            'partner' => 'NSIA Assurances',
            'status' => 'actif',
            'premium' => 150000,
            'start_date' => now(),
        ]);

        InsuranceCommission::create([
            'contract_id' => $contract->id,
            'status' => 'en_attente',
            'amount' => 15000,
            'rate' => 10,
            'date' => now(),
        ]);

        InsuranceAppointment::create([
            'customer_id' => 1,
            'advisor_id' => 1,
            'status' => 'planifié',
            'date' => now()->addDays(2),
        ]);
    }
}
