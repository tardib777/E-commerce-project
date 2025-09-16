<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PayPalGateway implements PaymentGateway
{
    protected $provider;

    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));
    }

    public function pay(Order $order)
    {
        $this->provider->getAccessToken();
        $response = $this->provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('payments.success', ['method' => 'paypal', 'order_id' => $order->id]),
                "cancel_url" => route('payments.cancel', ['method' => 'paypal', 'order_id' => $order->id]),
            ],
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $order->total_price,
                ],
            ]],
        ]);

        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return back()->with('error', 'Unable to process PayPal payment.');
    }

    public function success(array $data, Order $order)
    {
        $this->provider->getAccessToken();
        $response = $this->provider->capturePaymentOrder($data['token']);

        if ($response['status'] === 'COMPLETED') {
            Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'amount' => $order->total_price,
                'method' => 'paypal',
                'status' => 'paid',
                'transaction_id' => $data['token'],
            ]);
            $order->update(['status' => 'paid']);
            return redirect()
            ->route('orders.index')
            ->with('success', 'PayPal payment completed successfully!');
        }
        return redirect()
        ->route('orders.index')
        ->with('error', $response['message'] ?? 'PayPal payment failed.');
    }

    public function cancel(array $data, Order $order)
    {
        return back()->with('error', 'PayPal transaction canceled.');
    }
}
