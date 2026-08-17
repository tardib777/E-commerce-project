<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\RemoveCategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\CategoryService;
class CategoryController extends Controller
{
    protected $categoryService;
    public function __construct(CategoryService $categoryService){
        $this->categoryService=$categoryService;
    }
    public function index(){
        $categories=$this->categoryService->index();
        return response()->json([$categories],200);
    }
    public function show($id){
        $products=$this->categoryService->show($id);
        return response()->json([$products],200);
    }
    public function store(CategoryRequest $request){
        $validated=$request->validated();
        $category=$this->categoryService->store($validated);
        return response()->json([$category],201);
    }
    public function destroy(RemoveCategoryRequest $request){
        $validated=$request->validated();
        $message=$this->categoryService->destroy($validated);
        return response()->json([$message],200);

    }
}
