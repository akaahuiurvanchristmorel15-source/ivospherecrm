<?php

use App\Models\Customer;
use App\Models\PrintFormat;
use App\Models\PrintJob;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('admin can access print dashboard and print jobs listing', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/print');
    $response->assertStatus(200);
    $response->assertSee('Tableau de Bord - PRINT');

    $responseJobs = $this->actingAs($admin)->get('/print/jobs');
    $responseJobs->assertStatus(200);
    $responseJobs->assertSee('PJ-2026-001');
});

test('admin can access create print job page and store a job', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();
    $customer = Customer::first();
    $format = PrintFormat::first();

    // 1. Can view create page
    $responseCreate = $this->actingAs($admin)->get('/print/jobs/create');
    $responseCreate->assertStatus(200);
    $responseCreate->assertSee('Nouveau Travail');

    // 2. Can store new print job
    $responseStore = $this->actingAs($admin)->post('/print/jobs', [
        'reference' => 'PJ-TEST-999',
        'customer_id' => $customer->id,
        'type' => 'numerique',
        'format_id' => $format->id,
        'quantity' => 250,
        'unit_price' => 75,
        'status' => 'en_production',
        'deadline' => now()->addDays(3)->format('Y-m-d'),
        'specifications' => 'Impression recto-verso 350g',
    ]);

    $responseStore->assertRedirect('/print/jobs');
    $this->assertDatabaseHas('print_jobs', [
        'reference' => 'PJ-TEST-999',
        'quantity' => 250,
        'total' => 18750,
    ]);

    $job = PrintJob::where('reference', 'PJ-TEST-999')->first();

    // 3. Can view show page
    $responseShow = $this->actingAs($admin)->get("/print/jobs/{$job->id}");
    $responseShow->assertStatus(200);
    $responseShow->assertSee('PJ-TEST-999');
    $responseShow->assertSee('18 750 FCFA', false);

    // 4. Can view edit page
    $responseEdit = $this->actingAs($admin)->get("/print/jobs/{$job->id}/edit");
    $responseEdit->assertStatus(200);
    $responseEdit->assertSee('Modifier le Travail');

    // 5. Can update the job
    $responseUpdate = $this->actingAs($admin)->put("/print/jobs/{$job->id}", [
        'reference' => 'PJ-TEST-999',
        'customer_id' => $customer->id,
        'type' => 'offset',
        'format_id' => $format->id,
        'quantity' => 500,
        'unit_price' => 60,
        'status' => 'terminé',
    ]);

    $responseUpdate->assertRedirect('/print/jobs');
    $this->assertDatabaseHas('print_jobs', [
        'reference' => 'PJ-TEST-999',
        'quantity' => 500,
        'total' => 30000,
        'status' => 'terminé',
    ]);

    // 6. Can delete the job
    $responseDelete = $this->actingAs($admin)->delete("/print/jobs/{$job->id}");
    $responseDelete->assertRedirect('/print/jobs');
    $this->assertDatabaseMissing('print_jobs', [
        'id' => $job->id,
    ]);
});

test('admin can access sport dashboard and articles listing', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/sport');
    $response->assertStatus(200);
    $response->assertSee('Tableau de Bord - SPORT');

    $responseArticles = $this->actingAs($admin)->get('/sport/articles');
    $responseArticles->assertStatus(200);
    $responseArticles->assertSee('Articles & Maillots Personnalisés', false);
});

test('admin can access tech dashboard and projects listing', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/tech');
    $response->assertStatus(200);
    $response->assertSee('Tableau de Bord - TECH');

    $responseProjects = $this->actingAs($admin)->get('/tech/projects');
    $responseProjects->assertStatus(200);
    $responseProjects->assertSee('Site Web Corporate');
});

test('admin can access media dashboard and equipment listing', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/media');
    $response->assertStatus(200);
    $response->assertSee('Tableau de Bord - MEDIA', false);

    $responseEquipment = $this->actingAs($admin)->get('/media/equipment');
    $responseEquipment->assertStatus(200);
    $responseEquipment->assertSee('Camera Sony A7III');
});

test('admin can access assurance dashboard and contracts listing', function () {
    $admin = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($admin)->get('/assurance');
    $response->assertStatus(200);
    $response->assertSee('Tableau de Bord - ASSURANCE');

    $responseContracts = $this->actingAs($admin)->get('/assurance/contracts');
    $responseContracts->assertStatus(200);
    $responseContracts->assertSee('IC-2026-001');
});
