<?php

namespace App\Services\MobileMoney\Mixx;

/**
 * Builds Mixx by Yas request documents.
 *
 * W2A collection (SYNC_BILLPAY_REQUEST):
 *   TYPE, TXNID, MSISDN, AMOUNT, COMPANYNAME, CUSTOMERREFERENCEID, SENDERNAME
 *
 * A2W disbursement:
 *   TYPE, REFERENCEID, MSISDN, PIN, MSISDN1, AMOUNT, SENDERNAME, BRAND_ID, LANGUAGE1
 *
 * Field names follow the integration document; the envelope/endpoint
 * details come from the current partner agreement (see MixxConfig).
 */
class MixxRequestBuilder
{
    public static function billpay(string $txnId, string $msisdn, float $amount, string $companyName, string $customerReferenceId, string $senderName): array
    {
        return [
            'TYPE' => 'SYNC_BILLPAY_REQUEST',
            'TXNID' => $txnId,
            'MSISDN' => $msisdn,
            'AMOUNT' => number_format($amount, 2, '.', ''),
            'COMPANYNAME' => $companyName,
            'CUSTOMERREFERENCEID' => $customerReferenceId,
            'SENDERNAME' => $senderName,
        ];
    }

    public static function disbursement(string $referenceId, string $msisdn, string $pin, string $amount, string $senderName, ?string $brandId = null, ?string $language = null): array
    {
        return [
            'TYPE' => 'DISBURSEMENT_REQUEST',
            'REFERENCEID' => $referenceId,
            'MSISDN' => $msisdn,
            'PIN' => $pin,
            'MSISDN1' => $msisdn,
            'AMOUNT' => number_format((float) $amount, 2, '.', ''),
            'SENDERNAME' => $senderName,
            'BRAND_ID' => $brandId ?? '',
            'LANGUAGE1' => $language ?? 'sw',
        ];
    }

    public static function toXml(array $fields, string $root = 'COMMAND'): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n<{$root}>\n";
        foreach ($fields as $tag => $value) {
            $xml .= '  <'.$tag.'>'.htmlspecialchars((string) $value, ENT_XML1, 'UTF-8').'</'.$tag.">\n";
        }

        return $xml.'</'.$root.'>';
    }
}
