<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\RemoveCategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    public function index(){
        $categories=Category::with('products')->get();
        return response()->json([$categories,'message' => 'success'],200);
    }
    public function show($id){
        $category=Category::findOrFail($id);
        return response()->json([$category->products],200);
    }
    public function store(CategoryRequest $request){
        $validated=$request->validated();
        $category=Category::create(['name' => $validated['name']]);
        return response()->json([$category,'message' => 'created successfully'],201);
    }
    public function destroy(RemoveCategoryRequest $request){
        $validated=$request->validated();
        $category=Category::where('name','=',$validated['name'])->delete();
        return response()->json([$category,'message' => 'deleted successfully'],200);

    }
}
