<?php

use App\Models\Product;
use App\Models\User;
use App\Services\QrCodeService;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    $this->actingAs($this->admin);

    $this->product = Product::first();
    if (! $this->product) {
        $this->product = Product::create([
            'name' => 'Produit Test QR EAN',
            'sku' => 'TEST-QREAN-01',
            'barcode' => Product::generateEan13(),
            'selling_price' => 5000,
            'purchase_price' => 3000,
            'is_active' => true,
        ]);
    }
});

test('chaque produit possède un code EAN-13 valide', function () {
    expect($this->product->ean)->not->toBeEmpty();
    expect(strlen($this->product->ean))->toBeGreaterThanOrEqual(12);
    expect($this->product->formatted_ean)->not->toBeEmpty();

    // Création sans code-barres génère automatiquement un EAN-13
    $newProduct = Product::create([
        'name' => 'Nouvel Article Automatique',
        'sku' => 'AUTO-EAN-'.uniqid(),
        'selling_price' => 2500,
        'is_active' => true,
    ]);

    expect($newProduct->barcode)->not->toBeEmpty();
    expect(strlen($newProduct->barcode))->toBe(13);
    expect(str_starts_with($newProduct->barcode, '618'))->toBeTrue();
});

test('le service QrCodeService génère des flux PNG et des étiquettes valides', function () {
    $service = app(QrCodeService::class);

    $png = $service->generatePng($this->product->ean);
    expect($png)->not->toBeEmpty();
    // Signature PNG standard (8 premiers octets : \x89PNG\r\n\x1a\n)
    expect(substr($png, 0, 4))->toBe("\x89PNG");

    $labelPng = $service->generateProductLabelPng($this->product);
    expect($labelPng)->not->toBeEmpty();
    expect(substr($labelPng, 0, 4))->toBe("\x89PNG");
    expect(strlen($labelPng))->toBeGreaterThan(1000);
});

test('le catalogue commercial redirige vers le catalogue unifié avec QR codes et EAN', function () {
    $response = $this->get(route('commercial.products.index'));

    $response->assertRedirect(route('stock.index', ['tab' => 'disponibilite']));

    $catalogResponse = $this->get(route('stock.index', ['tab' => 'disponibilite']));
    $catalogResponse->assertStatus(200);
    $catalogResponse->assertSee($this->product->sku);
    $catalogResponse->assertSee($this->product->formatted_ean);
    $catalogResponse->assertSee('open-product-qr');
    $catalogResponse->assertSee('qr-download');
});

test('le téléchargement du QR code commercial au format PNG fonctionne', function () {
    $response = $this->get(route('commercial.products.qr-download', $this->product));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Content-Disposition', 'attachment; filename="qr-code-'.$this->product->sku.'-ean-'.$this->product->ean.'.png"');
    expect(substr($response->getContent(), 0, 4))->toBe("\x89PNG");
});

test('le téléchargement de l étiquette complète commerciale en PNG fonctionne', function () {
    $response = $this->get(route('commercial.products.qr-download', ['product' => $this->product, 'label' => 1]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Content-Disposition', 'attachment; filename="etiquette-produit-'.$this->product->sku.'-ean-'.$this->product->ean.'.png"');
    expect(substr($response->getContent(), 0, 4))->toBe("\x89PNG");
});

test('le téléchargement du QR code stock au format PNG fonctionne', function () {
    $response = $this->get(route('stock.products.qr-download', $this->product));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Content-Disposition', 'attachment; filename="qr-code-'.$this->product->sku.'-ean-'.$this->product->ean.'.png"');
    expect(substr($response->getContent(), 0, 4))->toBe("\x89PNG");
});

test('le téléchargement de l étiquette stock au format PNG fonctionne', function () {
    $response = $this->get(route('stock.products.qr-download', ['product' => $this->product, 'label' => 1]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Content-Disposition', 'attachment; filename="etiquette-produit-'.$this->product->sku.'-ean-'.$this->product->ean.'.png"');
    expect(substr($response->getContent(), 0, 4))->toBe("\x89PNG");
});

test('le tableau de bord stock disponibilite contient les boutons QR Code et EAN', function () {
    $response = $this->get(route('stock.index', ['tab' => 'disponibilite']));

    $response->assertStatus(200);
    $response->assertSee($this->product->sku);
    $response->assertSee('qr-download');
});
