<?php

namespace App\Stripe;

final readonly class PriceId extends PrefixedId
{
    #[\Override]
    protected static function prefix(): string
    {
        return 'price_';
    }
}
