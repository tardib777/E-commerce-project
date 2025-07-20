<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{
    public function store(ProductRequest $request){
        $validated=$request->validated();
        $product=Product::create(['name' => $validated['name'],'description' => $validated['description'],'price' => $validated['price'],'available_quantity' => $validated['available_quantity'],'category_id' => $validated['category_id'],'image' => uploadFile($validated['image'],'productsImages')]);
        return response()->json([$product,'message' => 'stored successfully'],201);
    }
    public function update(ProductUpdateRequest $request,string $id){
        $validated=$request->validated();
        $product=Product::where('id','=',$id)->firstOrFail();
        if('productsImages/'.$validated['image'] == $product->image){
            $product->update(['name' => $validated['name'],'description' => $validated['description'],'price' => $validated['price'],'available_quantity' => $validated['available_quantity'],'category_id' => $validated['category_id'],'image' => $product->image]);
        }
        else{
            Storage::disk('public')->delete($product->image);
            $product->image = uploadFile($validated['image'],'productsImages');
            $product->update(['name' => $validated['name'],'description' => $validated['description'],'price' => $validated['price'],'available_quantity' => $validated['available_quantity'],'category_id' => $validated['category_id'],'image' => $product->image]);
        }
        return response()->json([$product,'message' => 'updated successfully'],200);

    }
    public function destroy(string $id){
        $product=Product::where('id','=',$id)->firstOrFail();
        Storage::disk('public')->delete($product->image);
        $product->delete();
        return response()->json(['message' => 'deleted successfully'],200);
    }
}
