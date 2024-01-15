<?php

namespace App\Stripe;

final readonly class Subscription
{
    public function __construct(
        public SubscriptionId $id,
        public CustomerId $customerId,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $startsAt,
        public ?\DateTimeImmutable $cancelAt,
        public bool $cancelAtPeriodEnd,
        public ?\DateTimeImmutable $canceledAt,
        public string $description,
    ) {
    }
}
