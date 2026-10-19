<?php

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\QrCodeService;
use App\Support\PdfRenderer;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());

    $this->plateau = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->cocody = Warehouse::create(['name' => 'Dépôt Cocody', 'code' => 'WH-COC', 'is_active' => true]);

    $this->ream = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true]);
    $this->ink = Product::create(['name' => 'Cartouche encre noire', 'sku' => 'ENC-NR', 'is_active' => true]);
    $this->stapler = Product::create(['name' => 'Agrafeuse épuisée', 'sku' => 'AGR-01', 'is_active' => true]);

    WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $this->ream->id, 'physical_quantity' => 40, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $this->stapler->id, 'physical_quantity' => 0, 'reserved_quantity' => 0]);
    WarehouseStock::create(['warehouse_id' => $this->cocody->id, 'product_id' => $this->ink->id, 'physical_quantity' => 12, 'reserved_quantity' => 0]);
});

test('warehouse QR codes are downloaded as an A4 PDF', function () {
    $response = $this->get(route('stock.warehouses.qr-codes', $this->plateau));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('QR-codes-wh-plt-'.now()->format('Y-m-d').'.pdf');

    expect(str_starts_with($response->getContent(), '%PDF'))->toBeTrue();
});

test('QR codes PDF only contains the products in stock in the warehouse, encoding their EAN', function () {
    $expectedQr = base64_encode(app(QrCodeService::class)->generateCompactSvg($this->ream->ean));

    $this->mock(PdfRenderer::class)
        ->shouldReceive('fromHtml')
        ->once()
        ->withArgs(fn (string $html) => str_contains($html, 'size: A4 portrait')
            && str_contains($html, 'Rame papier A4')
            && str_contains($html, 'PAP-A4')
            && str_contains($html, $expectedQr)
            && ! str_contains($html, 'Cartouche encre noire')
            && ! str_contains($html, 'Agrafeuse épuisée'))
        ->andReturn('%PDF-fake');

    $this->get(route('stock.warehouses.qr-codes', $this->plateau))->assertOk();
});

test('QR codes are laid out in a grid of 12 per A4 page', function () {
    foreach (range(1, 12) as $number) {
        $product = Product::create(['name' => "Article {$number}", 'sku' => "ART-{$number}", 'is_active' => true]);
        WarehouseStock::create(['warehouse_id' => $this->plateau->id, 'product_id' => $product->id, 'physical_quantity' => 3, 'reserved_quantity' => 0]);
    }

    $this->mock(PdfRenderer::class)
        ->shouldReceive('fromHtml')
        ->once()
        ->withArgs(fn (string $html) => substr_count($html, 'class="label-qr"') === 13
            && str_contains($html, 'Page 2 / 2')
            && ! str_contains($html, 'Page 3'))
        ->andReturn('%PDF-fake');

    $this->get(route('stock.warehouses.qr-codes', $this->plateau))->assertOk();
});

test('warehouse without products redirects with a clear message', function () {
    $empty = Warehouse::create(['name' => 'Dépôt Vide', 'code' => 'WH-VID', 'is_active' => true]);

    $this->get(route('stock.warehouses.qr-codes', $empty))
        ->assertRedirect(route('stock.warehouses.show', $empty))
        ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Aucun produit en stock'));
});

test('users without access to warehouses cannot download the QR codes PDF', function () {
    $cyberAgent = User::factory()->create(['is_active' => true]);
    $cyberAgent->roles()->attach(Role::where('slug', 'agent_cyber')->first());

    $this->actingAs($cyberAgent)
        ->get(route('stock.warehouses.qr-codes', $this->plateau))
        ->assertForbidden();
});

test('warehouse pages link to the QR codes PDF', function () {
    $url = route('stock.warehouses.qr-codes', $this->plateau);

    $this->get(route('stock.warehouses.index'))->assertOk()->assertSee($url, false);
    $this->get(route('stock.warehouses.show', $this->plateau))
        ->assertOk()
        ->assertSee('PDF des QR codes')
        ->assertSee($url, false);
});
