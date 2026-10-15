<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    Storage::fake('public');

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());
});

test('photo upload returns the new image url as json for the bulk edit page', function () {
    $product = Product::create(['name' => 'Cahier 200p', 'sku' => 'CAH-200', 'is_active' => true]);

    $response = $this->postJson(route('stock.products.quick-image', $product), [
        'image' => UploadedFile::fake()->image('cahier.jpg', 800, 600),
    ]);

    $product->refresh();

    $response->assertOk()->assertJson(['success' => true, 'image_url' => asset('storage/'.$product->image)]);
    Storage::disk('public')->assertExists($product->image);
});

test('replacing a photo keeps the file when another product still uses it', function () {
    Storage::disk('public')->put('products/shared.jpg', 'image');
    $first = Product::create(['name' => 'Stylo bleu', 'sku' => 'STY-B', 'is_active' => true, 'image' => 'products/shared.jpg']);
    Product::create(['name' => 'Stylo rouge', 'sku' => 'STY-R', 'is_active' => true, 'image' => 'products/shared.jpg']);

    $this->postJson(route('stock.products.quick-image', $first), [
        'image' => UploadedFile::fake()->image('stylo.jpg'),
    ])->assertOk();

    Storage::disk('public')->assertExists('products/shared.jpg');
});

test('common image gives each product its own copy', function () {
    $first = Product::create(['name' => 'Gomme', 'sku' => 'GOM-1', 'is_active' => true, 'selling_price' => 100]);
    $second = Product::create(['name' => 'Règle', 'sku' => 'REG-1', 'is_active' => true, 'selling_price' => 200]);

    $this->post(route('stock.products.bulk-update'), [
        'common_image' => UploadedFile::fake()->image('commune.jpg'),
        'apply_common_image_to_all' => 1,
        'products' => [
            ['id' => $first->id, 'selling_price' => 100],
            ['id' => $second->id, 'selling_price' => 200],
        ],
    ])->assertRedirect(route('stock.index', ['tab' => 'disponibilite']));

    $firstImage = $first->refresh()->image;
    $secondImage = $second->refresh()->image;

    expect($firstImage)->not->toBeNull()->not->toBe($secondImage);
    Storage::disk('public')->assertExists([$firstImage, $secondImage]);
});

test('bulk update with only photos changed redirects with a success message', function () {
    $this->post(route('stock.products.bulk-update'), ['images_updated_count' => 3])
        ->assertRedirect(route('stock.index', ['tab' => 'disponibilite']))
        ->assertSessionHas('success', 'Les photos de 3 article(s) ont été mises à jour avec succès.');
});
