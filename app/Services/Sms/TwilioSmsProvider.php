<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Sends real SMS through the Twilio Messages REST API using the built-in
 * HTTP client, so no extra composer package is required.
 *
 * Credentials come purely from env (TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM).
 */
class TwilioSmsProvider implements SmsProvider
{
    protected string $sid;
    protected string $token;
    protected string $from;

    public function __construct()
    {
        $this->sid = (string) (config('services.twilio.sid') ?? '');
        $this->token = (string) (config('services.twilio.token') ?? '');
        $this->from = (string) (config('services.twilio.from') ?? '');
    }

    public function send(string $to, string $message): void
    {
        if ($this->sid === '' || $this->token === '' || $this->from === '') {
            throw new RuntimeException('Twilio credentials are missing. Set TWILIO_SID, TWILIO_TOKEN and TWILIO_FROM in your .env.');
        }

        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', rawurlencode($this->sid));

        $response = Http::withBasicAuth($this->sid, $this->token)
            ->asForm()
            ->post($url, [
                'To' => $to,
                'From' => $this->from,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            Log::error('Twilio send failed', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $details = $response->json('message') ?? 'Unknown error';
            throw new RuntimeException("Twilio send failed: {$details}");
        }
    }

    public function shouldExposeOtp(): bool
    {
        return false;
    }
}