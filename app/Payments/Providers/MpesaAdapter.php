<?php

namespace App\Payments\Providers;

class MpesaAdapter extends BaseProviderAdapter
{
    public function code(): string
    {
        return 'mpesa';
    }

    public function name(): string
    {
        return 'M-Pesa';
    }
}
