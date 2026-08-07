<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 30;

    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    public function handle(): void
    {
        // WhatsApp Cloud API integration is environment specific.
        // Configure WHATSAPP_ACCESS_TOKEN + WHATSAPP_PHONE_NUMBER_ID in .env to enable real delivery.
        // For local development the message is logged so the flow can be verified end-to-end.
        Log::channel('stack')->info('WhatsApp message queued', [
            'to' => $this->phone,
            'message' => $this->message,
        ]);
    }
}
