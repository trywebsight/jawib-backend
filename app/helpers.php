<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


if (!function_exists('clear_cache')) {
    function clear_cache($key = null)
    {
        try {
            Cache::forget($key);
            if ($key == '*') {
                Cache::flush();
            }
            Artisan::call('optimize:clear');
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }
}
if (!function_exists('settings')) {
    function settings($key, $else = null)
    {
        $settings = Cache::rememberForever('settings', function () {
            return \App\Models\Setting::pluck('value', 'key')->toArray();
        });
        return $settings[$key] ?? $else;
    }
}

if (!function_exists('slugify')) {
    function slugify($text)
    {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Trim
        $text = trim($text, '-');
        // Remove duplicate dashes
        $text = preg_replace('~-+~', '-', $text);
        // Lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

if (!function_exists('formated_price')) {
    function formated_price($price, $float = true)
    {
        $decimal  = $float ? 2 : 0;
        return number_format($price, $decimal) . " " . __('KD');
    }
}

if (!function_exists('email_to_username')) {
    function email_to_username($email)
    {
        // Extract the part before '@' from the email
        $baseUsername = Str::before($email, '@');
        // Sanitize the username (remove unwanted characters)
        $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $baseUsername);
        $username = $baseUsername;
        // Check if the username is unique
        $isUnique = !DB::table('users')->where('username', $username)->exists();
        if ($isUnique) {
            return $username;
        } else {
            // Append random digits until a unique username is found
            do {
                $randomNumber = rand(1000, 9999);
                $username = $baseUsername . '_' . $randomNumber;
            } while (DB::table('users')->where('username', $username)->exists());

            return $username;
        }
    }
}
