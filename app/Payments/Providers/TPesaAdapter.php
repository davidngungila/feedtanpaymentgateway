<?php

namespace App\Payments\Providers;

class TPesaAdapter extends BaseProviderAdapter
{
    public function code(): string
    {
        return 'tpesa';
    }

    public function name(): string
    {
        return 'T-Pesa';
    }
}
