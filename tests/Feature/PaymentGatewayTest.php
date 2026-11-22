<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a customer can pay an order with the dummy gateway', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '25.00',
    ]);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertRedirect(route('orders.show', $order));

    $payment = $order->payments()->firstOrFail();

    expect($payment->status)->toBe(Payment::STATUS_SUCCEEDED)
        ->and($payment->gateway)->toBe('dummy')
        ->and($payment->method)->toBe('card')
        ->and($payment->amount)->toBe('25.00')
        ->and($payment->currency)->toBe('PHP')
        ->and($payment->reference)->toStartWith('DUM-')
        ->and($order->fresh()->isPaid())->toBeTrue()
        ->and($order->fresh()->amountDue())->toBe('0.00');

    $this->actingAs($customer)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Payment history')
        ->assertSee('Paid in full')
        ->assertSee($payment->reference);
});

test('a customer can pay the remaining balance in more than one charge', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '30.00',
    ]);

    $customer->payments()->create([
        'order_id' => $order->id,
        'gateway' => 'dummy',
        'method' => 'card',
        'amount' => '10.00',
        'currency' => 'PHP',
        'status' => Payment::STATUS_SUCCEEDED,
        'reference' => 'DUM-PARTIAL',
    ]);

    expect($order->fresh()->amountDue())->toBe('20.00');

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->amountDue())->toBe('0.00')
        ->and($order->fresh()->paidAmountCents())->toBe(3000)
        ->and($order->fresh()->payments()->where('status', Payment::STATUS_SUCCEEDED)->count())->toBe(2);
});

test('an order cannot be paid twice in full', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '15.00',
    ]);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertRedirect();

    $this->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertSessionHasErrors('payment');

    expect($order->payments()->count())->toBe(1);
});

test('quote pending, unaccepted, and cancelled orders cannot be paid', function () {
    $customer = User::factory()->create();
    $quoteOrder = $customer->orders()->create([
        'status' => Order::STATUS_QUOTE_REQUESTED,
        'subtotal' => '10.00',
    ]);
    $unacceptedQuoteOrder = $customer->orders()->create([
        'status' => Order::STATUS_QUOTE_SENT,
        'subtotal' => '10.00',
        'quoted_total' => '20.00',
    ]);
    $cancelledOrder = $customer->orders()->create([
        'status' => Order::STATUS_CANCELLED,
        'subtotal' => '10.00',
    ]);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $quoteOrder), ['method' => 'card'])
        ->assertSessionHasErrors('payment');

    $this->post(route('orders.payment.store', $unacceptedQuoteOrder), ['method' => 'card'])
        ->assertSessionHasErrors('payment');

    $this->post(route('orders.payment.store', $cancelledOrder), ['method' => 'card'])
        ->assertSessionHasErrors('payment');

    expect(Payment::count())->toBe(0);
});

test('an accepted quote can be paid using the quoted total', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PROCESSING,
        'subtotal' => '10.00',
        'quoted_total' => '20.00',
        'customer_accepted_at' => now(),
    ]);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertRedirect(route('orders.show', $order));

    expect($order->payments()->firstOrFail()->amount)->toBe('20.00')
        ->and($order->fresh()->isPaid())->toBeTrue();
});

test('a declined charge is recorded as failed and can be retried', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '40.00',
    ]);

    $gateway = Mockery::mock(PaymentGateway::class);
    $gateway->shouldReceive('name')->andReturn('dummy');
    $gateway->shouldReceive('charge')->once()->andReturn(PaymentResult::failed('Card declined.'));
    $this->app->instance(PaymentGateway::class, $gateway);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertSessionHasErrors('payment');

    $failed = $order->payments()->firstOrFail();
    expect($failed->status)->toBe(Payment::STATUS_FAILED)
        ->and($failed->failure_reason)->toBe('Card declined.')
        ->and($failed->reference)->toBeNull()
        ->and($order->fresh()->isPaid())->toBeFalse();

    $this->app->forgetInstance(PaymentGateway::class);

    $this->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->isPaid())->toBeTrue()
        ->and($order->payments()->where('status', Payment::STATUS_SUCCEEDED)->count())->toBe(1);
});

test('a customer cannot pay another customers order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = $owner->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '10.00',
    ]);

    $this->actingAs($other)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertNotFound();

    expect(Payment::count())->toBe(0);
});

test('staff can see recorded payments on the order and in the order list', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '12.00',
    ]);
    $payment = $customer->payments()->create([
        'order_id' => $order->id,
        'gateway' => 'dummy',
        'method' => 'card',
        'amount' => '12.00',
        'currency' => 'PHP',
        'status' => Payment::STATUS_SUCCEEDED,
        'reference' => 'DUM-ADMINVIEW',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Payment history')
        ->assertSee($payment->reference);

    $this->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('Payment')
        ->assertSee('Paid');
});

test('a paid order cannot be cancelled by the customer or staff', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '10.00',
    ]);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertRedirect(route('orders.show', $order));

    $this->patch(route('orders.cancel', $order))->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->status)->toBe(Order::STATUS_PENDING);

    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertDontSee('Cancel order')
        ->assertSee('Payment history');

    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), ['status' => Order::STATUS_CANCELLED])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(Order::STATUS_PENDING);
});

test('a gateway exception is recorded as a failed payment instead of a server error', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => Order::STATUS_PENDING,
        'subtotal' => '18.00',
    ]);

    $gateway = Mockery::mock(PaymentGateway::class);
    $gateway->shouldReceive('name')->andReturn('dummy');
    $gateway->shouldReceive('charge')->once()->andThrow(new RuntimeException('Provider offline.'));
    $this->app->instance(PaymentGateway::class, $gateway);

    $this->actingAs($customer)
        ->post(route('orders.payment.store', $order), ['method' => 'card'])
        ->assertSessionHasErrors('payment');

    $failed = $order->payments()->firstOrFail();
    expect($failed->status)->toBe(Payment::STATUS_FAILED)
        ->and($failed->failure_reason)->toBe('The payment gateway could not be reached.')
        ->and($failed->gateway_response['error'])->toBe('Provider offline.')
        ->and($order->fresh()->isPaid())->toBeFalse();
});
