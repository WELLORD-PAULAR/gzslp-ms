<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a customer can submit an order from the cart with purchase-time snapshots', function () {
    $customer = User::factory()->create();
    $product = Product::create([
        'name' => 'A4 Matte Print',
        'description' => 'Printed on matte stock.',
        'price' => '12.50',
        'paper_size' => 'A4',
        'paper_weight' => '150 gsm',
        'paper_finish' => 'Matte',
        'is_active' => true,
    ]);
    $variant = $product->variants()->firstOrFail();
    $cart = $customer->cart()->create();
    $cart->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 2]);

    $this->actingAs($customer)
        ->post(route('orders.store'), ['customer_note' => 'Please print with a white border.'])
        ->assertRedirect(route('orders.show', 1));

    $order = Order::with('items')->firstOrFail();
    expect($order->user_id)->toBe($customer->id)
        ->and($order->status)->toBe(Order::STATUS_PENDING)
        ->and($order->subtotal)->toBe('25.00')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->item_name)->toBe('A4 Matte Print')
        ->and($order->items->first()->unit_price)->toBe('12.50')
        ->and($order->items->first()->quantity)->toBe(2)
        ->and($order->items->first()->paper_finish)->toBe('Matte')
        ->and($order->items->first()->variant_name)->toBe('Standard')
        ->and($order->customer_note)->toBe('Please print with a white border.');

    expect($cart->items()->count())->toBe(0);

    $product->update(['name' => 'Renamed in catalog', 'price' => '19.00']);
    expect($order->items->first()->fresh()->item_name)->toBe('A4 Matte Print')
        ->and($order->items->first()->fresh()->unit_price)->toBe('12.50');

    $this->get(route('orders.index'))->assertOk()->assertSee('A4 Matte Print');
    $this->get(route('orders.show', $order))->assertOk()->assertSee('25.00');
});

test('quote-only services create quote-requested orders with a pre-quote subtotal', function () {
    $customer = User::factory()->create();
    $product = Product::create(['name' => 'Basic Print', 'price' => '7.50', 'is_active' => true]);
    $variant = $product->variants()->firstOrFail();
    $service = Service::create(['name' => 'Custom Artwork', 'starting_price' => null, 'is_active' => true]);
    $cart = $customer->cart()->create();
    $cart->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 2]);
    $cart->items()->create(['service_id' => $service->id, 'quantity' => 1]);

    $this->actingAs($customer)
        ->post(route('orders.store'))
        ->assertRedirect(route('orders.show', 1));

    $order = Order::with('items')->firstOrFail();
    expect($order->status)->toBe(Order::STATUS_QUOTE_REQUESTED)
        ->and($order->subtotal)->toBe('15.00')
        ->and($order->current_total)->toBeNull()
        ->and($order->items)->toHaveCount(2)
        ->and($cart->items()->count())->toBe(0);

    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Quote requested')
        ->assertSee('15.00')
        ->assertSee('Quote required');

    $this->patch(route('orders.cancel', $order))->assertRedirect(route('orders.show', $order));
    expect($order->fresh()->current_total)->toBeNull();
});

test('customers can cancel pending orders but cannot view another customers order', function () {
    $owner = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $order = $owner->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '10.00',
    ]);

    $this->actingAs($otherCustomer)
        ->get(route('orders.show', $order))
        ->assertNotFound();

    $this->actingAs($owner)
        ->patch(route('orders.cancel', $order))
        ->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED);
});

test('customers cannot cancel orders after processing starts', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PROCESSING,
        'subtotal' => '10.00',
    ]);

    $this->actingAs($customer)
        ->patch(route('orders.cancel', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHasErrors('order');

    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING);
});

test('an admin can quote a request then advance its status through allowed steps', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_QUOTE_REQUESTED,
        'subtotal' => '15.00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk();

    $this->patch(route('admin.orders.update', $order), [
        'status' => Order::STATUS_QUOTE_SENT,
        'quoted_total' => '22.50',
        'admin_note' => 'Includes design preparation.',
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_QUOTE_SENT)
        ->and($order->fresh()->quoted_total)->toBe('22.50')
        ->and($order->fresh()->current_total)->toBe('22.50');

    $this->patch(route('admin.orders.update', $order), [
        'status' => Order::STATUS_PROCESSING,
        'admin_note' => 'Customer approved the quote.',
    ])->assertSessionHasErrors('status');

    $this->actingAs($customer)
        ->patch(route('orders.accept-quote', $order))
        ->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING)
        ->and($order->fresh()->customer_accepted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => Order::STATUS_COMPLETED,
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_COMPLETED);
    $this->get(route('admin.orders.show', $order))->assertOk();
});

test('order status changes reject invalid transitions and missing quote prices', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_QUOTE_REQUESTED,
        'subtotal' => '0.00',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.update', $order), [
            'status' => Order::STATUS_COMPLETED,
        ])
        ->assertSessionHasErrors('status');

    $this->patch(route('admin.orders.update', $order), [
        'status' => Order::STATUS_QUOTE_SENT,
    ])->assertSessionHasErrors('quoted_total');

    expect($order->fresh()->status)->toBe(Order::STATUS_QUOTE_REQUESTED);
});

test('only admins can access the order management queue', function () {
    $this->get(route('admin.orders.index'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();

    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $this->actingAs($admin)->get(route('orders.index'))->assertForbidden();
});

test('employees can review orders but cannot change their status', function () {
    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();
    $order = User::factory()->create()->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '10.00',
    ]);

    $this->actingAs($employee)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertDontSee('Next status');
    $this->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertDontSee('Update order');
    $this->patch(route('admin.orders.update', $order), ['status' => Order::STATUS_PROCESSING])
        ->assertForbidden();

    expect($order->fresh()->status)->toBe(Order::STATUS_PENDING);
});

test('an empty or unavailable cart cannot become an order', function () {
    $customer = User::factory()->create();
    $cart = $customer->cart()->create();

    $this->actingAs($customer)
        ->from(route('cart'))
        ->post(route('orders.store'))
        ->assertSessionHasErrors('cart');
    expect(Order::count())->toBe(0);

    $product = Product::create(['name' => 'Print', 'price' => '8.00', 'is_active' => true]);
    $variant = $product->variants()->firstOrFail();
    $cart->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1]);
    $product->update(['is_active' => false]);

    $this->post(route('orders.store'))->assertSessionHasErrors('cart');
    expect(Order::count())->toBe(0)
        ->and($cart->items()->count())->toBe(1);
});

test('customers cannot accept a quote belonging to another customer', function () {
    $owner = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $order = $owner->orders()->create([
        'status' => Order::STATUS_QUOTE_SENT,
        'subtotal' => '0.00',
        'quoted_total' => '25.00',
    ]);

    $this->actingAs($otherCustomer)
        ->patch(route('orders.accept-quote', $order))
        ->assertNotFound();

    expect($order->fresh()->status)->toBe(Order::STATUS_QUOTE_SENT);
});

test('custom artwork is stored privately and only the customer or staff can download it', function () {
    Storage::fake('local');
    $customer = User::factory()->create();
    $product = Product::create(['name' => 'Custom print', 'price' => '20.00', 'is_active' => true]);
    $variant = $product->variants()->firstOrFail();
    $customer->cart()->create()->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1]);

    $this->actingAs($customer)
        ->post(route('orders.store'), [
            'customer_note' => 'Print this artwork.',
            'artwork' => UploadedFile::fake()->image('design.png'),
        ])
        ->assertRedirect(route('orders.show', 1));

    $order = Order::firstOrFail();
    Storage::disk('local')->assertExists($order->artwork_path);
    $this->get(route('orders.artwork', $order))->assertOk();

    $otherCustomer = User::factory()->create();
    $this->actingAs($otherCustomer)->get(route('orders.artwork', $order))->assertNotFound();

    $employee = User::factory()->create();
    $employee->forceFill(['role' => User::ROLE_EMPLOYEE])->save();
    $this->actingAs($employee)->get(route('admin.orders.artwork', $order))->assertOk();
});

function stockedProduct(string $onHand = '10.000', string $perUnit = '1.000'): array
{
    $product = Product::create(['name' => 'Printed shirt', 'category' => 'shirt', 'price' => '30.00', 'is_active' => true]);
    $variant = $product->variants()->firstOrFail();
    $supply = InventoryItem::create([
        'name' => 'Blank shirt / Large',
        'category' => 'blank_shirt',
        'unit' => 'piece',
        'quantity_on_hand' => $onHand,
        'reorder_level' => '2.000',
        'is_active' => true,
    ]);
    $variant->supplies()->attach($supply->id, ['quantity_per_unit' => $perUnit]);

    return [$product, $variant, $supply];
}

function adminUser(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    return $admin;
}

test('placing a priced order deducts the product supplies and lowers the available count', function () {
    $customer = User::factory()->create();
    [$product, $variant, $supply] = stockedProduct();
    $customer->cart()->create()->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);

    $this->actingAs($customer)->get(route('products.show', $product))->assertSee('10 available');
    $this->post(route('orders.store'))->assertRedirect(route('orders.show', 1));

    $order = Order::firstOrFail();
    expect($order->status)->toBe(Order::STATUS_PENDING)
        ->and($order->inventory_consumed_at)->not->toBeNull()
        ->and($supply->fresh()->quantity_on_hand)->toBe('7.000')
        ->and($supply->movements()->where('order_id', $order->id)->count())->toBe(1)
        ->and($supply->movements()->where('order_id', $order->id)->first()->type)->toBe(InventoryMovement::TYPE_USED);

    $this->get(route('products.show', $product))->assertSee('7 available');
});

test('starting production does not deduct supplies a second time', function () {
    $customer = User::factory()->create();
    $admin = adminUser();
    [$product, $variant, $supply] = stockedProduct();
    $customer->cart()->create()->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);
    $this->actingAs($customer)->post(route('orders.store'));
    $order = Order::firstOrFail();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => Order::STATUS_PROCESSING,
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING)
        ->and($supply->fresh()->quantity_on_hand)->toBe('7.000')
        ->and($supply->movements()->where('order_id', $order->id)->count())->toBe(1);
});

test('an order the supplies cannot cover is rejected at checkout and saves nothing', function () {
    $customer = User::factory()->create();
    [$product, $variant, $supply] = stockedProduct('2.000');
    $cart = $customer->cart()->create();
    $cart->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);

    $this->actingAs($customer)
        ->from(route('cart'))
        ->post(route('orders.store'))
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0)
        ->and($supply->fresh()->quantity_on_hand)->toBe('2.000')
        ->and($supply->movements()->count())->toBe(0)
        ->and($cart->items()->count())->toBe(1);
});

test('cancelling an order returns its supplies once', function () {
    $customer = User::factory()->create();
    [$product, $variant, $supply] = stockedProduct();
    $customer->cart()->create()->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);
    $this->actingAs($customer)->post(route('orders.store'));
    $order = Order::firstOrFail();
    expect($supply->fresh()->quantity_on_hand)->toBe('7.000');

    $this->patch(route('orders.cancel', $order))->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED)
        ->and($order->fresh()->inventory_consumed_at)->toBeNull()
        ->and($supply->fresh()->quantity_on_hand)->toBe('10.000')
        ->and($supply->movements()->where('type', InventoryMovement::TYPE_RETURNED)->count())->toBe(1);

    $this->patch(route('orders.cancel', $order))->assertSessionHasErrors('order');
    expect($supply->fresh()->quantity_on_hand)->toBe('10.000');
});

test('an admin cancelling a processing order returns its supplies', function () {
    $customer = User::factory()->create();
    $admin = adminUser();
    [$product, $variant, $supply] = stockedProduct();
    $customer->cart()->create()->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);
    $this->actingAs($customer)->post(route('orders.store'));
    $order = Order::firstOrFail();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => Order::STATUS_PROCESSING]);
    $this->patch(route('admin.orders.update', $order), ['status' => Order::STATUS_CANCELLED])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED)
        ->and($supply->fresh()->quantity_on_hand)->toBe('10.000');
});

test('a quote order takes its supplies when the quote is accepted and returns them on cancel', function () {
    $customer = User::factory()->create();
    $admin = adminUser();
    [$product, $variant, $supply] = stockedProduct();
    $service = Service::create(['name' => 'Custom Artwork', 'starting_price' => null, 'is_active' => true]);
    $cart = $customer->cart()->create();
    $cart->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 2]);
    $cart->items()->create(['service_id' => $service->id, 'quantity' => 1]);

    $this->actingAs($customer)->post(route('orders.store'));
    $order = Order::firstOrFail();

    expect($order->status)->toBe(Order::STATUS_QUOTE_REQUESTED)
        ->and($order->inventory_consumed_at)->toBeNull()
        ->and($supply->fresh()->quantity_on_hand)->toBe('10.000');

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => Order::STATUS_QUOTE_SENT,
        'quoted_total' => '90.00',
    ]);
    expect($supply->fresh()->quantity_on_hand)->toBe('10.000');

    $this->actingAs($customer)->patch(route('orders.accept-quote', $order));
    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING)
        ->and($supply->fresh()->quantity_on_hand)->toBe('8.000');

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => Order::STATUS_CANCELLED]);
    expect($supply->fresh()->quantity_on_hand)->toBe('10.000');
});

test('production cannot start on an older order if supplies are now short', function () {
    $customer = User::factory()->create();
    $admin = adminUser();
    [$product, $variant, $supply] = stockedProduct();
    $customer->cart()->create()->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);
    $this->actingAs($customer)->post(route('orders.store'));
    $order = Order::firstOrFail();

    // Simulate an order placed before supplies were taken at checkout.
    $order->forceFill(['inventory_consumed_at' => null])->save();
    $supply->update(['quantity_on_hand' => '2.000']);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.update', $order), ['status' => Order::STATUS_PROCESSING])
        ->assertSessionHasErrors('inventory');

    expect($order->fresh()->status)->toBe(Order::STATUS_PENDING)
        ->and($supply->fresh()->quantity_on_hand)->toBe('2.000');
});

test('a product option without a recipe can still be ordered and deducts nothing', function () {
    $customer = User::factory()->create();
    $product = Product::create(['name' => 'Sticker', 'category' => 'paper', 'price' => '5.00', 'is_active' => true]);
    $variant = $product->variants()->firstOrFail();
    $customer->cart()->create()->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 2,
    ]);

    $this->actingAs($customer)->post(route('orders.store'))->assertRedirect(route('orders.show', 1));

    expect(Order::count())->toBe(1)
        ->and(InventoryMovement::count())->toBe(0);
});
