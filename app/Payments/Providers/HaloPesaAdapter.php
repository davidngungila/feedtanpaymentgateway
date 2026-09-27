<?php

namespace App\Payments\Providers;

class HaloPesaAdapter extends BaseProviderAdapter
{
    public function code(): string
    {
        return 'halopesa';
    }

    public function name(): string
    {
        return 'HaloPesa';
    }
}
