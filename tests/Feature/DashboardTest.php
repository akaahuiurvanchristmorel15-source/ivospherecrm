<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('unauthenticated users are redirected to login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated user can view dashboard with all real-time indicators', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertStatus(200);

    // 1. Chiffre d'affaires & Trésorerie
    $response->assertSee("Chiffre d'Affaires", false);
    $response->assertSee('CA du Jour');
    $response->assertSee('CA du Mois');
    $response->assertSee('CA Annuel');
    $response->assertSee('Trésorerie', false);
    $response->assertSee('Dépenses', false);
    $response->assertSee('Bénéfice estimé', false);
    $response->assertSee('Factures impayées', false);

    // 2. Activité Commerciale
    $response->assertSee('Nombre de ventes');
    $response->assertSee('Commandes');
    $response->assertSee('Clients actifs');
    $response->assertSee('Prospects');
    $response->assertSee('Cmd en attente');
    $response->assertSee('Devis en attente');

    // 3. Alertes Stocks & Opérations
    $response->assertSee('Ruptures de stock');
    $response->assertSee('Sous seuil minimal');
    $response->assertSee('RDV du jour');
    $response->assertSee('Tâches à réaliser', false);
    $response->assertSee('Évènements à venir', false);

    // 4. Domaines
    $response->assertSee('IVOSPHERE PRINT');
    $response->assertSee('IVOSPHERE SPORT');
    $response->assertSee('IVOSPHERE TECH');
    $response->assertSee('IVOSPHERE MEDIA');
    $response->assertSee('IVOSPHERE ASSURANCE');

    // 5. Filtres temporels présents
    $response->assertSee("Aujourd'hui", false);
    $response->assertSee('Cette semaine');
    $response->assertSee('Ce mois');
    $response->assertSee('Ce trimestre');
    $response->assertSee('Cette année', false);
    $response->assertSee('Période personnalisée', false);
});

test('dashboard can filter by temporal periods', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    // Aujourd'hui
    $responseToday = $this->actingAs($user)->get('/dashboard?period=today');
    $responseToday->assertStatus(200);
    $responseToday->assertSee("Aujourd'hui", false);

    // Cette semaine
    $responseWeek = $this->actingAs($user)->get('/dashboard?period=this_week');
    $responseWeek->assertStatus(200);
    $responseWeek->assertSee('Cette semaine');

    // Ce trimestre
    $responseQuarter = $this->actingAs($user)->get('/dashboard?period=this_quarter');
    $responseQuarter->assertStatus(200);
    $responseQuarter->assertSee('Ce trimestre');

    // Cette année
    $responseYear = $this->actingAs($user)->get('/dashboard?period=this_year');
    $responseYear->assertStatus(200);
    $responseYear->assertSee('Cette année', false);

    // Période personnalisée
    $responseCustom = $this->actingAs($user)->get('/dashboard?period=custom&date_from=2026-01-01&date_to=2026-12-31');
    $responseCustom->assertStatus(200);
    $responseCustom->assertSee('Période personnalisée', false);
    $responseCustom->assertSee('01/01/2026');
    $responseCustom->assertSee('31/12/2026');
});

test('dashboard can filter by specific domain', function () {
    $user = User::where('email', 'admin@ivosphere.com')->first();

    // Filtre PRINT
    $responsePrint = $this->actingAs($user)->get('/dashboard?domain=PRINT');
    $responsePrint->assertStatus(200);
    $responsePrint->assertSee('IVOSPHERE PRINT');

    // Filtre TECH
    $responseTech = $this->actingAs($user)->get('/dashboard?domain=TECH');
    $responseTech->assertStatus(200);
    $responseTech->assertSee('IVOSPHERE TECH');

    // Filtre SPORT
    $responseSport = $this->actingAs($user)->get('/dashboard?domain=SPORT');
    $responseSport->assertStatus(200);
    $responseSport->assertSee('IVOSPHERE SPORT');
});
