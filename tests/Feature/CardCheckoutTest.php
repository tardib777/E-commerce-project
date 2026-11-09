<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CardCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Role::firstOrCreate(['name' => 'customer']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    private function customer(string $email): User
    {
        $user = User::create([
            'firstname' => 'Test',
            'lastname'  => 'Customer',
            'email'     => $email,
            'password'  => bcrypt('secret-password'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole('customer');

        return $user;
    }

    private function orderFor(User $user, string $status = 'pending'): Order
    {
        return Order::create([
            'user_id'     => $user->id,
            'total_price' => 25.50,
            'status'      => $status,
        ]);
    }

    public function test_owner_sees_card_checkout_with_visa_and_mastercard(): void
    {
        $owner = $this->customer('owner@example.com');
        $order = $this->orderFor($owner);

        $this->actingAs($owner)
            ->get(route('orders.checkout', $order->id))
            ->assertOk()
            ->assertSee('Credit / Debit Card (Visa, Mastercard)')
            ->assertSee('$25.50')
            ->assertDontSee('NOWPayment');
    }

    public function test_customer_cannot_checkout_pay_or_cancel_someone_elses_order(): void
    {
        $owner = $this->customer('owner@example.com');
        $other = $this->customer('other@example.com');
        $order = $this->orderFor($owner);

        $this->actingAs($other)->get(route('orders.checkout', $order->id))->assertNotFound();
        $this->actingAs($other)->get(route('orders.payment.pay', ['method' => 'stripe', 'order_id' => $order->id]))->assertNotFound();
        $this->actingAs($other)->put(route('orders.cancel', $order->id))->assertNotFound();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_paid_order_cannot_be_checked_out_again(): void
    {
        $owner = $this->customer('owner@example.com');
        $order = $this->orderFor($owner, 'paid');

        $this->actingAs($owner)
            ->get(route('orders.checkout', $order->id))
            ->assertRedirect(route('orders.index'));
    }

    public function test_removed_crypto_gateway_is_rejected(): void
    {
        $owner = $this->customer('owner@example.com');
        $order = $this->orderFor($owner);

        $this->actingAs($owner)
            ->get(route('orders.payment.pay', ['method' => 'NOWPayment', 'order_id' => $order->id]))
            ->assertStatus(422);
    }

    public function test_paying_redirects_to_a_stripe_hosted_card_checkout(): void
    {
        if (!config('services.stripe.secret')) {
            $this->markTestSkipped('STRIPE_SECRET not configured.');
        }

        $owner = $this->customer('owner@example.com');
        $order = $this->orderFor($owner);

        $response = $this->actingAs($owner)
            ->get(route('orders.payment.pay', ['method' => 'stripe', 'order_id' => $order->id, 'currency' => 'USD']));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://checkout.stripe.com/', $response->headers->get('Location'));
    }
}
