<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderItemRequest;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    public function store(OrderRequest $request)
    {
        $validated=$request->validated();
        $items=$validated['items'];
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $name=$product->name;
            $available=$product->available_quantity;
            if($available < $item['quantity']){
                return response()->json(["message" => "Sorry, We only $available of the product $name in our store please mimize the quantity number to an available number or cancel the order and reorder again without this product"]);
            }
            $available-=$item['quantity'];
            $product->update(['available_quantity' => $available]);
            $total += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'total_price' => $total,
            'status' => 'pending',
        ]);

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }

        return response()->json(['message' => 'Order placed successfully', 'order' => $order->load('items')], 201);
    }

    public function index()
    {
        $orders = Order::with('items.product')->where('user_id', Auth::id())->get();
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('items.product')->where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return response()->json($order);
    }
    public function cancel($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be canceled.'], 403);
        }

        $order->status = 'canceled';
        $order->save();

        return response()->json(['message' => 'Order canceled successfully']);
    }
    public function addItem(OrderItemRequest $request, $orderId)
    {
        $validated=$request->validated();

        $order = Order::findOrFail($orderId);

        // Optional: Only allow changes if order is not finalized
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Cannot modify this order.'], 403);
        }
        $product=Product::findOrFail($validated['product_id']);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price * $validated['quantity']
        ]);
        $order->update(['total_price' => $order->total_price+=$product->price * $validated['quantity']]);
        return response()->json(['message' => 'Item added.', 'item' => $item], 201);
    }
    public function deleteItem($orderId, $itemId)
    {
        $order = Order::findOrFail($orderId);
        $item = $order->items()->where('id', $itemId)->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete item from this order.'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }


}
