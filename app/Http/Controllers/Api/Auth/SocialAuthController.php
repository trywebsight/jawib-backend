<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
        return $this->success(['url' => $redirectUrl]);
    }
    public function callback($provider)
    {
        if (request()->has('error')) {
            return $this->error(['errors' => [request()->get('error')]]);
        }

        $data = $this->handleProviderCallback($provider);
        return $this->success($data, __('google login successful'));
    }

    private function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $user = User::firstOrCreate(
            [
                'email' => $socialUser->getEmail()
            ],
            [
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
                'social_provider' => $provider,
                'social_id' => $socialUser->getId(),
                'username' => email_to_username($socialUser->getEmail()),
                'password' => bcrypt(uniqid()),
                'email_verified_at' => now(),
                'phone_verified' => now()
            ]
        );
        $token = $user->createToken('authToken')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    private function isValidProvider($provider)
    {
        return array_key_exists($provider, config('services'));
    }
}
