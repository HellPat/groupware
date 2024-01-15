<?php

namespace App\Stripe;

final readonly class SubscriptionId extends PrefixedId
{
    protected static function prefix(): string
    {
        return 'sub_';
    }
}
