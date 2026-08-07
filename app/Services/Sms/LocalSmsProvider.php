<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Development transport. Logs the message instead of sending a real SMS,
 * and exposes the OTP back to the caller for local testing.
 */
class LocalSmsProvider implements SmsProvider
{
    public function send(string $to, string $message): void
    {
        Log::info("Sending SMS to {$to}: {$message}");
    }

    public function shouldExposeOtp(): bool
    {
        return true;
    }
}