<?php 
namespace App\Services;
use App\Models\Order;
use App\Http\Requests\OrderRequest;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService{
    public function index(){
        $orders=Auth::user()->orders()->with('products')->get();
        return $orders;
    }
    public function GetPendingOrCreate(){
        $order=Auth::user()->orders()->where('status','pending')->first();
        if(!$order){
            $order=Auth::user()->orders()->create(["status"=>"pending","total_price"=>0]);
        }
        return $order;
    }
    public function addProductToOrder(array $data,$order){
        try{
            $product=Product::where('id',$data['product_id'])->firstOrFail();
            $order->products()->attach($data['product_id'],[
                'quantity'=>$data['quantity'],
                'price'=>$data['price']
            ]);
            $product->available_quantity -= $data['quantity'];
            $order->total_price += $data['price'];
            $product->save();
            $order->save();
            return true;
        }
        catch(Exception $e){
            throw new Exception("Failed to add or remove product to order: ".$e->getMessage());
        }
    }
    public function removeProductFromOrder($order,$product_id){
        try{
            $orderItem=$order->products()->where('product_id',$product_id)->first();
            $order->products()->detach($product_id);
            $product=Product::where('id',$product_id)->first();
            $product->available_quantity += $orderItem->pivot->quantity;
            $order->total_price -= $orderItem->pivot->price;
            $product->save();
            $order->save();
            return true;
        }
        catch(Exception $e){
            throw new Exception("Failed to add or remove product to order: ".$e->getMessage());
        }
    }
  

}