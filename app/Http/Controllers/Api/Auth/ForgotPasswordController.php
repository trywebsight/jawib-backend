<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendOTP(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone' => 'required|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return $this->error(['errors'  => $validator->errors()], __('invalid phone number'), 422);
        }

        $user = User::where('phone', $request->phone)->first();

        $existOtp = UserOtp::where('user_id', $user->id)->first();
        if ($existOtp && $existOtp->updated_at) {
            $lastOtpSentAt = Carbon::parse($existOtp->updated_at);
            // Allow sending a new OTP only if more than 60 seconds have passed
            if ($lastOtpSentAt->diffInSeconds(Carbon::now()) < 60) {
                return $this->error([], __("please wait :seconds seconds before requesting a new otp", ['seconds' => intval(60 - $lastOtpSentAt->diffInSeconds(Carbon::now()))]), 422);
            }
        }

        // Generate a 4-digit OTP
        // $otp = rand(1000, 9999);
        $otp = 1234;

        // Store OTP in cache for 10 minutes
        // Cache::put('password_reset_otp_' . $user->phone, $otp, now()->addMinutes(10));
        UserOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(15),
            ]
        );

        // Send OTP via phone
        try {
            // Mail::to($phone)->send(new ForgotPasswordOtp($otp, $phone));
            // (new KwtService)->sendSms($phoneNumber, "Your verification code is: $otp");
        } catch (\Exception $e) {
            // Handle phone sending failure
            return $this->error(['errors' => [$e->getMessage()]], __('failed to send otp please try again later'), 500);
        }

        return $this->success([], __('otp has been sent to your phone'));
    }

    public function verifyOTP(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone' => 'required|exists:users,phone',
            'otp'   => 'required|exists:user_otps,otp',
        ]);

        if ($validator->fails()) {
            return $this->error(['errors'  => $validator->errors()], __('invalid otp'), 422);
        }

        $userOtp = UserOtp::where('otp', $request->otp)->first();
        if (!$userOtp || $userOtp->expires_at < now() || $userOtp->user?->phone != $request->phone) {
            return $this->error(['errors'  => ['otp' => __('invalit otp')]], __('invalid otp'), 422);
        }

        return $this->success([], __('valid otp'));
    }


    public function resetPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'phone'                 => 'required|exists:users,phone',
            'otp'                   => 'required|exists:user_otps,otp',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error(['errors'  => $validator->errors()], '', 422);
        }

        $phone       = $request->phone;
        $otp         = $request->otp;
        $newPassword = $request->password;

        $user = User::where('phone', $phone)->first();
        $userOtp = UserOtp::where('user_id', $user->id)->where('otp', $otp)->first();

        if (!$userOtp || $userOtp->expires_at < now() || $userOtp->user_id != $user->id) {
            return $this->error(['errors'  => ['otp' => __('invalit otp')]], __('invalid otp'), 422);
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        $userOtp->delete();

        return $this->success([], __('password has been reset successfully'), 200);
    }
}
