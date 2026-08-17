<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService){
        $this->authService=$authService;
    }
    public function create(){
        return view('register');
    }
    public function register(RegisterRequest $request){
        $validated=$request->validated();
        $this->authService->register($validated);
        return view('logForm');
    }
    public function loginForm(){
        return view('login');
    }
    public function login(LoginRequest $request)
    {
        $this->authService->login($request);
        return redirect()->route('index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('logForm');
    }
}
