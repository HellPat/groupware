<?php

namespace App\Stripe;

final readonly class ProductId extends PrefixedId
{
    protected static function prefix(): string
    {
        return 'prod_';
    }
}
