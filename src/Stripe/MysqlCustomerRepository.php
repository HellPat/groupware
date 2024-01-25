<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;
use Stripe\Event;
use Webmozart\Assert\Assert;

final readonly class MysqlCustomerRepository implements StripeEventAware
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    private function createOrUpdate(Customer $customer): void
    {
        $this->connection->executeStatement(
            '
            INSERT INTO customer 
                (id, email, name, description, created_at)
            VALUES (:id, :email, :name, :description, :created_at) 
            ON DUPLICATE KEY UPDATE
                email = :email,
                name = :name,
                description = :description,
                created_at = :created_at
            ',
            [
                'id' => $customer->id,
                'email' => $customer->email,
                'name' => $customer->name,
                'description' => $customer->description,
                'created_at' => $customer->createdAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            ]
        );
    }

    private function remove(CustomerId $id): void
    {
        $this->connection->executeStatement(
            'DELETE FROM customer WHERE id = :id',
            ['id' => $id->__toString()]
        );
    }

    /**
     * @return list<Customer>
     */
    public function all(): array
    {
        $stmt = $this->connection->executeQuery(
            'SELECT id,email,name,description,created_at FROM customer'
        );

        $customers = [];
        while ($row = $stmt->fetchAssociative()) {
            $customers[] = new Customer(
                id: new CustomerId((string) $row['id']),
                email: (string) $row['email'],
                name: (string) $row['name'],
                description: (string) $row['description'],
                createdAt: new \DateTimeImmutable((string) $row['created_at']),
            );
        }

        return $customers;
    }

    private static function extract(Event $event): \Stripe\Customer
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $obj = $event->data->object;
        Assert::isInstanceOf($obj, \Stripe\Customer::class);

        return $obj;
    }

    public function handleStripeEvent(Event $event): void
    {
        match ($event->type) {
            Event::CUSTOMER_CREATED, Event::CUSTOMER_UPDATED => $this->createOrUpdateCustomer(self::extract($event)),
            Event::CUSTOMER_DELETED => $this->removeCustomer(self::extract($event)),
            default => null,
        };
    }

    private function createOrUpdateCustomer(\Stripe\Customer $customer): void
    {
        $this->createOrUpdate(new Customer(
            new CustomerId($customer->id),
            (string) $customer->email,
            (string) $customer->name,
            (string) $customer->description,
            new \DateTimeImmutable('@' . $customer->created)
        ));
    }

    private function removeCustomer(\Stripe\Customer $customer): void
    {
        $this->remove(new CustomerId($customer->id));
    }
}
