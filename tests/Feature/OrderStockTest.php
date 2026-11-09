<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderStockTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Role::firstOrCreate(['name' => 'customer']);

        $this->customer = User::create([
            'firstname' => 'Test', 'lastname' => 'Customer',
            'email' => 'buyer@example.com', 'password' => bcrypt('secret-password'),
        ]);
        $this->customer->forceFill(['email_verified_at' => now()])->save();
        $this->customer->assignRole('customer');

        $category = Category::create(['name' => 'Phones']);
        $this->product = Product::create([
            'name' => 'Phone case', 'description' => str_repeat('A sturdy phone case. ', 3),
            'price' => 10.00, 'available_quantity' => 3, 'category_id' => $category->id, 'image' => 'x.jpg',
        ]);
    }

    private function add(int $quantity, array $extra = [])
    {
        return $this->actingAs($this->customer)
            ->from(route('orders.addProductPage', $this->product->id))
            ->post(route('orders.addProduct'), ['product_id' => $this->product->id, 'quantity' => $quantity] + $extra);
    }

    private function order(): Order
    {
        return Order::where('user_id', $this->customer->id)->firstOrFail();
    }

    public function test_cannot_order_more_than_available_stock(): void
    {
        $this->add(5)->assertSessionHasErrors('quantity');

        $this->assertSame(3, $this->product->fresh()->available_quantity);
    }

    public function test_buying_the_last_units_leaves_zero_not_negative(): void
    {
        $this->add(3)->assertOk();
        $this->add(1)->assertSessionHasErrors('quantity');

        $this->assertSame(0, $this->product->fresh()->available_quantity);
    }

    public function test_price_comes_from_the_product_not_the_request(): void
    {
        $this->add(2, ['price' => 0])->assertOk();

        $this->assertEquals(20.00, (float) $this->order()->total_price);
    }

    public function test_adding_same_product_twice_merges_into_one_line_and_removal_restores_all_stock(): void
    {
        $this->add(1)->assertOk();
        $this->add(2)->assertOk();

        $order = $this->order();
        $this->assertSame(1, $order->products()->count());
        $this->assertSame(3, (int) $order->products()->first()->pivot->quantity);
        $this->assertSame(0, $this->product->fresh()->available_quantity);

        $this->actingAs($this->customer)
            ->delete(route('orders.removeProduct', [$order->id, $this->product->id]))
            ->assertRedirect(route('orders.index'));

        $this->assertSame(3, $this->product->fresh()->available_quantity);
        $this->assertEquals(0.0, (float) $order->fresh()->total_price);
    }

    public function test_cancelling_an_order_returns_its_stock(): void
    {
        $this->add(2)->assertOk();

        $this->actingAs($this->customer)->put(route('orders.cancel', $this->order()->id));

        $this->assertSame(3, $this->product->fresh()->available_quantity);
        $this->assertSame('canceled', $this->order()->status);
    }
}
