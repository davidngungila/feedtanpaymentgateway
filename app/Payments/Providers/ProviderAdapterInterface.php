<?php

namespace App\Payments\Providers;

use Illuminate\Http\Request;

/**
 * Contract every mobile-money provider adapter must honour.
 *
 * Adapters translate provider-specific APIs into the single internal
 * transaction structure used by the payment engine, so M-Pesa, Airtel
 * Money, Mixx by Yas, HaloPesa and T-Pesa all flow through the same
 * collections, reconciliation and settlement pipeline.
 */
interface ProviderAdapterInterface
{
    public function code(): string;

    public function name(): string;

    public function color(): string;

    public function supportsPhone(string $digits): bool;

    /**
     * Send a collection request (USSD push) to the customer's phone.
     *
     * @param  array{amount:float,currency:string,order_reference:string,phone:string,customer_name?:string,description?:string}  $input
     * @return array{success:bool,status:string,provider_reference:?string,raw:mixed,error:?string,http_status:int}
     */
    public function initiate(array $input): array;

    /**
     * Query the provider for the current status of a collection.
     *
     * @return array{success:bool,status:string,amount:?float,provider_reference:?string,raw:mixed,error:?string,http_status:int}
     */
    public function query(string $providerReference): array;

    /**
     * Verify an inbound webhook (signature, timestamp, replay).
     *
     * @return array{valid:bool,event_id:?string,error:?string,http_status:int}
     */
    public function verifyWebhookSignature(Request $request): array;

    /**
     * Extract the normalized event from a verified webhook payload.
     *
     * @return array{provider_transaction_id:?string,order_reference:?string,status:string,amount:?float,phone:?string,raw:mixed}
     */
    public function extractWebhookEvent(Request $request): array;

    /**
     * Map a provider status string to the internal canonical status:
     * PENDING | PROCESSING | SUCCESS | FAILED | REVERSED
     */
    public function normalizeStatus(?string $providerStatus): string;
}
