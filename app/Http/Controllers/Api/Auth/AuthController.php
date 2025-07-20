<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $validated=$request->validated();
        $user=User::create(['firstname'=> $validated['firstname'],'lastname'=> $validated['lastname'],'email'=> $validated['email'],'password'=>Hash::make($validated['password']),'balance'=>$validated['balance']]);
        $user->assignRole('customer');
        return response()->json([$user,'message'=>'stored successfully'],201);
    }
    public function login(LoginRequest $request){
        $validated=$request->validated();
        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json(['message' => 'بيانات غير صحيحة'], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('welcome')->plainTextToken;
        $user->setRememberToken($token);
        $user->update();
        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => $user
        ],200);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        $user=Auth::user();
        $user->remember_token=null;
        $user->update();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ],200);
    }
}
