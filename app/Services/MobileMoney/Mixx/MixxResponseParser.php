<?php

namespace App\Services\MobileMoney\Mixx;

/**
 * Parses Mixx by Yas XML responses into plain arrays.
 *
 * W2A (SYNC_BILLPAY_RESPONSE):
 *   TXNID, REFID, RESULT, ERRORCODE, ERRORDESCRIPTION, MSISDN, FLAG, CONTENT
 *
 * A2W: TYPE, REFERENCEID, TXNID, TXNSTATUS, MESSAGE
 */
class MixxResponseParser
{
    public static function billpay(string $xml): array
    {
        $data = self::toArray($xml);

        return [
            'txnid' => self::get($data, ['TXNID']),
            'refid' => self::get($data, ['REFID']),
            'result' => self::get($data, ['RESULT']),
            'errorcode' => self::get($data, ['ERRORCODE']),
            'errordescription' => self::get($data, ['ERRORDESCRIPTION']),
            'msisdn' => self::get($data, ['MSISDN']),
            'flag' => self::get($data, ['FLAG']),
            'content' => self::get($data, ['CONTENT']),
            'amount' => self::get($data, ['AMOUNT', 'COLLECTEDAMOUNT']),
            'sendername' => self::get($data, ['SENDERNAME']),
            'raw' => $data,
        ];
    }

    public static function disbursement(string $xml): array
    {
        $data = self::toArray($xml);

        return [
            'type' => self::get($data, ['TYPE']),
            'referenceid' => self::get($data, ['REFERENCEID']),
            'txnid' => self::get($data, ['TXNID']),
            'txnstatus' => self::get($data, ['TXNSTATUS']),
            'message' => self::get($data, ['MESSAGE']),
            'raw' => $data,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function toArray(string $xml): array
    {
        $xml = trim($xml);
        if ($xml === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $node = simplexml_load_string($xml);
        if ($node === false) {
            return [];
        }

        $out = [];
        $walk = function ($n) use (&$walk, &$out) {
            foreach ($n->children() as $child) {
                if (count($child->children()) > 0) {
                    $walk($child);
                } else {
                    $out[strtoupper($child->getName())] = trim((string) $child);
                }
            }
        };
        $walk($node);

        return $out;
    }

    protected static function get(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return (string) $data[$key];
            }
        }

        return null;
    }
}
