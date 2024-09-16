<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;


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

// whatsapp
if (!function_exists('waz_send')) {
    function waz_send($msg, $to, $media_url = null)
    {
        $waz_api = settings('WA_URL', env('WA_URL'));
        $waz_token = settings('WA_ACCESS_TOKEN', env('WA_ACCESS_TOKEN'));
        $waz_id = settings('WA_INSTANCE_ID', env('WA_INSTANCE_ID'));
        $msg = urlencode($msg);

        // remove any special characters
        if (strlen($to) == 8 && substr($to, 0, 3) !== '965') {
            $to = "965$to";
        }
        $to = preg_replace('/\D/', '', $to) . "@c.us";


        // send with image or not
        $url = $media_url
            ? "$waz_api/api/send?number=$to&type=media&message=$msg&media_url=$media_url&instance_id=$waz_id&access_token=$waz_token&filename=image.png"
            : "$waz_api/api/send?number=$to&type=text&message=$msg&instance_id=$waz_id&access_token=$waz_token";
        try {
            $res = Http::get($url);
            $body = $res->json();
            if (isset($body['message'])) {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return $th->getMessage();
            return false;
        }
    }
}
