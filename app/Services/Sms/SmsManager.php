<?php

namespace App\Services\Sms;

use RuntimeException;

/**
 * Resolves the active SMS transport from the SMS_PROVIDER env variable.
 *
 * To add a new provider later:
 *   1. write a class implementing SmsProvider
 *   2. add it to the `services.sms.providers` config map
 * and switch at runtime only by changing SMS_PROVIDER — no other code change.
 */
class SmsManager implements SmsProvider
{
    protected SmsProvider $driver;

    public function __construct()
    {
        $this->driver = $this->resolveProvider();
    }

    public function send(string $to, string $message): void
    {
        $this->driver->send($to, $message);
    }

    public function shouldExposeOtp(): bool
    {
        return $this->driver->shouldExposeOtp();
    }

    public function driver(): SmsProvider
    {
        return $this->driver;
    }

    protected function resolveProvider(): SmsProvider
    {
        $name = strtolower((string) (config('services.sms.provider') ?: 'local'));
        $providers = (array) (config('services.sms.providers') ?? []);

        $class = $providers[$name] ?? null;
        if ($class === null) {
            throw new RuntimeException("Unsupported SMS provider configured: {$name}");
        }

        return app($class);
    }
}