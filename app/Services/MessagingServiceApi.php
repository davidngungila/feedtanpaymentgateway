<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessagingServiceApi
{
    public const BASE_URL = 'https://messaging-service.co.tz';
    public const DEFAULT_SENDER = 'TANZANIATIP';

    /**
     * Send single SMS via Messaging Service API V2.
     * Supports both Bearer and token query methods.
     *
     * @param string $to  E.164 without + e.g. 255655000000
     * @param string $text
     * @param string|null $from  Sender ID
     * @param string|null $token Bearer token (if null, uses config)
     * @param bool $useTest  Use test endpoint (no charge, dummy delivery)
     * @return array ['success'=>bool, 'status'=>int, 'body'=>mixed, 'messageId'=>mixed, 'error'=>string|null]
     */
    public function sendSingle(string $to, string $text, ?string $from = null, ?string $token = null, bool $useTest = false): array
    {
        $from = $from ?: config('services.messaging.from', self::DEFAULT_SENDER);
        $token = $token ?: config('services.messaging.token');
        $endpoint = $useTest ? '/api/sms/v2/test/text/single' : '/api/sms/v2/text/single';

        if (empty($token)) {
            return ['success' => false, 'status' => 422, 'error' => 'Missing Messaging Service token. Configure in .env (MESSAGING_TOKEN) or Settings → Gateways & APIs.'];
        }

        $to = $this->normalizeNumber($to);
        $text = trim($text);

        try {
            // Primary: Bearer header, JSON body (as per docs)
            $response = Http::withHeaders([
                    'Authorization' => str_starts_with($token, 'Bearer ') ? $token : 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post(self::BASE_URL . $endpoint, [
                    'from' => $from,
                    'to' => $to,
                    'text' => $text,
                ]);

            return $this->parseSendResponse($response, $to, $text);
        } catch (\Throwable $e) {
            Log::error('MessagingService sendSingle failed', ['to' => $to, 'error' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send via Link GET method (alternative):
     * GET https://messaging-service.co.tz/link/sms/v2/text/single?token=...&from=...&to=...&text=...
     */
    public function sendViaLink(string $to, string $text, ?string $from = null, ?string $token = null): array
    {
        $from = $from ?: config('services.messaging.from', self::DEFAULT_SENDER);
        $token = $token ?: config('services.messaging.token');
        // Link method expects token without Bearer prefix
        $rawToken = preg_replace('/^Bearer\s+/i', '', $token ?? '');

        if (empty($rawToken)) {
            return ['success' => false, 'status' => 422, 'error' => 'Missing token for link method.'];
        }

        $to = $this->normalizeNumber($to);

        try {
            $response = Http::timeout(30)->get(self::BASE_URL . '/link/sms/v2/text/single', [
                'token' => $rawToken,
                'from' => $from,
                'to' => $to,
                'text' => $text,
            ]);

            return $this->parseSendResponse($response, $to, $text);
        } catch (\Throwable $e) {
            Log::error('MessagingService sendViaLink failed', ['to' => $to, 'error' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send bulk (multiple recipients, same text).
     * POST https://messaging-service.co.tz/api/sms/v2/text/multi  (or /test/text/multi)
     * Body: { from, to: [numbers], text }
     */
    public function sendBulk(array $recipients, string $text, ?string $from = null, ?string $token = null, bool $useTest = false): array
    {
        $from = $from ?: config('services.messaging.from', self::DEFAULT_SENDER);
        $token = $token ?: config('services.messaging.token');
        $endpoint = $useTest ? '/api/sms/v2/test/text/multi' : '/api/sms/v2/text/multi';

        if (empty($token)) {
            return ['success' => false, 'status' => 422, 'error' => 'Missing Messaging Service token.'];
        }

        $tos = array_map(fn($n) => $this->normalizeNumber($n), $recipients);

        try {
            $response = Http::withHeaders([
                    'Authorization' => str_starts_with($token, 'Bearer ') ? $token : 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post(self::BASE_URL . $endpoint, [
                    'from' => $from,
                    'to' => $tos,
                    'text' => $text,
                ]);

            return $this->parseSendResponse($response, implode(',', $tos), $text);
        } catch (\Throwable $e) {
            Log::error('MessagingService sendBulk failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Normalize Tanzanian number to 255XXXXXXXXX.
     */
    public function normalizeNumber(string $number): string
    {
        $n = preg_replace('/[^0-9]/', '', $number);
        if (str_starts_with($n, '0')) {
            $n = '255' . substr($n, 1);
        }
        if (!str_starts_with($n, '255') && strlen($n) === 9) {
            $n = '255' . $n;
        }
        return $n;
    }

    private function parseSendResponse($response, string $to, string $text): array
    {
        $status = $response->status();
        $body = $response->json() ?? $response->body();

        // Messaging Service returns 200 with messages array even on success
        $success = $response->successful();

        // Extract messageId / status if available
        $messageId = null;
        $price = null;
        if (is_array($body) && isset($body['messages'][0])) {
            $messageId = $body['messages'][0]['messageId'] ?? null;
            $price = $body['messages'][0]['price'] ?? null;
            // Check per-message status group
            $groupId = $body['messages'][0]['status']['groupId'] ?? null;
            // 18 = PENDING (sent), 20 = DELIVERY, 22 = FAILED, 19 = REJECTED
            if (in_array($groupId, [18, 20], true)) {
                $success = true;
            } elseif (in_array($groupId, [22, 19], true)) {
                $success = false;
            }
        }

        if (!$success) {
            Log::warning('MessagingService send failed', ['status' => $status, 'body' => $body, 'to' => $to]);
        }

        return [
            'success' => $success,
            'status' => $status,
            'body' => $body,
            'messageId' => $messageId,
            'price' => $price,
            'to' => $to,
            'text' => $text,
            'error' => $success ? null : ($body['message'] ?? $body['error'] ?? "SMS send failed (HTTP $status)"),
        ];
    }

    /**
     * Number validation (Number Context) - optional helper.
     * POST https://messaging-service.co.tz/api/number/context  (example, adjust per docs)
     */
    public function validateNumber(string $number, ?string $token = null): array
    {
        // Placeholder - implement per actual Number Context endpoint if needed
        $normalized = $this->normalizeNumber($number);
        return ['normalized' => $normalized, 'valid' => preg_match('/^255[67][0-9]{8}$/', $normalized) === 1];
    }
}
