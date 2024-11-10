<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\KwtService;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'country_code'      => 'required|integer|max_digits:4',
            'phone'             => 'required|string|max:255|unique:users,phone',
            'username'          => 'required|string|max:255|unique:users,username',
            'email'             => 'sometimes|nullable|string|max:255|unique:users,email',
            'password'          => 'required|string',
            'country'           => 'nullable|string',
            'dob'               => 'nullable|date',
            'gender'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error(['errors' => $validator->errors()], $validator->errors()->first(), 422);
        }

        $validatedData = $validator->validated();
        $validatedData['password'] = bcrypt($validatedData['password']);
        $user = User::create($validatedData);

        $this->sendOtp($user);

        return $this->success(['user' => $user], __('user created successfully') . " " . __('please verify your phone number'), 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;

        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error(['errors' => ['login' => ['The provided credentials are incorrect.']]], __('invalid username or password'), 422);
        }
        if (!$user->phone_verified) {
            return $this->error(null, __('phone number is not verified'), 403);
        }
        return $this->success(['user' => $user, 'token' => $user->createToken('authtoken')->plainTextToken]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $user->currentAccessToken()->delete();
            return $this->success(null, 'Logged out successfully');
        } catch (\Throwable $th) {
            return $this->success(null, 'You are Logged out already');
        }
    }

    // VERIFIY PHONE NUMBER
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone'   => 'required|numeric|exists:users,phone',
            'otp'     => 'required|integer',
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return $this->error(null, __('phone number not registered'), 422);
        }

        $userOtp = UserOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$userOtp) {
            return $this->error(null, 'Invalid OTP or OTP has expired.', 422);
        }
        $user->phone_verified = true;
        $user->save();
        // Delete the OTP record
        $userOtp->delete();
        // Log in the user and generate a token
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->success([
            'token' => $token,
            'user' => $user
        ], __('phone number verified successfully'));
    }

    // SEND OTP
    private function sendOtp($user, $otp = null)
    {
        $otp = $otp ?? mt_rand(1000, 9999);
        $phoneNumber = $user->phone_number();
        // Save OTP in user_otps table
        UserOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(5),
            ]
        );
        try {
            (new KwtService)->sendSms($phoneNumber, "Your verification code is: $otp");
        } catch (Exception $e) {
            Log::debug("error sending otp: {$e->getMessage()}");
            return false;
        }
        return true;
    }

    // RESEND OTP
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return $this->error(null, __('phone number not registered'), 422);
        }
        if ($user->phone_verified) {
            return $this->error(null, __('phone number is already verified'), 422);
        }

        if (!$this->sendOtp($user)) {
            return $this->error(null, __('failed to send otp'), 422);
        }

        // Return success response
        return $this->success(null, __('verification code has been resent'));
    }
}
