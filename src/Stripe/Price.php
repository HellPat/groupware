<?php

namespace App\Stripe;

use Money\Money;

final readonly class Price
{
    public function __construct(
        public PriceId $id,
        public ProductId $productId,
        public bool $active,
        public string $billingScheme,
        public Money $unitAmount,
    ) {
    }
}
