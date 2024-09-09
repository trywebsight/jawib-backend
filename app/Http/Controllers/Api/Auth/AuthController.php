<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:255|unique:users',
            'email'     => 'sometimes|string|max:255|unique:users',
            'password'  => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->errors()], $validator->errors()->first(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('authtoken')->plainTextToken,
        ], 'User created Successfully', 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error(['errors' => ['email' => ['The provided credentials are incorrect.']]], 'invalid username or password', 422);
        }
        // $user->tokens()->delete();

        return $this->success(['user' => $user, 'token' => $user->createToken('authtoken')->plainTextToken]);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        try {
            $user = auth('sanctum')->user();
            // $user->currentAccessToken()->delete();
            // $user->tokens()->delete();
            $user->currentAccessToken()->delete();
            return $this->success(null, 'Logged out successfully');
        } catch (\Throwable $th) {
            return $this->success(null, 'You are Logged out already');
        }
    }
}
