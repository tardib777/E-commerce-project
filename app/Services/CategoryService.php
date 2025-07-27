<?php
namespace App\Services;
use App\Models\Category;
use App\Http\Requests\RemoveCategoryRequest;
class CategoryService{
    public function index(){
        $categories=Category::with('products')->get();
        return $categories;
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
        Category::where('name','=',$data['name'])->delete();
        return 'تم حذف التصنيف بنجاح';

    }
}