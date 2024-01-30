<?php

namespace App\Stripe;

final readonly class CustomerId extends PrefixedId
{
    #[\Override]
    protected static function prefix(): string
    {
        return 'cus_';
    }
}
