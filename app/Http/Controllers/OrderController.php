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
        // Fetch the available crypto coins, but never let a NOWPayments outage/timeout
        // break checkout: fall back to an empty list (customer can still pick on the
        // NOWPayments hosted page since pay_currency is optional at invoice creation).
        $currencies = [];
        try {
            $resp = Http::withHeader('x-api-key', config('NOWPayment.sandbox.api_key'))
                ->get('https://api-sandbox.nowpayments.io/v1/merchant/coins');
            if ($resp->successful()) {
                $currencies = $resp->json()['selectedCurrencies'] ?? [];
            }
        } catch (\Throwable $e) {
            $currencies = [];
        }
        return view('orders.Checkout',['order' => $order, 'gateways' => $gateways, 'currencies' => $currencies]);
    }
    public function pay($method, $order_id, $crypto_currency = null)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
        return $gateway->pay($order, $crypto_currency);
    }

    public function success(Request $request, $method, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
       return $gateway->success($request->all(), $order);

    }

    public function checkPayment($order_id)
    {
        // Manual re-check for delayed crypto confirmations: re-polls NOWPayments for
        // the payment id recorded against this order and reconciles the status.
        $order = Order::where('id', $order_id)->where('user_id', Auth::id())->firstOrFail();

        $payment = DB::table('transactions')->where('order_id', $order->id)
            ->orderByDesc('id')->first();

        if (!$payment || empty($payment->transaction_id)) {
            return redirect()->route('orders.index')
                ->with('error', 'No payment has been started for this order yet.');
        }

        $status = (new \App\Services\Payments\NOWPaymentGateway())
            ->applyPaymentStatus($order, $payment->transaction_id);

        $order->refresh();
        if ($order->status === 'paid') {
            return redirect()->route('orders.index')->with('success', 'Payment confirmed — order marked as paid.');
        }

        return redirect()->route('orders.index')
            ->with('error', 'Payment not confirmed yet' . ($status ? " (status: {$status})" : '') . '. Please try again shortly.');
    }

    public function cancelPayment(Request $request, $method, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
        $gateway->cancel($request->all(), $order);

        return redirect()->route('orders.index')->with('error', 'Payment canceled.');
    }
    public function nowPaymentCallback(Request $request){
        Log::info('Received NOWPayment callback', $request->all());

        // Verify the IPN signature: HMAC-SHA512 of the request payload sorted by
        // key, using the IPN secret. This proves the call really came from NOWPayments.
        $signature = $request->header('x-nowpayments-sig');
        $params = $request->all();
        ksort($params);
        $computedSignature = hash_hmac('sha512', json_encode($params, JSON_UNESCAPED_SLASHES), config('NOWPayment.sandbox.IPN_key'));
        if (!$signature || !hash_equals($computedSignature, $signature)) {
            Log::error('Invalid NOWPayment IPN signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $paymentId = $request->input('payment_id');
        $orderId   = $request->input('order_id');
        if (!$paymentId || !$orderId) {
            Log::error('Missing payment_id or order_id in NOWPayment callback', $params);
            return response()->json(['error' => 'Missing payment_id or order_id'], 400);
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Order not found in NOWPayment callback', ['order_id' => $orderId]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        // NOWPayments marks a payment 'finished' (or 'confirmed') once the funds
        // are settled. Activate the order and record the transaction once.
        if (in_array($request->input('payment_status'), ['confirmed', 'finished'], true)) {
            DB::transaction(function () use ($order, $paymentId) {
                $alreadyRecorded = DB::table('transactions')
                    ->where('transaction_id', $paymentId)
                    ->exists();

                if (!$alreadyRecorded) {
                    DB::table('transactions')->insert([
                        'order_id'       => $order->id,
                        'user_id'        => $order->user_id,
                        'amount'         => $order->total_price,
                        'method'         => 'NOWPayment',
                        'status'         => 'paid',
                        'transaction_id' => $paymentId,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                if ($order->status !== 'paid') {
                    $order->update(['status' => 'paid']);
                }
            });

            return response()->json(['message' => 'Payment confirmed and order activated']);
        }

        // Other intermediate statuses (waiting, confirming, sending, ...) are
        // acknowledged so NOWPayments stops retrying this notification.
        return response()->json(['message' => 'Notification received']);
    }
}
