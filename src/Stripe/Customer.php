<?php

namespace App\Stripe;

final readonly class Customer
{
    public function __construct(
        public CustomerId $id,
        public string $email,
        public string $name,
        public string $description,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
