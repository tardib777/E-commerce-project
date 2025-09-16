<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\StripePaymentController;

Auth::routes(['verify' => true]);
Route::middleware(['auth','verified'])->group(function(){
    Route::middleware('role:admin')->group(function(){
        Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
        Route::post('/products/store',[ProductController::class,'store'])->name('products.store');
        Route::get('/products/edit/{id}',[ProductController::class,'edit'])->name('products.edit');
        Route::post('/products/update/{id}',[ProductController::class,'update'])->name('products.update');
        Route::delete('/products/delete/{id}',[ProductController::class,'destroy'])->name('products.destroy');
    });
   Route::middleware('role:customer')->group(function(){
    Route::get('/orders/create/{id}',[OrderController::class,'create'])->name('orders.create');
    Route::post('/orders/store',[OrderController::class,'store'])->name('orders.store');
    Route::get('/orders/add/{product_id}/{order_id}',[OrderController::class,'addProduct'])->name('orders.addProduct');
    Route::put('/orders/add/{order_id}', [OrderController::class,'update'])->name('orders.update');
    Route::delete('/orders/items/delete/{order_id}/{item_id}',[OrderController::class,'deleteItem'])->name('items.delete');
    Route::post('/orders/cancel/{id}',[OrderController::class,'cancel'])->name('orders.cancel');
    Route::get('/payments/checkout/{order_id}',[PaymentController::class,'checkout'])->name('payments.checkout');
    Route::get('/payments/pay/{method}/{order_id}', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::get('/payments/success/{method}/{order_id}', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel/{method}/{order_id}', [PaymentController::class, 'cancel'])->name('payments.cancel');

    });
    Route::middleware('role:admin|customer')->group(function(){
            Route::get('/orders/index',[OrderController::class,'index'])->name('orders.index');
    });
   
});
 Route::get('/home/{id?}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
 Route::get('/products/show/{id}',[ProductController::class,'show'])->name('products.show');
Route::get('/',function(){
    return redirect()->route('home');
});

