<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class NOWPaymentGateway implements PaymentGateway
{
    protected $apikey;

    public function __construct()
    {
        $this->apikey = config('NOWPayment.sandbox.api_key');
    }
    public function pay(Order $order, $crypto_currency = null)
    {
        // Implement NOWPayment payment logic here
        //Create Invoice

         $response=Http::withHeaders([
            'X-API-KEY' => $this->apikey,
            'Content-Type' => 'application/json'
         ])->post('https://api-sandbox.nowpayments.io/v1/invoice', [
            "price_amount" => $order->total_price,
            "price_currency" => 'USD',
            'pay_currency' => $crypto_currency ?? request('crypto_currency'),
            "ipn_callback_url" => route('orders.payment.nowpayment.callback'),

            ]);
            if(!$response->successful()){
                throw new \Exception('Failed to create NOWPayment invoice: ' . $response->body());
            }
            return redirect()->away($response->json()['invoice_url']);
    }
    public function success(array $data, Order $order, $paymentId=null)
    {
        // Implement NOWPayment success logic here
        $response=Http::withHeaders([
            'X-API-KEY' => $this->apikey,
            'Content-Type' => 'application/json'
        ])->get("https://api-sandbox.nowpayments.io/v1/payment/$paymentId");
        return $response->json();
    
       /* $this->provider->getAccessToken();
        $response = $this->provider->capturePaymentOrder($data['token']);

        if ($response['status'] === 'COMPLETED') {
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
                return redirect()
                ->route('orders.index')
                ->with('success', 'PayPal payment completed successfully!');
            });
        }
        return redirect()
        ->route('orders.index')
        ->with('error', $response['message'] ?? 'PayPal payment failed.');*/
    }

    public function cancel(array $data, Order $order)
    {
        /*return back()->with('error', 'PayPal transaction canceled.');*/
    }
}
