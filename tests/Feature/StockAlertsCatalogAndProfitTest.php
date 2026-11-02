<?php

use App\Models\Customer;
use App\Models\Domain;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Support\PdfRenderer;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
    $this->actingAs($this->admin);

    $this->plateau = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->cocody = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);
});

function productInWarehouse(string $name, Warehouse $warehouse, int $quantity, int $minStock = 5, array $attributes = []): Product
{
    $product = Product::create($attributes + ['name' => $name, 'sku' => strtoupper(uniqid()), 'min_stock' => $minStock, 'is_active' => true]);
    WarehouseStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'physical_quantity' => $quantity, 'reserved_quantity' => 0]);

    return $product;
}

function saleOf(Product $product, int $quantity, float $unitPrice, string $status = 'livree', ?string $date = null, float $tax = 0): Order
{
    $customer = Customer::firstOrCreate(['name' => 'Client comptoir'], ['code' => 'CLI-TEST', 'type' => 'particulier', 'status' => 'actif']);

    $order = Order::create([
        'reference' => 'CMD-'.uniqid(),
        'customer_id' => $customer->id,
        'user_id' => test()->admin->id,
        'date' => $date ?? now()->toDateString(),
        'status' => $status,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'description' => $product->name,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'tax_amount' => $tax,
        'total' => $quantity * $unitPrice + $tax,
    ]);

    return $order;
}

test('the alerts catalog of a warehouse keeps its low and out of stock articles, out of stock first', function () {
    productInWarehouse('Article abondant', $this->plateau, 40);
    productInWarehouse('Article faible', $this->plateau, 3);
    productInWarehouse('Article au seuil', $this->plateau, 5);
    productInWarehouse('Article en rupture', $this->plateau, 0);
    productInWarehouse('Article inactif', $this->plateau, 0, 5, ['is_active' => false]);
    productInWarehouse('Article ailleurs', $this->cocody, 0);

    $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]))
        ->assertOk()
        ->assertSee('Articles faibles et en rupture')
        ->assertSee('Rupture')
        ->assertViewHas('sections', fn ($sections) => $sections->sole()['rows']->pluck('product.name')->all()
            === ['Article en rupture', 'Article au seuil', 'Article faible']);
});

test('the alerts catalog hides the reference and minimum stock columns, the usual catalog keeps its reference', function () {
    $product = productInWarehouse('Article faible', $this->plateau, 3);

    $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]))
        ->assertOk()
        ->assertSee('Article faible')
        ->assertSee('Faible')
        ->assertDontSee('Référence')
        ->assertDontSee('Seuil mini')
        ->assertDontSee($product->sku);

    $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id]))
        ->assertOk()
        ->assertSee('Référence')
        ->assertSee($product->sku);
});

test('an article is low from one unit even without a minimum stock', function () {
    productInWarehouse('Dernier exemplaire', $this->plateau, 1, 0);
    productInWarehouse('Deux exemplaires', $this->plateau, 2, 0);

    $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]))
        ->assertViewHas('sections', fn ($sections) => $sections->sole()['rows']->pluck('product.name')->all() === ['Dernier exemplaire']);
});

test('the alerts catalog uses the quantity of each warehouse', function () {
    $product = productInWarehouse('Rame papier', $this->plateau, 40);
    WarehouseStock::create(['warehouse_id' => $this->cocody->id, 'product_id' => $product->id, 'physical_quantity' => 2, 'reserved_quantity' => 0]);

    $this->get(route('stock.products.print-catalog', ['per_warehouse' => 1, 'alerts_only' => 1]))
        ->assertOk()
        ->assertViewHas('sections', fn ($sections) => $sections->sole()['warehouse']->is($this->cocody)
            && $sections->sole()['rows']->sole()['quantity'] === 2.0);
});

test('a warehouse without alert gets an explicit empty catalog', function () {
    productInWarehouse('Article abondant', $this->plateau, 40);

    $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]))
        ->assertOk()
        ->assertSee('Aucun article faible ou en rupture');
});

test('the alerts catalog downloads as a named PDF', function () {
    productInWarehouse('Article en rupture', $this->plateau, 0);

    $this->mock(PdfRenderer::class)
        ->shouldReceive('fromHtml')
        ->once()
        ->withArgs(fn (string $html) => str_contains($html, 'Article en rupture') && str_contains($html, 'Articles faibles et en rupture'))
        ->andReturn('%PDF-fake');

    $this->get(route('stock.products.download-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]))
        ->assertOk()
        ->assertDownload('Catalogue-faibles-ruptures-wh-plt-'.now()->format('Y-m-d').'.pdf');
});

test('the usual catalog of a warehouse still leaves out empty articles', function () {
    productInWarehouse('Article abondant', $this->plateau, 40);
    productInWarehouse('Article en rupture', $this->plateau, 0);

    $this->get(route('stock.products.print-catalog', ['warehouse_id' => $this->plateau->id]))
        ->assertSee('Catalogue produits')
        ->assertViewHas('sections', fn ($sections) => $sections->sole()['rows']->pluck('product.name')->all() === ['Article abondant']);
});

test('warehouse pages link to the alerts catalog', function () {
    $this->get(route('stock.warehouses.index'))
        ->assertOk()
        ->assertSee(route('stock.products.download-catalog', ['per_warehouse' => 1, 'alerts_only' => 1]))
        ->assertSee(route('stock.products.download-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]));

    $this->get(route('stock.warehouses.show', $this->plateau))
        ->assertOk()
        ->assertSee(route('stock.products.download-catalog', ['warehouse_id' => $this->plateau->id, 'alerts_only' => 1]));
});

test('the profit to earn is the margin of the current stock', function () {
    productInWarehouse('Rame papier', $this->plateau, 10, 5, ['purchase_price' => 2500, 'selling_price' => 3500]);
    productInWarehouse('Sans prix achat', $this->plateau, 2, 5, ['purchase_price' => 0, 'selling_price' => 1000]);

    // 10 × 1 000 + 2 × (1 000 − 650, coût estimé à 65 %)
    $this->get(route('stock.index'))
        ->assertOk()
        ->assertSee('Bénéfice total à gagner')
        ->assertViewHas('profitToEarn', fn ($profit) => (float) $profit === 10700.0);
});

test('the profit earned counts validated sales excluding tax and at purchase cost', function () {
    $ream = productInWarehouse('Rame papier', $this->plateau, 10, 5, ['purchase_price' => 2500, 'selling_price' => 3500]);

    saleOf($ream, 3, 3500, 'livree', tax: 630);
    saleOf($ream, 1, 3000, 'livrée');
    saleOf($ream, 5, 3500, 'annulée');
    saleOf($ream, 5, 3500, 'en_attente');

    // (3 × 1 000) + (1 × 500) : la taxe, les ventes annulées et en attente ne comptent pas
    $this->get(route('stock.index'))
        ->assertOk()
        ->assertSee('Bénéfice gagné')
        ->assertViewHas('profitEarned', fn ($profit) => (float) $profit === 3500.0);
});

test('the profit earned of the month leaves out earlier sales', function () {
    $ream = productInWarehouse('Rame papier', $this->plateau, 10, 5, ['purchase_price' => 2500, 'selling_price' => 3500]);

    saleOf($ream, 1, 3500);
    saleOf($ream, 2, 3500, date: now()->subMonthNoOverflow()->toDateString());

    $this->get(route('stock.index'))
        ->assertViewHas('profitEarned', fn ($profit) => (float) $profit === 3000.0)
        ->assertViewHas('profitEarnedThisMonth', fn ($profit) => (float) $profit === 1000.0);
});

test('the profits follow the selected domain and stay at zero without sales', function () {
    $domainId = Domain::query()->value('id');
    $otherDomainId = Domain::query()->whereKeyNot($domainId)->value('id');

    $ream = productInWarehouse('Rame papier', $this->plateau, 10, 5, ['purchase_price' => 2500, 'selling_price' => 3500, 'domain_id' => $domainId]);
    saleOf($ream, 1, 3500);

    $this->get(route('stock.index', ['domain_id' => $otherDomainId]))
        ->assertOk()
        ->assertViewHas('profitToEarn', fn ($profit) => (float) $profit === 0.0)
        ->assertViewHas('profitEarned', fn ($profit) => (float) $profit === 0.0);

    $this->get(route('stock.index', ['domain_id' => $domainId]))
        ->assertViewHas('profitToEarn', fn ($profit) => (float) $profit === 10000.0)
        ->assertViewHas('profitEarned', fn ($profit) => (float) $profit === 1000.0);
});
