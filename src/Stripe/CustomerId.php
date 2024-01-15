<?php

namespace App\Stripe;

final readonly class CustomerId extends PrefixedId
{
    protected static function prefix(): string
    {
        return 'cus_';
    }
}
