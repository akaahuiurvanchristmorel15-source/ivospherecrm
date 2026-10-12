<?php

use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());
});

test('every finance page without parameters loads', function () {
    $failures = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'finance.')
            && in_array('GET', $route->methods(), true)
            && $route->parameterNames() === [])
        ->mapWithKeys(fn ($route) => [$route->getName() => $this->get(route($route->getName()))->getStatusCode()])
        ->reject(fn (int $status) => $status === 200);

    expect($failures->all())->toBe([]);
});
