<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function payWithWallet(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::where('id', $orderId)
                      ->where('user_id', $user->id)
                      ->where('status', 'pending')
                      ->firstOrFail();
        if($order->status !='pending'){
            return response()->json(["message" => "You have already paid the order"]);
        }
        else{
            if ($user->balance < $order->total_price) {
                return response()->json(['message' => 'Insufficient wallet balance'], 400);
            }

            // Deduct balance
            $user->balance -= $order->total_price;
            $user->save();

            // Log transaction
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $order->total_price,
                'type' => 'debit',
                'reason' => 'order_payment',
            ]);

            // Create payment
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $order->total_price,
                'method' => 'wallet',
                'status' => 'paid',
            ]);

            // Mark order as paid
            $order->status = 'shipped';
            $order->save();

            return response()->json([
                'message' => 'Payment successful.',
                'payment' => $payment,
            ]);
        }
    }
        
}

