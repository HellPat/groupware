<?php

namespace App\Stripe;

use Webmozart\Assert\Assert;

final readonly class Customer
{
    public function __construct(
        public CustomerId $id,
        public string $email,
        public string $name,
        public string $description,
        public \DateTimeImmutable $createdAt,
    ) {
        Assert::string($this->email);
        Assert::string($this->name);
        Assert::string($this->description);
    }
}
