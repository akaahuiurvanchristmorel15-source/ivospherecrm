<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());

    $this->promotion = Promotion::create([
        'code' => 'VIP10',
        'name' => 'Remise VIP',
        'type' => 'percent',
        'value' => 10,
        'min_amount' => 0,
        'is_active' => true,
    ]);

    $this->vip = Customer::create(['code' => 'CLI-VIP', 'name' => 'Client VIP', 'type' => 'particulier', 'status' => 'actif']);
    $this->other = Customer::create(['code' => 'CLI-AUTRE', 'name' => 'Autre client', 'type' => 'particulier', 'status' => 'actif']);
});

function posCheckoutPayload(Customer $customer, string $promoCode = 'VIP10'): array
{
    return [
        'customer_id' => $customer->id,
        'payment_method' => 'credit',
        'amount_paid' => 0,
        'promo_code' => $promoCode,
        'items' => [[
            'product_id' => Product::first()->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'discount' => 0,
        ]],
    ];
}

test('promo code can be assigned to customers and reopened to everyone', function () {
    $this->put(route('commercial.promotions.customers', $this->promotion), ['customer_ids' => [$this->vip->id]])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->promotion->customers()->pluck('customers.id')->all())->toBe([$this->vip->id]);

    $this->put(route('commercial.promotions.customers', $this->promotion), ['customer_ids' => []])
        ->assertRedirect();

    expect($this->promotion->customers()->count())->toBe(0);
});

test('assigning unknown customers is rejected', function () {
    $this->put(route('commercial.promotions.customers', $this->promotion), ['customer_ids' => [999999]])
        ->assertSessionHasErrors('customer_ids.0');

    expect($this->promotion->customers()->count())->toBe(0);
});

test('assigned customer gets the discount once and the code is marked as used', function () {
    $this->promotion->customers()->attach($this->vip);

    $this->post(route('commercial.pos.checkout'), posCheckoutPayload($this->vip))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $order = Order::where('customer_id', $this->vip->id)->latest('id')->first();
    $pivot = $this->promotion->customers()->first()->pivot;

    expect((float) $order->discount)->toBe(1000.0)
        ->and($pivot->used_at)->not->toBeNull()
        ->and($pivot->order_id)->toBe($order->id);

    $this->post(route('commercial.pos.checkout'), posCheckoutPayload($this->vip))
        ->assertSessionHas('error');

    expect(Order::where('customer_id', $this->vip->id)->count())->toBe(1);
});

test('restricted promo code is refused for other and walk-in customers', function () {
    $this->promotion->customers()->attach($this->vip);

    $this->post(route('commercial.pos.checkout'), posCheckoutPayload($this->other))
        ->assertSessionHas('error');

    $walkIn = posCheckoutPayload($this->other);
    unset($walkIn['customer_id']);

    $this->post(route('commercial.pos.checkout'), $walkIn)
        ->assertSessionHas('error');

    expect(Order::where('discount', '>', 0)->exists())->toBeFalse();
});

test('unassigned promo code stays usable by every customer', function () {
    $this->post(route('commercial.pos.checkout'), posCheckoutPayload($this->other))
        ->assertSessionHasNoErrors();

    expect((float) Order::where('customer_id', $this->other->id)->value('discount'))->toBe(1000.0);
});

test('promotions page shows customer assignment', function () {
    $this->promotion->customers()->attach($this->vip);

    $this->get(route('commercial.promotions.index'))
        ->assertOk()
        ->assertSee('1 client(s) réservé(s)')
        ->assertSee('Attribuer');
});

test('artisan command promo:assign assigns promo code to specified customers or clears them', function () {
    $this->artisan('promo:assign', ['code' => 'VIP10', '--customer' => [$this->vip->code]])
        ->assertSuccessful();

    expect($this->promotion->customers()->pluck('customers.id')->all())->toBe([$this->vip->id]);

    $this->artisan('promo:assign', ['code' => 'VIP10', '--clear' => true])
        ->assertSuccessful();

    expect($this->promotion->customers()->count())->toBe(0);
});
