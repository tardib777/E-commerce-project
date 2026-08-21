<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);
Route::middleware(['auth','verified'])->group(function(){
    Route::middleware('role:admin')->group(function(){
        Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
        Route::post('/products/store',[ProductController::class,'store'])->name('products.store');
        Route::get('/products/edit/{id}',[ProductController::class,'edit'])->name('products.edit');
        Route::post('/products/update/{id}',[ProductController::class,'update'])->name('products.update');
        Route::delete('/products/delete/{id}',[ProductController::class,'destroy'])->name('products.destroy');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/admin/orders', [OrderController::class, 'adminIndex'])->name('admin.orders');
        Route::get('/admin/transactions', [TransactionController::class, 'index'])->name('admin.transactions');
    });
   Route::middleware('role:customer')->group(function(){
    Route::get('/orders/index',[OrderController::class,'index'])->name('orders.index');
    Route::get('/orders/addProduct/{product_id}',[OrderController::class,'addProductPage'])->name('orders.addProductPage');
    Route::post('/orders/addProduct',[OrderController::class,'addProduct'])->name('orders.addProduct');
    Route::delete('/orders/product/delete/{order}/{product_id}',[OrderController::class,'removeProduct'])->name('orders.removeProduct');
    Route::put('/orders/cancel/{order_id}',[OrderController::class,'cancel'])->name('orders.cancel');
    Route::get('/orders/checkout/{order}',[OrderController::class,'checkout'])->name('orders.checkout');
    Route::get('/orders/pay/{method}/{order_id}/{currency?}', [OrderController::class, 'pay'])->name('orders.payment.pay');
    Route::match(['get', 'post'], '/orders/success/{method}/{order_id}/{request?}', [OrderController::class, 'success'])
        ->name('orders.payment.success')
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::get('/orders/cancel/{method}/{order_id}', [OrderController::class, 'cancelPayment'])->name('orders.payment.cancel');
    });
    Route::middleware('role:admin|customer')->group(function(){
            //Route::get('/orders/index',[OrderController::class,'index'])->name('orders.index');
    });

});

// Stripe invoice.paid webhook — no user session on the incoming event, so it
// must stay outside auth/verified middleware and exempt from CSRF.
Route::post('/stripe/webhook', [OrderController::class, 'stripeWebhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

 Route::get('/home/{id?}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
 Route::get('/products/show/{id}',[ProductController::class,'show'])->name('products.show');
Route::get('/',function(){
    return redirect()->route('home');
});
