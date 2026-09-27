<?php

namespace App\Payments;

use App\Payments\Providers\AirtelMoneyAdapter;
use App\Payments\Providers\HaloPesaAdapter;
use App\Payments\Providers\MixxByYasAdapter;
use App\Payments\Providers\MpesaAdapter;
use App\Payments\Providers\ProviderAdapterInterface;
use App\Payments\Providers\TPesaAdapter;
use InvalidArgumentException;

/**
 * Single place that knows the five providers and resolves
 * the right adapter — by explicit code or by TZ phone prefix.
 */
class ProviderRegistry
{
    /**
     * @var array<string, class-string<ProviderAdapterInterface>>
     */
    protected static array $adapters = [
        'mpesa' => MpesaAdapter::class,
        'airtel' => AirtelMoneyAdapter::class,
        'mixx' => MixxByYasAdapter::class,
        'halopesa' => HaloPesaAdapter::class,
        'tpesa' => TPesaAdapter::class,
    ];

    /**
     * @return string[]
     */
    public static function codes(): array
    {
        return array_keys(self::$adapters);
    }

    public static function get(string $code): ProviderAdapterInterface
    {
        $code = strtolower(trim($code));

        if (! isset(self::$adapters[$code])) {
            throw new InvalidArgumentException("Unknown provider '{$code}'. Expected one of: ".implode(', ', self::codes()));
        }

        return app(self::$adapters[$code]);
    }

    /**
     * Detect the provider from a Tanzanian mobile number.
     * Returns null when no provider prefix matches.
     */
    public static function detect(string $phone): ?string
    {
        foreach (self::codes() as $code) {
            if (self::get($code)->supportsPhone($phone)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Provider metadata for selects, cards and reports.
     *
     * @return array<string, array{name:string,color:string,code:string,prefixes:array,fee:array}>
     */
    public static function meta(): array
    {
        $out = [];
        foreach (self::codes() as $code) {
            $adapter = self::get($code);
            $out[$code] = [
                'code' => $code,
                'name' => $adapter->name(),
                'color' => $adapter->color(),
                'prefixes' => (array) config("mobile_money.providers.{$code}.prefixes", []),
                'fee' => config("mobile_money.providers.{$code}.fee", ['percent' => 0, 'flat' => 0]),
            ];
        }

        return $out;
    }

    public static function name(string $code): string
    {
        return self::meta()[$code]['name'] ?? $code;
    }
}
