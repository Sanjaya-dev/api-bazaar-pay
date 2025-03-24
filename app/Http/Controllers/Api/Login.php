<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class Login extends Controller
{
    function Login(Request $request) {

        // validation input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // check user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        }

        // generate token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // return response
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);

    }
}
