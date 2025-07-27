<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentService;
class PaymentController extends Controller
{
    protected $paymentService;
    public function __construct(PaymentService $paymentService){
        $this->paymentService=$paymentService;
    }
    public function payment(Request $request,$orderId)
    {
        $array=$this->paymentService->payment($request,$orderId);
        return response()->json([$array],200);
        
    }  
}

