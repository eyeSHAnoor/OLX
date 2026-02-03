<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $accessToken;
    protected string $phoneNumberId;

    public function __construct()
    {
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Send a plain text message (like greeting)
     */
    public function sendText(string $toPhone, string $message): array
    {
        $url = "https://graph.facebook.com/v19.0/{$this->phoneNumberId}/messages";

        $response = Http::withToken($this->accessToken)
            ->withOptions(['verify' => 'C:\laragon\etc\ssl\cacert.pem'])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $toPhone,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ]);

        return $response->json();
    }

    /**
     * Send OTP using a pre-approved template (must be created in WhatsApp Manager)
     */
    public function sendOtp(string $toPhone, string $otp): array
    {
        $url = "https://graph.facebook.com/v19.0/{$this->phoneNumberId}/messages";

        $response = Http::withToken($this->accessToken)
            ->withOptions(['verify' => 'C:\laragon\etc\ssl\cacert.pem'])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $toPhone,
                'type' => 'template',
                'template' => [
                    'name' => 'otp_template', // your template name
                    'language' => ['code' => 'en'],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp],
                            ],
                        ],
                    ],
                ],
            ]);

        return $response->json();
    }
}
