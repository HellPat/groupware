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
        private EntityManagerInterface $em,
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
            default => throw new UnrecoverableMessageHandlingException('Unknown event type: ' . $action->type),
        };
    }

    public function updateCustomer(Event $action): void
    {
        $this->deleteCustomer($action);
        $this->createCustomer($action);
    }

    private function createCustomer(Event $action): void
    {
        $this->em->persist(
            new Customer(
                $action->data->object->id,
                $action->data->object->email,
                $action->data->object->name,
                $action->data->object->description ?? '',
                new \DateTimeImmutable('@' . $action->data->object->created),
            )
        );
    }

    private function deleteCustomer(Event $action): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM customer WHERE id = :id',
            ['id' => $action->data->object->id]
        );
    }
}
