<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService){
        $this->categoryService=$categoryService;
    }

    /**
     * Display a listing of all categories.
     */
    public function index()
    {
        $categories=Category::withCount('products')->latest()->get();
        return view('categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request)
    {
        $this->categoryService->store($request->validated());
        return redirect()->route('categories.index')->with('success','Category added successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        $category->products()->detach();
        $category->delete();
        return redirect()->route('categories.index')->with('success','Category deleted successfully.');
    }
}
