<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use PasswordValidationRules;
    //API login
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

    //API register
    public function register(Request $request)
    {
        try {
           $request->validate([
            'nama' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:user'],
            'password' => $this->passwordRules()
           ]);

           User::created([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'houseNumber' => $request->houseNumber,
            'phoneNumber' => $request->phoneNumber,
            'city' => $request->city,
            'password' => Hash::make($request->password),
           ]);

           $user = User::where('email', $request->email)->first();

           $tokenResult = $user->createToken('authToken')->plainTextToken;

           return ResponseFormatter::success([
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' =>$user
           ]);
        }catch (Exception $error){
            ResponseFormatter::error([
                'message' => 'Somthing went wrong',
                'error' => $error
            ],'Aunthentication',500);
        }
    }

    //API logout
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    //API pengambilan data user
    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(),'Data profile user berhasil diambil');
    }

    //API update profile
    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Update');
    }

    
}
