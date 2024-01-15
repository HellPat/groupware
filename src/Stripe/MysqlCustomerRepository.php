<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;

final readonly class MysqlCustomerRepository
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function createOrUpdate(Customer $customer): void
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

    public function remove(CustomerId $id): void
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
                id: new CustomerId($row['id']),
                email: $row['email'],
                name: $row['name'],
                description: $row['description'],
                createdAt: new \DateTimeImmutable($row['created_at']),
            );
        }

        return $customers;
    }
}
