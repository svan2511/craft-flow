<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\PaymentRepository;
use App\Services\Sms\SmsProvider;

class AuthService
{
    public function __construct(
        protected PaymentRepository $payments,
        protected SmsProvider $sms,
    ) {}

    /**
     * @return array{user: User, otp: string, dev_otp: string|null, retry_in: int}
     */
    public function sendOtp(string $phone): array
    {
        $user = User::firstOrCreate(['phone' => $phone]);

        $otp = (string) random_int(1000, 9999);

        $this->sms->send(
            $this->normalizeE164($phone),
            "Your Craft Flow verification code is {$otp}. Do not share it with anyone.",
        );

        $user->forceFill([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ])->save();

        $isLocalEnv = in_array(app()->environment(), ['local', 'testing'], true);

        return [
            'user' => $user,
            'otp' => $otp,
            'dev_otp' => $isLocalEnv && $this->sms->shouldExposeOtp() ? $otp : null,
            'retry_in' => 30,
        ];
    }

    /**
     * Normalize a local phone number to E.164 (+country code), as required
     * by providers like Twilio. Defaults to the country code from
     * SMS_COUNTRY_CODE (India = 91).
     */
    protected function normalizeE164(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';
        $countryCode = (string) (config('services.sms.country_code') ?: '91');

        if ($digits === '') {
            return $phone;
        }

        // 10-digit local number: 8449197958 -> +918449197958
        if (strlen($digits) === 10) {
            return '+'.$countryCode.$digits;
        }

        // Leading zero: 08449197958 -> +918449197958
        if (str_starts_with($digits, '0')) {
            return '+'.$countryCode.substr($digits, 1);
        }

        return '+'.$digits;
    }

    /**
     * @return array{token: string, user: User}|null
     */
    public function verifyOtp(string $phone, string $otp): ?array
    {
        $user = User::where('phone', $phone)->first();

        if ($user === null || $user->otp === null || $user->otp !== $otp) {
            return null;
        }

        if ($user->otp_expires_at === null || $user->otp_expires_at->isPast()) {
            return null;
        }

        $user->forceFill([
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

        $token = $user->createToken('craft-flow-mobile')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}