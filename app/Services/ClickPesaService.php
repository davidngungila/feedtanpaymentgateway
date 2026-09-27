<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPesaService
{
    public const BASE_URL = 'https://api.clickpesa.com';
    public const ENDPOINT_TOKEN = '/third-parties/generate-token';
    public const ENDPOINT_PREVIEW_USSD = '/third-parties/payments/preview-ussd-push-request';
    public const ENDPOINT_INITIATE_USSD = '/third-parties/payments/initiate-ussd-push-request';
    public const ENDPOINT_QUERY_PAYMENT = '/third-parties/payments/{orderReference}';
    public const ENDPOINT_QUERY_ALL = '/third-parties/payments/all';
    public const CACHE_KEY = 'clickpesa:jwt';
    public const CACHE_TTL_MINUTES = 50; // token valid 60min, refresh at 50

    /**
     * Generate JWT token via ClickPesa API.
     * POST https://api.clickpesa.com/third-parties/generate-token
     * Headers: api-key, client-id
     * Returns ['success'=>bool, 'token'=>string, 'raw'=>array, 'error'=>string, 'status'=>int]
     */
    public function generateToken(?string $clientId = null, ?string $apiKey = null): array
    {
        $clientId = $clientId ?: config('services.clickpesa.client_id');
        $apiKey = $apiKey ?: config('services.clickpesa.api_key');

        if (empty($clientId) || empty($apiKey)) {
            return [
                'success' => false,
                'status' => 422,
                'error' => 'Missing ClickPesa client-id or api-key. Configure in .env or Settings → Gateways & APIs.',
                'raw' => null,
            ];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'api-key' => $apiKey,
                    'client-id' => $clientId,
                ])
                ->post(self::BASE_URL . self::ENDPOINT_TOKEN);

            $status = $response->status();
            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false) && !empty($body['token'])) {
                $token = $body['token'];
                // Cache stripped Bearer prefix if present
                $cacheToken = preg_replace('/^Bearer\s+/i', '', $token);
                Cache::put(self::CACHE_KEY, $cacheToken, now()->addMinutes(self::CACHE_TTL_MINUTES));
                // Also cache raw with Bearer for convenience
                Cache::put(self::CACHE_KEY . ':bearer', $token, now()->addMinutes(self::CACHE_TTL_MINUTES));

                return [
                    'success' => true,
                    'status' => $status,
                    'token' => $token,
                    'bearer' => str_starts_with($token, 'Bearer ') ? $token : 'Bearer ' . $token,
                    'raw' => $body,
                    'error' => null,
                ];
            }

            // Handle 401 / 403 and other errors
            $error = match ($status) {
                401 => 'Unauthorized (401): Invalid client-id or api-key.',
                403 => 'Forbidden (403): API key not authorized for this client.',
                default => $body['message'] ?? $body['error'] ?? "ClickPesa token request failed (HTTP $status).",
            };

            Log::warning('ClickPesa token failed', ['status' => $status, 'body' => $body, 'client_id' => $clientId]);

            return [
                'success' => false,
                'status' => $status,
                'error' => $error,
                'raw' => $body,
                'token' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('ClickPesa token exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'status' => 0,
                'error' => 'cURL Error #: ' . $e->getMessage(),
                'raw' => null,
                'token' => null,
            ];
        }
    }

    /**
     * Get cached token or generate new one.
     */
    public function getToken(?string $clientId = null, ?string $apiKey = null, bool $forceRefresh = false): ?string
    {
        if (!$forceRefresh && Cache::has(self::CACHE_KEY . ':bearer')) {
            return Cache::get(self::CACHE_KEY . ':bearer');
        }
        if (!$forceRefresh && Cache::has(self::CACHE_KEY)) {
            $cached = Cache::get(self::CACHE_KEY);
            return str_starts_with($cached, 'Bearer ') ? $cached : 'Bearer ' . $cached;
        }

        $result = $this->generateToken($clientId, $apiKey);
        return $result['success'] ? $result['bearer'] : null;
    }

    /**
     * Get token for Authorization header (without Bearer prefix handling).
     */
    public function getBearerToken(?string $clientId = null, ?string $apiKey = null): ?string
    {
        return $this->getToken($clientId, $apiKey);
    }

    /**
     * Clear cached token (e.g. after 401).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY . ':bearer');
    }

    /**
     * Preview USSD-PUSH request — validates phone, amount, orderReference and returns activeMethods + sender details.
     * POST https://api.clickpesa.com/third-parties/payments/preview-ussd-push-request
     * Body: { amount, currency, orderReference, phoneNumber, fetchSenderDetails, checksum }
     * Header: Authorization: Bearer <token>, Content-Type: application/json
     * @return array ['success'=>bool, 'status'=>int, 'body'=>array, 'activeMethods'=>array, 'sender'=>array, 'error'=>string|null]
     */
    public function previewUssdPush(array $payload, ?string $clientId = null, ?string $apiKey = null): array
    {
        $payload = array_merge([
            'amount' => null,
            'currency' => 'TZS',
            'orderReference' => null,
            'phoneNumber' => null,
            'fetchSenderDetails' => false,
            'checksum' => null,
        ], $payload);

        // Basic validation
        if (empty($payload['amount']) || empty($payload['orderReference']) || empty($payload['phoneNumber'])) {
            return ['success' => false, 'status' => 422, 'error' => 'amount, orderReference and phoneNumber are required.', 'body' => null];
        }
        if (strlen($payload['orderReference']) > 20) {
            return ['success' => false, 'status' => 422, 'error' => 'orderReference must be alphanumeric, max 20 characters.', 'body' => null];
        }

        $token = $this->getToken($clientId, $apiKey);
        if (!$token) {
            return ['success' => false, 'status' => 401, 'error' => 'Unable to obtain ClickPesa token. Check client-id / api-key.', 'body' => null];
        }

        try {
            $response = Http::withToken(preg_replace('/^Bearer\s+/i', '', $token))
                ->timeout(30)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post(self::BASE_URL . self::ENDPOINT_PREVIEW_USSD, $payload);

            $status = $response->status();
            $body = $response->json() ?? $response->body();

            if ($response->status() === 401) { $this->clearCache(); }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $status,
                    'body' => $body,
                    'activeMethods' => $body['activeMethods'] ?? [],
                    'sender' => $body['sender'] ?? null,
                    'error' => null,
                ];
            }

            $error = $body['message'] ?? $body['error'] ?? "Preview USSD-PUSH failed (HTTP $status).";
            Log::warning('ClickPesa previewUssdPush failed', ['status' => $status, 'body' => $body, 'payload' => $payload]);

            return ['success' => false, 'status' => $status, 'error' => $error, 'body' => $body, 'activeMethods' => [], 'sender' => null];
        } catch (\Throwable $e) {
            Log::error('ClickPesa previewUssdPush exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'error' => 'cURL Error #: ' . $e->getMessage(), 'body' => null];
        }
    }

    /**
     * Initiate USSD-PUSH — sends payment request to customer's phone.
     * POST https://api.clickpesa.com/third-parties/payments/initiate-ussd-push-request
     * Body: { amount, currency, orderReference, phoneNumber, checksum }
     * @return array ['success'=>bool, 'status'=>int, 'body'=>array, 'id'=>string, 'error'=>string|null]
     */
    public function initiateUssdPush(array $payload, ?string $clientId = null, ?string $apiKey = null): array
    {
        $payload = array_merge([
            'amount' => null,
            'currency' => 'TZS',
            'orderReference' => null,
            'phoneNumber' => null,
            'checksum' => null,
        ], $payload);

        if (empty($payload['amount']) || empty($payload['orderReference']) || empty($payload['phoneNumber'])) {
            return ['success' => false, 'status' => 422, 'error' => 'amount, orderReference and phoneNumber are required.', 'body' => null];
        }
        if (strlen($payload['orderReference']) > 20) {
            return ['success' => false, 'status' => 422, 'error' => 'orderReference max 20 alphanumeric.', 'body' => null];
        }
        // Remove null checksum if not needed
        if (empty($payload['checksum'])) { unset($payload['checksum']); }

        $token = $this->getToken($clientId, $apiKey);
        if (!$token) {
            return ['success' => false, 'status' => 401, 'error' => 'Unable to obtain ClickPesa token.', 'body' => null];
        }

        try {
            $response = Http::withToken(preg_replace('/^Bearer\s+/i', '', $token))
                ->timeout(30)
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->post(self::BASE_URL . self::ENDPOINT_INITIATE_USSD, $payload);

            $status = $response->status();
            $body = $response->json() ?? $response->body();
            if ($status === 401) { $this->clearCache(); }

            if ($response->successful()) {
                return ['success' => true, 'status' => $status, 'body' => $body, 'id' => $body['id'] ?? null, 'error' => null];
            }

            $error = $body['message'] ?? $body['error'] ?? "Initiate USSD-PUSH failed (HTTP $status).";
            Log::warning('ClickPesa initiateUssdPush failed', ['status' => $status, 'body' => $body, 'payload' => $payload]);
            return ['success' => false, 'status' => $status, 'error' => $error, 'body' => $body];
        } catch (\Throwable $e) {
            Log::error('ClickPesa initiateUssdPush exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'status' => 0, 'error' => 'cURL Error #: ' . $e->getMessage(), 'body' => null];
        }
    }

    /**
     * Query Payment Status by orderReference
     * GET https://api.clickpesa.com/third-parties/payments/{orderReference}
     */
    public function queryPayment(string $orderReference, ?string $clientId = null, ?string $apiKey = null): array
    {
        if (empty($orderReference)) {
            return ['success' => false, 'status' => 422, 'error' => 'orderReference required.', 'body' => null];
        }

        $token = $this->getToken($clientId, $apiKey);
        if (!$token) {
            return ['success' => false, 'status' => 401, 'error' => 'Unable to obtain token.', 'body' => null];
        }

        try {
            $endpoint = str_replace('{orderReference}', urlencode($orderReference), self::ENDPOINT_QUERY_PAYMENT);
            $response = Http::withToken(preg_replace('/^Bearer\s+/i', '', $token))
                ->timeout(30)
                ->withHeaders(['Accept' => 'application/json'])
                ->get(self::BASE_URL . $endpoint);

            $status = $response->status();
            $body = $response->json() ?? $response->body();
            if ($status === 401) { $this->clearCache(); }

            if ($response->successful()) {
                return ['success' => true, 'status' => $status, 'body' => $body, 'error' => null];
            }

            $error = $body['message'] ?? $body['error'] ?? "Query payment failed (HTTP $status).";
            return ['success' => false, 'status' => $status, 'error' => $error, 'body' => $body];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 0, 'error' => 'cURL Error #: ' . $e->getMessage(), 'body' => null];
        }
    }

    /**
     * Query All Payments with filters
     * GET https://api.clickpesa.com/third-parties/payments/all?orderBy=DESC&limit=20&status=...&startDate=...&endDate=...&skip=...
     */
    public function queryAllPayments(array $filters = [], ?string $clientId = null, ?string $apiKey = null): array
    {
        $token = $this->getToken($clientId, $apiKey);
        if (!$token) {
            return ['success' => false, 'status' => 401, 'error' => 'Unable to obtain token.', 'body' => null];
        }

        try {
            $response = Http::withToken(preg_replace('/^Bearer\s+/i', '', $token))
                ->timeout(30)
                ->withHeaders(['Accept' => 'application/json'])
                ->get(self::BASE_URL . self::ENDPOINT_QUERY_ALL, $filters);

            $status = $response->status();
            $body = $response->json() ?? $response->body();
            if ($status === 401) { $this->clearCache(); }

            if ($response->successful()) {
                return ['success' => true, 'status' => $status, 'body' => $body, 'data' => $body['data'] ?? [], 'totalCount' => $body['totalCount'] ?? 0, 'error' => null];
            }

            $error = $body['message'] ?? $body['error'] ?? "Query all payments failed (HTTP $status).";
            return ['success' => false, 'status' => $status, 'error' => $error, 'body' => $body];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 0, 'error' => 'cURL Error #: ' . $e->getMessage(), 'body' => null];
        }
    }

    // ==================== PAYOUTS ====================

    public const ENDPOINT_ACCOUNT_BALANCE = '/third-parties/account/balance';
    public const ENDPOINT_PREVIEW_MOBILE_PAYOUT = '/third-parties/payouts/preview-mobile-money-payout';
    public const ENDPOINT_CREATE_MOBILE_PAYOUT = '/third-parties/payouts/create-mobile-money-payout';
    public const ENDPOINT_PREVIEW_BANK_PAYOUT = '/third-parties/payouts/preview-bank-payout';
    public const ENDPOINT_CREATE_BANK_PAYOUT = '/third-parties/payouts/create-bank-payout';
    public const ENDPOINT_QUERY_PAYOUT = '/third-parties/payouts/{orderReference}';
    public const ENDPOINT_QUERY_ALL_PAYOUTS = '/third-parties/payouts/all';
    public const ENDPOINT_BANKS_LIST = '/third-parties/list/banks';
    public const ENDPOINT_LIPA_PROVIDERS = '/third-parties/payouts/lipa-namba-providers';
    public const ENDPOINT_PREVIEW_LIPA = '/third-parties/payouts/preview-lipa-namba-payout';
    public const ENDPOINT_CREATE_LIPA = '/third-parties/payouts/create-lipa-namba-payout';

    public function getAccountBalance(?string $clientId = null, ?string $apiKey = null): array
    {
        $token = $this->getToken($clientId, $apiKey);
        if (!$token) { return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.']; }
        try {
            $response = Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->get(self::BASE_URL . self::ENDPOINT_ACCOUNT_BALANCE);
            $status=$response->status(); $body=$response->json() ?? $response->body();
            if($status===401) $this->clearCache();
            if($response->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'data'=>$body,'error'=>null];
            $error = $body['message'] ?? "Balance failed (HTTP $status).";
            if($status===404) $error = 'No balance account is linked to this merchant yet. Balance becomes available after first successful payment/deposit.';
            return ['success'=>false,'status'=>$status,'error'=>$error,'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function previewMobileMoneyPayout(array $payload, ?string $clientId=null, ?string $apiKey=null): array
    {
        $payload = array_merge(['amount'=>null,'phoneNumber'=>null,'orderReference'=>null,'currency'=>'TZS','checksum'=>null], $payload);
        if(empty($payload['amount'])||empty($payload['phoneNumber'])||empty($payload['orderReference'])) return ['success'=>false,'status'=>422,'error'=>'amount, phoneNumber, orderReference required.'];
        if(empty($payload['checksum'])) unset($payload['checksum']);
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->withHeaders(['Content-Type'=>'application/json'])->post(self::BASE_URL.self::ENDPOINT_PREVIEW_MOBILE_PAYOUT, $payload);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? $body['error'] ?? "Preview mobile payout failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function createMobileMoneyPayout(array $payload, ?string $clientId=null, ?string $apiKey=null): array
    {
        $payload = array_merge(['amount'=>null,'phoneNumber'=>null,'orderReference'=>null,'currency'=>'TZS','checksum'=>null], $payload);
        if(empty($payload['amount'])||empty($payload['phoneNumber'])||empty($payload['orderReference'])) return ['success'=>false,'status'=>422,'error'=>'amount, phoneNumber, orderReference required.'];
        if(empty($payload['checksum'])) unset($payload['checksum']);
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->withHeaders(['Content-Type'=>'application/json'])->post(self::BASE_URL.self::ENDPOINT_CREATE_MOBILE_PAYOUT, $payload);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            $error=$body['message'] ?? $body['error'] ?? "Create mobile payout failed (HTTP $status).";
            if(str_contains($error,'60 seconds')) $error.=' (Rate limit: 1 payout per 60s)';
            return ['success'=>false,'status'=>$status,'error'=>$error,'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function previewBankPayout(array $payload, ?string $clientId=null, ?string $apiKey=null): array
    {
        $payload = array_merge(['amount'=>null,'accountNumber'=>null,'orderReference'=>null,'bic'=>null,'currency'=>'TZS','accountCurrency'=>'TZS','checksum'=>null], $payload);
        if(empty($payload['amount'])||empty($payload['accountNumber'])||empty($payload['orderReference'])||empty($payload['bic'])) return ['success'=>false,'status'=>422,'error'=>'amount, accountNumber, orderReference, bic required.'];
        if(empty($payload['checksum'])) unset($payload['checksum']);
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->withHeaders(['Content-Type'=>'application/json'])->post(self::BASE_URL.self::ENDPOINT_PREVIEW_BANK_PAYOUT, $payload);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? $body['error'] ?? "Preview bank payout failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function createBankPayout(array $payload, ?string $clientId=null, ?string $apiKey=null): array
    {
        $payload = array_merge(['amount'=>null,'accountNumber'=>null,'accountName'=>null,'orderReference'=>null,'bic'=>null,'currency'=>'TZS','accountCurrency'=>'TZS','checksum'=>null], $payload);
        if(empty($payload['amount'])||empty($payload['accountNumber'])||empty($payload['orderReference'])||empty($payload['bic'])) return ['success'=>false,'status'=>422,'error'=>'amount, accountNumber, accountName, orderReference, bic required.'];
        if(empty($payload['accountName'])) $payload['accountName'] = $payload['accountNumber'];
        if(empty($payload['checksum'])) unset($payload['checksum']);
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->withHeaders(['Content-Type'=>'application/json'])->post(self::BASE_URL.self::ENDPOINT_CREATE_BANK_PAYOUT, $payload);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? $body['error'] ?? "Create bank payout failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function queryPayout(string $orderReference, ?string $clientId=null, ?string $apiKey=null): array
    {
        if(empty($orderReference)) return ['success'=>false,'status'=>422,'error'=>'orderReference required.'];
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $endpoint=str_replace('{orderReference}',urlencode($orderReference), self::ENDPOINT_QUERY_PAYOUT);
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->get(self::BASE_URL.$endpoint);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? "Query payout failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function queryAllPayouts(array $filters=[], ?string $clientId=null, ?string $apiKey=null): array
    {
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->get(self::BASE_URL.self::ENDPOINT_QUERY_ALL_PAYOUTS, $filters);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'data'=>$body['data']??[],'totalCount'=>$body['totalCount']??0,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? "Query all payouts failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function getBanksList(?string $clientId=null, ?string $apiKey=null): array
    {
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->get(self::BASE_URL.self::ENDPOINT_BANKS_LIST);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'data'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>"Banks list failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function getLipaNambaProviders(?string $clientId=null, ?string $apiKey=null): array
    {
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->get(self::BASE_URL.self::ENDPOINT_LIPA_PROVIDERS);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'data'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>"Lipa providers failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function previewLipaNambaPayout(array $payload, ?string $clientId=null, ?string $apiKey=null): array
    {
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->withHeaders(['Content-Type'=>'application/json'])->post(self::BASE_URL.self::ENDPOINT_PREVIEW_LIPA, $payload);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? "Preview Lipa failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    public function createLipaNambaPayout(array $payload, ?string $clientId=null, ?string $apiKey=null): array
    {
        $token=$this->getToken($clientId,$apiKey);
        if(!$token) return ['success'=>false,'status'=>401,'error'=>'Unable to obtain token.'];
        try {
            $r=Http::withToken(preg_replace('/^Bearer\s+/i','',$token))->timeout(30)->withHeaders(['Content-Type'=>'application/json'])->post(self::BASE_URL.self::ENDPOINT_CREATE_LIPA, $payload);
            $status=$r->status(); $body=$r->json() ?? $r->body();
            if($status===401) $this->clearCache();
            if($r->successful()) return ['success'=>true,'status'=>$status,'body'=>$body,'error'=>null];
            return ['success'=>false,'status'=>$status,'error'=>$body['message'] ?? "Create Lipa failed (HTTP $status).",'body'=>$body];
        } catch(\Throwable $e){ return ['success'=>false,'status'=>0,'error'=>'cURL Error #: '.$e->getMessage()]; }
    }

    /**
     * Example: Preview USSD-PUSH or other ClickPesa API calls using the token.
     * Add your business logic here (e.g. POST /third-parties/... with Authorization: Bearer <token>).
     */
    public function request(string $method, string $endpoint, array $data = [], ?string $clientId = null, ?string $apiKey = null): array
    {
        $token = $this->getToken($clientId, $apiKey);
        if (!$token) {
            return ['success' => false, 'error' => 'Unable to obtain ClickPesa token.'];
        }

        try {
            $response = Http::withToken(preg_replace('/^Bearer\s+/i', '', $token))
                ->timeout(30)
                ->{strtolower($method)}(self::BASE_URL . $endpoint, $data);

            if ($response->status() === 401) {
                $this->clearCache();
            }

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
