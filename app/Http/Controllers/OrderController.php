<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderService;
use App\Models\Product;
use App\Services\Payments\PaymentFactory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Log;
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
        $currencies=Http::withHeader('x-api-key',config('NOWPayment.sandbox.api_key'))->get('https://api-sandbox.nowpayments.io/v1/merchant/coins')->json()['selectedCurrencies'];
        return view('orders.Checkout',['order' => $order, 'gateways' => $gateways, 'currencies' => $currencies]);
    }
    public function pay($method, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
        return $gateway->pay($order);
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
    public function nowPaymentCallback(Request $request){
        Log::info('Received NOWPayment callback',$request->headers->all());
    $signiture=$request->header('x-nowpayments-sig');
    $payload=file_get_contents('php://input');
    $computedSignature=hash_hmac('sha512', $payload, config('NOWPayment.sandbox.IPN_key'));
    if (!hash_equals($computedSignature,$signiture)) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }
    $data=$request->all();
    if(!$data['payment_id'] || !$data['order_id']){
        Log::error('Missing payment_id or order_id in NOWPayment callback', $data);
        return response()->json(['error' => 'Missing payment_id or order_id'], 400);
    }
    $order=Order::find($data['order_id']);
    if(!$order){
        Log::error('Order not found in NOWPayment callback', ['order_id' => $data['order_id']]);
        return response()->json(['error' => 'Order not found'], 404);
    }
        // مثال:
        if ($data['payment_status'] == 'confirmed') {
            // ✅ تفعيل الطلب
            DB::transaction(function () use ($order, $data) {
                DB::insert('transactions', [
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'amount' => $order->total_price,
                    'method' => 'paypal',
                    'status' => 'paid',
                    'transaction_id' => $data['token'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $order->update(['status' => 'paid']);
            }); 
             return response()->json(['message' => 'Payment confirmed and order activated']);
        }

    }
}
