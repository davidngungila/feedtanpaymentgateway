<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public const CACHE_PREFIX = 'otp:';
    public const TTL_MINUTES = 5;
    public const LENGTH = 6;

    public function generate(string $key): string
    {
        $otp = str_pad((string)random_int(0, 999999), self::LENGTH, '0', STR_PAD_LEFT);
        Cache::put(self::CACHE_PREFIX . $key, $otp, now()->addMinutes(self::TTL_MINUTES));
        // Also store attempts
        Cache::put(self::CACHE_PREFIX . $key . ':attempts', 0, now()->addMinutes(self::TTL_MINUTES));
        return $otp;
    }

    public function send(string $phone, string $otp, ?string $template = null, ?string $token = null, ?string $from = null, bool $isTest = false): array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) { $phone = '255' . substr($phone, 1); }

        $text = $template ? str_replace(['{otp}','{code}'], $otp, $template) : "Your ClickPesa verification code is: $otp. Valid for ".self::TTL_MINUTES." minutes. Do not share.";

        $messaging = app(MessagingServiceApi::class);
        $result = $messaging->sendSingle($phone, $text, $from, $token, $isTest);

        if (!$result['success']) {
            Log::warning('OTP SMS failed', ['phone'=>$phone, 'error'=>$result['error']]);
        } else {
            Log::info('OTP sent', ['phone'=>$phone, 'otp'=>$otp, 'messageId'=>$result['messageId'] ?? null]);
        }

        return $result;
    }

    public function verify(string $key, string $code): bool
    {
        $cached = Cache::get(self::CACHE_PREFIX . $key);
        if (!$cached) { return false; }

        $attempts = (int)Cache::get(self::CACHE_PREFIX . $key . ':attempts', 0);
        if ($attempts >= 5) {
            Cache::forget(self::CACHE_PREFIX . $key);
            return false;
        }

        Cache::increment(self::CACHE_PREFIX . $key . ':attempts');

        if (hash_equals((string)$cached, (string)$code)) {
            Cache::forget(self::CACHE_PREFIX . $key);
            Cache::forget(self::CACHE_PREFIX . $key . ':attempts');
            return true;
        }

        return false;
    }

    public function has(string $key): bool
    {
        return Cache::has(self::CACHE_PREFIX . $key);
    }

    public function clear(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::CACHE_PREFIX . $key . ':attempts');
    }

    public function generateAndSend(string $phone, string $key, ?string $template = null, ?string $token = null, ?string $from = null, bool $isTest = false): array
    {
        $otp = $this->generate($key);
        $result = $this->send($phone, $otp, $template, $token, $from, $isTest);
        return ['otp'=>$otp, 'result'=>$result, 'key'=>$key];
    }
}
