<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'logo'              => media_url(settings('site_logo')),
            'contact'   => [
                'website'       => settings('contact_website'),
                'email'         => settings('contact_email'),
                'instagram'     => settings('contact_instagram'),
            ],
            'sound_effects' => [
                'win_sound'     => media_url(settings('win_sound_effect')),
                'lose_sound'    => media_url(settings('lose_sound_effect')),
            ],
        ];

        return $this->success($settings, __('app settings'));
    }
}
