<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderService;
use App\Models\Product;
use App\Services\Payments\PaymentFactory;
use Exception;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService=$orderService;
    }
    public function index(){
        $orders=$this->orderService->index();
        $user=Auth::user();
        return view('orders.show',compact('orders','user'));
    }
    public function adminIndex(){
        $orders=Order::with('user','products')->latest()->get();
        return view('orders.admin_index',compact('orders'));
    }
    public function addProductPage($product_id){
        $product=Product::where('id',$product_id)->firstOrFail();
        return view('orders.addProduct',compact('product'));
    }
    public function addProduct(Request $request)
    {
        $data=$request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);
        $data['price']=$data['quantity']*$data['price'];
        $order=$this->orderService->GetPendingOrCreate();
        $status=$this->orderService->addProductToOrder($data,$order);
        if($status == true){
            return view('orders.success');
        }
    }
    public function removeProduct($order,$product_id){
        $order=Order::where('id',$order)->firstOrFail();
        $status=$this->orderService->removeProductFromOrder($order,$product_id);
        if($status == true){
            return redirect()->route('orders.index');
        }
    }
    public function cancel($order_id){
        $order=Order::where('id',$order_id)->firstOrFail();
        foreach($order->products as $product){
            $product->available_quantity += $product->pivot->quantity;
            $product->save();
        }
        $order->update(['status'=>'canceled']);
        return redirect()->route('orders.index');
    }
    public function checkout(Order $order){
        $gateways = config('payments.gateways');
        $currencies = config('payments.currencies');
        return view('orders.Checkout',['order' => $order, 'gateways' => $gateways, 'currencies' => $currencies]);
    }
    public function pay($method, $order_id, $currency = 'USD')
    {
        $order = Order::findOrFail($order_id);
        if (!array_key_exists($currency, config('payments.currencies'))) {
            abort(422, 'Unsupported currency.');
        }
        $gateway = PaymentFactory::make($method);
        return $gateway->pay($order, $currency);
    }

    public function success(Request $request, $method, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
       return $gateway->success($request->all(), $order);

    }

    public function cancelPayment(Request $request, $method, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
        $gateway->cancel($request->all(), $order);

        return redirect()->route('orders.index')->with('error', 'Payment canceled.');
    }
}
