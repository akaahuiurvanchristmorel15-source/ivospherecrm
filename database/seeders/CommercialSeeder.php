<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CommercialAppointment;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommercialSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $domains = Domain::all();
        $firstDomainId = $domains->first()?->id;

        // 1. Categories & Brands
        $categoriesData = ['Papeterie & Impression', 'Textiles Sportifs', 'Équipements Studio', 'Solutions Digitales & IA', 'Accessoires'];
        $categories = [];
        foreach ($categoriesData as $idx => $catName) {
            $categories[] = Category::firstOrCreate(
                ['slug' => 'cat-'.($idx + 1)],
                ['name' => $catName, 'is_active' => true]
            );
        }

        $brandsData = ['IVOSPHERE Original', 'Canon Pro', 'Nike Pro Côte d\'Ivoire', 'Sony Alpha Series'];
        $brands = [];
        foreach ($brandsData as $idx => $brandName) {
            $brands[] = Brand::firstOrCreate(
                ['slug' => 'brand-'.($idx + 1)],
                ['name' => $brandName, 'is_active' => true]
            );
        }

        // 2. Suppliers
        $suppliers = [];
        for ($i = 1; $i <= 3; $i++) {
            $suppliers[] = Supplier::firstOrCreate(
                ['code' => 'FOU-000'.$i],
                [
                    'name' => 'Fournisseur Ivoire '.$i,
                    'company' => 'Société Grossiste SARL '.$i,
                    'email' => 'contact@fournisseur'.$i.'.ci',
                    'phone' => '+225 0506070'.$i,
                    'status' => 'actif',
                ]
            );
        }

        // 3. Products with Barcode & Max Stock
        $productsSeed = [
            ['name' => 'T-shirt Personnalisé Sublimation HD', 'sku' => 'TSH-SUB-001', 'barcode' => Product::generateEan13(1001), 'price' => 7500, 'cost' => 3500],
            ['name' => 'Bâche Publicitaire Grand Format 440g/m²', 'sku' => 'BAC-GF-002', 'barcode' => Product::generateEan13(1002), 'price' => 15000, 'cost' => 7000],
            ['name' => 'Maillot Officiel Équipe Sport IVOSPHERE', 'sku' => 'SPT-ML-003', 'barcode' => Product::generateEan13(1003), 'price' => 12500, 'cost' => 6000],
            ['name' => 'Ballon de Compétition FIFA Pro Match', 'sku' => 'SPT-BL-004', 'barcode' => Product::generateEan13(1004), 'price' => 25000, 'cost' => 14000],
            ['name' => 'Borne Interactive Tactile 43 Pouces', 'sku' => 'TCH-BRN-005', 'barcode' => Product::generateEan13(1005), 'price' => 750000, 'cost' => 450000],
            ['name' => 'Pack Shooting Photo Studio & Retouche HD', 'sku' => 'MED-SHT-006', 'barcode' => Product::generateEan13(1006), 'price' => 85000, 'cost' => 30000],
        ];

        $products = [];
        foreach ($productsSeed as $idx => $p) {
            $products[] = Product::firstOrCreate(
                ['sku' => $p['sku']],
                [
                    'category_id' => $categories[$idx % count($categories)]->id,
                    'brand_id' => $brands[$idx % count($brands)]->id,
                    'supplier_id' => $suppliers[$idx % count($suppliers)]->id,
                    'domain_id' => $domains->get($idx % max(1, $domains->count()))?->id ?? $firstDomainId,
                    'name' => $p['name'],
                    'barcode' => $p['barcode'],
                    'purchase_price' => $p['cost'],
                    'selling_price' => $p['price'],
                    'tax_rate' => 18,
                    'unit' => 'pièce',
                    'min_stock' => 5,
                    'max_stock' => 100,
                    'is_active' => true,
                ]
            );
        }

        // 4. Customers with Loyalty Levels & WhatsApp
        $customersData = [
            [
                'code' => 'CLI-0001', 'name' => 'Kouamé Jean-Marc', 'type' => 'particulier', 'category' => 'standard',
                'company' => null, 'email' => 'jm.kouame@example.ci', 'phone' => '+225 0707070101', 'whatsapp' => '+2250707070101',
                'loyalty_points' => 350, 'loyalty_level' => 'BRONZE',
            ],
            [
                'code' => 'CLI-0002', 'name' => 'Société Africaine de Distribution (SAD)', 'type' => 'entreprise', 'category' => 'vip',
                'company' => 'Groupe SAD SA', 'nif' => 'CI-1998-004455-X', 'email' => 'direction@sad-ci.com', 'phone' => '+225 2720202020', 'whatsapp' => '+2250708080808',
                'loyalty_points' => 5400, 'loyalty_level' => 'PREMIUM',
            ],
            [
                'code' => 'CLI-0003', 'name' => 'Bamba Salimata', 'type' => 'particulier', 'category' => 'revendeur',
                'company' => 'Boutique Élégance Sport', 'email' => 'salimata.bamba@gmail.com', 'phone' => '+225 0505050505', 'whatsapp' => '+2250505050505',
                'loyalty_points' => 1250, 'loyalty_level' => 'SILVER',
            ],
            [
                'code' => 'CLI-0004', 'name' => 'Ministère du Sport et des Loisirs', 'type' => 'entreprise', 'category' => 'institutionnel',
                'company' => 'Direction des Grands Évènements', 'nif' => 'CI-GOUV-SPORT-01', 'email' => 'contact@sports.gouv.ci', 'phone' => '+225 2520000000', 'whatsapp' => null,
                'loyalty_points' => 3200, 'loyalty_level' => 'GOLD',
            ],
        ];

        $customers = [];
        foreach ($customersData as $cd) {
            $customers[] = Customer::firstOrCreate(
                ['code' => $cd['code']],
                array_merge($cd, [
                    'user_id' => $user->id,
                    'commercial_id' => $user->id,
                    'domain_id' => $firstDomainId,
                    'status' => 'actif',
                    'city' => 'Abidjan',
                    'country' => 'Côte d\'Ivoire',
                    'address' => 'Cocody Angré 8e Tranche',
                ])
            );
        }

        // 5. Prospects across the 7 Kanban Stages (§7)
        $prospectsData = [
            ['name' => 'Clinique Sainte-Marie', 'company' => 'Groupe Santé Plus', 'stage' => 'nouveau', 'prob' => 10, 'val' => 1500000],
            ['name' => 'Fédération Ivoirienne d\'Athlétisme', 'company' => 'FIA Côte d\'Ivoire', 'stage' => 'contacte', 'prob' => 25, 'val' => 3800000],
            ['name' => 'Hôtel Ivoire Palace Grand-Bassam', 'company' => 'Bassam Hospitality', 'stage' => 'interesse', 'prob' => 50, 'val' => 2400000],
            ['name' => 'Groupe Scolaire Les Pépites', 'company' => 'Éducation Élite', 'stage' => 'devis_envoye', 'prob' => 70, 'val' => 4500000],
            ['name' => 'Banque Atlantique CI (DRH)', 'company' => 'Banque Atlantique', 'stage' => 'negociation', 'prob' => 85, 'val' => 8200000],
            ['name' => 'Orange CI — Direction Marketing', 'company' => 'Orange Côte d\'Ivoire', 'stage' => 'gagne', 'prob' => 100, 'val' => 12000000],
            ['name' => 'Garage Moderne Treichville', 'company' => 'GMT SARL', 'stage' => 'perdu', 'prob' => 0, 'val' => 900000],
        ];

        foreach ($prospectsData as $pd) {
            Prospect::firstOrCreate(
                ['name' => $pd['name']],
                [
                    'company' => $pd['company'],
                    'email' => strtolower(str_replace(' ', '', $pd['company'])).'@example.ci',
                    'phone' => '+225 07'.rand(10000000, 99999999),
                    'source' => 'Recommandation / Réseau',
                    'stage' => $pd['stage'],
                    'probability' => $pd['prob'],
                    'estimated_value' => $pd['val'],
                    'status' => in_array($pd['stage'], ['gagne', 'perdu']) ? $pd['stage'] : 'en_cours',
                    'commercial_id' => $user->id,
                    'assigned_to' => $user->id,
                    'domain_id' => $firstDomainId,
                    'next_follow_up' => now()->addDays(rand(1, 10)),
                    'notes' => 'Opportunité commerciale majeure identifiée lors du cycle de vente.',
                ]
            );
        }

        // 6. Promotions (§39)
        Promotion::firstOrCreate(
            ['code' => 'BIENVENUE10'],
            [
                'name' => 'Offre Découverte Nouveaux Clients',
                'type' => 'percent',
                'value' => 10,
                'min_amount' => 50000,
                'is_active' => true,
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(6),
            ]
        );

        Promotion::firstOrCreate(
            ['code' => 'REMISE5000'],
            [
                'name' => 'Réduction Flash Comptoir POS',
                'type' => 'fixed',
                'value' => 5000,
                'min_amount' => 30000,
                'is_active' => true,
            ]
        );

        // 7. Commercial Appointments (§8)
        CommercialAppointment::firstOrCreate(
            ['title' => 'Présentation Catalogue Print & Enseignes'],
            [
                'customer_id' => $customers[1]->id,
                'user_id' => $user->id,
                'date' => now()->addDays(1),
                'time' => '10:00',
                'location' => 'Siège Client — Plateau Immeuble Jeceda',
                'status' => 'planifié',
                'notes' => 'Démo échantillons papier et bâche 440g pour campagne annuelle.',
            ]
        );

        CommercialAppointment::firstOrCreate(
            ['title' => 'Négociation Contrat Équipements Sportifs'],
            [
                'customer_id' => $customers[3]->id,
                'user_id' => $user->id,
                'date' => now()->subDays(2),
                'time' => '14:00',
                'location' => 'Cabinet Ministériel',
                'status' => 'effectué',
                'notes' => 'Contrat validé pour 3 200 000 FCFA. Bon de commande signé remis en séance.',
            ]
        );

        // 8. Quotations, Orders, Invoices & Payments
        $q = Quotation::firstOrCreate(
            ['reference' => 'DEV-2026-0001'],
            [
                'customer_id' => $customers[1]->id,
                'user_id' => $user->id,
                'domain_id' => $firstDomainId,
                'date' => now()->subDays(5),
                'valid_until' => now()->addDays(25),
                'status' => 'envoyé',
                'subtotal' => 150000,
                'tax_amount' => 27000,
                'discount' => 0,
                'total' => 177000,
            ]
        );
        if ($q->items()->count() === 0) {
            $q->items()->create([
                'product_id' => $products[0]->id,
                'description' => $products[0]->name,
                'quantity' => 20,
                'unit_price' => 7500,
                'tax_rate' => 18,
                'tax_amount' => 27000,
                'total' => 177000,
            ]);
        }

        $inv = Invoice::firstOrCreate(
            ['reference' => 'FAC-2026-0001'],
            [
                'customer_id' => $customers[1]->id,
                'user_id' => $user->id,
                'domain_id' => $firstDomainId,
                'type' => 'standard',
                'date' => now()->subDays(3),
                'due_date' => now()->addDays(27),
                'status' => 'partielle',
                'subtotal' => 200000,
                'tax_amount' => 36000,
                'discount' => 0,
                'total' => 236000,
                'paid_amount' => 150000,
            ]
        );
        if ($inv->items()->count() === 0) {
            $inv->items()->create([
                'product_id' => $products[1]->id,
                'description' => $products[1]->name,
                'quantity' => 10,
                'unit_price' => 15000,
                'tax_rate' => 18,
                'tax_amount' => 27000,
                'total' => 177000,
            ]);
        }

        Payment::firstOrCreate(
            ['reference' => 'PAY-2026-0001'],
            [
                'invoice_id' => $inv->id,
                'customer_id' => $inv->customer_id,
                'domain_id' => $firstDomainId,
                'user_id' => $user->id,
                'date' => now()->subDays(2),
                'amount' => 150000,
                'method' => 'virement',
                'status' => 'valide',
                'notes' => 'Acompte de 65% perçu par virement SGBCI',
            ]
        );
    }
}
