<?php
namespace App\Services;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService{
    public function index(){
        $orders=Auth::user()->orders()->with('products')->get();
        return $orders;
    }
    public function GetPendingOrCreate(){
        $order=Auth::user()->orders()->where('status','pending')->first();
        if(!$order){
            $order=Auth::user()->orders()->create(["status"=>"pending","total_price"=>0]);
        }
        return $order;
    }

    /**
     * Reserve stock for a product in a pending order.
     *
     * Runs in one transaction with the product row locked, so two customers
     * buying the last unit at the same time can't both get it and stock can
     * never go below zero. The line price always comes from the database,
     * never from the request. Adding a product that is already in the order
     * increases that line instead of creating a duplicate one.
     *
     * @throws ValidationException when there isn't enough stock
     */
    public function addProductToOrder(array $data, Order $order): bool
    {
        return DB::transaction(function () use ($data, $order) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $quantity = (int) $data['quantity'];

            if ($quantity < 1 || $quantity > $product->available_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => $product->available_quantity > 0
                        ? "Only {$product->available_quantity} left in stock."
                        : 'This product is out of stock.',
                ]);
            }

            $lineTotal = round($product->price * $quantity, 2);
            $existing = $order->products()->where('product_id', $product->id)->first();

            if ($existing) {
                $order->products()->updateExistingPivot($product->id, [
                    'quantity' => $existing->pivot->quantity + $quantity,
                    'price'    => $existing->pivot->price + $lineTotal,
                ]);
            } else {
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price'    => $lineTotal,
                ]);
            }

            $product->decrement('available_quantity', $quantity);
            $order->total_price += $lineTotal;
            $order->save();

            return true;
        });
    }

    /**
     * Return a product line's stock and remove it from the order.
     * Restores every row for that product (older data may hold duplicates).
     */
    public function removeProductFromOrder(Order $order, $product_id): bool
    {
        return DB::transaction(function () use ($order, $product_id) {
            $lines = $order->products()->where('product_id', $product_id)->get();
            if ($lines->isEmpty()) {
                return true;
            }

            $quantity = $lines->sum(fn ($line) => $line->pivot->quantity);
            $price    = $lines->sum(fn ($line) => $line->pivot->price);

            Product::whereKey($product_id)->lockForUpdate()->first()?->increment('available_quantity', $quantity);
            $order->products()->detach($product_id);
            $order->total_price = max(0, $order->total_price - $price);
            $order->save();

            return true;
        });
    }
}
