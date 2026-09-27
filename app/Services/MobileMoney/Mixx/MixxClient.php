<?php

namespace App\Services\MobileMoney\Mixx;

use App\Models\ProviderApiLog;
use App\Support\Security\Crypto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTPS XML transport for Mixx by Yas.
 *
 * Every call is timed and stored encrypted in provider_api_logs
 * (request/response, HTTP status, attempt). Credentials are scrubbed
 * before anything is persisted or logged.
 */
class MixxClient
{
    public function __construct(protected MixxConfig $config) {}

    /**
     * @return array{transport_ok:bool,http_status:int,headers:array,body:string,duration_ms:int,error:?string}
     */
    public function postXml(string $path, string $xml, string $operation, ?string $transactionId = null, int $attempt = 1, array $scrub = []): array
    {
        $base = $this->config->baseUrl();
        if (! $base) {
            return $this->fail($operation, $transactionId, $attempt, 'Mixx API base URL is not configured.');
        }

        $url = $base.'/'.ltrim($path, '/');
        $headers = $this->authHeaders();
        $started = (int) (microtime(true) * 1000);

        try {
            $response = Http::timeout($this->config->timeout())
                ->withHeaders($headers + ['Content-Type' => 'text/xml; charset=utf-8', 'Accept' => 'text/xml'])
                ->withBody($xml, 'text/xml')
                ->post($url);

            $duration = (int) (microtime(true) * 1000) - $started;
            $result = [
                'transport_ok' => true,
                'http_status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'duration_ms' => $duration,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            $duration = (int) (microtime(true) * 1000) - $started;
            Log::warning('Mixx transport exception', ['operation' => $operation, 'message' => $e->getMessage()]);
            $result = [
                'transport_ok' => false,
                'http_status' => 0,
                'headers' => [],
                'body' => '',
                'duration_ms' => $duration,
                'error' => 'Could not reach Mixx: '.$e->getMessage(),
            ];
        }

        $this->storeLog($operation, $transactionId, $attempt, $headers, $xml, $result, $scrub);

        return $result;
    }

    protected function fail(string $operation, ?string $transactionId, int $attempt, string $error): array
    {
        $this->storeLog($operation, $transactionId, $attempt, [], '', [
            'transport_ok' => false, 'http_status' => 503, 'headers' => [], 'body' => '', 'duration_ms' => 0, 'error' => $error,
        ], []);

        return ['transport_ok' => false, 'http_status' => 503, 'headers' => [], 'body' => '', 'duration_ms' => 0, 'error' => $error];
    }

    protected function storeLog(string $operation, ?string $transactionId, int $attempt, array $headers, string $xml, array $result, array $scrub): void
    {
        try {
            ProviderApiLog::create([
                'provider' => MixxConfig::PROVIDER,
                'transaction_id' => $transactionId,
                'operation' => $operation,
                'http_status' => $result['http_status'],
                'duration_ms' => $result['duration_ms'],
                'attempt' => $attempt,
                'success' => $result['transport_ok'] && (int) $result['http_status'] >= 200 && (int) $result['http_status'] < 300,
                'request_headers_encrypted' => $this->scrubHeaders($headers),
                'request_body_encrypted' => $this->scrubBody($xml, $scrub),
                'response_headers_encrypted' => is_array($result['headers']) ? $result['headers'] : [],
                'response_body_encrypted' => (string) ($result['body'] ?? ''),
                'error' => $result['error'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Mixx API log store failed', ['message' => $e->getMessage()]);
        }
    }

    protected function scrubHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower((string) $k);
            $out[$k] = in_array($lk, ['authorization', 'api-key', 'client-id', 'x-api-key'], true) ? '***REDACTED***' : $v;
        }

        return $out;
    }

    protected function scrubBody(string $xml, array $scrub): string
    {
        // Remove highly sensitive elements (e.g. PIN) before persistence.
        foreach (array_merge(['PIN'], $scrub) as $tag) {
            $xml = preg_replace('/<'.$tag.'>.*?<\/'.$tag.'>/is', '<'.$tag.'>***REDACTED***</'.$tag.'>', $xml);
        }

        return $xml;
    }

    protected function authHeaders(): array
    {
        $credential = $this->config->credential();
        $type = $this->config->authType();

        if (! $credential) {
            return [];
        }

        if ($type === 'bearer' && ! empty($credential->api_key)) {
            return ['Authorization' => 'Bearer '.$credential->api_key];
        }

        $headers = [];
        if (! empty($credential->api_key)) {
            $headers['api-key'] = $credential->api_key;
        }
        if (! empty($credential->client_id)) {
            $headers['client-id'] = $credential->client_id;
        }

        return $headers;
    }

    public static function maskMsisdn(?string $msisdn): string
    {
        return Crypto::maskPhone($msisdn);
    }
}
