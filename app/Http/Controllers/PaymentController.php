<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Payments\PaymentFactory;
use App\Models\Order;

class PaymentController extends Controller
{
    public function checkout($order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateways = config('payments.gateways');
        return view('orders.checkout', compact('order', 'gateways'));
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

    public function cancel(Request $request, $method, $order_id)
    {
        $order = Order::findOrFail($order_id);
        $gateway = PaymentFactory::make($method);
        $gateway->cancel($request->all(), $order);

        return redirect()->route('orders.index')->with('error', 'Payment canceled.');
    }
}
