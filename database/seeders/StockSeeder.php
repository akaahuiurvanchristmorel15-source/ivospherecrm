<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentMaintenance;
use App\Models\PhysicalInventory;
use App\Models\PhysicalInventoryItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseRequest;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\SupplierReception;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            $admin = User::factory()->create();
        }

        // Création des entrepôts
        $warehousesData = [
            ['name' => 'Entrepôt Général', 'code' => 'ENT-GEN', 'address' => 'Siège Principal Abidjan'],
            ['name' => 'Boutique PRINT', 'code' => 'ENT-PRI', 'address' => 'Atelier Imprimerie Plateau'],
            ['name' => 'Dépôt SPORT', 'code' => 'ENT-SPO', 'address' => 'Boutique Équipement Sportif'],
            ['name' => 'Labo TECH', 'code' => 'ENT-TEC', 'address' => 'Centre R&D Tech & IA'],
            ['name' => 'Studio MEDIA', 'code' => 'ENT-MED', 'address' => 'Parc Événementiel & Studio'],
        ];

        $warehouses = [];
        foreach ($warehousesData as $data) {
            $warehouses[] = Warehouse::firstOrCreate(['code' => $data['code']], $data);
        }

        $products = Product::take(10)->get();
        $supplier = Supplier::first();

        if ($products->count() > 0) {
            foreach ($products as $index => $product) {
                $initialQty = ($index === 0) ? 125 : rand(40, 220);

                // 1. Entrée initiale
                StockService::processMovement([
                    'warehouse_id' => $warehouses[0]->id,
                    'product_id' => $product->id,
                    'type' => 'entree',
                    'reason_motif' => 'achat_fournisseur',
                    'quantity' => $initialQty,
                    'user_id' => $admin->id,
                    'date' => now()->subDays(rand(15, 45)),
                    'reference' => 'ENT-2026-00'.($index + 1),
                    'notes' => 'Approvisionnement initial en stock central',
                ]);

                // 2. Sortie
                StockService::processMovement([
                    'warehouse_id' => $warehouses[0]->id,
                    'product_id' => $product->id,
                    'type' => 'sortie',
                    'reason_motif' => 'vente',
                    'quantity' => rand(5, 15),
                    'user_id' => $admin->id,
                    'date' => now()->subDays(rand(1, 10)),
                    'reference' => 'SOR-2026-00'.($index + 1),
                    'notes' => 'Sortie pour commande client validée',
                ]);

                // 3. Transfert vers un pôle métier
                $destWarehouse = $warehouses[($index % 4) + 1];
                $trfQty = rand(10, 25);

                StockService::processMovement([
                    'warehouse_id' => $warehouses[0]->id,
                    'product_id' => $product->id,
                    'destination_warehouse_id' => $destWarehouse->id,
                    'type' => 'transfert',
                    'reason_motif' => 'transfert_sortant',
                    'quantity' => $trfQty,
                    'user_id' => $admin->id,
                    'date' => now()->subDays(rand(1, 5)),
                    'reference' => 'TRF-2026-00'.($index + 1),
                    'notes' => 'Dotation inter-entrepôts',
                ]);

                // Enrichir WarehouseStock avec réservé et en commande
                WarehouseStock::where('warehouse_id', $warehouses[0]->id)
                    ->where('product_id', $product->id)
                    ->update([
                        'reserved_quantity' => rand(2, 12),
                        'incoming_quantity' => ($index % 2 === 0) ? 50 : 0,
                        'location_aisle' => 'Allée '.chr(65 + ($index % 4)).'-0'.(($index % 5) + 1),
                    ]);

                // Créer enregistrement dans stock_transfers
                if ($index < 4) {
                    StockTransfer::create([
                        'reference' => 'TRF-2026-0'.($index + 10),
                        'product_id' => $product->id,
                        'source_warehouse_id' => $warehouses[0]->id,
                        'destination_warehouse_id' => $destWarehouse->id,
                        'user_id' => $admin->id,
                        'quantity' => $trfQty,
                        'reason' => 'Réassort boutique pôle '.$destWarehouse->name,
                        'status' => $index === 0 ? 'en_attente' : 'receptionne',
                        'shipped_at' => now()->subDays(2),
                        'received_at' => $index === 0 ? null : now()->subDay(),
                    ]);
                }

                // Lots, N° Série & Dates d'expiration
                if ($index < 5) {
                    ProductBatch::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouses[0]->id,
                        'batch_number' => 'LOT-2026-0'.($index + 1),
                        'serial_number' => 'SN-IVO-9900'.($index + 1),
                        'imei' => $index === 2 ? '35890109283746'.($index + 1) : null,
                        'manufacturing_date' => now()->subMonths(4),
                        'expiration_date' => now()->addDays(($index + 1) * 20), // 20j, 40j, 60j, 80j...
                        'warranty_months' => 12,
                        'quantity' => 25,
                        'status' => 'disponible',
                    ]);
                }
            }

            // 4. Inventaire Physique de démonstration
            $inv = PhysicalInventory::create([
                'reference' => 'INV-2026-001',
                'warehouse_id' => $warehouses[0]->id,
                'user_id' => $admin->id,
                'date' => now()->toDateString(),
                'status' => 'en_cours',
                'notes' => 'Inventaire tournant mensuel du dépôt central',
            ]);

            foreach ($products->take(4) as $idx => $prod) {
                $sysQty = StockService::getStock($prod->id, $warehouses[0]->id);
                $diff = ($idx === 1) ? -2 : (($idx === 2) ? 1 : 0);
                PhysicalInventoryItem::create([
                    'physical_inventory_id' => $inv->id,
                    'product_id' => $prod->id,
                    'system_quantity' => $sysQty,
                    'real_quantity' => max(0, $sysQty + $diff),
                    'discrepancy' => $diff,
                    'unit_cost' => $prod->purchase_price ?? 2500,
                    'reason' => $diff !== 0 ? 'Écart constaté au comptage physique' : 'RAS - Conforme',
                ]);
            }

            // 5. Demandes d'achat & Réceptions fournisseurs
            $pr = PurchaseRequest::create([
                'reference' => 'DA-2026-001',
                'product_id' => $products[0]->id,
                'warehouse_id' => $warehouses[1]->id,
                'supplier_id' => $supplier?->id,
                'user_id' => $admin->id,
                'quantity' => 200,
                'estimated_unit_price' => $products[0]->purchase_price ?? 3500,
                'urgency' => 'haute',
                'reason' => 'Seuil critique atteint sur Papier A4 80g',
                'status' => 'en_attente_finance',
            ]);

            SupplierReception::create([
                'reference' => 'REC-2026-001',
                'purchase_request_id' => $pr->id,
                'supplier_id' => $supplier?->id,
                'product_id' => $products[0]->id,
                'warehouse_id' => $warehouses[0]->id,
                'user_id' => $admin->id,
                'ordered_quantity' => 100,
                'received_quantity' => 98,
                'missing_quantity' => 1,
                'damaged_quantity' => 1,
                'batch_number' => 'LOT-PAP-0926',
                'quality_status' => 'partiel',
                'received_at' => now()->subDays(2)->toDateString(),
                'notes' => '2 ramettes endommagées pendant le transport',
            ]);

            // 6. Maintenance d'équipement
            $eq = Equipment::first();
            EquipmentMaintenance::create([
                'reference' => 'MNT-2026-001',
                'equipment_id' => $eq?->id,
                'product_id' => $products[0]->id,
                'title' => 'Révision préventive & calibration optique',
                'type' => 'maintenance',
                'technician' => 'Kouassi Fabrice (Ingénieur Maintenance)',
                'cost' => 45000,
                'started_at' => now()->subDays(3)->toDateString(),
                'status' => 'en_cours',
                'notes' => 'Changement de lampe et test de sonorisation',
            ]);
        }
    }
}
