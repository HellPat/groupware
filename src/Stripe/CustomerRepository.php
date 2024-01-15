<?php

namespace App\Stripe;

use Doctrine\ORM\EntityManagerInterface;

final readonly class CustomerRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }
    
    public function createOrUpdate(Customer $customer): void
    {
        $this->em->getConnection()->executeStatement('
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
        $this->em->getConnection()->executeStatement(
            'DELETE FROM customer WHERE id = :id',
            ['id' => $id->__toString()]
        );
    }
}