<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
    Route::get('/orders/index',[OrderController::class,'index'])->name('orders.index');
    Route::get('/orders/addProduct/{product_id}',[OrderController::class,'addProductPage'])->name('orders.addProductPage');
    Route::post('/orders/addProduct',[OrderController::class,'addProduct'])->name('orders.addProduct');
    Route::delete('/orders/product/delete/{order}/{product_id}',[OrderController::class,'removeProduct'])->name('orders.removeProduct');
    Route::put('/orders/cancel/{order_id}',[OrderController::class,'cancel'])->name('orders.cancel');
    Route::get('/orders/checkout/{order}',[OrderController::class,'checkout'])->name('orders.checkout');
    Route::get('/orders/pay/{method}/{order_id}/{crypto_currency?}', [OrderController::class, 'pay'])->name('orders.payment.pay');
    Route::get('/orders/success/{method}/{order_id}/{request?}', [OrderController::class, 'success'])->name('orders.payment.success');
    Route::get('/orders/cancel/{method}/{order_id}/{NP_id?}', [OrderController::class, 'cancel'])->name('orders.payment.cancel');
    Route::post('/nowpayment/callback', [OrderController::class, 'nowPaymentCallback'])->name('orders.payment.nowpayment.callback')->withoutMiddleware([VerifyCsrfToken::class]);
    });
    Route::middleware('role:admin|customer')->group(function(){
            //Route::get('/orders/index',[OrderController::class,'index'])->name('orders.index');
    });
   
});
 Route::get('/home/{id?}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
 Route::get('/products/show/{id}',[ProductController::class,'show'])->name('products.show');
Route::get('/',function(){
    return redirect()->route('home');
});

