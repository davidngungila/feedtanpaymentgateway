<?php

namespace App\Payments\Providers;

use App\Services\MobileMoney\Mixx\MixxClient;
use App\Services\MobileMoney\Mixx\MixxConfig;
use App\Services\MobileMoney\Mixx\MixxErrorMapper;
use App\Services\MobileMoney\Mixx\MixxRequestBuilder;
use App\Services\MobileMoney\Mixx\MixxResponseParser;
use App\Services\MobileMoney\Mixx\MixxValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Mixx by Yas adapter, backed by the native Mixx stack
 * (XML SYNC_BILLPAY over HTTPS, DB-driven error mapping).
 *
 * Used by the generic payment engine. It only transports and
 * normalizes — financial records stay owned by the engine, so a
 * collection can never be recorded twice.
 */
class MixxByYasAdapter extends BaseProviderAdapter
{
    public function code(): string
    {
        return 'mixx';
    }

    public function name(): string
    {
        return 'Mixx by Yas';
    }

    public function normalizeStatus(?string $providerStatus): string
    {
        $upper = strtoupper((string) $providerStatus);
        if (in_array($upper, ['UNKNOWN', 'HOLD'], true)) {
            return $upper;
        }

        return parent::normalizeStatus($providerStatus);
    }

    public function initiate(array $input): array
    {
        $config = MixxConfig::make();

        try {
            $msisdn = MixxValidator::msisdn($input['phone'] ?? '');
            $amount = MixxValidator::amount($input['amount'] ?? 0, $config);
        } catch (InvalidArgumentException $e) {
            return $this->result(false, 'FAILED', null, null, $e->getMessage(), 422);
        }

        if (! $config->isConfigured()) {
            return $this->result(false, 'FAILED', null, null, 'Mixx integration is not configured yet (base URL + initiate path).', 503);
        }

        $ref = $input['order_reference'];
        Log::info('Payment initiated', [
            'provider' => 'mixx', 'reference' => $ref, 'amount' => $amount,
            'phone' => \App\Support\Security\Crypto::maskPhone($msisdn), 'status' => 'PENDING',
        ]);
        $fields = MixxRequestBuilder::billpay(
            $ref, $msisdn, $amount,
            $config->companyName(), $ref, $input['customer_name'] ?? 'Mwanachama'
        );

        $send = (new MixxClient($config))->postXml(
            $config->initiatePath(), MixxRequestBuilder::toXml($fields), 'w2a.initiate', $ref, 1
        );

        if (! $send['transport_ok']) {
            // Timeout / no response: leave PENDING for the verifier (error111-style).
            return $this->result(true, 'UNKNOWN', null, ['transport_error' => $send['error']], $send['error'].' Recorded as pending — verification will resolve it.', 200);
        }

        $parsed = MixxResponseParser::billpay($send['body']);
        $mapped = MixxErrorMapper::map('mixx', $parsed['errorcode'] ?? $parsed['result'] ?? '');
        $status = $mapped['internal_status'];
        $providerRef = $parsed['refid'] ?? $parsed['txnid'];

        if ($status === 'FAILED') {
            return $this->result(false, 'FAILED', $providerRef, $parsed['raw'], $parsed['errordescription'] ?? $mapped['description'], 422);
        }

        return $this->result(true, $status, $providerRef, $parsed['raw'], $status === 'SUCCESS' ? null : $mapped['description'], 200);
    }

    public function query(string $providerReference): array
    {
        $config = MixxConfig::make();
        $path = $config->statusPath();
        if (! $config->baseUrl() || ! $path) {
            return $this->result(false, 'PENDING', $providerReference, null, 'No Mixx status-query path configured.', 503);
        }

        $send = (new MixxClient($config))->postXml(
            str_replace('{reference}', urlencode($providerReference), $path),
            MixxRequestBuilder::toXml(['TYPE' => 'STATUS_QUERY_REQUEST', 'TXNID' => $providerReference]),
            'w2a.query', $providerReference, 1
        );

        if (! $send['transport_ok']) {
            return $this->result(false, 'PENDING', $providerReference, null, $send['error'], 0);
        }

        $parsed = MixxResponseParser::billpay($send['body']);
        $mapped = MixxErrorMapper::map('mixx', $parsed['errorcode'] ?? $parsed['result'] ?? '');

        return $this->result(
            true, $mapped['internal_status'],
            $parsed['refid'] ?? $parsed['txnid'] ?? $providerReference,
            $parsed['raw'], null, $send['http_status']
        );
    }

    /**
     * @return array{success:bool,status:string,provider_reference:?string,raw:mixed,error:?string,http_status:int}
     */
    protected function result(bool $success, string $status, ?string $providerRef, mixed $raw, ?string $error, int $http): array
    {
        // Base adapter logs the initiation line itself; keep it quiet here.
        return [
            'success' => $success,
            'status' => $status,
            'provider_reference' => $providerRef,
            'raw' => $raw,
            'error' => $error,
            'http_status' => $http,
        ];
    }

    // Webhook verification/extraction inherit the HMAC logic from BaseProviderAdapter.
    public function verifyWebhookSignature(Request $request): array
    {
        return parent::verifyWebhookSignature($request);
    }

    public function extractWebhookEvent(Request $request): array
    {
        return parent::extractWebhookEvent($request);
    }
}
