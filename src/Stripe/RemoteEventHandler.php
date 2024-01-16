<?php

namespace App\Stripe;

use Money\Currency;
use Money\Money;
use Stripe\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoteEventHandler
{
    public function __construct(
        private MysqlCustomerRepository $customers,
        private MysqlSubscriptionRepository $subscriptions,
        private MysqlProductRepository $products,
        private MysqlPriceRepository $prices,
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
            Event::CUSTOMER_CREATED, Event::CUSTOMER_UPDATED => $this->createOrUpdateCustomer($action->data->object),
            Event::CUSTOMER_DELETED => $this->removeCustomer($action->data->object),
            // subscription
            Event::CUSTOMER_SUBSCRIPTION_CREATED, Event::CUSTOMER_SUBSCRIPTION_UPDATED => $this->createOrUpdateSubscription($action->data->object),
            Event::CUSTOMER_SUBSCRIPTION_DELETED => $this->removeSubscription($action->data->object),
            // product
            Event::PRODUCT_CREATED, Event::PRODUCT_UPDATED => $this->createOrUpdateProduct($action->data->object),
            Event::PRODUCT_DELETED => $this->removeProduct($action->data->object),
            // price
            Event::PRICE_CREATED, Event::PRICE_UPDATED => $this->createOrUpdatePrice($action->data->object),
            Event::PRICE_DELETED => $this->removePrice($action->data->object),
            // unknown
            default => null, // throw new UnrecoverableMessageHandlingException('Unknown event type: ' . $action->type),
        };
    }

    public function createOrUpdateCustomer(\Stripe\Customer $customer): void
    {
        $this->customers->createOrUpdate(new Customer(
            new CustomerId($customer->id),
            (string) $customer->email,
            (string) $customer->name,
            (string) $customer->description,
            new \DateTimeImmutable('@' . $customer->created)
        ));
    }

    private function removeCustomer(\Stripe\Customer $customer): void
    {
        $this->customers->remove(new CustomerId($customer->id));
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

    private function createOrUpdateProduct(\Stripe\Product $product): void
    {
        $this->products->createOrUpdate(new Product(
            new ProductId($product->id),
            $product->name,
            $product->default_price ? new PriceId((string) $product->default_price) : null,
            $product->active,
            new \DateTimeImmutable('@' . $product->created),
            $product->type, // TODO: this should be an enum
            (string) $product->description,
        ));
    }

    private function removeProduct(\Stripe\Product $product): void
    {
        $this->products->remove(new ProductId($product->id));
    }

    private function removePrice(\Stripe\Price $price): void
    {
        $this->prices->remove(new PriceId($price->id));
    }

    private function createOrUpdatePrice(\Stripe\Price $object): void
    {
        // TODO: handle $object->recurring
        // TODO: why does not handle multiple currencies?
        $this->prices->createOrUpdate(new Price(
            new PriceId($object->id),
            new ProductId($object->product),
            $object->active,
            $object->billing_scheme, // TODO: this should be an enum
            new Money($object->unit_amount, new Currency($object->currency))
        ));
    }
}
