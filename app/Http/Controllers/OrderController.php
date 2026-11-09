<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderService;
use App\Models\Product;
use App\Services\Payments\PaymentFactory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
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
        // Price is deliberately not taken from the request: OrderService
        // prices each line from the product record, and checks stock.
        $data=$request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $order=$this->orderService->GetPendingOrCreate();
        $status=$this->orderService->addProductToOrder($data,$order);
        if($status == true){
            return view('orders.success');
        }
    }
    /**
     * Load an order that belongs to the signed-in customer, or 404.
     * Every customer-facing order action goes through this so one
     * customer can never view, change, cancel or pay another's order
     * just by editing the id in the URL.
     */
    protected function ownedOrder($orderId): Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    /**
     * Only pending orders can be paid; paid/canceled ones are bounced back.
     */
    protected function ensurePayable(Order $order): void
    {
        if ($order->status !== 'pending') {
            abort(redirect()->route('orders.index')->with('error', "Order #{$order->id} is {$order->status} and can't be paid."));
        }
    }

    public function removeProduct($order,$product_id){
        $order=$this->ownedOrder($order);
        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', "Order #{$order->id} is {$order->status} and can't be changed.");
        }
        $status=$this->orderService->removeProductFromOrder($order,$product_id);
        if($status == true){
            return redirect()->route('orders.index');
        }
    }
    public function cancel($order_id){
        $order=$this->ownedOrder($order_id);
        if ($order->status !== 'pending') {
            return redirect()->route('orders.index')->with('error', "Order #{$order->id} is already {$order->status}.");
        }
        foreach($order->products as $product){
            $product->available_quantity += $product->pivot->quantity;
            $product->save();
        }
        $order->update(['status'=>'canceled']);
        return redirect()->route('orders.index');
    }
    public function checkout($order){
        $order = $this->ownedOrder($order);
        $this->ensurePayable($order);
        $gateways = config('payments.gateways');
        $currencies = config('payments.currencies');
        return view('orders.Checkout',['order' => $order, 'gateways' => $gateways, 'currencies' => $currencies]);
    }
    public function pay($method, $order_id, $currency = 'USD')
    {
        $order = $this->ownedOrder($order_id);
        $this->ensurePayable($order);
        if (!array_key_exists($method, config('payments.gateways'))) {
            abort(422, 'Unsupported payment method.');
        }
        if (!array_key_exists($currency, config('payments.currencies'))) {
            abort(422, 'Unsupported currency.');
        }
        $gateway = PaymentFactory::make($method);
        return $gateway->pay($order, $currency);
    }

    public function success(Request $request, $method, $order_id)
    {
        $order = $this->ownedOrder($order_id);
        $gateway = PaymentFactory::make($method);
       return $gateway->success($request->all(), $order);

    }

    public function cancelPayment(Request $request, $method, $order_id)
    {
        $order = $this->ownedOrder($order_id);
        $gateway = PaymentFactory::make($method);
        $gateway->cancel($request->all(), $order);

        return redirect()->route('orders.index')->with('error', 'Payment canceled.');
    }

    public function stripeWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $gateway = PaymentFactory::make('stripe');
            $gateway->fulfillFromWebhook($event->data->object);
        }

        return response('OK', 200);
    }
}
