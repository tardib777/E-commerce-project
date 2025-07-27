<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

//shared between Admin and Customers
Route::middleware(['auth:sanctum','role:admin|customer'])->group(function(){
    //orders
    Route::get('/orders', [OrderController::class, 'index']);//list all your orders
    Route::get('/orders/{id}', [OrderController::class, 'show']);//show only a specific order
    Route::post('/orders', [OrderController::class, 'store']);//create order
    Route::delete('/orders/{id}', [OrderController::class, 'cancel']); // Cancel order
    Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);   // Add item
    Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'deleteItem']); // Delete item
    //payment
    Route::post('/orders/{id}/pay', [PaymentController::class, 'payment']);  //pay an order
});
//special for Admin
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    //categories
    Route::post('/categories/store',[CategoryController::class,'store']);
    Route::post('/categories/delete',[CategoryController::class,'destroy']);
    //products
    Route::post('/products/create',[ProductController::class,'store']);
    Route::put('/products/update/{id}',[ProductController::class,'update']);
    Route::delete('/products/delete/{id}',[ProductController::class,'destroy']);
    //orders
});
Route::get('/categories',[CategoryController::class,'index']);
Route::get('/categories/{id}',[CategoryController::class,'show']);

