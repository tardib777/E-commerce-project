<?php 
namespace App\Services;
use App\Models\Order;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderItemRequest;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderService{
    public function store(array $data)
    {
        $items=$data['items'];
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $name=$product->name;
            $available=$product->available_quantity;
            if($available < $item['quantity']){
                return "في المخزن يرجى تقليل الكمية المطلوبة $name من المنتج $available ليس لدينا سوى";
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

        return ['message' => 'تم إنشاء الطلب بنجاح', 'order' => $order->load('items')];
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
        $items=$order->items;
        if ($order->status !== 'pending') {
            return 'تم إلغاء هذا الطلب مسبقا';
        }
        foreach($items as $item){
            $item->product->available_quantity+=$item->quantity;
            $item->product->update(['available_quantity' => $item->product->available_quantity]);
        }
        $order->status = 'canceled';
        $order->save();

        return 'تم إلغاء الطلب بنجاح';
    }
    public function addItem(array $data, $orderId)
    {

        $order = Order::findOrFail($orderId);

        // Optional: Only allow changes if order is not finalized
        if ($order->status !== 'pending') {
            return 'لا يمكنك تعديل هذا الطلب';
        }
        $product=Product::findOrFail($data['product_id']);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'price' => $product->price * $data['quantity'],
        ]);
        $item->product->available_quantity-=$item->quantity;
        $item->product->update(['available_quantity' => $item->product->available_quantity]);
        $order->update(['total_price' => $order->total_price+=$product->price * $data['quantity']]);
        return ['message' => 'تمت إضافة المنتج بنجاح', 'item' => $item];
    }
    public function deleteItem($orderId, $itemId)
    {
        $order = Order::findOrFail($orderId);
        $item = $order->items->where('id',$itemId)->firstOrFail();
        $product=$item->product;
        if ($order->status !== 'pending') {
            return 'لا يمكنك حذف العنصر من هذا الطلب لأنه تم دفع ثمنه أو إلغاؤه';
        }
        $item->delete();
        $item->product->available_quantity+=$item->quantity;
        $item->product->update(['available_quantity' => $item->product->available_quantity]);
        $order->update(['total_price' => $order->total_price-=$item->product->price * $item->quantity]);
        return 'تم حذف العنصر بنجاح';
    }


}