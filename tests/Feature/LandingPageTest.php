<?php

use App\Models\Customer;
use App\Models\Prospect;
use App\Models\User;

test('official showcase landing page is accessible at root url', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertViewIs('welcome');
    $response->assertSeeText('IVOSPHERE');
    $response->assertSeeText('Des solutions professionnelles pour donner vie à vos projets');
});

test('official showcase landing page contains all 5 business poles', function () {
    $response = $this->get('/');

    $response->assertSeeText('IVOSPHERE PRINT');
    $response->assertSeeText('IVOSPHERE SPORT');
    $response->assertSeeText('IVOSPHERE TECH');
    $response->assertSeeText('IVOSPHERE MEDIA & ÉVÈNEMENTS');
    $response->assertSeeText('IVOSPHERE ASSURANCE');
});

test('official showcase landing page contains all core showcase sections', function () {
    $response = $this->get('/');

    $response->assertSeeText('Nos prestations');
    $response->assertSeeText('Détail des prestations');
    $response->assertSeeText('Pourquoi choisir IVOSPHERE ?');
    $response->assertSeeText('Notre processus');
    $response->assertSeeText('Réalisations et projets');
    $response->assertSeeText('Vous avez un projet ? Parlons-en.');
});

test('official showcase landing page displays quote form with required fields', function () {
    $response = $this->get('/');

    $response->assertSeeText('Nom et prénom');
    $response->assertSeeText('Téléphone');
    $response->assertSeeText('Email');
    $response->assertSeeText('Pôle concerné');
    $response->assertSeeText('Votre projet');
    $response->assertSeeText('Envoyer ma demande de devis');
});

test('official showcase landing page displays process steps and reasons', function () {
    $response = $this->get('/');

    $response->assertSeeText('Professionnalisme');
    $response->assertSeeText('Plusieurs expertises');
    $response->assertSeeText('Accompagnement personnalisé');
    $response->assertSeeText('Votre besoin');
    $response->assertSeeText('Étude du projet');
    $response->assertSeeText('Proposition et devis');
    $response->assertSeeText('Réalisation');
    $response->assertSeeText('Livraison');
    $response->assertSeeText('Suivi');
});

test('submitting quote form via POST /devis registers applicant as a customer and creates prospect', function () {
    $payload = [
        'name' => 'Koffi Paul',
        'phone' => '+225 07 11 22 33 44',
        'email' => 'koffi.paul@test.com',
        'pole' => 'print',
        'message' => 'Besoin de 5000 flyers et 200 carnets de facture.',
    ];

    $response = $this->post('/devis', $payload);

    $response->assertRedirect(url('/#devis'));
    $response->assertSessionHas('quote_sent', true);

    // Vérifie que le demandeur est bien enregistré comme client dans l'application
    $this->assertDatabaseHas('customers', [
        'name' => 'Koffi Paul',
        'phone' => '+225 07 11 22 33 44',
        'email' => 'koffi.paul@test.com',
        'status' => 'actif',
    ]);

    $customer = Customer::where('email', 'koffi.paul@test.com')->first();
    expect($customer)->not->toBeNull();
    expect($customer->code)->toStartWith('CLI-');

    // Vérifie également l'enregistrement du prospect dans le CRM
    $this->assertDatabaseHas('prospects', [
        'name' => 'Koffi Paul',
        'phone' => '+225 07 11 22 33 44',
        'email' => 'koffi.paul@test.com',
    ]);
});

test('submitting quote form directly to POST / also registers applicant as a customer', function () {
    $payload = [
        'name' => 'Aya Touré',
        'phone' => '+225 01 02 03 04 05',
        'email' => 'aya.toure@test.com',
        'pole' => 'tech',
        'message' => 'Développement d’une application de gestion.',
    ];

    $response = $this->post('/', $payload);

    $response->assertRedirect(url('/#devis'));
    $response->assertSessionHas('quote_sent', true);

    $this->assertDatabaseHas('customers', [
        'name' => 'Aya Touré',
        'phone' => '+225 01 02 03 04 05',
        'email' => 'aya.toure@test.com',
        'status' => 'actif',
    ]);

    $this->assertDatabaseHas('prospects', [
        'name' => 'Aya Touré',
        'phone' => '+225 01 02 03 04 05',
        'email' => 'aya.toure@test.com',
    ]);
});

test('honeypot field prevents spam submission and does not create customer', function () {
    $payload = [
        'website' => 'http://spambot.com',
        'name' => 'Spam Bot',
        'phone' => '123456789',
        'message' => 'Spam content',
    ];

    $response = $this->post('/devis', $payload);

    $response->assertRedirect(url('/#devis'));
    $this->assertDatabaseMissing('customers', [
        'name' => 'Spam Bot',
    ]);
    $this->assertDatabaseMissing('prospects', [
        'name' => 'Spam Bot',
    ]);
});

test('authenticated and guest users can browse the showcase landing page', function () {
    $guestResponse = $this->get('/');
    $guestResponse->assertStatus(200);

    $user = User::factory()->create();
    $authResponse = $this->actingAs($user)->get('/');
    $authResponse->assertStatus(200);
});
