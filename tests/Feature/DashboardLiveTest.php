<?php

use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());
});

test('the home page renders the live dashboard that refreshes itself', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSeeLivewire('dashboard-live')
        ->assertSee('wire:poll.30s.visible', false);
});

test('filters from the address are applied on first display', function () {
    Livewire::withQueryParams(['period' => 'this_year', 'domain' => 'PRINT'])
        ->test('dashboard-live')
        ->assertSet('period', 'this_year')
        ->assertViewHas('periodLabel', 'Cette année')
        ->assertViewHas('currentDomain', fn ($domain) => $domain?->code === 'PRINT');
});

test('changing the period updates the figures without reloading', function () {
    Livewire::test('dashboard-live')
        ->assertViewHas('periodLabel', 'Ce mois')
        ->set('period', 'today')
        ->assertViewHas('periodLabel', "Aujourd'hui");
});

test('a custom period needs an end date after the start date', function () {
    Livewire::test('dashboard-live')
        ->set('customFrom', '2026-10-10')
        ->set('customTo', '2026-10-01')
        ->call('applyCustomPeriod')
        ->assertHasErrors('customTo')
        ->set('customTo', '2026-10-20')
        ->call('applyCustomPeriod')
        ->assertHasNoErrors()
        ->assertSet('period', 'custom')
        ->assertViewHas('periodLabel', 'Période personnalisée');
});
