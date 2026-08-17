<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService){
        $this->authService=$authService;
    }
    public function register(RegisterRequest $request){
        $validated=$request->validated();
        $user=$this->authService->register($validated);
        return response()->json([$user,'message'=>'تم إنشاء الحساب بنجاح'],201);
    }
    public function login(LoginRequest $request){
        $auth=$this->authService->login($request);
        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $auth['token'],
            'user' => $auth['user']
        ],200);
    }
    public function logout(Request $request){
        $message=$this->authService->logout($request);
        return response()->json([
            'message' => $message
        ],200);
    }
}
