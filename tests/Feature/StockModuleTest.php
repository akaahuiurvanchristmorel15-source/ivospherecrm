<?php

use App\Models\Domain;
use App\Models\PhysicalInventory;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PurchaseRequest;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\SupplierReception;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockService;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('commercial manager can access stock dashboard with WMS KPIs and tabs', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get('/stock');

    $response->assertStatus(200);
    $response->assertSee('Gestion des Stocks');
    $response->assertSee('Stock Disponible');
    $response->assertSee('Mode Scan / Magasinier');
    $response->assertSee('Disponible = Physique');
});

test('commercial manager can view warehouses list', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get('/stock/warehouses');

    $response->assertStatus(200);
    $response->assertSee('ENT-GEN');
});

test('stock movements are properly recorded and affect stock balance', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $warehouse = Warehouse::first();
    $product = Product::first();

    $stockBefore = StockService::getStock($product->id, $warehouse->id);

    $movement = StockService::processMovement([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'type' => 'entree',
        'reason_motif' => 'achat_fournisseur',
        'quantity' => 50,
        'user_id' => $admin->id,
        'date' => now(),
        'reference' => 'TEST-ENT-001',
        'notes' => 'Test entrée de stock',
    ]);

    $stockAfter = StockService::getStock($product->id, $warehouse->id);

    expect($stockAfter)->toBe($stockBefore + 50);
    expect($movement->type)->toBe('entree');
    expect($movement->reason_motif)->toBe('achat_fournisseur');
    expect($movement->stock_after)->toBe($stockBefore + 50);
});

test('physical inventory calculates difference and automatically adjusts stock', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $warehouse = Warehouse::first();
    $product = Product::first();

    $response = $this->actingAs($admin)->post('/stock/inventories', [
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'counted_quantity' => 45,
        'reason' => 'Comptage mensuel de contrôle',
        'auto_validate' => 1,
    ]);

    $response->assertRedirect();
    expect(PhysicalInventory::count())->toBeGreaterThan(0);
    expect(StockService::getStock($product->id, $warehouse->id))->toBe(45);
});

test('purchase request and supplier reception workflow operates and increments stock', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $warehouse = Warehouse::first();
    $product = Product::first();

    $stockBefore = StockService::getStock($product->id, $warehouse->id);

    $prResponse = $this->actingAs($admin)->post('/stock/purchase-requests', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'requested_quantity' => 30,
        'priority' => 'urgente',
        'reason' => 'Rupture imminente',
    ]);
    $prResponse->assertRedirect();
    expect(PurchaseRequest::where('product_id', $product->id)->exists())->toBeTrue();

    $recResponse = $this->actingAs($admin)->post('/stock/receptions', [
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'ordered_quantity' => 30,
        'received_quantity' => 28,
        'damaged_quantity' => 2,
        'quality_status' => 'partiel',
        'batch_number' => 'LOT-TEST-2026',
    ]);
    $recResponse->assertRedirect();

    expect(SupplierReception::where('batch_number', 'LOT-TEST-2026')->exists())->toBeTrue();
    expect(StockService::getStock($product->id, $warehouse->id))->toBe($stockBefore + 28);
});

test('barcode scanner page and fast action work properly', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();
    $warehouse = Warehouse::first();
    $product = Product::first();

    $page = $this->actingAs($commercial)->get('/stock/scanner');
    $page->assertStatus(200);
    $page->assertSee('TERMINAL MAGASINIER');

    $stockBefore = StockService::getStock($product->id, $warehouse->id);

    $scanAction = $this->actingAs($commercial)->post('/stock/scanner/action', [
        'code' => $product->sku,
        'action_type' => 'entree',
        'warehouse_id' => $warehouse->id,
        'quantity' => 12,
    ]);
    $scanAction->assertRedirect();
    expect(StockService::getStock($product->id, $warehouse->id))->toBe($stockBefore + 12);
});

test('stock alert can be resolved by commercial manager', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();
    $warehouse = Warehouse::first();
    $product = Product::first();

    $alert = StockAlert::firstOrCreate([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
    ], [
        'min_quantity' => 10,
        'current_quantity' => 2,
        'status' => 'active',
    ]);

    $response = $this->actingAs($commercial)->patch("/stock/alerts/{$alert->id}/resolve");

    $response->assertRedirect();
    expect($alert->fresh()->status)->toBe('resolved');
});

test('can create a product from the stock module with initial stock and warehouse', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $warehouse = Warehouse::first();
    $domain = Domain::first();

    $createPage = $this->actingAs($admin)->get(route('stock.products.create'));
    $createPage->assertStatus(200);
    $createPage->assertSee('Nouveau Produit — Stocks & Logistique');
    $createPage->assertSee('initial_warehouse_id');

    $sku = 'STK-PRD-'.rand(1000, 9999);
    $barcode = '618'.rand(100000000, 999999999);

    $storeResponse = $this->actingAs($admin)->post(route('stock.products.store'), [
        'sku' => $sku,
        'name' => 'Bobine Papier Traceur 80g',
        'barcode' => $barcode,
        'domain_id' => $domain->id,
        'purchase_price' => 12000,
        'selling_price' => 18500,
        'tax_rate' => 18,
        'unit' => 'rouleau',
        'min_stock' => 5,
        'max_stock' => 50,
        'initial_warehouse_id' => $warehouse->id,
        'initial_quantity' => 25,
        'is_active' => '1',
    ]);

    $storeResponse->assertRedirect(route('stock.index', ['tab' => 'disponibilite']));

    $product = Product::where('sku', $sku)->first();
    expect($product)->not->toBeNull()
        ->and($product->name)->toBe('Bobine Papier Traceur 80g')
        ->and($product->barcode)->toBe($barcode);

    $whStock = WarehouseStock::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();
    expect($whStock)->not->toBeNull()
        ->and($whStock->physical_quantity)->toBe(25);

    $movement = StockMovement::where('product_id', $product->id)
        ->where('type', 'entree')
        ->first();
    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe(25)
        ->and($movement->reason_motif)->toBe('stock_initial');
});

test('stock dashboard displays revenue booster section for top-selling products', function () {
    $commercial = User::where('email', 'commercial@ivosphere.com')->first();

    $response = $this->actingAs($commercial)->get(route('stock.index'));

    $response->assertStatus(200);
    $response->assertSee('Booster de Revenus : Optimisation des Produits Vedettes');
    $response->assertSee('CA Top Ventes');
    $response->assertSee('Gain Prix Potentiel');
    $response->assertSee('Pack Synergie');
    $response->assertSee('Anti-Rupture');
});

test('authorized user can optimize product price via revenue booster', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $product = Product::first();
    $oldPrice = (float) $product->selling_price;
    $newPrice = $oldPrice + 1500;

    $response = $this->actingAs($admin)->post(route('stock.revenue-booster.optimize-price'), [
        'product_id' => $product->id,
        'selling_price' => $newPrice,
        'reason' => 'Test optimisation tarifaire IA +5%',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $product->refresh();
    expect((float) $product->selling_price)->toBe((float) $newPrice);
});

test('authorized user can create cross-selling synergy bundle promotion', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $products = Product::take(2)->get();
    $primary = $products[0];
    $secondary = $products[1];

    $code = 'PACK-TEST-'.rand(100, 999);

    $response = $this->actingAs($admin)->post(route('stock.revenue-booster.create-bundle'), [
        'primary_product_id' => $primary->id,
        'secondary_product_id' => $secondary->id,
        'code' => $code,
        'name' => 'Pack Synergie Test Vente Croisée',
        'discount_percent' => 10,
        'min_amount' => 20000,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $promotion = Promotion::where('code', $code)->first();
    expect($promotion)->not->toBeNull()
        ->and($promotion->type)->toBe('percent')
        ->and((float) $promotion->value)->toBe(10.0)
        ->and($promotion->is_active)->toBeTrue();
});

test('authorized user can secure VIP replenishment for fast-selling product', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $product = Product::first();

    $response = $this->actingAs($admin)->post(route('stock.revenue-booster.secure-stock'), [
        'product_id' => $product->id,
        'requested_quantity' => 60,
        'reason' => 'Bouclier Anti-Rupture Test',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $pr = PurchaseRequest::where('product_id', $product->id)
        ->where('urgency', 'urgente')
        ->latest('id')
        ->first();

    expect($pr)->not->toBeNull()
        ->and($pr->quantity)->toBe(60)
        ->and($pr->reference)->toStartWith('DA-VIP-');
});
