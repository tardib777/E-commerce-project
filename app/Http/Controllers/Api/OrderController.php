<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderItemRequest;
use Illuminate\Http\Request;
use App\Models\Product;
class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService=$orderService;
    }
   /* public function index()
    {
        $orders = $this->orderService->index();
        return response()->json($orders);
    }
    public function store(OrderRequest $request)
    {
        $validated=$request->validated();
        $array=$this->orderService->store($validated);
        return response()->json([$array], 201);
    }
    public function show($id)
    {
        $order = $this->orderService->show($id);
        return response()->json($order);
    }
    public function cancel($id)
    {
        $message=$this->orderService->cancel($id);
        return response()->json([$message]);
    }*/
    public function addOrRemoveItem(Request $request, $order_id)
    {
        $data=$request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $price=Product::where('id',$data['product_id'])->value('price');
        $data['price']=$data['quantity']*$price;
        $status=$this->orderService->addOrRemoveProductToOrder($data,$order_id);
        if($status == true){
            return response()->json(["message" => "success"], 201);
        }
    }
    /*public function deleteItem($orderId, $itemId)
    {
        $message=$this->orderService->deleteItem($orderId,$itemId);
        return response()->json([$message]);
    }*/


}
