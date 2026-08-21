<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Stripe;

/**
 * Accepts card payments through Stripe Invoices. The customer is billed
 * via a Stripe-hosted invoice page; Stripe has no return URL for hosted
 * invoices, so payment confirmation arrives asynchronously through the
 * /stripe/webhook route (invoice.paid) rather than the success()/cancel()
 * redirect flow the other gateways use.
 */
class StripeGateway implements PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function pay(Order $order, string $currency = 'USD')
    {
        $customer = $this->findOrCreateCustomer($order);

        $invoice = Invoice::create([
            'customer'           => $customer->id,
            'collection_method'  => 'send_invoice',
            'days_until_due'     => 1,
            'auto_advance'       => false,
            'metadata'           => ['order_id' => (string) $order->id],
        ]);

        InvoiceItem::create([
            'customer'    => $customer->id,
            'invoice'     => $invoice->id,
            'amount'      => (int) round(((float) $order->total_price) * 100),
            'currency'    => strtolower($currency),
            'description' => "Order #{$order->id}",
        ]);

        $invoice = $invoice->finalizeInvoice();

        return redirect()->away($invoice->hosted_invoice_url);
    }

    public function success(array $data, Order $order)
    {
        return redirect()->route('orders.index')
            ->with('success', 'If your Stripe payment succeeded, your order will update shortly.');
    }

    public function cancel(array $data, Order $order)
    {
        return redirect()->route('orders.index')->with('error', 'Stripe payment canceled.');
    }

    /**
     * Called from the /stripe/webhook route once Stripe confirms payment.
     */
    public function fulfillFromWebhook(object $invoice): void
    {
        $orderId = $invoice->metadata->order_id ?? null;
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        $this->markPaid($order, $invoice->id);
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

    protected function findOrCreateCustomer(Order $order): Customer
    {
        $email = $order->user->email ?? null;

        if ($email) {
            $existing = Customer::all(['email' => $email, 'limit' => 1]);
            if (!empty($existing->data)) {
                return $existing->data[0];
            }
        }

        return Customer::create([
            'email' => $email,
            'name'  => trim(($order->user->firstname ?? '') . ' ' . ($order->user->lastname ?? '')) ?: null,
        ]);
    }
}
