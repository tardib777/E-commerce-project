<?php 
namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService{
    public function payment(Request $request,$orderId)
    {
        $user=Auth::user();
        $order=Order::where('id', $orderId)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->firstOrFail();
        //paymant
        $status=$order->status;
        $amount=$order->total_price;
        $balance=$user->balance;
        if($status !='pending'){
            return "لقد دفعت ثمن هذا الطلب مسبقا";
        }

        else if($balance<$amount){
            return 'ليس لديك رصيد كاف في محفظتك';
        }
        else{
            $payment=null;
            DB::beginTransaction();
            try{
                $user->update(['balance' => $user->balance-=$amount]);
                $payment=Payment::create([
                    'order_id' => $order->id,
                    'user_id' =>$user->id,
                    'amount' => $amount,
                    'status' => 'paid',
                ]);
                $order->status='shipped';
                $order->save();
                DB::commit();
            } catch(Exception $e){
                DB::rollBack();
            }
            return ['تم الدفع بنجاح',$payment];
        }
    }
            
}

            


