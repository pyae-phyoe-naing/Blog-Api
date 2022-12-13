<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        // validate user
        $request->validate(
            [
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|max:20'
            ]
        );
        // register user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        // generate token with passport
        $token = $user->createToken('Blog')->accessToken;
        return ResponseHelper::success(['access_token' => $token]);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );
        // Login check
        if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
            $user = auth()->user();
            // generate token with passport
            $token = $user->createToken('Blog')->accessToken;
            return ResponseHelper::success(['access_token' => $token]);
        }
        return ResponseHelper::fail('Credential Error');
    }

    // Logout

    public function logout(){
        auth()->user()->token()->revoke();
        return ResponseHelper::success([],'Successfully logout.');
    }
}
