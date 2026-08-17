<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Accepts Visa card payments through CyberSource Secure Acceptance Hosted
 * Checkout (developer.visa.com). The customer is redirected to a
 * CyberSource-hosted payment page; CyberSource POSTs the signed result
 * back to our success route once they pay, decline, or cancel.
 *
 * Field names and the HMAC-SHA256 signing scheme follow CyberSource's
 * published Secure Acceptance Configuration Guide. This has not been
 * exercised against a live sandbox yet — verify against real sandbox
 * responses once CYBERSOURCE_* credentials are set in .env.
 */
class VisaGateway implements PaymentGateway
{
    protected ?string $accessKey;
    protected ?string $profileId;
    protected ?string $secretKey;
    protected string $endpoint;

    public function __construct()
    {
        $this->accessKey = config('cybersource.access_key');
        $this->profileId = config('cybersource.profile_id');
        $this->secretKey = config('cybersource.secret_key');
        $mode = config('cybersource.mode', 'sandbox');
        $this->endpoint = config("cybersource.endpoints.$mode");
    }

    public function pay(Order $order, string $currency = 'USD')
    {
        $returnUrl = route('orders.payment.success', ['method' => 'visa', 'order_id' => $order->id]);

        $fields = [
            'access_key'                   => $this->accessKey,
            'profile_id'                   => $this->profileId,
            'transaction_uuid'             => (string) Str::uuid(),
            'signed_date_time'             => gmdate('Y-m-d\TH:i:s\Z'),
            'locale'                       => 'en',
            'transaction_type'             => 'sale',
            'reference_number'             => (string) $order->id,
            'amount'                       => number_format((float) $order->total_price, 2, '.', ''),
            'currency'                     => $currency,
            'payment_method'               => 'card',
            'override_custom_receipt_page' => $returnUrl,
            'override_custom_cancel_page'  => $returnUrl,
        ];

        $fields['signed_field_names'] = implode(',', array_keys($fields)) . ',signed_field_names';
        $fields['unsigned_field_names'] = '';
        $fields['signature'] = $this->sign($fields);

        return view('payments.visa.redirect', [
            'endpoint' => $this->endpoint,
            'fields'   => $fields,
        ]);
    }

    public function success(array $data, Order $order)
    {
        if (!$this->verify($data)) {
            return redirect()->route('orders.index')->with('error', 'Invalid Visa payment signature.');
        }

        return match ($data['decision'] ?? null) {
            'ACCEPT' => $this->markPaid($order, $data),
            'CANCEL' => redirect()->route('orders.index')->with('error', 'Visa payment canceled.'),
            default  => redirect()->route('orders.index')->with('error', 'Visa payment declined: ' . ($data['message'] ?? 'unknown error')),
        };
    }

    public function cancel(array $data, Order $order)
    {
        return redirect()->route('orders.index')->with('error', 'Visa payment canceled.');
    }

    protected function markPaid(Order $order, array $data)
    {
        DB::transaction(function () use ($order, $data) {
            DB::insert('transactions', [
                'order_id'       => $order->id,
                'user_id'        => Auth::id(),
                'amount'         => $order->total_price,
                'method'         => 'visa',
                'status'         => 'paid',
                'transaction_id' => $data['transaction_id'] ?? null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $order->update(['status' => 'paid']);
        });

        return redirect()->route('orders.index')->with('success', 'Visa payment completed successfully!');
    }

    protected function sign(array $fields): string
    {
        return base64_encode(hash_hmac('sha256', $this->dataToSign($fields, explode(',', $fields['signed_field_names'])), $this->secretKey, true));
    }

    protected function verify(array $data): bool
    {
        if (empty($data['signature']) || empty($data['signed_field_names'])) {
            return false;
        }

        $expected = base64_encode(hash_hmac(
            'sha256',
            $this->dataToSign($data, explode(',', $data['signed_field_names'])),
            $this->secretKey,
            true
        ));

        return hash_equals($expected, $data['signature']);
    }

    protected function dataToSign(array $fields, array $signedFieldNames): string
    {
        return implode(',', array_map(
            fn ($name) => $name . '=' . ($fields[$name] ?? ''),
            $signedFieldNames
        ));
    }
}
