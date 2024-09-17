<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class KwtsmsService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $senderName;

    public function __construct()
    {
        $this->baseUrl = 'https://www.kwtsms.com/API/';
        $this->username = config('services.kwt_sms.username');
        $this->password = config('services.kwt_sms.password');
        $this->senderName = config('services.kwt_sms.senderName');
    }

    public function getBalance()
    {
        $url = $this->baseUrl . 'balance/';

        $params = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $response = $this->sendRequest($url, $params);

        return $response;
    }

    /**
     * Send SMS using kwtsms.com API.
     *
     * @param string $phoneNumber
     * @param string $message
     * @param int $language
     * @param bool $isTest
     * @throws \Exception if SMS sending fails
     */
    public function sendSms($phoneNumber, $message, $language = 1)
    {
        $url = $this->baseUrl . 'send/';

        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'sender' => $this->senderName,
            'mobile' => $phoneNumber,
            'lang' => $language,
            'message' => $message,
        ];

        try {
            $this->sendRequest($url, $params);
        } catch (Exception $e) {
            throw new Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }


    protected function sendRequest($url, $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = "Error: " . curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        } else {
            curl_close($ch);
            return $result;
        }
    }
}
