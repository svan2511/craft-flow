<?php

namespace App\Services\Sms;

interface SmsProvider
{
    /**
     * Deliver an SMS message.
     */
    public function send(string $to, string $message): void;

    /**
     * Whether this provider is a development-only transport that should
     * allow the OTP to be surfaced back to the caller (e.g. logged locally).
     */
    public function shouldExposeOtp(): bool;
}
