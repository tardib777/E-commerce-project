<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductUpdateRequest;
class ProductController extends Controller
{
    protected $productService;
    public function __construct(ProductService $productService){
        $this->productService=$productService;
    }
    public function store(ProductRequest $request){
        $validated=$request->validated();
        $product=$this->productService->store($validated);
        return response()->json([$product],201);
    }
    public function update(ProductUpdateRequest $request,string $id){
        $validated=$request->validated();
        $message=$this->productService->update($validated,$id);
        return response()->json([$message],200);

    }
    public function destroy(string $id){
        $message=$this->productService->destroy($id);
        return response()->json([$message],200);
    }
}
