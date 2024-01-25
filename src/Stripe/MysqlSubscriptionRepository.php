<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;
use Stripe\Event;
use Webmozart\Assert\Assert;

final readonly class MysqlSubscriptionRepository implements StripeEventAware
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    private function createOrUpdate(Subscription $subscription): void
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

    private function remove(SubscriptionId $id): void
    {
        $this->connection->executeStatement(
            'DELETE FROM subscription WHERE id = :id',
            ['id' => $id->__toString()]
        );
    }

    private static function extract(Event $event): \Stripe\Subscription
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Subscription::class);

        return $obj;
    }

    public function handleStripeEvent(Event $event): void
    {
        match ($event->type) {
            Event::CUSTOMER_SUBSCRIPTION_CREATED, Event::CUSTOMER_SUBSCRIPTION_UPDATED => $this->createOrUpdateSubscription(self::extract($event)),
            Event::CUSTOMER_SUBSCRIPTION_DELETED => $this->removeSubscription(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdateSubscription(\Stripe\Subscription $subscription): void
    {
        $this->createOrUpdate(new Subscription(
            new SubscriptionId($subscription->id),
            new CustomerId((string) $subscription->customer),
            new \DateTimeImmutable('@' . $subscription->created),
            new \DateTimeImmutable('@' . $subscription->start_date),
            $subscription->cancel_at ? new \DateTimeImmutable('@' . $subscription->cancel_at) : null,
            $subscription->cancel_at_period_end,
            $subscription->canceled_at ? new \DateTimeImmutable('@' . $subscription->canceled_at) : null,
            (string) $subscription->description,
        ));
    }

    private function removeSubscription(\Stripe\Subscription $subscription): void
    {
        $this->remove(new SubscriptionId($subscription->id));
    }
}
