<?php

namespace App\Stripe;

final readonly class Product
{
    public function __construct(
        public ProductId $id,
        public string $name,
        public ?PriceId $price,
        public bool $active,
        public \DateTimeImmutable $createdAt,
        public string $type,
        public string $description,
    ) {
    }
}
