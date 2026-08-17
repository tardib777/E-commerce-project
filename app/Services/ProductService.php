<?php
namespace App\Services;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ProductService{
    public function store(array $data){
        $product=Product::create(['name' => $data['name'],'description' => $data['description'],'price' => $data['price'],'available_quantity' => $data['available_quantity'],'category_id' => $data['category_id'],'image' => $this->storeImage($data['image'])]);
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
            $this->deleteImage($product->image);
            $product->image = $this->storeImage($data['image']);
            $product->update(['name' => $data['name'],'description' => $data['description'],'price' => $data['price'],'available_quantity' => $data['available_quantity'],'category_id' => $data['category_id'],'image' => $product->image]);
            $product->categories()->sync([1,$data['category_id']]);
        }
        return 'تم تعديل المنتج بنجاح';

    }
    public function destroy(string $id){
        $product=Product::where('id',$id)->firstOrFail();
        $this->deleteImage($product->image);
        $product->categories()->detach();
        $product->delete();
        return 'تم حذف المنتج بنجاح';
    }

    /**
     * Store an uploaded product image under storage/productsImages and return its
     * relative path (e.g. "productsImages/xyz.jpg"), matching how the views serve
     * images via asset('storage/'.$product->image). Replaces the previously
     * undefined uploadFile() helper that caused a 500 on create/update.
     */
    private function storeImage($file): string
    {
        $dir = storage_path('productsImages');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = uniqid('prod_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return 'productsImages/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && is_file(storage_path($path))) {
            @unlink(storage_path($path));
        }
    }
}