<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NOWPaymentGateway implements PaymentGateway
{
    protected $apikey;

    public function __construct()
    {
        $this->apikey = config('NOWPayment.sandbox.api_key');
    }
    public function pay(Order $order, $crypto_currency = null)
    {
        // Create a NOWPayments invoice and redirect the customer to the hosted
        // payment page. The order is only marked paid later, via the IPN callback.
        $payload = [
            'price_amount'     => $order->total_price,
            'price_currency'   => 'USD',
            'order_id'         => (string) $order->id,
            'order_description' => 'Order #' . $order->id,
            'ipn_callback_url' => route('orders.payment.nowpayment.callback'),
            'success_url'      => route('orders.payment.success', ['method' => 'NOWPayment', 'order_id' => $order->id]),
            'cancel_url'       => route('orders.payment.cancel', ['method' => 'NOWPayment', 'order_id' => $order->id]),
        ];

        // pay_currency is optional: only send it when the customer picked a coin.
        // If omitted, NOWPayments lets them choose the currency on its hosted page.
        // Sending an empty value makes the API reject the request ("must be a string").
        $currency = $crypto_currency ?? request('crypto_currency');
        if (!empty($currency)) {
            $payload['pay_currency'] = $currency;
        }

        $response = Http::withHeaders([
            'X-API-KEY' => $this->apikey,
            'Content-Type' => 'application/json',
        ])->post('https://api-sandbox.nowpayments.io/v1/invoice', $payload);

        if (!$response->successful()) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Could not start the crypto payment. Please try again.');
        }

        return redirect()->away($response->json()['invoice_url']);
    }

    public function success(array $data, Order $order)
    {
        // The IPN webhook is the primary confirmation path, but on hosts that block
        // inbound webhooks we also reconcile here by polling NOWPayments for the
        // payment status when the customer returns. NOWPayments appends the payment
        // id to the return URL as NP_id.
        $paymentId = $data['NP_id'] ?? $data['payment_id'] ?? null;

        if ($paymentId) {
            $status = $this->applyPaymentStatus($order, (string) $paymentId);
            if (in_array($status, ['finished', 'confirmed'], true)) {
                return redirect()->route('orders.index')
                    ->with('success', 'Payment received — your order is now marked as paid.');
            }
            if ($status !== null) {
                return redirect()->route('orders.index')
                    ->with('success', "Your crypto payment is being confirmed (status: {$status}). Use \"Check payment status\" on the order in a moment to refresh.");
            }
        }

        return redirect()->route('orders.index')
            ->with('success', 'Thanks! Your crypto payment is being processed. Use "Check payment status" on the order once it confirms.');
    }

    /**
     * Poll NOWPayments for a payment's status and reconcile it with the order.
     * This is the fallback to the IPN webhook for hosts that block inbound callbacks:
     * it uses an OUTBOUND call (GET /v1/payment/{id}), records/updates the transaction,
     * and marks the order paid once the payment is confirmed.
     * Returns the NOWPayments payment_status, or null if the lookup failed.
     */
    public function applyPaymentStatus(Order $order, string $paymentId): ?string
    {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apikey,
        ])->get("https://api-sandbox.nowpayments.io/v1/payment/{$paymentId}");

        if (!$response->successful()) {
            return null;
        }

        $status = $response->json()['payment_status'] ?? null;
        if (!$status) {
            return null;
        }

        $paid = in_array($status, ['finished', 'confirmed'], true);

        try {
            DB::transaction(function () use ($order, $paymentId, $status, $paid) {
                $existing = DB::table('transactions')->where('transaction_id', $paymentId)->first();
                if ($existing) {
                    DB::table('transactions')->where('transaction_id', $paymentId)
                        ->update(['status' => $paid ? 'paid' : $status, 'updated_at' => now()]);
                } else {
                    DB::table('transactions')->insert([
                        'order_id'       => $order->id,
                        'user_id'        => $order->user_id,
                        'amount'         => $order->total_price,
                        'method'         => 'NOWPayment',
                        'status'         => $paid ? 'paid' : $status,
                        'transaction_id' => $paymentId,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                if ($paid && $order->status !== 'paid') {
                    $order->update(['status' => 'paid']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('NOWPayment status reconcile failed: ' . $e->getMessage());
        }

        return $status;
    }

    public function cancel(array $data, Order $order)
    {
        return redirect()
            ->route('orders.index')
            ->with('error', 'NOWPayments transaction canceled.');
    }
}
