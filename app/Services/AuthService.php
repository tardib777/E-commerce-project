<?php
namespace App\Services;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthService{
    public function register(array $data){
        $user=User::create(['firstname'=> $data['firstname'],'lastname'=> $data['lastname'],'email'=> $data['email'],'password'=>Hash::make($data['password']),'balance'=>$data['balance']]);
        $user->assignRole('customer');
        return $user;
    }
    public function login(LoginRequest $request){
        if (!Auth::attempt($request->only('email','password'))) {
            return 'بيانات البريد الإلكتروني وكلمة السر غير صحيحة ';
        }
        $user = Auth::user();
        $token = $user->createToken('welcome')->plainTextToken;
        $user->setRememberToken($token);
        $user->update();
        $auth=["user" => $user, "token" => $token];
        return $auth;
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        $user=Auth::user();
        $user->remember_token=null;
        $user->update();
        return 'تم تسجيل الخروج بنجاح';
    }
}