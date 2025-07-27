<?php
use App\Models\Category;
use App\Models\Payment;
use App\Models\Order;
if (! function_exists('insertCategory')) {
    function insertCategory(...$category){
        $len = count($category);
        for($i = 0; $i < $len; $i++) {
            Category::create(['name' => $category[$i]]);
        }
    }
    
}
if (!function_exists('uploadFile')) {
        function uploadFile($file, $folder, $disk = 'public') {
        $fileName = time() . $file->getClientOriginalName();
        $path = $file->storeAs($folder, $fileName, $disk);
        return $path;
    }
}
function pay($user,$order,$amount){
    $payment=null;
    DB::transaction(function($user,$amount,$order,$payment){
        $user->update(['balance' => $user->balance-=$amount]);
        $payment=Payment::create([
            'order_id' => $order->id,
            'user_id' =>$user->id,
            'amount' => $amount,
            'status' => 'paid',
        ]);
        $order->status='shipped';
        $order->save();
    });
    return ['message' => 'تم الدفع بنجاح', 'payment' => $payment];

}