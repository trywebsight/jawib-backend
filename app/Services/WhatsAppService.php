<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $whatsappApiUrl;
    protected $accessToken;

    public function __construct()
    {
        $this->whatsappApiUrl = settings("WA_OFFICIAL_URL", "https://graph.facebook.com/v20.0/381214161737198");
        $this->accessToken = settings('WA_OFFICIAL_ACCESS_TOKEN');
    }

    /**
     * Send a text message to a WhatsApp user.
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function sendMessage(string $phoneNumber, string $message, string $mediaUrl = null): array
    {

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phoneNumber),
        ];
        if ($mediaUrl) {
            $payload['type'] = 'image';
            $payload['image'] = [
                'link' => $mediaUrl,
                'caption' => $message,
            ];
        } else {
            $payload['type'] = 'text';
            $payload['text'] = [
                'body' => $message,
            ];
        }
        $response = Http::withToken($this->accessToken)
            ->post("{$this->whatsappApiUrl}/messages", $payload);

        Log::debug($response);
        return $response->json();
    }

    /**
     * Send a template message to a WhatsApp user.
     *
     * @param string $phoneNumber
     * @param string $templateName
     * @param array $templateParameters
     * @return array
     */
    public function sendTemplateMessage(string $phoneNumber, string $templateName, array $templateParameters): array
    {
        $response = Http::withToken($this->accessToken)
            ->post("{$this->whatsappApiUrl}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($phoneNumber),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => 'en_US'],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => $this->formatTemplateParameters($templateParameters),
                        ]
                    ],
                ],

            ]);


        return $response->json();
    }

    /**
     * Format the phone number to include the country code.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/[^0-9]/', '', $phoneNumber); // Adjust as needed
    }

    /**
     * Format template parameters for the API call.
     *
     * @param array $parameters
     * @return array
     */
    private function formatTemplateParameters(array $parameters): array
    {
        return array_map(function ($param) {
            return [
                'type' => 'text',
                'text' => $param,
            ];
        }, $parameters);
    }
}
