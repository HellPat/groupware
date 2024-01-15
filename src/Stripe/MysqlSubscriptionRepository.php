<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;

final readonly class MysqlSubscriptionRepository
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function createOrUpdate(Subscription $subscription): void
    {
        $this->connection->executeStatement(
            '
            INSERT INTO subscription
                (id, customer_id, created_at, starts_at, canceled_at, cancel_at_period_end, description)
            VALUES (:id, :customer_id, :created_at, :starts_at, :canceled_at, :cancel_at_period_end, :description)
            ON DUPLICATE KEY UPDATE
                 customer_id = :customer_id,
                 created_at = :created_at,
                 starts_at = :starts_at,
                 cancel_at_period_end = :cancel_at_period_end,
                 canceled_at = :canceled_at,
                 description = :description
            ',
            [
                'id' => $subscription->id->__toString(),
                'customer_id' => $subscription->customerId->__toString(),
                'created_at' => $subscription->createdAt->format('Y-m-d H:i:s'),
                'starts_at' => $subscription->startsAt->format('Y-m-d H:i:s'),
                'cancel_at_period_end' => $subscription->cancelAtPeriodEnd ? 1 : 0,
                'canceled_at' => $subscription->canceledAt?->format('Y-m-d H:i:s'),
                'description' => $subscription->description,
            ]
        );
    }

    public function remove(SubscriptionId $id): void
    {
        $this->connection->executeStatement(
            'DELETE FROM subscription WHERE id = :id',
            ['id' => $id->__toString()]
        );
    }
}
