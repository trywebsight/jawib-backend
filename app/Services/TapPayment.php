<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TapPayment
{

    const BASE_URL = "https://api.tap.company/v2/";

    protected static $apiKey;

    private static function setHeaders()
    {
        self::$apiKey = config('services.tap.api_key');
        return [
            'Authorization' => 'Bearer ' . self::$apiKey,
            'Content-Type' => 'application/json'
        ];
    }

    public static function createCharge(array $data)
    {
        $response = Http::retry(2, 150)
            ->withHeaders(self::setHeaders())
            ->post(self::BASE_URL . 'charges', $data);

        return $response->json();
    }

    public static function retrieveCharge(string $chargeId)
    {
        $response = Http::retry(2, 150)
            ->withHeaders(self::setHeaders())
            ->get(self::BASE_URL . 'charges/' . $chargeId);

        return $response->json();
    }
}
