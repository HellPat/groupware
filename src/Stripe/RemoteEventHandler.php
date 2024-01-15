<?php

namespace App\Stripe;

use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class RemoteEventHandler
{
    public function __construct(
        private CustomerRepository $customers
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
            Event::CUSTOMER_CREATED => $this->createCustomer($action),
            Event::CUSTOMER_DELETED => $this->deleteCustomer($action),
            Event::CUSTOMER_UPDATED => $this->updateCustomer($action),
            default => null,
        };
    }

    public function updateCustomer(Event $action): void
    {
        $this->deleteCustomer($action);
        $this->createCustomer($action);
    }

    private function createCustomer(Event $action): void
    {
        $this->customers->createOrUpdate(new Customer(
            new CustomerId($action->data->object->id),
            (string) $action->data->object->email,
            (string) $action->data->object->name,
            (string) $action->data->object->description,
            new \DateTimeImmutable('@' . $action->data->object->created)
        ));
    }

    private function deleteCustomer(Event $action): void
    {
        $this->customers->remove(new CustomerId($action->data->object->id));
    }
}
