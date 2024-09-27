<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', 'unique:users,email'], // Assuming 'users' is the table name
                'password' => ['required', 'confirmed', Password::defaults()],
            ],
            [],
            [
                'name' => 'Name',
                'email' => 'Email',
                'password' => 'Password',
                'password_confirmation' => 'Confirm Password',
            ]
        );


        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation Error', $validator->errors()->messages());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'token' => $user->createToken('registerUserToken')->plainTextToken
        ];
        return ApiResponse::sendResponse(201, 'User registered successfully', $data);
    }
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ],
            [],
            [
                'email' => 'Email',
                'password' => 'Password',
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation Error', $validator->errors()->messages());
        }

        if (Auth::attempt($request->only(['email','password']))) {
            $user = $request->user();
            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('loginUserToken')->plainTextToken
            ];
            return ApiResponse::sendResponse(200, 'User logged in successfully', $data);
        }
        return ApiResponse::sendResponse(401, 'Invalid credentials',[]);
    }
    
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(204,'User Logged out successfully', []);
    }
}
