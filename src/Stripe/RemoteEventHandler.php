<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoteEventHandler
{
    public function __construct(
        private MysqlCustomerRepository $customers,
        private MysqlSubscriptionRepository $subscriptions,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function __invoke(RemoteEvent $event): void
    {
        $action = Event::constructFrom(
            (array) json_decode($event->payload, true, 512, JSON_THROW_ON_ERROR)
        );

        match ($action->type) {
            // customer
            Event::CUSTOMER_CREATED, Event::CUSTOMER_UPDATED => $this->createOrUpdateCustomer($action),
            Event::CUSTOMER_DELETED => $this->removeCustomer($action),
            // subscription
            Event::CUSTOMER_SUBSCRIPTION_CREATED, Event::CUSTOMER_SUBSCRIPTION_UPDATED => $this->createOrUpdateSubscription($action),
            Event::CUSTOMER_SUBSCRIPTION_DELETED => $this->removeSubscription($action),
            default => null,
        };
    }

    public function createOrUpdateCustomer(Event $action): void
    {
        $this->customers->createOrUpdate(new Customer(
            new CustomerId($action->data->object->id),
            (string) $action->data->object->email,
            (string) $action->data->object->name,
            (string) $action->data->object->description,
            new \DateTimeImmutable('@' . $action->data->object->created)
        ));
    }

    private function removeCustomer(Event $action): void
    {
        $this->customers->remove(new CustomerId($action->data->object->id));
    }

    private function createOrUpdateSubscription(Event $action): void
    {
        $this->subscriptions->createOrUpdate(new Subscription(
            new SubscriptionId($action->data->object->id),
            new CustomerId($action->data->object->customer),
            new \DateTimeImmutable('@' . $action->data->object->created),
            new \DateTimeImmutable('@' . $action->data->object->start_date),
            $action->data->object->cancel_at ? new \DateTimeImmutable('@' . $action->data->object->cancel_at) : null,
            (bool) $action->data->object->cancel_at_period_end,
            $action->data->object->canceled_at ? new \DateTimeImmutable('@' . $action->data->object->canceled_at) : null,
            (string) $action->data->object->description,
        ));
    }

    private function removeSubscription(Event $action): void
    {
        $this->subscriptions->remove(new SubscriptionId($action->data->object->id));
    }
}
