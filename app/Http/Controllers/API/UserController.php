<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            //validasi input
            $request ->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            //mengecek credential(login)
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'messege' => 'Unauthorized'
                ], 'Authentication failed',500);
            }
            //jika hash tidak sesuai error
            // /Exception
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password,[])) {
                throw new Exception('Invalid Credentials');
            }

            //jika berhasil maka loginkan
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Aunthenticated');
        }catch(Exception $error){
            return ResponseFormatter::error([
                'massage' => 'Somenthing went wrong',
                'error' => $error
            ], 'Aunthentication Failed', 500);
        }
    }
}
