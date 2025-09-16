<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderItemRequest;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService=$orderService;
    }
    public function index(){
        $array=$this->orderService->index();
        $orders=$array[0];
        $user=$array[1];
        return view('orders.show',compact('orders','user'));
    }
    public function create($id){
        $product=Product::where('id',$id)->firstOrFail();
        return view('orders.create',compact('product'));
    }
    public function store(OrderRequest $request)
    {
        $validated=$request->validated();
        $this->orderService->store($validated);
        return redirect()->route(route: 'orders.index');

    }
    public function addProduct($product_id,$order_id){
        $product=Product::where('id',$product_id)->firstOrFail();
        return view('orders.addProduct',compact('order_id','product'));
        
    }
    //to update the pending order by storing the new item/product in it
    public function update(OrderItemRequest $request,$orderId){
        $validated=$request->validated();
        $this->orderService->addItem($validated,$orderId);
        return redirect()->route('orders.index');
    }
    public function deleteItem($order_id,$item_id){
        $this->orderService->deleteItem($order_id,$item_id);
        return redirect()->route('orders.index');
    }
    public function cancel($id){
        $this->orderService->cancel($id);
        return redirect()->route('orders.index');
    }
    public function paymentMethod(){
        return view('orders.paymentMethod');
    }
}
