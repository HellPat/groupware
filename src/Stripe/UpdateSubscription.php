<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Webmozart\Assert\Assert;

#[AutoconfigureTag('stripe.remote_event_handler')]
final readonly class UpdateSubscription
{
    public function __construct(
        private MysqlSubscriptionRepository $subscriptions,
    ) {
    }

    private static function extract(Event $event): \Stripe\Subscription
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Subscription::class);

        return $obj;
    }

    public function __invoke(Event $event): void
    {
        match ($event->type) {
            Event::CUSTOMER_SUBSCRIPTION_CREATED, Event::CUSTOMER_SUBSCRIPTION_UPDATED => $this->createOrUpdateSubscription(self::extract($event)),
            Event::CUSTOMER_SUBSCRIPTION_DELETED => $this->removeSubscription(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdateSubscription(\Stripe\Subscription $subscription): void
    {
        $this->subscriptions->createOrUpdate(new Subscription(
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
        $this->subscriptions->remove(new SubscriptionId($subscription->id));
    }
}
