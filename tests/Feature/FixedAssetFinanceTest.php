<?php

use App\Models\AssetCategory;
use App\Models\AssetRental;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\FixedAsset;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('finance manager can view fixed assets dashboard with 3 notions and KPIs', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get('/finance/assets');

    $response->assertStatus(200);
    $response->assertSee('Immobilisations');
    $response->assertSee('Valeur des Actifs');
    $response->assertSee('IMM-MED-001');
    $response->assertSee('IMM-PRT-001');
});

test('finance manager can view create asset form', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get('/finance/assets/create');

    $response->assertStatus(200);
    $response->assertSee('Ajouter une Immobilisation');
    $response->assertSee('Nom du matériel');
});

test('finance manager can create a new fixed asset with automatic schedule', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $category = AssetCategory::first();
    $domain = Domain::first();

    $response = $this->actingAs($finance)->post('/finance/assets', [
        'name' => 'Drone Professionnel DJI Inspire 3 8K',
        'asset_category_id' => $category->id,
        'domain_id' => $domain->id,
        'brand' => 'DJI',
        'model' => 'Inspire 3',
        'serial_number' => 'DJI-INS3-2026',
        'acquisition_date' => '2026-02-01',
        'purchase_price' => 7500000,
        'additional_fees' => 250000,
        'residual_value' => 500000,
        'useful_life_years' => 3,
        'depreciation_method' => 'lineaire',
        'status' => 'disponible',
        'condition' => 'neuf',
        'is_rental_eligible' => 1,
        'rental_price_per_day' => 120000,
        'rental_deposit_amount' => 500000,
    ]);

    $asset = FixedAsset::where('name', 'Drone Professionnel DJI Inspire 3 8K')->first();
    expect($asset)->not->toBeNull();
    expect((float) $asset->acquisition_value)->toBe(7750000.0);
    expect($asset->depreciations()->count())->toBe(3);

    $response->assertRedirect(route('finance.assets.show', $asset));
});

test('finance manager can view 360 asset page with all tabs and calculations', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $asset = FixedAsset::where('code', 'IMM-MED-001')->first();

    $response = $this->actingAs($finance)->get('/finance/assets/'.$asset->id);

    $response->assertStatus(200);
    $response->assertSee($asset->name);
    $response->assertSee('Amortissement');
    $response->assertSee('Prestations');
    $response->assertSee('1 500 000');
});

test('finance manager can record an asset usage attributing revenue', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $asset = FixedAsset::where('code', 'IMM-MED-001')->first();
    $customer = Customer::first();

    $initialRevenue = $asset->total_revenue_generated;

    $response = $this->actingAs($finance)->post('/finance/assets/'.$asset->id.'/usages', [
        'title' => 'Tournage Clip Vidéo Artiste International',
        'date' => '2026-03-25',
        'customer_id' => $customer->id,
        'duration_hours' => 12,
        'revenue_generated' => 800000,
        'notes' => 'Tournage studio + extérieur avec équipe son et lumière.',
    ]);

    $response->assertRedirect();
    $asset->refresh();

    expect($asset->total_revenue_generated)->toBe($initialRevenue + 800000.0);
});

test('finance manager can create and transition an equipment rental', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $asset = FixedAsset::where('code', 'IMM-MED-001')->first();
    $customer = Customer::first();

    // 1. Nouvelle location
    $response = $this->actingAs($finance)->post('/finance/assets-rentals', [
        'fixed_asset_id' => $asset->id,
        'customer_id' => $customer->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-03',
        'daily_rate' => 35000,
        'deposit_amount' => 150000,
        'condition_at_departure' => 'tres_bon',
    ]);

    $response->assertRedirect();
    $rental = AssetRental::where('fixed_asset_id', $asset->id)->latest('id')->first();
    expect($rental)->not->toBeNull();
    expect($rental->status)->toBe('loue');
    expect((float) $rental->total_amount)->toBe(105000.0); // 3 jours à 35000

    $asset->refresh();
    expect($asset->status)->toBe('loue');

    // 2. Retour client & passage en contrôle
    $responseReturn = $this->actingAs($finance)->patch('/finance/assets-rentals/'.$rental->id.'/status', [
        'status' => 'retourne_controle',
        'actual_return_date' => '2026-04-03',
        'condition_at_return' => 'tres_bon',
    ]);

    $rental->refresh();
    $asset->refresh();
    expect($rental->status)->toBe('retourne_controle');
    expect($asset->status)->toBe('en_maintenance');

    // 3. Clôture du contrat & restitution de caution
    $responseClose = $this->actingAs($finance)->patch('/finance/assets-rentals/'.$rental->id.'/status', [
        'status' => 'cloture',
        'deposit_returned' => 1,
    ]);

    $rental->refresh();
    $asset->refresh();
    expect($rental->status)->toBe('cloture');
    expect($rental->deposit_returned)->toBeTrue();
    expect($asset->status)->toBe('disponible');
});

test('finance manager can record maintenance and restore status when completed', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();
    $asset = FixedAsset::where('code', 'IMM-MED-001')->first();

    // Enregistrement d'une maintenance en cours (immobilise le matériel)
    $response = $this->actingAs($finance)->post('/finance/assets/'.$asset->id.'/maintenances', [
        'type' => 'curative',
        'maintenance_date' => '2026-04-05',
        'provider_name' => 'Atelier Tech Sony',
        'cost' => 85000,
        'description' => 'Nettoyage capteur et révision autofocus',
        'status' => 'en_cours',
    ]);

    $response->assertRedirect();
    $asset->refresh();
    expect($asset->status)->toBe('en_maintenance');

    $maintenance = $asset->maintenances()->latest()->first();
    expect($maintenance)->not->toBeNull();
    expect((float) $maintenance->cost)->toBe(85000.0);

    // Finalisation de la maintenance
    $responseComplete = $this->actingAs($finance)->patch('/finance/assets/maintenances/'.$maintenance->id.'/complete');
    $responseComplete->assertRedirect();

    $asset->refresh();
    expect($asset->status)->toBe('disponible');
});

test('finance manager can export assets registry as CSV', function () {
    $finance = User::where('email', 'finance@ivosphere.com')->first();

    $response = $this->actingAs($finance)->get('/finance/assets/export');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->getContent())->toContain('Code;Nom;Domaine');
    expect($response->getContent())->toContain('IMM-MED-001');
});
