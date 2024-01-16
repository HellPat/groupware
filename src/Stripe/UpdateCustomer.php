<?php

namespace App\Stripe;

use Stripe\Event;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Webmozart\Assert\Assert;

#[AutoconfigureTag('stripe.remote_event_handler')]
final readonly class UpdateCustomer
{
    public function __construct(
        private MysqlCustomerRepository $customers,
    ) {
    }

    private static function extract(Event $event): \Stripe\Customer
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Customer::class);

        return $obj;
    }

    public function __invoke(Event $event): void
    {
        match ($event->type) {
            Event::CUSTOMER_CREATED, Event::CUSTOMER_UPDATED => $this->createOrUpdateCustomer(self::extract($event)),
            Event::CUSTOMER_DELETED => $this->removeCustomer(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdateCustomer(\Stripe\Customer $customer): void
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
}
