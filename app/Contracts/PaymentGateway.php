<?php
namespace App\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    public function pay(Order $order, string $currency = 'USD');
    public function success(array $data, Order $order);
    public function cancel(array $data, Order $order);
}
