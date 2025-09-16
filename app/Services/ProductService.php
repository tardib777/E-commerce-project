<?php
namespace App\Services;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ProductService{
    public function store(array $data){
        $product=Product::create(['name' => $data['name'],'description' => $data['description'],'price' => $data['price'],'available_quantity' => $data['available_quantity'],'category_id' => $data['category_id'],'image' => uploadFile($data['image'],'productsImages')]);
        $product->categories()->attach([1,$data['category_id']]);
        return ["product" => $product, "message" => "تمت إضافة المنتج بنجاح"];
    }
    public function update(array $data,string $id){
        $product=Product::where('id',$id)->firstOrFail();
        if('productsImages/'.$data['image'] == $product->image){
            $product->update(['name' => $data['name'],'description' => $data['description'],'price' => $data['price'],'available_quantity' => $data['available_quantity'],'category_id' => $data['category_id'],'image' => $product->image]);
            $product->categories()->sync([1,$data['category_id']]);
        }
        else{
            Storage::disk('public')->delete($product->image);
            $product->image = uploadFile($data['image'],'productsImages');
            $product->update(['name' => $data['name'],'description' => $data['description'],'price' => $data['price'],'available_quantity' => $data['available_quantity'],'category_id' => $data['category_id'],'image' => $product->image]);
            $product->categories()->sync([1,$data['category_id']]);
        }
        return 'تم تعديل المنتج بنجاح';

    }
    public function destroy(string $id){
        $product=Product::where('id',$id)->firstOrFail();
        Storage::disk('public')->delete($product->image);
        $product->categories()->detach();
        $product->delete();
        return 'تم حذف المنتج بنجاح';
    }
}