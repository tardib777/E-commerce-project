<?php
// app/Services/Payments/PaymentFactory.php
namespace App\Services\Payments;

use App\Contracts\PaymentGateway;

class PaymentFactory
{
    public static function make(string $method): PaymentGateway
    {
        return match ($method) {
            'visa' => new VisaGateway(),
            default => throw new \Exception("Unsupported payment method: $method"),
        };
    }
}
