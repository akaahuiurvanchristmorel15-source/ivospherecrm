<?php

use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();

    $this->manager = User::create(['name' => 'Chef de dépôt', 'email' => 'chef@ivosphere.ci', 'password' => 'MotDePasse!2026', 'is_active' => true, 'all_domains' => true]);
    $this->manager->roles()->sync([Role::where('slug', 'responsable')->value('id')]);

    $this->warehouse = Warehouse::create(['name' => 'Dépôt Plateau', 'code' => 'WH-PLT', 'is_active' => true]);
    $this->product = Product::create(['name' => 'Rame papier A4', 'sku' => 'PAP-A4', 'is_active' => true, 'selling_price' => 3500]);
});

test('catalogue permissions are listed in roles and only granted to the administrator', function () {
    foreach (['products.edit', 'products.adjust', 'products.delete'] as $slug) {
        $roles = Permission::where('slug', $slug)->firstOrFail()->roles()->pluck('slug')->all();

        expect($roles)->toBe(['administrateur']);
    }
});

test('a manager without the permissions cannot edit, adjust or delete catalogue products', function () {
    $this->actingAs($this->manager);

    $this->get(route('stock.products.edit', $this->product))->assertForbidden();
    $this->put(route('stock.products.update', $this->product), ['name' => 'Modifié'])->assertForbidden();
    $this->get(route('stock.products.bulk-edit'))->assertForbidden();
    $this->post(route('stock.products.bulk-update'), [])->assertForbidden();
    $this->post(route('stock.adjust'), ['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id, 'action_type' => 'set', 'quantity' => 50])->assertForbidden();
    $this->delete(route('stock.products.destroy', $this->product))->assertForbidden();
    $this->post(route('stock.products.bulk-destroy'), ['product_ids' => [$this->product->id]])->assertForbidden();
    $this->delete(route('commercial.products.destroy', $this->product))->assertForbidden();

    $this->post(route('stock.scanner.action'), ['code' => 'PAP-A4', 'action_type' => 'inventaire', 'warehouse_id' => $this->warehouse->id, 'quantity' => 50])
        ->assertSessionHas('error');

    expect($this->product->fresh()->name)->toBe('Rame papier A4');

    $this->get(route('stock.index', ['tab' => 'disponibilite']))
        ->assertOk()
        ->assertDontSee(route('stock.products.edit', $this->product), false)
        ->assertDontSee("dispatch('open-stock-adjust'", false)
        ->assertDontSee("dispatch('open-delete-product'", false);
});

test('the administrator keeps every catalogue action', function () {
    $this->actingAs($this->admin)
        ->get(route('stock.index', ['tab' => 'disponibilite']))
        ->assertOk()
        ->assertSee(route('stock.products.edit', $this->product), false)
        ->assertSee("dispatch('open-stock-adjust'", false)
        ->assertSee("dispatch('open-delete-product'", false);

    $this->actingAs($this->admin)->get(route('stock.products.edit', $this->product))->assertOk();
    $this->actingAs($this->admin)->delete(route('stock.products.destroy', $this->product))->assertRedirect();
});

test('the administrator can grant a catalogue permission to a role', function () {
    Role::where('slug', 'responsable')->firstOrFail()->permissions()->attach(Permission::where('slug', 'products.edit')->value('id'));

    $this->actingAs($this->manager->fresh())->get(route('stock.products.edit', $this->product))->assertOk();
    $this->actingAs($this->manager->fresh())->delete(route('stock.products.destroy', $this->product))->assertForbidden();
});

test('adding products and printing the catalogue are granted to the roles that see the stock and can be withdrawn', function () {
    $stockRoles = Permission::where('slug', 'stocks.view')->firstOrFail()->roles()->pluck('slug')->push('administrateur')->unique()->sort()->values()->all();

    foreach (['products.create', 'stocks.catalog'] as $slug) {
        expect(Permission::where('slug', $slug)->firstOrFail()->roles()->pluck('slug')->sort()->values()->all())->toBe($stockRoles);
    }

    $this->actingAs($this->manager)->get(route('stock.products.create'))->assertOk();
    $this->actingAs($this->manager)->get(route('stock.products.print-catalog'))->assertOk();

    $manager = Role::where('slug', 'responsable')->firstOrFail();
    $manager->permissions()->detach(Permission::whereIn('slug', ['products.create', 'stocks.catalog'])->pluck('id'));
    $this->manager = $this->manager->fresh();

    $this->actingAs($this->manager);
    $this->get(route('stock.products.create'))->assertForbidden();
    $this->get(route('stock.products.bulk-create'))->assertForbidden();
    $this->post(route('stock.products.store'), ['name' => 'Nouveau'])->assertForbidden();
    $this->post(route('commercial.products.store'), ['name' => 'Nouveau'])->assertForbidden();
    $this->get(route('stock.products.print-catalog'))->assertForbidden();
    $this->get(route('stock.products.download-catalog'))->assertForbidden();

    $this->get(route('stock.index'))
        ->assertOk()
        ->assertDontSee(route('stock.products.create'), false)
        ->assertDontSee(route('stock.products.download-catalog'), false);
});
