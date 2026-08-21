<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Accepts card payments through Stripe Checkout. The customer is
 * redirected to a Stripe-hosted payment page; unlike Stripe Invoices,
 * Checkout Sessions support success_url/cancel_url, so Stripe redirects
 * the browser straight back to our success() route once payment
 * completes. We re-verify the session server-side (via the Stripe API)
 * before marking the order paid, rather than trusting the redirect
 * itself. The /stripe/webhook route (checkout.session.completed) marks
 * the order paid too, so fulfillment still happens even if the customer
 * closes their browser before the redirect back.
 */
class StripeGateway implements PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function pay(Order $order, string $currency = 'USD')
    {
        $session = Session::create([
            'mode'                => 'payment',
            'customer_email'      => $order->user->email ?? null,
            'line_items'          => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => strtolower($currency),
                    'unit_amount'  => (int) round(((float) $order->total_price) * 100),
                    'product_data' => ['name' => "Order #{$order->id}"],
                ],
            ]],
            'metadata'    => ['order_id' => (string) $order->id],
            'success_url' => route('orders.payment.success', ['method' => 'stripe', 'order_id' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('orders.payment.cancel', ['method' => 'stripe', 'order_id' => $order->id]),
        ]);

        return redirect()->away($session->url);
    }

    public function success(array $data, Order $order)
    {
        $sessionId = $data['session_id'] ?? null;
        if (!$sessionId) {
            return redirect()->route('orders.index')->with('error', 'Missing Stripe session.');
        }

        $session = Session::retrieve($sessionId);

        $matchesOrder = ($session->metadata->order_id ?? null) == $order->id;
        if (!$matchesOrder || $session->payment_status !== 'paid') {
            return redirect()->route('orders.index')->with('error', 'Stripe payment not confirmed.');
        }

        $this->markPaid($order, $session->payment_intent ?: $session->id);

        return redirect()->route('orders.index')->with('success', 'Stripe payment completed successfully!');
    }

    public function cancel(array $data, Order $order)
    {
        return redirect()->route('orders.index')->with('error', 'Stripe payment canceled.');
    }

    /**
     * Called from the /stripe/webhook route once Stripe confirms payment.
     */
    public function fulfillFromWebhook(object $session): void
    {
        $orderId = $session->metadata->order_id ?? null;
        if (!$orderId || $session->payment_status !== 'paid') {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        $this->markPaid($order, $session->payment_intent ?: $session->id);
    }

    protected function markPaid(Order $order, ?string $transactionId): void
    {
        DB::transaction(function () use ($order, $transactionId) {
            $alreadyRecorded = DB::table('transactions')
                ->where('transaction_id', $transactionId)
                ->exists();

            if (!$alreadyRecorded) {
                DB::table('transactions')->insert([
                    'order_id'       => $order->id,
                    'user_id'        => Auth::id() ?? $order->user_id,
                    'amount'         => $order->total_price,
                    'method'         => 'stripe',
                    'status'         => 'paid',
                    'transaction_id' => $transactionId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            if ($order->status !== 'paid') {
                $order->update(['status' => 'paid']);
            }
        });
    }
}
