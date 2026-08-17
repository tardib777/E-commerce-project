<?php
namespace App\Services;
use App\Models\Category;
use App\Models\Product;
class CategoryService{
    public function index($id){
        $category=Category::findOrFail($id);
        $products=Product::whereAttachedTo($category)->get();
        return $products;
    }
    public function show($id){
        $category=Category::findOrFail($id);
        return $category->products;
    }
    public function store(array $data){
        $category=Category::create(['name' => $data['name']]);
        return ["name" => $category, "message" => 'تمت إضافة الصنف بنجاح'];
    }
    public function destroy(array $data){
        $category=Category::where('name',$data['name']);
        $category->products()->detach();
        $category->delete();
        return 'تم حذف التصنيف بنجاح';

    }
}