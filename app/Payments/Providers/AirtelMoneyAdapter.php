<?php

namespace App\Payments\Providers;

class AirtelMoneyAdapter extends BaseProviderAdapter
{
    public function code(): string
    {
        return 'airtel';
    }

    public function name(): string
    {
        return 'Airtel Money';
    }
}
