<?php

namespace App\Stripe;

use Doctrine\DBAL\Connection;

final readonly class MysqlProductRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function createOrUpdate(Product $product): void
    {
        $this->connection->executeStatement(
            '
            INSERT INTO product
                (id, name, default_price_id, active, type, description, created_at)
            VALUES (:id, :name, :default_price_id, :active, :type, :description, :created_at)
            ON DUPLICATE KEY UPDATE
                name = :name,
                default_price_id = :default_price_id,
                active = :active,
                type = :type,
                description = :description
            ',
            [
                'id' => $product->id->__toString(),
                'name' => $product->name,
                'default_price_id' => $product->price?->__toString(),
                'active' => $product->active ? 1 : 0,
                'type' => $product->type,
                'description' => $product->description,
                'created_at' => $product->createdAt->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function remove(ProductId $id): void
    {
        $this->connection->executeStatement(
            'DELETE FROM product WHERE id = :id',
            ['id' => $id->__toString()]
        );
    }

    /**
     * @return list<Product>
     */
    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, name, default_price_id, active, type, description, created_at FROM product'
        );

        return array_map(
            fn (array $row) => new Product(
                id: new ProductId((string) $row['id']),
                name: (string) $row['name'],
                price: $row['default_price_id'] ? new PriceId((string) $row['default_price_id']) : null,
                active: (bool) $row['active'],
                createdAt: new \DateTimeImmutable((string) $row['created_at']),
                type: (string) $row['type'],
                description: (string) $row['description'],
            ),
            $rows
        );
    }
}
